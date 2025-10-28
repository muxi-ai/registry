<?php

/**
 * Home controller populates the landing page with registry highlights.
 */
class Home extends TinyController
{
    /**
     * Compose landing page sections using aggregate and curated formation data.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Aggregate totals for the hero metrics displayed at the top of the homepage.
        $stats = tiny::db()->getOneQuery("
            SELECT
                (SELECT COUNT(*) FROM formations) as total_formations,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT SUM(total_downloads) FROM formations) as total_downloads
        ");

        // Fetch the most recently published formations to power the "New arrivals" list.
        $recentFormations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            ORDER BY f.published_at DESC
            LIMIT 4
        ");

        // Surface the top downloaded formations so visitors see what the community values.
        $popularFormations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type
            FROM formations f
            JOIN users u ON f.user_id = u.id
            ORDER BY f.total_downloads DESC
            LIMIT 4
        ");

        // Rank the most active publishers to highlight builders with meaningful traction.
        $activeUsers = tiny::db()->getQuery("
            SELECT u.*,
                COUNT(f.id) as formations_count,
                SUM(f.total_downloads) as total_user_downloads
            FROM users u
            JOIN formations f ON f.user_id = u.id
            GROUP BY u.id
            ORDER BY total_user_downloads DESC
            LIMIT 8
        ");

        // Pass structured data to the home view; the view maps each section to UI components.
        $response->render('home', [
            'stats' => $stats,
            'formations' => [
                'recent' => $recentFormations,
                'popular' => $popularFormations,
            ],
            'activeUsers' => $activeUsers,
        ]);
    }
}
