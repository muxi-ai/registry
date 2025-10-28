<?php

/**
 * Controller responsible for rendering formation pages.
 * Handles URLs like /@user/formation-name
 */
class Formation extends TinyController
{
    private $username;
    private $formationName;

    /**
     * Parse the route and extract username and formation name
     */
    public function __construct()
    {
        // Route format: @username/formation-name
        $this->username = substr(tiny::router()->controller, 1); // Remove @ prefix
        $this->formationName = tiny::router()->section;

        tiny::data()->username = $this->username;
        tiny::data()->formationName = $this->formationName;
    }

    /**
     * Render the formation page
     */
    public function get($request, $response)
    {
        // Query formation from database
        $formation = tiny::db()->getOneQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type, u.github_avatar
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE u.registry_username = ? AND f.name = ?
            LIMIT 1
        ", [$this->username, $this->formationName]);

        if (!$formation) {
            http_response_code(404);
            $response->render('404');
        }

        // Get latest version details
        $latestVersion = tiny::db()->getOneQuery("
            SELECT * FROM versions
            WHERE formation_id = ?
            ORDER BY published_at DESC
            LIMIT 1
        ", [$formation['id']]);

        // Get version stats if available
        $stats = null;
        if ($latestVersion) {
            $stats = tiny::db()->getOneQuery("
                SELECT * FROM formation_stats
                WHERE version_id = ?
                LIMIT 1
            ", [$latestVersion['id']]);
        }

        // Get all versions for version history
        $versions = tiny::db()->getQuery("
            SELECT * FROM versions
            WHERE formation_id = ?
            ORDER BY published_at DESC
        ", [$formation['id']]);

        tiny::data()->formation = $formation;
        tiny::data()->latestVersion = $latestVersion;
        tiny::data()->stats = $stats;
        tiny::data()->versions = $versions;

        $response->render('formation/index');
    }
}
