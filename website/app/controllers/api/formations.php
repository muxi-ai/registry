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
        $orgName = $_POST['org'] ?? null;

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
        mkdir($tempDir, 0755, true);

        try {
            // 1. Unzip uploaded file
            $zip = new ZipArchive();
            if ($zip->open($uploadedFile['tmp_name']) !== true) {
                throw new Exception('Invalid ZIP file or unable to extract');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // 2. Parse formation.yaml
            $formationYamlPath = $tempDir . '/formation.yaml';
            if (!file_exists($formationYamlPath)) {
                throw new Exception('formation.yaml not found in ZIP archive');
            }

            $formationData = $this->parseSimpleYaml($formationYamlPath);
            if (!$formationData) {
                throw new Exception('Invalid formation.yaml format');
            }

            // 3. Validate required fields
            $requiredFields = ['id', 'version', 'description'];
            foreach ($requiredFields as $field) {
                if (!isset($formationData[$field]) || empty($formationData[$field])) {
                    throw new Exception("Missing or empty required field: $field");
                }
            }

            // Validate version format (semver)
            if (!preg_match('/^\d+\.\d+\.\d+$/', $formationData['version'])) {
                throw new Exception('Version must be in semver format (e.g., 1.0.0)');
            }

            // 4. Check/create README.md
            $readmePath = $tempDir . '/README.md';
            if (!file_exists($readmePath)) {
                // Generate comprehensive README using LLM
                $readme = $this->generateReadmeWithLLM($formationData, $tempDir);
                file_put_contents($readmePath, $readme);
            }

            // 5. Determine GitHub owner (user or org)
            $githubOwner = $orgName ?? $user->github_username;

            // 6. If org specified, verify membership
            if ($orgName) {
                $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
                $this->github->setToken($githubToken);

                if (!$this->github->isOrgMember($orgName, $user->github_username)) {
                    throw new Exception("You are not a member of organization: $orgName");
                }
            }

            // 7. Create/verify GitHub repository
            $repoName = "muxi-{$formationData['id']}";
            $fullRepoName = "$githubOwner/$repoName";

            $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($user->id);
            $this->github->setToken($githubToken);

            $repo = $this->createOrGetGitHubRepo($githubOwner, $repoName, $formationData);

            // 8. Push files to GitHub
            $this->pushFilesToGitHub($fullRepoName, $tempDir);

            // 9. Create GitHub release
            $version = $formationData['version'];
            $release = $this->createGitHubRelease($fullRepoName, $version, $formationData);

            // 10. Repack and upload as release asset
            $zipPath = $this->repackFormation($tempDir, $formationData['id']);
            $asset = $this->uploadReleaseAsset($fullRepoName, $release['id'], $zipPath, 'formation.zip');

            // 11. Store formation metadata in database
            $formation = $this->storeFormationInDatabase($user->id, $formationData, $repo, $release);

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
            // Cleanup temp directory
            $this->removeDirectory($tempDir);
            if (isset($zipPath) && file_exists($zipPath)) {
                unlink($zipPath);
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
     * @return string Generated README content
     */
    private function generateReadmeWithLLM($formationData, $tempDir)
    {
        try {
            // Analyze formation structure
            $structure = $this->analyzeFormationStructure($tempDir);
            
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
            
            $systemPrompt = "You are a technical documentation expert. Generate comprehensive, professional README files for MUXI formations (AI agent configurations).";
            
            $userPrompt = <<<PROMPT
Generate a comprehensive README.md for this MUXI formation:

{$formationInfo}

Requirements:
1. Create a professional, well-structured README with these sections:
   - Title and description
   - Features/capabilities
   - Installation instructions (use: muxi pull @owner/{$formationData['id']})
   - Usage/configuration guide (if applicable)
   - Requirements/dependencies
   - License information

2. Suggest up to 3 relevant categories for this formation (e.g., "customer-support", "automation", "data-processing")

3. Return ONLY valid JSON in this exact format:
{
  "readme": "# Full README content here...",
  "categories": ["category1", "category2", "category3"]
}

Important:
- Make the README engaging and informative
- Use markdown formatting
- Be specific about what this formation does
- Include code examples if relevant based on the structure
- Keep categories lowercase with hyphens (e.g., "customer-support")
PROMPT;
            
            // Call OpenAI
            $response = tiny::openai()->sendMessage(
                $userPrompt,
                $systemPrompt,
                [],
                2000,  // More tokens for comprehensive README
                'gpt-4o-mini'
            );
            
            $result = json_decode($response, true);
            
            if ($result && !$result['error'] && isset($result['data'])) {
                $data = $result['data'];
                
                // Store categories for later use (could be saved to database)
                if (isset($data['categories'])) {
                    // TODO: Store categories in database
                    // For now, just log them
                    error_log("Generated categories: " . implode(', ', $data['categories']));
                }
                
                return $data['readme'] ?? $this->generateBasicReadme($formationData);
            }
            
            // Fallback to basic README if LLM fails
            return $this->generateBasicReadme($formationData);
            
        } catch (Exception $e) {
            // Log error and fallback to basic README
            error_log("LLM README generation failed: " . $e->getMessage());
            return $this->generateBasicReadme($formationData);
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
            } elseif (strpos($relativePath, 'knowledge') !== false || strpos($relativePath, '.md') !== false) {
                $structure['components']['knowledge']++;
            }
        }
        
        return $structure;
    }
    
    /**
     * Generate basic README from formation data (fallback)
     */
    private function generateBasicReadme($formationData)
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
muxi pull @owner/{$id}
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
     * Upload release asset
     */
    private function uploadReleaseAsset($repoName, $releaseId, $zipPath, $fileName)
    {
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
    private function storeFormationInDatabase($userId, $formationData, $repo, $release)
    {
        $data = [
            'user_id' => $userId,
            'name' => $formationData['id'],
            'description' => $formationData['description'],
            'readme_md' => file_get_contents($repo['html_url'] . '/raw/main/README.md') ?? '',
            'latest_version' => $formationData['version'],
            'github_repo' => $repo['full_name'],
            'github_stars' => $repo['stargazers_count'] ?? 0,
            'license' => $formationData['license'] ?? $repo['license']['spdx_id'] ?? null,
            'published_at' => $release['published_at'] ?? date('Y-m-d H:i:s'),
            'last_synced_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Check if formation already exists
        $existing = tiny::db()->getOne('formations', [
            'user_id' => $userId,
            'name' => $formationData['id']
        ]);

        if ($existing) {
            // Update existing
            tiny::db()->update('formations', $data, ['id' => $existing['id']]);
            $formationId = $existing['id'];
        } else {
            // Insert new
            $formationId = tiny::db()->insert('formations', $data);
        }

        // Store version info
        $versionExists = tiny::db()->getOne('versions', [
            'formation_id' => $formationId,
            'version' => $formationData['version']
        ]);

        if (!$versionExists) {
            tiny::db()->insert('versions', [
                'formation_id' => $formationId,
                'version' => $formationData['version'],
                'release_notes' => $release['body'] ?? '',
                'download_url' => $release['assets'][0]['browser_download_url'] ?? null,
                'published_at' => $release['published_at'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
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
