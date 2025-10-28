<?php

declare(strict_types=1);

/**
 * Search Model
 *
 * Handles formation search across the registry using multi-strategy approach:
 * 1. FTS5 exact match (fast, indexed)
 * 2. FTS5 prefix match (partial words)
 * 3. LIKE pattern fallback (fuzzy search)
 * 4. Levenshtein distance (typo correction)
 */
class Search extends TinyModel
{
    /**
     * Search formations using multi-strategy approach
     *
     * @param string $query Search query string
     * @param string $sort Sort order: trending|downloads|recent|stars
     * @param int $limit Maximum number of results (1-100)
     * @return array Array of formation objects
     */
    public function searchFormations(string $query, string $sort = 'trending', int $limit = 20): array
    {
        if (empty($query)) {
            return [];
        }

        $formations = [];

        // Strategy 1: Try exact FTS5 match first (fast, precise)
        $formations = $this->searchFTS5Exact($query);

        // Strategy 2: If no exact match, try prefix matching for partial words
        if (empty($formations) && strlen($query) >= 3) {
            $formations = $this->searchFTS5Prefix($query);
        }

        // Strategy 3: If still no match, try fuzzy search with LIKE patterns
        if (empty($formations)) {
            $formations = $this->searchLikePattern($query);
        }

        // Strategy 4: Last resort - Levenshtein distance for typo correction
        // Only used by web UI for "did you mean" suggestions, not returned here

        // Apply sort order
        $formations = $this->sortResults($formations, $sort);

        // Apply limit
        $formations = array_slice($formations, 0, $limit);

        return $formations;
    }

    /**
     * Search using FTS5 exact match
     *
     * @param string $query Search query
     * @return array Array of formations
     */
    private function searchFTS5Exact(string $query): array
    {
        return tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.id IN (
                SELECT rowid FROM formations_fts
                WHERE formations_fts MATCH ?
            )
            ORDER BY f.total_downloads DESC
        ", [$query]);
    }

    /**
     * Search using FTS5 prefix match
     *
     * @param string $query Search query
     * @return array Array of formations
     */
    private function searchFTS5Prefix(string $query): array
    {
        $prefixQuery = $query . '*';
        return tiny::db()->getQuery("
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

    /**
     * Search using LIKE pattern matching (fallback)
     *
     * @param string $query Search query
     * @return array Array of formations
     */
    private function searchLikePattern(string $query): array
    {
        $likeQuery = '%' . $query . '%';
        return tiny::db()->getQuery("
            SELECT DISTINCT f.*, u.registry_username, u.github_username, u.github_type,
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

    /**
     * Search with Levenshtein distance for typo correction
     * Returns both results and suggested correction
     *
     * @param string $query Search query
     * @param int $threshold Maximum edit distance (default: 3)
     * @return array ['formations' => array, 'correction' => string|null]
     */
    public function searchWithTypoCorrection(string $query, int $threshold = 3): array
    {
        // Get all formations for comparison
        $allFormations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
        ");

        $matches = [];
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

                    // Track the closest match for "did you mean" message
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

        return [
            'formations' => $matches,
            'correction' => $bestMatch
        ];
    }

    /**
     * Sort search results based on specified criteria
     *
     * @param array $formations Array of formations to sort
     * @param string $sort Sort order: trending|downloads|recent|stars
     * @return array Sorted array of formations
     */
    private function sortResults(array $formations, string $sort): array
    {
        if (empty($formations)) {
            return $formations;
        }

        usort($formations, function($a, $b) use ($sort) {
            return match($sort) {
                'downloads' => ($b['total_downloads'] ?? 0) <=> ($a['total_downloads'] ?? 0),
                'stars' => ($b['github_stars'] ?? 0) <=> ($a['github_stars'] ?? 0),
                'recent' => ($b['published_at'] ?? '') <=> ($a['published_at'] ?? ''),
                'trending' => $this->calculateTrendingScore($b) <=> $this->calculateTrendingScore($a),
                default => ($b['total_downloads'] ?? 0) <=> ($a['total_downloads'] ?? 0)
            };
        });

        return $formations;
    }

    /**
     * Calculate trending score based on recent downloads and velocity
     *
     * @param array $formation Formation data
     * @return float Trending score
     */
    private function calculateTrendingScore(array $formation): float
    {
        // TODO: Implement proper trending algorithm using downloads table
        // For now, use total downloads as proxy
        return (float)($formation['total_downloads'] ?? 0);
    }

    /**
     * Get trending formations (last 7 days)
     *
     * @param int $limit Maximum number of results
     * @return array Array of formations
     */
    public function getTrending(int $limit = 10): array
    {
        return tiny::db()->getQuery("
            SELECT
                f.*,
                u.registry_username,
                u.github_username,
                u.github_type,
                COALESCE(SUM(d.download_count), 0) as downloads_7d
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN downloads d
                ON f.id = d.formation_id
                AND d.day >= DATE('now', '-7 days')
            WHERE f.is_public = 1
            GROUP BY f.id
            ORDER BY downloads_7d DESC, f.github_stars DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get most downloaded formations (all time)
     *
     * @param int $limit Maximum number of results
     * @return array Array of formations
     */
    public function getPopular(int $limit = 10): array
    {
        return tiny::db()->getQuery("
            SELECT
                f.*,
                u.registry_username,
                u.github_username,
                u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.is_public = 1
            ORDER BY f.total_downloads DESC, f.github_stars DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get recently published formations
     *
     * @param int $limit Maximum number of results
     * @return array Array of formations
     */
    public function getRecent(int $limit = 10): array
    {
        return tiny::db()->getQuery("
            SELECT
                f.*,
                u.registry_username,
                u.github_username,
                u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.is_public = 1
            ORDER BY f.published_at DESC
            LIMIT ?
        ", [$limit]);
    }
}
