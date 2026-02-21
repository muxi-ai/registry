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
        $validSorts = ['trending', 'recent', 'downloads', 'name'];

        if (!in_array($sort, $validSorts)) {
            $sort = 'recent';
        }

        // Fetch the user's formations to populate the dashboard listing with selected sorting (exclude soft-deleted)
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
                WHERE f.user_id = ? AND f.deleted_at IS NULL
                GROUP BY f.id
                ORDER BY downloads_7d DESC, f.github_stars DESC
            ", [$user->id]);
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
                WHERE f.user_id = ? AND f.deleted_at IS NULL
                ORDER BY {$orderBy}
            ", [$user->id]);
        }

        // Aggregate personal stats so the dashboard can show quick-glance metrics (exclude soft-deleted).
        $stats = tiny::db()->getOneQuery("
            SELECT
                COUNT(*) as formations_count,
                COALESCE(SUM(total_downloads), 0) as total_downloads,
                COALESCE(SUM(github_stars), 0) as total_stars
            FROM formations
            WHERE user_id = ? AND deleted_at IS NULL
        ", [$user->id]);

        // Fetch user's GitHub organizations where they are admin
        $adminOrgs = [];
        $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId((int)$user->id);
        if ($githubToken) {
            tiny::helpers(['github']);
            $github = tiny::github();
            $github->setToken($githubToken);
            $ghOrgs = $github->getUserOrgs();
            foreach ($ghOrgs as $org) {
                $role = $github->getOrgMembership($org['login']);
                if ($role === 'admin') {
                    // Check if org has a registry profile
                    $registryUser = tiny::db()->getOne('users', ['github_username' => $org['login']]);
                    $adminOrgs[] = [
                        'login' => $org['login'],
                        'avatar_url' => $org['avatar_url'] ?? '',
                        'registry_username' => $registryUser ? $registryUser['registry_username'] : $org['login'],
                    ];
                }
            }
        }

        tiny::data()->formations = $formations;
        tiny::data()->stats = $stats;
        tiny::data()->sort = $sort;
        tiny::data()->adminOrgs = $adminOrgs;

        $response->render('account/index');
    }
}
