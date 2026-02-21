<?php

tiny::helpers(['github', 'openai']);

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

    // Security constants
    private const MAX_FORMATION_SIZE = 50 * 1024 * 1024; // 50MB
    private const MAX_EXTRACTED_SIZE = 100 * 1024 * 1024; // 100MB (allow 2x for compression)
    private const DIR_PERMISSIONS = 0755;

    /**
     * Initialize GitHub API client
     */
    public function __construct()
    {
        $this->github = tiny::github(null, 'MUXI-Registry');
    }

    /**
     * GET /api/formations - List authenticated user's formations
     * GET /api/formations/@:user/:name[:version][?pull=true] - Get specific formation
     *
     * For listing: requires authentication, returns user's formations
     * For specific: lazy discovery from GitHub, optional version, optional download tracking
     */
    public function get($request, $response)
    {
        // Parse route: /api/formations/@user/name[:version]
        // Remove /api/formations/ prefix
        $remaining = preg_replace('#^/api/formations/?#', '', tiny::router()->uri);
        $parts = array_filter(explode('/', $remaining));

        // If no path parts, list user's formations
        if (count($parts) < 2) {
            return $this->listUserFormations($request, $response);
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
     * DELETE /api/formations/@:user/:name
     *
     * Delete a formation (requires authentication, owner only)
     *
     * Query params:
     * - delete_github=true: Also delete the GitHub repository
     */
    public function delete($request, $response)
    {
        error_log("🚀 DELETE method called! URI: " . tiny::router()->uri);
        
        // Authentication required
        $user = tiny::user();
        if (!$user) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Authentication required',
                'id' => 'API-01'
            ], 401);
        }

        // Parse route: /api/formations/@user/name
        $remaining = preg_replace('#^/api/formations/?#', '', tiny::router()->uri);
        $parts = array_filter(explode('/', $remaining));

        if (count($parts) < 2) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Invalid formation path. Expected: /api/formations/@user/name',
                'id' => 'API-10'
            ], 400);
        }

        $parts = array_values($parts);
        $username = ltrim($parts[0], '@');
        $name = $parts[1];

        // Get formation from database (exclude already deleted)
        $formation = tiny::db()->getOneQuery("
            SELECT f.*, u.registry_username, u.github_username, u.id as owner_user_id
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE u.registry_username = ? AND f.name = ? AND f.deleted_at IS NULL
            LIMIT 1
        ", [$username, $name]);

        if (!$formation) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Formation not found',
                'id' => 'API-04'
            ], 404);
        }

        // Check ownership: must be owner, publisher, or org admin
        $isOwner = ($formation['owner_user_id'] == $user->id);
        $isPublisher = ($formation['published_by_user_id'] == $user->id);
        $isOrgAdmin = false;

        if (!$isOwner && !$isPublisher) {
            // Check if formation belongs to an org the user is admin of
            $ownerUser = tiny::db()->getOne('users', ['id' => $formation['owner_user_id']]);
            if ($ownerUser && strtolower($ownerUser['github_type'] ?? '') === 'organization') {
                $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
                if ($githubToken) {
                    $this->github->setToken($githubToken);
                    $role = $this->github->getOrgMembership($ownerUser['github_username'], $user->github_username);
                    $isOrgAdmin = ($role === 'admin');
                }
            }
        }

        if (!$isOwner && !$isPublisher && !$isOrgAdmin) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'You do not have permission to delete this formation',
                'id' => 'API-02'
            ], 403);
        }

        $deleteGithub = isset($_GET['delete_github']) && $_GET['delete_github'] === 'true';
        $formationId = $formation['id'];
        $githubRepo = $formation['github_repo'];
        
        error_log("🔍 Delete request: deleteGithub=" . ($deleteGithub ? 'true' : 'false') . ", _GET=" . json_encode($_GET) . ", REQUEST_URI=" . $_SERVER['REQUEST_URI']);

        try {
            // Soft delete: set deleted_at timestamp and rename to free up the name
            // This allows republishing a formation with the same name
            $deletedAt = date('Y-m-d H:i:s');
            $deletedName = $name . '_deleted_' . time();
            
            tiny::db()->update('formations', [
                'name' => $deletedName,
                'deleted_at' => $deletedAt
            ], ['id' => $formationId]);

            error_log("🗑️ Soft-deleted formation: @{$username}/{$name} -> {$deletedName} (ID: {$formationId})");

            // Optionally delete GitHub repository
            $githubDeleted = false;
            $githubError = null;
            if ($deleteGithub && $githubRepo) {
                error_log("🔍 Attempting to delete GitHub repo: {$githubRepo}");
                try {
                    $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
                    error_log("🔍 GitHub token for user {$user->id}: " . ($githubToken ? 'found (' . strlen($githubToken) . ' chars)' : 'NOT FOUND'));
                    if (!$githubToken) {
                        $githubError = 'No GitHub token found. Please reconnect your GitHub account.';
                        error_log("⚠️ Cannot delete GitHub repo {$githubRepo}: no token");
                    } else {
                        $this->github->setToken($githubToken);
                        error_log("🔍 Calling deleteRepo for: {$githubRepo}");
                        $this->github->deleteRepo($githubRepo);
                        $githubDeleted = true;
                        error_log("🗑️ Deleted GitHub repository: {$githubRepo}");
                    }
                } catch (Exception $e) {
                    $githubError = $e->getMessage();
                    error_log("⚠️ Failed to delete GitHub repo {$githubRepo}: " . $e->getMessage());
                    // Don't fail the whole operation if GitHub deletion fails
                }
            } else {
                error_log("🔍 GitHub delete skipped: deleteGithub={$deleteGithub}, githubRepo={$githubRepo}");
            }

            $responseData = [
                'status' => 'ok',
                'message' => 'Formation deleted successfully',
                'formation' => [
                    'name' => $name,
                    'user' => $username
                ],
                'github_deleted' => $githubDeleted,
                'github_delete_requested' => $deleteGithub,
                'github_repo' => $githubRepo
            ];

            if ($githubError) {
                $responseData['github_error'] = $githubError;
            }

            return $response->sendJSON($responseData);

        } catch (Exception $e) {
            error_log("❌ Error deleting formation: " . $e->getMessage());
            return $response->sendJSON([
                'error' => true,
                'message' => 'Failed to delete formation: ' . $e->getMessage(),
                'id' => 'API-15'
            ], 500);
        }
    }

    /**
     * List authenticated user's formations
     *
     * GET /api/formations
     *
     * Returns array of formations owned by or published by the authenticated user
     */
    private function listUserFormations($request, $response)
    {
        $user = tiny::user();
        if (!$user) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Authentication required',
                'id' => 'API-01'
            ], 401);
        }

        // Get formations owned by or published by the user (exclude soft-deleted)
        $formations = tiny::db()->getQuery("
            SELECT 
                f.id,
                f.name,
                f.description,
                f.latest_version,
                f.github_repo,
                f.github_stars,
                f.total_downloads,
                f.published_at,
                f.created_at,
                u.registry_username
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE (f.user_id = ? OR f.published_by_user_id = ?) 
              AND f.deleted_at IS NULL
            ORDER BY f.published_at DESC
        ", [$user->id, $user->id]);

        // Format response
        $result = array_map(function($f) {
            return [
                'name' => $f['name'],
                'user' => $f['registry_username'],
                'description' => $f['description'],
                'version' => $f['latest_version'],
                'github_repo' => $f['github_repo'],
                'stats' => [
                    'downloads' => (int)$f['total_downloads'],
                    'stars' => (int)$f['github_stars']
                ],
                'published_at' => $f['published_at'],
                'created_at' => $f['created_at']
            ];
        }, $formations);

        return $response->sendJSON([
            'formations' => $result,
            'count' => count($result)
        ]);
    }

    /**
     * POST /api/formations/publish
     *
     * Publish a formation from uploaded ZIP file (requires authentication)
     *
     * Accepts multipart/form-data with:
     * - file: formation.zip (required)
     * - org: organization name (optional, defaults to user)
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

        // Validate file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'No formation.zip file uploaded or upload failed',
                'id' => 'API-13'
            ], 400);
        }

        $uploadedFile = $_FILES['file'];
        $orgName = $_GET['org'] ?? $_POST['org'] ?? null; // Support both query param and POST data

        // Validate file size
        if ($uploadedFile['size'] > self::MAX_FORMATION_SIZE) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Formation ZIP must be under 50MB',
                'size_mb' => round($uploadedFile['size'] / 1024 / 1024, 2),
                'id' => 'API-16'
            ], 400);
        }

        // Validate file is a ZIP
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ['application/zip', 'application/x-zip-compressed'])) {
            return $response->sendJSON([
                'error' => true,
                'message' => 'Uploaded file must be a ZIP archive',
                'id' => 'API-14'
            ], 400);
        }

        // Process and publish formation
        try {
            $result = $this->processAndPublishFormation($user, $uploadedFile, $orgName);
            return $response->sendJSON($result);
        } catch (Exception $e) {
            // Log detailed error with context
            tiny::log('Formation publish error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $response->sendJSON([
                'error' => true,
                'message' => $e->getMessage(),
                'id' => 'API-15'
            ], 400);
        }
    }

    /**
     * Process uploaded formation ZIP and publish to GitHub
     *
     * @param object $user Authenticated user
     * @param array $uploadedFile $_FILES array entry
     * @param string|null $orgName Optional organization name
     * @return array Success response
     * @throws Exception on any error
     */
    private function processAndPublishFormation($user, $uploadedFile, $orgName = null)
    {
        // Create temp directory
        $tempDir = sys_get_temp_dir() . '/muxi_' . uniqid();
        mkdir($tempDir, self::DIR_PERMISSIONS, true);

        try {
            // 1. Unzip uploaded file with security validation
            $zip = new ZipArchive();
            if ($zip->open($uploadedFile['tmp_name']) !== true) {
                throw new Exception('Invalid ZIP file or unable to extract');
            }

            // Security: Validate ZIP contents before extraction (Zip Slip protection)
            $extractedSize = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                $stat = $zip->statIndex($i);

                // Block path traversal attempts
                if (strpos($entry, '..') !== false || strpos($entry, '/..') !== false) {
                    $zip->close();
                    error_log("SECURITY: Path traversal attempt detected in ZIP: {$entry}");
                    throw new Exception('Invalid ZIP: Path traversal detected');
                }

                // Block absolute paths
                if ($entry[0] === '/' || (strlen($entry) > 1 && $entry[1] === ':')) {
                    $zip->close();
                    error_log("SECURITY: Absolute path detected in ZIP: {$entry}");
                    throw new Exception('Invalid ZIP: Absolute paths not allowed');
                }

                // Check total extracted size
                $extractedSize += $stat['size'];
                if ($extractedSize > self::MAX_EXTRACTED_SIZE) {
                    $zip->close();
                    throw new Exception('Extracted formation size exceeds 100MB limit');
                }
            }

            // Extract after validation
            $zip->extractTo($tempDir);
            $zip->close();

            error_log("✅ ZIP validated and extracted safely: {$zip->numFiles} files, " . round($extractedSize / 1024 / 1024, 2) . "MB");

            // 1b. Security cleanup: Remove sensitive files and macOS artifacts
            $this->removeSensitiveFiles($tempDir);

            // 2. Parse formation.yaml
            $formationYamlPath = $tempDir . '/formation.yaml';
            if (!file_exists($formationYamlPath)) {
                throw new Exception('formation.yaml not found in ZIP archive');
            }

            $formationData = $this->parseSimpleYaml($formationYamlPath);
            if (!$formationData) {
                throw new Exception('Invalid formation.yaml format');
            }

            // Log parsed formation data for debugging
            tiny::log('Formation data parsed', [
                'formation_id' => $formationData['id'] ?? 'unknown',
                'version' => $formationData['version'] ?? 'unknown',
                'has_description' => isset($formationData['description'])
            ]);

            // Use schema as version fallback if version not present
            if (!isset($formationData['version']) && isset($formationData['schema'])) {
                $formationData['version'] = $formationData['schema'];
            }

            // 3. Validate required fields and sanitize input
            $requiredFields = ['id', 'version', 'description'];
            foreach ($requiredFields as $field) {
                if (!isset($formationData[$field]) || empty($formationData[$field])) {
                    throw new Exception("Missing or empty required field: $field");
                }
            }

            // Validate formation ID format
            if (!preg_match('/^[a-z0-9-]{3,50}$/', $formationData['id'])) {
                throw new Exception('Formation ID must be 3-50 characters, lowercase letters, numbers, and hyphens only');
            }

            // Validate version format (semver)
            if (!preg_match('/^\d+\.\d+\.\d+$/', $formationData['version'])) {
                throw new Exception('Version must be in semver format (e.g., 1.0.0)');
            }

            // Sanitize and validate description
            $formationData['description'] = strip_tags($formationData['description']);
            if (strlen($formationData['description']) > 500) {
                throw new Exception('Description must be under 500 characters');
            }
            if (strlen($formationData['description']) < 10) {
                throw new Exception('Description must be at least 10 characters');
            }

            // 4. Analyze formation structure (for stats)
            $structure = $this->analyzeFormationStructure($tempDir);
            $formationData['_structure'] = $structure;
            error_log("Formation structure analyzed: " . json_encode($structure['components']));

            // 5. Check/create README.md
            $readmePath = $tempDir . '/README.md';
            if (!file_exists($readmePath)) {
                // Generate comprehensive README using LLM (structure already analyzed above)
                $llmResult = $this->generateReadmeWithLLM($formationData, $tempDir, $user->registry_username);
                if (is_array($llmResult)) {
                    // LLM returned both README and categories
                    file_put_contents($readmePath, $llmResult['readme']);
                    if (isset($llmResult['categories'])) {
                        $formationData['_generated_categories'] = $llmResult['categories'];
                        error_log("Stored categories in formationData: " . json_encode($llmResult['categories']));
                    }
                } else {
                    // Fallback template returned just string
                    file_put_contents($readmePath, $llmResult);
                }
            }

            // Store README content for database
            $formationData['_readme_content'] = file_get_contents($readmePath);

            // TEST MODE: Disabled - now using commented GitHub operations instead
            /*
            if (isset($_GET['test']) && $_GET['test'] === 'true') {
                $structure = $this->analyzeFormationStructure($tempDir);
                $readmeContent = file_get_contents($readmePath);
                $readmePreview = substr($readmeContent, 0, 500);

                return [
                    'status' => 'test_ok',
                    'message' => 'Formation processed successfully (test mode - no GitHub push)',
                    'formation_data' => [
                        'id' => $formationData['id'],
                        'version' => $formationData['version'],
                        'description' => $formationData['description'],
                        'categories' => $formationData['_generated_categories'] ?? null,
                    ],
                    'structure' => $structure,
                    'readme_preview' => $readmePreview . '...',
                    'readme_length' => strlen($readmeContent),
                    'temp_dir' => $tempDir,
                    'files_in_formation' => $structure['files']
                ];
            }
            */

            // 5. Determine GitHub owner (user or org)
            $githubOwner = $orgName ?? $user->github_username;

            // Log owner decision
            if ($orgName) {
                error_log("📦 Publishing to ORGANIZATION: {$orgName} (user: {$user->registry_username})");
            } else {
                error_log("📦 Publishing to USER: {$user->registry_username} (github: {$user->github_username})");
            }

            // ========== GITHUB OPERATIONS ==========

            // 6. Get user's GitHub OAuth token (already decrypted by model)
            $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
            if (!$githubToken) {
                throw new Exception("GitHub OAuth token not found. Please reconnect your GitHub account.");
            }
            $this->github->setToken($githubToken);
            $tokenPreview = substr($githubToken, 0, 10) . '...' . substr($githubToken, -4);
            error_log("🔑 Using OAuth token for user: {$user->registry_username}, token: {$tokenPreview}");

            // 7. If org specified, verify admin role and get/create org user
            $ownerUserId = $user->id; // Default to authenticated user
            if ($orgName) {
                $role = $this->github->getOrgMembership($orgName, $user->github_username);
                if ($role !== 'admin') {
                    throw new Exception("You must be an admin of organization: $orgName");
                }

                // Get or create the org's user record in database
                $orgUser = tiny::db()->getOne('users', ['github_username' => $orgName]);
                if (!$orgUser) {
                    // Lazy-create org record from GitHub
                    $ghOrg = $this->github->getOrg($orgName);
                    if (!$ghOrg) {
                        throw new Exception("Organization not found on GitHub: $orgName");
                    }
                    tiny::db()->insert('users', [
                        'github_id' => $ghOrg['id'],
                        'github_username' => $ghOrg['login'],
                        'registry_username' => $ghOrg['login'],
                        'github_avatar' => $ghOrg['avatar_url'] ?? '',
                        'github_email' => $ghOrg['email'] ?? '',
                        'github_type' => 'organization',
                        'is_verified' => false,
                        'created_at' => date('Y-m-d H:i:s'),
                        'last_seen_at' => date('Y-m-d H:i:s'),
                    ]);
                    $orgUser = tiny::db()->getOne('users', ['github_username' => $orgName]);
                    error_log("🏢 Auto-created org user record for: {$orgName} (ID: {$orgUser['id']})");
                }
                $ownerUserId = $orgUser['id'];
                error_log("🏢 Using org user ID: {$ownerUserId} for formation ownership");
            }

            // 8. Create/verify GitHub repository
            $repoName = "muxi-{$formationData['id']}";
            $fullRepoName = "$githubOwner/$repoName";

            $repo = $this->createOrGetGitHubRepo($githubOwner, $repoName, $formationData);

            // 9. Set GitHub topics (tags)
            $this->setGitHubTopics($fullRepoName, $formationData);

            // 10. Push files to GitHub
            $this->pushFilesToGitHub($fullRepoName, $tempDir);

            // 11. Create GitHub release
            $version = $formationData['version'];
            $release = $this->createGitHubRelease($fullRepoName, $version, $formationData);

            // 12. Repack and upload as release asset
            $zipPath = $this->repackFormation($tempDir, $formationData['id']);
            $asset = $this->uploadReleaseAsset($fullRepoName, $release['id'], $zipPath, 'formation.zip');

            // ========== END GITHUB OPERATIONS ==========

            // Mock GitHub data disabled - using real GitHub operations
            /*
            $repoName = "muxi-{$formationData['id']}";
            $fullRepoName = "$githubOwner/$repoName";
            $version = $formationData['version'];

            error_log("🎯 GitHub repo would be: {$fullRepoName}");

            $repo = [
                'full_name' => $fullRepoName,
                'html_url' => "https://github.com/$fullRepoName",
                'stargazers_count' => 0,
                'license' => ['spdx_id' => 'MIT']
            ];

            $release = [
                'tag_name' => "v{$version}",
                'published_at' => date('Y-m-d H:i:s'),
                'body' => $formationData['description'] ?? "Release v{$version}",
                'assets' => [[
                    'browser_download_url' => "https://github.com/$fullRepoName/releases/download/v{$version}/formation.zip"
                ]]
            ];

            $asset = [
                'browser_download_url' => "https://github.com/$fullRepoName/releases/download/v{$version}/formation.zip"
            ];
            */

            // 11. Store formation metadata in database
            error_log("🗄️ Storing in database. Repo: " . json_encode(['full_name' => $repo['full_name'] ?? 'MISSING']));
            error_log("🗄️ Release data: " . json_encode($release));
            error_log("🗄️ FormationData has _structure: " . (isset($formationData['_structure']) ? 'YES' : 'NO'));
            error_log("🗄️ Owner user ID: {$ownerUserId}, Published by user ID: {$user->id}");
            $formation = $this->storeFormationInDatabase($ownerUserId, $formationData, $repo, $release, $user->id);

            return [
                'status' => 'ok',
                'message' => 'Formation published successfully',
                'formation' => [
                    'name' => $formationData['id'],
                    'user' => $user->registry_username,
                    'version' => $version,
                    'github_repo' => $fullRepoName,
                    'registry_url' => tiny::getHomeURL("/@{$user->registry_username}/{$formationData['id']}", true),
                    'download_url' => $asset['browser_download_url'] ?? null
                ]
            ];

        } finally {
            // Cleanup temp directory with robust error handling
            if (isset($tempDir) && is_dir($tempDir)) {
                try {
                    $this->removeDirectory($tempDir);
                    error_log("🧹 Temp directory cleaned up: {$tempDir}");
                } catch (Exception $e) {
                    error_log("⚠️ CRITICAL: Failed to remove temp directory: {$tempDir} - " . $e->getMessage());

                    // Try forceful cleanup as last resort
                    if (function_exists('exec') && DIRECTORY_SEPARATOR === '/') {
                        exec('rm -rf ' . escapeshellarg($tempDir) . ' 2>&1', $output, $returnCode);
                        if ($returnCode !== 0) {
                            error_log("🚨 ALERT: Temp directory cleanup failed completely: {$tempDir}");
                            error_log("Output: " . implode("\n", $output));
                        } else {
                            error_log("✅ Temp directory force-cleaned: {$tempDir}");
                        }
                    }
                }
            }

            // Cleanup ZIP file
            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath);  // @ suppresses warnings if file already deleted
            }
        }
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
        if (!$formation) {
            throw new NotFoundException("Formation not found");
        }
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

        // Exclude soft-deleted formations
        if (!empty($formation['deleted_at'])) {
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
        $formation['downloads_this_week'] = $this->calculateDownloadsThisWeek($formation['id']);

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
            $downloadUrl = "https://github.com/{$formation['github_repo']}/releases/download/v{$formation['latest_version']}/formation.zip";
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
     * Calculate downloads for the last 7 days
     *
     * @param int $formationId Formation ID
     * @return int Total downloads in last 7 days
     */
    private function calculateDownloadsThisWeek($formationId)
    {
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

        $result = tiny::db()->getQuery("
            SELECT COALESCE(SUM(download_count), 0) as total
            FROM downloads
            WHERE formation_id = ?
            AND day >= ?
        ", [$formationId, $sevenDaysAgo]);

        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Generate comprehensive README using LLM
     *
     * @param array $formationData Parsed formation.yaml data
     * @param string $tempDir Path to extracted formation directory
     * @param string $registryUsername User's registry username
     * @return string Generated README content
     */
    private function generateReadmeWithLLM($formationData, $tempDir, $registryUsername)
    {
        try {
            // Use pre-analyzed structure if available, otherwise analyze now
            $structure = $formationData['_structure'] ?? $this->analyzeFormationStructure($tempDir);

            // Build prompt with formation data
            $formationInfo = json_encode([
                'id' => $formationData['id'] ?? 'unknown',
                'description' => $formationData['description'] ?? '',
                'version' => $formationData['version'] ?? '1.0.0',
                'runtime' => $formationData['runtime'] ?? 'Not specified',
                'author' => $formationData['author'] ?? null,
                'url' => $formationData['url'] ?? null,
                'license' => $formationData['license'] ?? 'MIT',
                'structure' => $structure
            ], JSON_PRETTY_PRINT);

            $systemPrompt = "You are a technical documentation expert. You MUST respond with valid JSON only. Generate comprehensive README files and categorize MUXI formations (AI agent configurations).";

            $userPrompt = <<<PROMPT
Generate documentation for this MUXI formation and return it as JSON.

Formation data:
{$formationInfo}

You MUST return a valid JSON object with this exact structure:
{
  "readme": "markdown content here",
  "categories": ["category1", "category2"]
}

For the "readme" field, create a professional README with:
- # {$formationData['id']} as title
- Description and features
- Installation: muxi pull @{$registryUsername}/{$formationData['id']}
- Usage/configuration guide
- Requirements (MUXI Runtime {$formationData['runtime']})
- License: MIT
- Links at bottom:
  * Formation on MUXI Registry: https://registry.muxi.org/@{$registryUsername}/{$formationData['id']}
  * MUXI Documentation: https://muxi.org

For "categories", suggest 2-3 relevant categories (lowercase with hyphens, e.g., "automation", "data-processing", "customer-support", "code-generation", "workflow-automation")

Remember: Your entire response must be valid JSON. Do not include any text outside the JSON object.
PROMPT;

            // Call OpenAI
            error_log("Calling OpenAI for README generation...");
            $response = tiny::openai()->sendMessage(
                $userPrompt,
                $systemPrompt,
                [],
                2000,  // More tokens for comprehensive README
                'gpt-4o-mini'
            );

            error_log("OpenAI raw response: " . substr($response, 0, 500));
            $result = json_decode($response, true);

            if ($result && !$result['error'] && isset($result['data'])) {
                $data = $result['data'];
                error_log("LLM data received: " . json_encode($data));

                // Check if data has readme and categories
                if (isset($data['readme']) && isset($data['categories'])) {
                    error_log("✅ LLM SUCCESS! Categories: " . implode(', ', $data['categories']));

                    // Return both README and categories as array
                    return [
                        'readme' => $data['readme'],
                        'categories' => $data['categories']
                    ];
                }

                error_log("⚠️ LLM data missing readme or categories fields");
            } else {
                error_log("⚠️ LLM generation failed: " . json_encode($result));
            }

            // Fallback to basic README if LLM fails
            error_log("Using fallback README template");
            return $this->generateBasicReadme($formationData, $registryUsername);

        } catch (Exception $e) {
            // Log error and fallback to basic README
            error_log("LLM README generation failed: " . $e->getMessage());
            return $this->generateBasicReadme($formationData, $registryUsername);
        }
    }

    /**
     * Analyze formation directory structure
     *
     * @param string $tempDir Path to formation directory
     * @return array Structure information
     */
    private function analyzeFormationStructure($tempDir)
    {
        $structure = [
            'files' => [],
            'components' => [
                'agents' => 0,
                'mcps' => 0,
                'sops' => 0,
                'triggers' => 0,
                'knowledge' => 0
            ]
        ];

        $files = $this->getFilesRecursive($tempDir);

        foreach ($files as $file) {
            $relativePath = str_replace($tempDir . '/', '', $file);
            $structure['files'][] = $relativePath;

            // Count component types based on file patterns
            if (strpos($relativePath, 'agent') !== false && strpos($relativePath, '.yaml') !== false) {
                $structure['components']['agents']++;
            } elseif (strpos($relativePath, 'mcp') !== false || strpos($relativePath, 'server') !== false) {
                $structure['components']['mcps']++;
            } elseif (strpos($relativePath, 'sop') !== false || strpos($relativePath, 'procedure') !== false) {
                $structure['components']['sops']++;
            } elseif (strpos($relativePath, 'trigger') !== false) {
                $structure['components']['triggers']++;
            } elseif (strpos($relativePath, 'knowledge/') !== false && strpos($relativePath, '.md') !== false) {
                // Only count .md files that are in a knowledge/ directory
                $structure['components']['knowledge']++;
            }
        }

        return $structure;
    }

    /**
     * Generate basic README from formation data (fallback)
     */
    private function generateBasicReadme($formationData, $registryUsername = 'owner')
    {
        $id = $formationData['id'];
        $description = $formationData['description'];
        $version = $formationData['version'];
        $author = $formationData['author'] ?? 'Unknown';
        $license = $formationData['license'] ?? 'MIT';

        return <<<MD
# {$id}

{$description}

## Installation

```bash
muxi pull @{$registryUsername}/{$id}
```

## Version

Current version: {$version}

## Author

{$author}

## License

{$license}

---

*This README was auto-generated. Please update with detailed documentation.*

MD;
    }

    /**
     * Create GitHub repository or get existing
     */
    private function createOrGetGitHubRepo($owner, $repoName, $formationData)
    {
        // Check if repo exists
        try {
            $repo = $this->github->getRepo("$owner/$repoName");
            return $repo;
        } catch (Exception $e) {
            // Create new repo
            return $this->github->createRepo($owner, $repoName, [
                'description' => $formationData['description'],
                'private' => false
            ]);
        }
    }

    /**
     * Push files to GitHub repository using Contents API
     */
    private function pushFilesToGitHub($repoName, $tempDir)
    {
        $files = $this->getFilesRecursive($tempDir);

        foreach ($files as $file) {
            $relativePath = str_replace($tempDir . '/', '', $file);
            $content = file_get_contents($file);

            // Check if file exists (for updates)
            $sha = $this->github->getFileSha($repoName, $relativePath);

            // Create or update file
            $this->github->createOrUpdateFile(
                $repoName,
                $relativePath,
                $content,
                $sha ? "Update $relativePath" : "Add $relativePath",
                $sha
            );
        }
    }

    /**
     * Create GitHub release
     */
    private function createGitHubRelease($repoName, $version, $formationData)
    {
        $tagName = "v{$version}";

        // Check if release already exists
        try {
            return $this->github->getRelease($repoName, $tagName);
        } catch (Exception $e) {
            // Create new release
            return $this->github->createRelease($repoName, [
                'tag_name' => $tagName,
                'name' => $tagName,
                'body' => $formationData['description'] ?? "Release $tagName",
                'draft' => false,
                'prerelease' => false
            ]);
        }
    }

    /**
     * Upload release asset (handles existing assets gracefully)
     */
    private function uploadReleaseAsset($repoName, $releaseId, $zipPath, $fileName)
    {
        // Check if asset already exists and delete it
        try {
            $release = $this->github->getReleaseById($repoName, $releaseId);
            if (isset($release['assets']) && is_array($release['assets'])) {
                foreach ($release['assets'] as $asset) {
                    if ($asset['name'] === $fileName) {
                        error_log("🗑️ Deleting existing asset: {$fileName} (id: {$asset['id']})");
                        $this->github->deleteReleaseAsset($repoName, $asset['id']);
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("⚠️ Could not check for existing assets: " . $e->getMessage());
        }

        // Upload the asset
        return $this->github->uploadReleaseAsset($repoName, $releaseId, $zipPath, $fileName);
    }

    /**
     * Repack formation directory into ZIP
     */
    private function repackFormation($dir, $formationId)
    {
        $zipPath = sys_get_temp_dir() . "/{$formationId}_" . time() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Unable to create ZIP archive');
        }

        $files = $this->getFilesRecursive($dir);
        foreach ($files as $file) {
            $relativePath = str_replace($dir . '/', '', $file);
            $zip->addFile($file, $relativePath);
        }

        $zip->close();
        return $zipPath;
    }

    /**
     * Store formation metadata in database
     */
    private function storeFormationInDatabase($userId, $formationData, $repo, $release, $publishedByUserId = null)
    {
        // Prepare categories as JSON string
        $categories = null;
        if (isset($formationData['_generated_categories']) && is_array($formationData['_generated_categories'])) {
            $categories = json_encode($formationData['_generated_categories']);
        }

        // Get README content from generated file if available
        $readmeContent = '';
        if (isset($formationData['_readme_content'])) {
            $readmeContent = $formationData['_readme_content'];
        }

        $data = [
            'user_id' => $userId,
            'published_by_user_id' => $publishedByUserId,
            'name' => $formationData['id'],
            'description' => $formationData['description'],
            'readme_md' => $readmeContent,
            'latest_version' => $formationData['version'],
            'github_repo' => $repo['full_name'],
            'github_stars' => $repo['stargazers_count'] ?? 0,
            'license' => $formationData['license'] ?? (isset($repo['license']['spdx_id']) ? $repo['license']['spdx_id'] : null),
            'categories' => $categories,
            'published_at' => $release['published_at'] ?? date('Y-m-d H:i:s'),
            'last_synced_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Check if formation already exists
        error_log("🔍 Checking for existing formation: user_id={$userId}, name={$formationData['id']}");
        $existing = tiny::db()->getOne('formations', [
            'user_id' => $userId,
            'name' => $formationData['id']
        ]);
        error_log("🔍 Existing formation found: " . ($existing ? "YES (id={$existing['id']})" : "NO"));

        if ($existing) {
            // Update existing
            tiny::db()->update('formations', $data, ['id' => $existing['id']]);
            $formationId = $existing['id'];
            error_log("✏️ Updated existing formation, formationId={$formationId}");
        } else {
            // Insert new
            tiny::db()->insert('formations', $data);
            $formationId = tiny::db()->lastInsertId();
            error_log("✨ Inserted new formation, formationId={$formationId}");
        }

        // Store version info
        $versionExists = tiny::db()->getOne('versions', [
            'formation_id' => $formationId,
            'version' => $formationData['version']
        ]);
        error_log("🔍 Checking if version exists for formation_id={$formationId}, version={$formationData['version']}: " . ($versionExists ? 'YES (skipping insert)' : 'NO (will insert)'));
        error_log("🔍 versionExists data: " . json_encode($versionExists));

        if (!$versionExists) {
            tiny::db()->insert('versions', [
                'formation_id' => $formationId,
                'version' => $formationData['version'],
                'release_notes' => $release['body'] ?? '',
                'download_url' => $release['assets'][0]['browser_download_url'] ?? null,
                'published_at' => $release['published_at'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $versionId = tiny::db()->lastInsertId();
            error_log("✅ Version inserted, versionId: $versionId");

            // Store formation stats (agents, mcps, sops, triggers, knowledge counts)
            error_log("🔍 Checking for _structure: " . (isset($formationData['_structure']) ? 'YES' : 'NO'));
            error_log("🔍 Checking for components: " . (isset($formationData['_structure']['components']) ? 'YES' : 'NO'));
            if (isset($formationData['_structure'])) {
                error_log("🔍 Structure contents: " . json_encode($formationData['_structure']));
            }

            if (isset($formationData['_structure']) && isset($formationData['_structure']['components'])) {
                $components = $formationData['_structure']['components'];
                error_log("📊 Inserting stats: agents={$components['agents']}, mcps={$components['mcps']}, sops={$components['sops']}, triggers={$components['triggers']}, knowledge={$components['knowledge']}");
                tiny::db()->insert('formation_stats', [
                    'version_id' => $versionId,
                    'agents_count' => $components['agents'] ?? 0,
                    'mcps_count' => $components['mcps'] ?? 0,
                    'sops_count' => $components['sops'] ?? 0,
                    'triggers_count' => $components['triggers'] ?? 0,
                    'knowledge_count' => $components['knowledge'] ?? 0,
                    'stats_json' => json_encode($formationData['_structure']),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                error_log("📊 Formation stats stored: agents={$components['agents']}, mcps={$components['mcps']}, sops={$components['sops']}, triggers={$components['triggers']}, knowledge={$components['knowledge']}");
            }
        }

        return $formationId;
    }

    /**
     * Get all files recursively from directory
     */
    private function getFilesRecursive($dir)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Set GitHub repository topics
     *
     * @param string $repoName Full repository name (owner/repo)
     * @param array $formationData Formation data including categories
     */
    private function setGitHubTopics($repoName, $formationData)
    {
        try {
            // Build topics list: muxi, formation, + generated categories
            $topics = ['muxi', 'formation'];

            if (isset($formationData['_generated_categories']) && is_array($formationData['_generated_categories'])) {
                foreach ($formationData['_generated_categories'] as $category) {
                    $topics[] = $category;
                }
            }

            // GitHub limits to 20 topics, ensure we don't exceed
            $topics = array_slice(array_unique($topics), 0, 20);

            $this->github->setTopics($repoName, $topics);

        } catch (Exception $e) {
            // Log error but don't fail publish if topics fail
            error_log("Failed to set GitHub topics: " . $e->getMessage());
        }
    }

    /**
     * Remove sensitive files and macOS artifacts from extracted formation
     *
     * Security measure to prevent accidental exposure of:
     * - .key (encryption key)
     * - secrets.enc (encrypted secrets)
     * - __MACOSX/ (macOS metadata)
     */
    private function removeSensitiveFiles($dir)
    {
        $sensitivePatterns = [
            '.key',           // Encryption key file
            'secrets.enc',    // Encrypted secrets file
            '__MACOSX'        // macOS artifact directory
        ];

        $removed = [];

        foreach ($sensitivePatterns as $pattern) {
            $path = $dir . '/' . $pattern;

            if (file_exists($path)) {
                if (is_dir($path)) {
                    $this->removeDirectory($path);
                    $removed[] = $pattern . '/ (directory)';
                } else {
                    unlink($path);
                    $removed[] = $pattern . ' (file)';
                }
            }
        }

        if (!empty($removed)) {
            error_log("🔒 Security cleanup: Removed " . implode(', ', $removed));
        }
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Parse simple YAML file (basic key-value pairs)
     *
     * Supports:
     * - Simple key: value pairs
     * - Quoted strings
     * - Multi-line values (with proper indentation)
     * - Comments (# lines)
     *
     * @param string $filePath Path to YAML file
     * @return array|false Parsed data or false on error
     */
    private function parseSimpleYaml($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $lines = explode("\n", $content);
        $data = [];
        $currentKey = null;
        $multilineValue = '';
        $inMultiline = false;

        foreach ($lines as $line) {
            // Skip empty lines
            $trimmed = trim($line);
            if (empty($trimmed)) {
                if ($inMultiline) {
                    $multilineValue .= "\n";
                }
                continue;
            }

            // Skip comments
            if (strpos($trimmed, '#') === 0) {
                continue;
            }

            // Check if this is a continuation of multiline value
            if ($inMultiline && (strpos($line, '  ') === 0 || strpos($line, "\t") === 0)) {
                $multilineValue .= "\n" . trim($line);
                continue;
            }

            // End multiline if we were in one
            if ($inMultiline) {
                $data[$currentKey] = $multilineValue;
                $inMultiline = false;
                $multilineValue = '';
            }

            // Parse key-value pair
            if (strpos($trimmed, ':') !== false) {
                list($key, $value) = explode(':', $trimmed, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^["\'](.+)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }

                // Check if value is empty or multiline indicator (multiline start)
                if (empty($value) || $value === '|' || $value === '>') {
                    $currentKey = $key;
                    $inMultiline = true;
                    $multilineValue = '';
                } else {
                    $data[$key] = $value;
                }
            }
        }

        // Handle last multiline value
        if ($inMultiline && $currentKey) {
            $data[$currentKey] = $multilineValue;
        }

        return $data;
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
