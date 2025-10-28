<?php

/**
 * Search controller provides full-text search across formations.
 */
class Search extends TinyController
{
    /**
     * Execute a user query and render matching formations.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Extract the search query from the URL parameter.
        $query = tiny::router()->query['q'] ?? '';

        $formations = [];
        $resultCount = 0;
        $correctedQuery = null; // Track if we used fuzzy search
        $originalQuery = $query;

        // Only execute search if the user provided a non-empty query.
        if (!empty($query)) {
            // Strategy 1: Try exact FTS5 match first (fast, precise).
            // FTS5 was set up in the schema with triggers to keep the index in sync.
            $formations = tiny::db()->getQuery("
                SELECT f.*, u.registry_username, u.github_username, u.github_type
                FROM formations f
                JOIN users u ON f.user_id = u.id
                WHERE f.id IN (
                    SELECT rowid FROM formations_fts
                    WHERE formations_fts MATCH ?
                )
                ORDER BY f.total_downloads DESC
            ", [$query]);

            // Strategy 2: If no exact match, try prefix matching for partial words.
            // Example: "supp" would match "support", "supplier", etc.
            if (empty($formations) && strlen($query) >= 3) {
                $prefixQuery = $query . '*';
                $formations = tiny::db()->getQuery("
                    SELECT f.*, u.registry_username, u.github_username, u.github_type
                    FROM formations f
                    JOIN users u ON f.user_id = u.id
                    WHERE f.id IN (
                        SELECT rowid FROM formations_fts
                        WHERE formations_fts MATCH ?
                    )
                    ORDER BY f.total_downloads DESC
                ", [$prefixQuery]);
            }

            // Strategy 3: If still no match, try fuzzy search with LIKE patterns.
            // This catches partial substring matches and similar words.
            if (empty($formations)) {
                $likeQuery = '%' . $query . '%';
                $formations = tiny::db()->getQuery("
                    SELECT DISTINCT f.*, u.registry_username, u.github_username, u.github_type,
                           -- Rank results: exact name match scores highest
                           CASE 
                               WHEN f.name LIKE ? THEN 3
                               WHEN f.description LIKE ? THEN 2
                               ELSE 1
                           END as relevance
                    FROM formations f
                    JOIN users u ON f.user_id = u.id
                    WHERE f.name LIKE ? 
                       OR f.description LIKE ?
                       OR f.readme_md LIKE ?
                    ORDER BY relevance DESC, f.total_downloads DESC
                ", [$query, $likeQuery, $likeQuery, $likeQuery, $likeQuery]);
            }

            // Strategy 4: Last resort - Levenshtein distance for typo correction.
            // Example: "supprot" → "support" (distance: 2)
            if (empty($formations) && strlen($query) >= 4) {
                // Get all formation names and find closest matches
                $allFormations = tiny::db()->getQuery("
                    SELECT f.*, u.registry_username, u.github_username, u.github_type
                    FROM formations f
                    JOIN users u ON f.user_id = u.id
                ");

                // Calculate Levenshtein distance for each and keep close matches
                $matches = [];
                $threshold = 3; // Max edit distance to consider a match
                $bestMatch = null;
                $bestDistance = PHP_INT_MAX;

                foreach ($allFormations as $formation) {
                    // Check distance against name and description words
                    $nameWords = explode('-', $formation['name']);
                    $descWords = explode(' ', strtolower($formation['description'] ?? ''));

                    foreach (array_merge($nameWords, $descWords) as $word) {
                        $distance = levenshtein(strtolower($query), strtolower($word));
                        if ($distance <= $threshold && $distance > 0) {
                            $formation['distance'] = $distance;
                            $matches[] = $formation;
                            
                            // Track the closest match for the "did you mean" message
                            if ($distance < $bestDistance) {
                                $bestDistance = $distance;
                                $bestMatch = $word;
                            }
                            
                            break; // Only count this formation once
                        }
                    }
                }

                // Sort by distance (closest first), then by downloads
                usort($matches, function($a, $b) {
                    if ($a['distance'] === $b['distance']) {
                        return $b['total_downloads'] <=> $a['total_downloads'];
                    }
                    return $a['distance'] <=> $b['distance'];
                });

                $formations = $matches;
                
                // If we found results via fuzzy search, set the corrected query
                if (!empty($formations) && $bestMatch) {
                    $correctedQuery = $bestMatch;
                }
            }

            $resultCount = count($formations);
        }

        // Pass results and the original query back to the view for rendering.
        $response->render('search', [
            'query' => $query,
            'originalQuery' => $originalQuery,
            'correctedQuery' => $correctedQuery,
            'formations' => $formations,
            'resultCount' => $resultCount,
        ]);
    }
}
