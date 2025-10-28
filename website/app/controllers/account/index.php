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

        // Fetch the user's formations to populate the dashboard listing.
        $formations = tiny::db()->getQuery("
            SELECT f.*, u.registry_username, u.github_username
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.published_at DESC
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

        $response->render('account/index');
    }
}
