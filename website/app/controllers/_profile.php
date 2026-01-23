<?php

/**
 * Controller responsible for rendering user profile pages.
 */
class Profile extends TinyController
{
    private $username;

    /**
     * Derive the username from the dynamic route and expose it to the view.
     */
    public function __construct()
    {
        // Strip the leading underscore from the route-based controller name.
        $this->username = substr(tiny::router()->controller, 1);
        // Share the username with Tiny's global data store for templates.
        tiny::data()->username = $this->username;
    }

    /**
     * Render the profile page for the resolved username.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Query user from database
        $profile = tiny::db()->getOneQuery("
            SELECT * FROM users
            WHERE registry_username = ?
            LIMIT 1
        ", [$this->username]);

        if (!$profile) {
            http_response_code(404);
            $response->render('404');
        }

        // Allow sorting formations by different metrics
        $default_sort = 'recent';
        $sort = tiny::router()->query['sort'] ?? $default_sort;
        $validSorts = ['trending', 'recent', 'downloads', 'name'];

        if (!in_array($sort, $validSorts)) {
            $sort = $default_sort;
        }

        // Get user's formations with selected sorting (exclude soft-deleted)
        if ($sort === 'trending') {
            // Trending: formations with most downloads in last 7 days
            $formations = tiny::db()->getQuery("
                SELECT
                    f.*,
                    u.registry_username,
                    u.github_username,
                    COALESCE(SUM(d.download_count), 0) as downloads_7d
                FROM formations f
                JOIN users u ON f.user_id = u.id
                LEFT JOIN downloads d
                    ON f.id = d.formation_id
                    AND d.day >= DATE('now', '-7 days')
                WHERE u.registry_username = ? AND f.deleted_at IS NULL
                GROUP BY f.id
                ORDER BY downloads_7d DESC, f.github_stars DESC
            ", [$this->username]);
        } else {
            // Map the sort parameter to the appropriate SQL ORDER BY clause
            $orderBy = match ($sort) {
                'recent' => 'f.published_at DESC',
                'downloads' => 'f.total_downloads DESC',
                'name' => 'f.name ASC',
                default => 'f.published_at DESC'
            };

            $formations = tiny::db()->getQuery("
                SELECT f.*, u.registry_username, u.github_username
                FROM formations f
                JOIN users u ON f.user_id = u.id
                WHERE u.registry_username = ? AND f.deleted_at IS NULL
                ORDER BY {$orderBy}
            ", [$this->username]);
        }

        // Get user stats (exclude soft-deleted)
        $stats = tiny::db()->getOneQuery("
            SELECT
                COUNT(*) as formations_count,
                COALESCE(SUM(total_downloads), 0) as total_downloads,
                COALESCE(SUM(github_stars), 0) as total_stars
            FROM formations
            WHERE user_id = ? AND deleted_at IS NULL
        ", [$profile['id']]);

        tiny::data()->profile = $profile;
        tiny::data()->formations = $formations;
        tiny::data()->stats = $stats;
        tiny::data()->sort = $sort;

        $response->render('profile/index');
    }
}
