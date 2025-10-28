<?php

tiny::helpers(['github']);

/**
 * API Controller for formations endpoints
 *
 * Handles:
 * - GET /api/formations/@:user/:name - Get formation metadata (lazy discovery)
 * - POST /api/formations/publish - Publish a formation (authenticated)
 */
class ApiFormations extends TinyController
{
    /** @var GitHub GitHub API client instance */
    private GitHub $github;
    
    /**
     * Initialize GitHub API client
     */
    public function __construct()
    {
        $this->github = tiny::github(null, 'MUXI-Registry');
    }

    /**
     * GET /api/formations/@:user/:name[:version][?pull=true]
     *
     * Get formation metadata with lazy discovery from GitHub
     * Optional :version to get specific version (default: latest)
     * Optional ?pull=true to track as download
     */
    public function get($request, $response)
    {
        // Parse route: /api/formations/@user/name[:version]
        // Remove /api/formations/ prefix
        $remaining = preg_replace('#^/api/formations/?#', '', tiny::router()->uri);
        $parts = array_filter(explode('/', $remaining));

        if (count($parts) < 2) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Invalid formation path. Expected: /api/formations/@user/name[:version]',
                'id' => 'API-10'
            ], 400);
        }

        // Remove @ prefix if present
        $parts = array_values($parts); // Re-index array
        $username = ltrim($parts[0], '@');
        $nameWithVersion = $parts[1];

        // Parse optional :version
        $versionParts = explode(':', $nameWithVersion, 2);
        $name = $versionParts[0];
        $requestedVersion = $versionParts[1] ?? null; // null = latest

        try {
            $formation = $this->findOrLazyFetch($username, $name, $requestedVersion);

            // Only track download if ?pull=true
            if (isset($_GET['pull']) && $_GET['pull'] === 'true') {
                $this->trackDownload($formation['formation_id'], $formation['version']);
            }

            return $response->sendJSON($formation);
        } catch (NotFoundException $e) {
            return $response->sendJSON([
                'error' => true,
                'message' => $e->getMessage(),
                'id' => 'API-04'
            ], 404);
        } catch (Exception $e) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Internal server error',
                'id' => 'API-11'
            ], 500);
        }
    }

    /**
     * POST /api/formations/publish
     *
     * Publish or update a formation (requires authentication)
     */
    public function post($request, $response)
    {
        // Authentication checked by middleware
        $user = tiny::user();
        if (!$user) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Authentication required',
                'id' => 'API-01'
            ], 401);
        }

        $data = $request->body(true);
        $githubRepo = $data['github_repo'] ?? '';
        $version = $data['version'] ?? null;

        // Validate repo format: owner/muxi-name
        if (!preg_match('/^[\w-]+\/muxi-[\w-]+$/', $githubRepo)) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Invalid repository name format. Must be: owner/muxi-name',
                'id' => 'API-08'
            ], 400);
        }

        list($repoOwner, $repoName) = explode('/', $githubRepo);

        // Check ownership
        if ($repoOwner !== $user->github_username) {
            // TODO: Check if it's an org the user has access to
            if (!$this->canPublishToOrg($user, $repoOwner)) {
                return $response->sendJSON([
                    'error' => true,
                    'message' => 'You don\'t have permission to publish this formation',
                    'id' => 'API-07'
                ], 403);
            }
        }

        // Get GitHub token for authenticated requests
        $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);

        // Fetch from GitHub
        $this->github->setToken($githubToken);
        try {
            $repo = $this->github->getRepo($githubRepo);
            $readme = $this->github->getReadme($githubRepo);
            $release = $version
                ? $this->github->getRelease($githubRepo, $version)
                : $this->github->getLatestRelease($githubRepo);
        } catch (Exception $e) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Repository or release not found on GitHub',
                'id' => 'API-09'
            ], 404);
        }

        // Extract formation name (remove muxi- prefix)
        $formationName = preg_replace('/^muxi-/', '', $repoName);

        // Create or update formation
        $formation = $this->createOrUpdateFormation(
            $user->id,
            $formationName,
            $repo,
            $readme,
            $release
        );

        return $response->sendJSON([
            'status' => 'ok',
            'message' => 'Formation published successfully',
            'formation' => [
                'name' => $formationName,
                'user' => $user->registry_username,
                'version' => ltrim($release['tag_name'], 'v'),
                'url' => tiny::getHomeURL("/@{$user->registry_username}/{$formationName}", true)
            ]
        ]);
    }

    /**
     * Find formation in database or lazy fetch from GitHub
     *
     * @param string $registryUsername Registry username
     * @param string $name Formation name
     * @param string|null $version Specific version or null for latest
     */
    private function findOrLazyFetch($registryUsername, $name, $version = null)
    {
        // 1. Try database first (fast path)
        $formation = $this->findInDatabase($registryUsername, $name, $version);

        if ($formation) {
            return $this->formatFormationResponse($formation);
        }

        // 2. Resolve registry username → GitHub username
        $user = tiny::db()->getOne('users', ['registry_username' => $registryUsername]);
        if (!$user) {
            throw new NotFoundException("User not found. Username: $registryUsername");
        }

        // 3. Try GitHub (lazy discovery)
        $this->github->clearToken();
        $repoName = "{$user['github_username']}/muxi-{$name}";

        try {
            $repo = $this->github->getRepo($repoName);
            $readme = $this->github->getReadme($repoName);
            $latestRelease = $this->github->getLatestRelease($repoName);
        } catch (Exception $e) {
            throw new NotFoundException("Formation not found");
        }

        // 4. Cache in database
        $this->cacheFormation($user['id'], $name, $repo, $readme, $latestRelease);

        // 5. Return freshly cached formation
        $formation = $this->findInDatabase($registryUsername, $name);
        return $this->formatFormationResponse($formation);
    }

    /**
     * Find formation in database
     *
     * @param string $registryUsername Registry username
     * @param string $name Formation name
     * @param string|null $version Specific version or null for latest
     */
    private function findInDatabase($registryUsername, $name, $version = null)
    {
        // Use getOne with simpler conditions
        $formation = tiny::db()->getOne('formations', ['name' => $name]);

        if (!$formation) {
            return null;
        }

        // Get user info
        $user = tiny::db()->getOne('users', ['id' => $formation['user_id']]);

        if (!$user || $user['registry_username'] !== $registryUsername) {
            return null;
        }

        // If specific version requested, verify it exists
        if ($version !== null) {
            $versionRecord = tiny::db()->getOne('versions', [
                'formation_id' => $formation['id'],
                'version' => $version
            ]);

            if (!$versionRecord) {
                return null; // Requested version doesn't exist
            }

            // Use specific version instead of latest
            $formation['latest_version'] = $version;
        }

        // Merge user info into formation
        $formation['registry_username'] = $user['registry_username'];
        $formation['github_username'] = $user['github_username'];
        $formation['github_avatar'] = $user['github_avatar'] ?? '';
        $formation['downloads_this_week'] = 0; // TODO: Calculate from downloads table

        return $formation;
    }

    /**
     * Cache formation from GitHub into database
     */
    private function cacheFormation($userId, $name, $repo, $readme, $release)
    {
        $data = [
            'user_id' => $userId,
            'name' => $name,
            'description' => $repo['description'] ?? '',
            'readme_md' => $readme,
            'latest_version' => ltrim($release['tag_name'], 'v'),
            'github_repo' => $repo['full_name'],
            'github_stars' => $repo['stargazers_count'] ?? 0,
            'license' => $repo['license']['spdx_id'] ?? null,
            'published_at' => $release['published_at'] ?? date('Y-m-d H:i:s'),
            'last_synced_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Check if formation already exists
        $existing = tiny::db()->getOne('formations', [
            'user_id' => $userId,
            'name' => $name
        ]);

        if ($existing) {
            tiny::db()->update('formations', $data, ['id' => $existing['id']]);
        } else {
            tiny::db()->insert('formations', $data);
        }
    }

    /**
     * Create or update formation in database
     */
    private function createOrUpdateFormation($userId, $name, $repo, $readme, $release)
    {
        $this->cacheFormation($userId, $name, $repo, $readme, $release);

        // Get user's registry username for response
        $user = tiny::db()->getOne('users', ['id' => $userId]);

        return [
            'name' => $name,
            'user' => $user['registry_username'],
            'version' => ltrim($release['tag_name'], 'v'),
            'github_repo' => $repo['full_name']
        ];
    }

    /**
     * Format formation data for API response
     */
    private function formatFormationResponse($formation)
    {
        $downloadUrl = null;
        if ($formation['latest_version']) {
            $downloadUrl = "https://github.com/{$formation['github_repo']}/releases/download/v{$formation['latest_version']}/bundle.zip";
        }

        return [
            'formation_id' => $formation['id'], // Internal ID for download tracking
            'name' => $formation['name'],
            'user' => $formation['registry_username'],
            'description' => $formation['description'],
            'version' => $formation['latest_version'],
            'github_repo' => $formation['github_repo'],
            'github_url' => "https://github.com/{$formation['github_repo']}",
            'install_command' => "muxi pull @{$formation['registry_username']}/{$formation['name']}",
            'download_url' => $downloadUrl,
            'stats' => [
                'downloads' => (int)$formation['total_downloads'],
                'downloads_this_week' => (int)($formation['downloads_this_week'] ?? 0),
                'stars' => (int)$formation['github_stars']
            ],
            'readme_url' => "https://raw.githubusercontent.com/{$formation['github_repo']}/main/README.md",
            'created_at' => $formation['created_at'],
            'updated_at' => $formation['last_synced_at']
        ];
    }

    /**
     * Track download (increment daily stats only)
     * Note: total_downloads is calculated from sum of downloads table
     */
    private function trackDownload($formationId, $version)
    {
        $today = date('Y-m-d');

        // Try to update existing record, or insert new one
        $existing = tiny::db()->getOne('downloads', [
            'formation_id' => $formationId,
            'version' => $version,
            'day' => $today
        ]);

        if ($existing) {
            // Increment existing daily count
            $newCount = ($existing['download_count'] ?? 0) + 1;
            tiny::db()->update('downloads', [
                'download_count' => $newCount
            ], ['id' => $existing['id']]);
        } else {
            // Create new daily record
            tiny::db()->insert('downloads', [
                'formation_id' => $formationId,
                'version' => $version,
                'day' => $today,
                'download_count' => 1
            ]);
        }
    }

    /**
     * Check if user can publish to an organization
     */
    private function canPublishToOrg($user, $orgName)
    {
        // Check if organization exists in our database
        $org = tiny::db()->getOne('users', [
            'github_username' => $orgName,
            'github_type' => 'organization'
        ]);

        if (!$org) {
            return false;
        }

        // Use GitHub API to check membership
        $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
        $this->github->setToken($githubToken);

        return $this->github->isOrgMember($orgName, $user->github_username);
    }
}

/**
 * Custom exception for not found errors
 */
class NotFoundException extends Exception {}
