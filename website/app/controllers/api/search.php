<?php

declare(strict_types=1);

/**
 * API Controller for search endpoints
 *
 * Handles:
 * - GET /api/search?q=query&sort=trending&limit=20 - Search formations
 */
class ApiSearch extends TinyController
{
    /**
     * GET /api/search
     *
     * Search formations with full-text search
     */
    public function get($request, $response)
    {
        $query = $request->query['q'] ?? null;

        if (!$query || trim($query) === '') {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Missing search query parameter: q',
                'id' => 'API-06'
            ], 400);
        }

        $sort = $request->query['sort'] ?? 'trending';
        $limit = min(max((int)($request->query['limit'] ?? 20), 1), 100);

        // Validate sort parameter
        $validSorts = ['trending', 'downloads', 'recent', 'stars'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'trending';
        }

        try {
            $results = $this->searchFormations($query, $sort, $limit);

            return $response->sendJSON([
                'query' => $query,
                'results' => $results,
                'total' => count($results),
                'limit' => $limit,
                'sort' => $sort
            ]);
        } catch (Exception $e) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Search failed',
                'id' => 'API-12'
            ], 500);
        }
    }

    /**
     * Search formations using the shared search model
     */
    private function searchFormations($query, $sort, $limit)
    {
        // Use the search model for all search logic
        $formations = tiny::model('search')->searchFormations($query, $sort, $limit);

        // Format results for API response
        return array_map(function($row) {
            return [
                'name' => $row['name'],
                'user' => $row['registry_username'],
                'description' => $row['description'] ?? '',
                'version' => $row['latest_version'] ?? '',
                'downloads' => (int)($row['total_downloads'] ?? 0),
                'stars' => (int)($row['github_stars'] ?? 0),
                'github_repo' => $row['github_repo'] ?? '',
                'url' => "/@{$row['registry_username']}/{$row['name']}",
                'api_url' => "/api/formations/@{$row['registry_username']}/{$row['name']}",
                'install_command' => "muxi pull @{$row['registry_username']}/{$row['name']}"
            ];
        }, $formations);
    }
}
