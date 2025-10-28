<?php

/**
 * Account controller shows the authenticated user's registry dashboard.
 */
class Account extends TinyController
{
    /**
     * Render profile metrics and formation list for the current user session.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Require authentication
        if (!tiny::user()) {
            $response->render('unauthorized');
            return;
        }

        $user = tiny::user();

        // Allow sorting formations by different metrics
        $sort = tiny::router()->query['sort'] ?? 'recent';
        $validSorts = ['recent', 'downloads', 'stars', 'name'];
        
        if (!in_array($sort, $validSorts)) {
            $sort = 'recent';
        }

        // Map the sort parameter to the appropriate SQL ORDER BY clause
        $orderBy = match ($sort) {
            'recent' => 'f.published_at DESC',
            'downloads' => 'f.total_downloads DESC',
            'stars' => 'f.github_stars DESC',
            'name' => 'f.name ASC',
            default => 'f.published_at DESC'
        };

        // Fetch the user's formations to populate the dashboard listing with selected sorting
        $formations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY {$orderBy}
        ", [$user->id]);

        // Aggregate personal stats so the dashboard can show quick-glance metrics.
        $stats = tiny::db()->getOneQuery("
            SELECT
                COUNT(*) as formations_count,
                COALESCE(SUM(total_downloads), 0) as total_downloads,
                COALESCE(SUM(github_stars), 0) as total_stars
            FROM formations
            WHERE user_id = ?
        ", [$user->id]);

        tiny::data()->formations = $formations;
        tiny::data()->stats = $stats;
        tiny::data()->sort = $sort;

        $response->render('account/index');
    }
}
