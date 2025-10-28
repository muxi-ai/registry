<?php
tiny::helpers(['markdown']);

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
     * Render the formation detail page with metadata, stats, and version history.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Query formation from database with user data for display and GitHub links.
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
            return;
        }

        // Fetch the latest published version to show current release info.
        $latestVersion = tiny::db()->getOneQuery("
            SELECT * FROM versions
            WHERE formation_id = ?
            ORDER BY published_at DESC
            LIMIT 1
        ", [$formation['id']]);

        // Pull component stats if available; not all formations have this data yet.
        $stats = null;
        if ($latestVersion) {
            $stats = tiny::db()->getOneQuery("
                SELECT * FROM formation_stats
                WHERE version_id = ?
                LIMIT 1
            ", [$latestVersion['id']]);
        }

        // Get full version history for the sidebar; shows progression over time.
        $versions = tiny::db()->getQuery("
            SELECT * FROM versions
            WHERE formation_id = ?
            ORDER BY published_at DESC
        ", [$formation['id']]);

        // Load download chart data (last 30 days, aggregated across all versions)
        $downloadChart = tiny::db()->getQuery("
            SELECT 
                day,
                SUM(download_count) as downloads
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-30 days')
            GROUP BY day
            ORDER BY day ASC
        ", [$formation['id']]);

        // Get downloads this week (last 7 days) for the header badge
        $weekResult = tiny::db()->getOneQuery("
            SELECT COALESCE(SUM(download_count), 0) as count
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-7 days')
        ", [$formation['id']]);
        $downloadsThisWeek = $weekResult ? $weekResult['count'] : 0;

        // Get mini chart data for this week (7 days)
        $weeklyChart = tiny::db()->getQuery("
            SELECT 
                day,
                SUM(download_count) as downloads
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-7 days')
            GROUP BY day
            ORDER BY day ASC
        ", [$formation['id']]);

        // Pass all formation data to the detail view for rendering.
        $response->render('formation/index', [
            'formation' => $formation,
            'latestVersion' => $latestVersion,
            'stats' => $stats,
            'versions' => $versions,
            'downloadChart' => $downloadChart,
            'downloadsThisWeek' => $downloadsThisWeek,
            'weeklyChart' => $weeklyChart,
        ]);
    }
}
