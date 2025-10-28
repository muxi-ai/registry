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

        // Get user's formations
        $formations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE u.registry_username = ?
            ORDER BY f.published_at DESC
        ", [$this->username]);

        // Get user stats
        $stats = tiny::db()->getOneQuery("
            SELECT
                COUNT(*) as formations_count,
                COALESCE(SUM(total_downloads), 0) as total_downloads,
                COALESCE(SUM(github_stars), 0) as total_stars
            FROM formations
            WHERE user_id = ?
        ", [$profile['id']]);

        tiny::data()->profile = $profile;
        tiny::data()->formations = $formations;
        tiny::data()->stats = $stats;

        $response->render('profile/index');
    }
}
