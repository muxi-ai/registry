<?php

/**
 * Browse controller provides a filterable, sortable list of all formations.
 */
class Browse extends TinyController
{
    /**
     * Render the browseable formations catalog with user-selected sorting.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Allow visitors to sort by different metrics: recent, popular, stars, or name.
        $sort = tiny::router()->query['sort'] ?? 'recent';
        $validSorts = ['recent', 'downloads', 'stars', 'name'];

        // Validate sort parameter to prevent SQL injection and ensure sensible defaults.
        if (!in_array($sort, $validSorts)) {
            $sort = 'recent';
        }

        // Map the sort parameter to the appropriate SQL ORDER BY clause.
        $orderBy = match ($sort) {
            'recent' => 'f.published_at DESC',
            'downloads' => 'f.total_downloads DESC',
            'stars' => 'f.github_stars DESC',
            'name' => 'f.name ASC',
            default => 'f.published_at DESC'
        };

        // Fetch all formations with the selected ordering. No pagination for MVP simplicity.
        $formations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            ORDER BY {$orderBy}
        ");

        // Count total formations for display metrics.
        $totalCount = tiny::db()->getOneQuery("
            SELECT COUNT(*) as count FROM formations
        ")['count'];

        // Pass formation data and active sort state so the view can render controls.
        $response->render('browse', [
            'formations' => $formations,
            'sort' => $sort,
            'totalCount' => $totalCount,
        ]);
    }
}
