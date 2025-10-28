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

        // Fetch trending formations (last 7 days) - the hottest content right now
        $trendingFormations = tiny::db()->getQuery("
            SELECT 
                f.id,
                f.name,
                f.description,
                f.latest_version,
                f.total_downloads,
                f.github_stars,
                u.registry_username,
                u.github_avatar,
                COALESCE(SUM(d.download_count), 0) as downloads_7d
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN downloads d 
                ON f.id = d.formation_id 
                AND d.day >= DATE('now', '-7 days')
            GROUP BY f.id
            HAVING downloads_7d > 0
            ORDER BY downloads_7d DESC, f.github_stars DESC
            LIMIT 8
        ");

        // Fetch the most recently published formations to power the "New arrivals" list.
        $recentFormations = tiny::db()->getQuery("
            SELECT 
                f.*,
                u.registry_username,
                u.github_avatar
            FROM formations f
            JOIN users u ON f.user_id = u.id
            ORDER BY f.published_at DESC, f.created_at DESC
            LIMIT 4
        ");

        // Surface the top downloaded formations so visitors see what the community values.
        $popularFormations = tiny::db()->getQuery("
            SELECT 
                f.*,
                u.registry_username,
                u.github_avatar
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.total_downloads > 0
            ORDER BY f.total_downloads DESC, f.github_stars DESC
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
                'trending' => $trendingFormations,
                'recent' => $recentFormations,
                'popular' => $popularFormations,
            ],
            'activeUsers' => $activeUsers,
        ]);
    }
}
