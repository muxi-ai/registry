<?php

tiny::helpers(['github']);

/**
 * API Controller for user-related endpoints
 *
 * Handles:
 * - GET /api/user/orgs - List authenticated user's GitHub organizations (with roles)
 */
class ApiUser extends TinyController
{
    /**
     * GET /api/user or /api/user/orgs
     */
    public function get($request, $response)
    {
        $user = tiny::user();
        if (!$user) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Authentication required',
                'id' => 'API-01'
            ], 401);
        }

        $slug = tiny::router()->slug;

        if ($slug === 'orgs') {
            return $this->listOrgs($user, $response);
        }

        return $response->sendJSON([
            'error' => true,
            'message' => 'Unknown endpoint',
            'id' => 'API-10'
        ], 404);
    }

    /**
     * List authenticated user's GitHub organizations with their role in each
     */
    private function listOrgs($user, $response)
    {
        $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
        if (!$githubToken) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'GitHub token not found. Please reconnect your GitHub account.',
                'id' => 'API-06'
            ], 400);
        }

        $github = tiny::github();
        $github->setToken($githubToken);

        $ghOrgs = $github->getUserOrgs();

        $orgs = array_map(function ($org) use ($github, $user) {
            $role = $github->getOrgMembership($org['login'], $user->github_username);
            return [
                'login' => $org['login'],
                'avatar_url' => $org['avatar_url'] ?? '',
                'description' => $org['description'] ?? '',
                'role' => $role,
                'can_publish' => $role === 'admin',
            ];
        }, $ghOrgs);

        return $response->sendJSON([
            'orgs' => $orgs,
            'count' => count($orgs)
        ]);
    }
}
