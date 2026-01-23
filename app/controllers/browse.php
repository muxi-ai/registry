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
        // Allow visitors to sort by different metrics: trending, recent, popular, or name.
        $sort = tiny::router()->query['sort'] ?? 'recent';
        $validSorts = ['trending', 'recent', 'downloads', 'name'];

        // Validate sort parameter to prevent SQL injection and ensure sensible defaults.
        if (!in_array($sort, $validSorts)) {
            $sort = 'recent';
        }

        // Fetch formations based on selected sort method (exclude soft-deleted)
        if ($sort === 'trending') {
            // Trending: formations with most downloads in last 7 days
            $formations = tiny::db()->getQuery("
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
                WHERE f.deleted_at IS NULL
                GROUP BY f.id
                ORDER BY downloads_7d DESC, f.github_stars DESC
            ");
        } else {
            // Map the sort parameter to the appropriate SQL ORDER BY clause.
            $orderBy = match ($sort) {
                'recent' => 'f.published_at DESC',
                'downloads' => 'f.total_downloads DESC',
                'name' => 'f.name ASC',
                default => 'f.published_at DESC'
            };

            // Fetch all formations with the selected ordering. No pagination for MVP simplicity.
            $formations = tiny::db()->getQuery("
                SELECT f.*, u.registry_username, u.github_username, u.github_type
                FROM formations f
                JOIN users u ON f.user_id = u.id
                WHERE f.deleted_at IS NULL
                ORDER BY {$orderBy}
            ");
        }

        // Count total formations for display metrics (exclude soft-deleted).
        $totalCount = tiny::db()->getOneQuery("
            SELECT COUNT(*) as count FROM formations WHERE deleted_at IS NULL
        ")['count'];

        // Pass formation data and active sort state so the view can render controls.
        $response->render('browse', [
            'formations' => $formations,
            'sort' => $sort,
            'totalCount' => $totalCount,
        ]);
    }
}
