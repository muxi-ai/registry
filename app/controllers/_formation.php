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
            WHERE u.registry_username = ? AND f.name = ? AND f.deleted_at IS NULL
            LIMIT 1
        ", [$this->username, $this->formationName]);

        // If not found, try lazy discovery from GitHub
        if (!$formation) {
            error_log("🔍 Lazy discovery: Formation not in DB, checking GitHub for @{$this->username}/{$this->formationName}");
            $formation = $this->lazyDiscoverFormation();

            if (!$formation) {
                error_log("❌ Formation not found in DB or GitHub: @{$this->username}/{$this->formationName}");
                http_response_code(404);
                $response->render('404');
                return;
            }

            error_log("✅ Lazy discovery successful, formation cached in DB");
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
        $downloadChartRaw = tiny::db()->getQuery("
            SELECT
                day,
                SUM(download_count) as downloads
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-30 days')
            GROUP BY day
            ORDER BY day ASC
        ", [$formation['id']]);
        $downloadChart = $this->fillMissingDays($downloadChartRaw, 30);

        // Get downloads this week (last 7 days) for the header badge
        $weekResult = tiny::db()->getOneQuery("
            SELECT COALESCE(SUM(download_count), 0) as count
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-7 days')
        ", [$formation['id']]);
        $downloadsThisWeek = $weekResult ? $weekResult['count'] : 0;

        // Get mini chart data for this week (7 days)
        $weeklyChartRaw = tiny::db()->getQuery("
            SELECT
                day,
                SUM(download_count) as downloads
            FROM downloads
            WHERE formation_id = ?
              AND day >= DATE('now', '-7 days')
            GROUP BY day
            ORDER BY day ASC
        ", [$formation['id']]);
        $weeklyChart = $this->fillMissingDays($weeklyChartRaw, 7);

        // Check if current user is owner/publisher or org admin (for delete permissions)
        $canDelete = false;
        if (@tiny::user()->id) {
            $userId = tiny::user()->id;
            $canDelete = ($formation['user_id'] == $userId) ||
                         ($formation['published_by_user_id'] == $userId);

            // If formation belongs to an org, check if current user is an admin
            if (!$canDelete && strtolower($formation['github_type']) === 'organization') {
                $orgUsername = $formation['github_username'];
                $githubToken = tiny::model('user')->getGitHubAccessTokenByUserId($userId);
                if ($githubToken) {
                    tiny::helpers(['github']);
                    $github = tiny::github();
                    $github->setToken($githubToken);
                    $role = $github->getOrgMembership($orgUsername, tiny::user()->github_username);
                    $canDelete = ($role === 'admin');
                }
            }
        }

        // Pass all formation data to the detail view for rendering.
        $response->render('formation/index', [
            'formation' => $formation,
            'latestVersion' => $latestVersion,
            'stats' => $stats,
            'versions' => $versions,
            'downloadChart' => $downloadChart,
            'downloadsThisWeek' => $downloadsThisWeek,
            'weeklyChart' => $weeklyChart,
            'canDelete' => $canDelete,
        ]);
    }

    /**
     * Lazy discover formation from GitHub if not in database
     *
     * @return array|null Formation data or null if not found
     */
    private function lazyDiscoverFormation()
    {
        // 1. Resolve registry username to GitHub username
        $user = tiny::db()->getOneQuery("
            SELECT * FROM users
            WHERE registry_username = ?
            LIMIT 1
        ", [$this->username]);

        if (!$user) {
            error_log("❌ User not found: @{$this->username}");
            return null;
        }

        // 2. Build GitHub repo name
        $githubUsername = $user['github_username'];
        $repoName = "{$githubUsername}/muxi-{$this->formationName}";
        error_log("🔍 Checking GitHub repo: {$repoName}");

        // 3. Fetch from GitHub
        tiny::helpers(['github']);
        $github = tiny::github();

        try {
            $repo = $github->getRepo($repoName);
        } catch (Exception $e) {
            error_log("❌ GitHub repo not found: {$repoName}");
            return null;
        }

        // 4. Fetch README
        $readme = null;
        try {
            $readme = $github->getReadme($repoName);
        } catch (Exception $e) {
            error_log("⚠️ README not found for {$repoName}");
        }

        // 5. Fetch latest release
        $latestRelease = null;
        $latestVersion = null;
        try {
            $latestRelease = $github->getLatestRelease($repoName);
            $latestVersion = ltrim($latestRelease['tag_name'], 'v');
        } catch (Exception $e) {
            error_log("⚠️ No releases found for {$repoName}");
        }

        // 6. Cache in database
        $formationId = tiny::db()->insert('formations', [
            'user_id' => $user['id'],
            'name' => $this->formationName,
            'description' => $repo['description'] ?? '',
            'readme_md' => $readme,
            'latest_version' => $latestVersion,
            'github_repo' => $repo['full_name'],
            'github_stars' => $repo['stargazers_count'] ?? 0,
            'license' => $repo['license']['spdx_id'] ?? null,
            'published_at' => $latestRelease['published_at'] ?? date('Y-m-d H:i:s'),
            'last_synced_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $formationId = tiny::db()->lastInsertId();

        error_log("💾 Cached formation in DB with ID: {$formationId}");

        // 7. Determine download URL (release or main branch)
        $downloadUrl = null;
        $versionId = null;

        if ($latestRelease) {
            // Has releases - use latest release
            tiny::db()->insert('versions', [
                'formation_id' => $formationId,
                'version' => $latestVersion,
                'release_notes' => $latestRelease['body'] ?? '',
                'download_url' => $latestRelease['assets'][0]['browser_download_url'] ?? null,
                'published_at' => $latestRelease['published_at'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $versionId = tiny::db()->lastInsertId();
            error_log("💾 Cached version {$latestVersion} with ID: {$versionId}");

            // Try release asset, fallback to zipball
            $downloadUrl = $latestRelease['assets'][0]['browser_download_url'] ?? $latestRelease['zipball_url'] ?? null;
        } else {
            // No releases - download main branch to get version from yaml
            $downloadUrl = "https://github.com/{$repoName}/archive/refs/heads/main.zip";
            error_log("⚠️ No releases found, using main branch: {$downloadUrl}");
        }

        // 8. Download and analyze structure for stats
        if ($downloadUrl) {
            $result = $this->analyzeFormationFromRelease($downloadUrl, true); // true = return version too
            $stats = $result['stats'] ?? null;
            $yamlVersion = $result['version'] ?? null;

            // If no release, create version from yaml
            if (!$latestRelease && $yamlVersion) {
                tiny::db()->insert('versions', [
                    'formation_id' => $formationId,
                    'version' => $yamlVersion,
                    'release_notes' => 'Main branch',
                    'download_url' => $downloadUrl,
                    'published_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $versionId = tiny::db()->lastInsertId();
                error_log("💾 Cached version {$yamlVersion} from yaml with ID: {$versionId}");

                // Update formation's latest_version
                tiny::db()->update('formations', [
                    'latest_version' => $yamlVersion
                ], ['id' => $formationId]);
            }

            // Store stats if we have both stats and version
            if ($stats && $versionId) {
                tiny::db()->insert('formation_stats', [
                    'version_id' => $versionId,
                    'agents_count' => $stats['agents'] ?? 0,
                    'mcps_count' => $stats['mcps'] ?? 0,
                    'sops_count' => $stats['sops'] ?? 0,
                    'triggers_count' => $stats['triggers'] ?? 0,
                    'knowledge_count' => $stats['knowledge'] ?? 0,
                    'stats_json' => json_encode($stats),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                error_log("📊 Cached formation stats: agents={$stats['agents']}, mcps={$stats['mcps']}, sops={$stats['sops']}");
            }
        }

        // 9. Return the cached formation
        return tiny::db()->getOneQuery("
            SELECT f.*, u.registry_username, u.github_username, u.github_type, u.github_avatar
            FROM formations f
            JOIN users u ON f.user_id = u.id
            WHERE f.id = ?
            LIMIT 1
        ", [$formationId]);
    }

    /**
     * Download release ZIP and analyze formation structure for stats
     *
     * @param string $downloadUrl URL to download the ZIP from
     * @param bool $returnVersion Whether to also return version from formation.yaml
     * @return array|null Component stats (and version if requested) or null if failed
     */
    private function analyzeFormationFromRelease($downloadUrl, $returnVersion = false)
    {
        $tempZip = sys_get_temp_dir() . '/lazy_' . uniqid() . '.zip';
        $tempDir = sys_get_temp_dir() . '/lazy_' . uniqid();

        try {
            // Download the ZIP
            error_log("⬇️ Downloading release from: {$downloadUrl}");
            $zipContent = file_get_contents($downloadUrl);
            if (!$zipContent) {
                error_log("❌ Failed to download release ZIP");
                return null;
            }

            file_put_contents($tempZip, $zipContent);

            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                error_log("❌ Failed to open ZIP file");
                @unlink($tempZip);
                return null;
            }

            $zip->extractTo($tempDir);
            $zip->close();
            @unlink($tempZip);

            // Analyze structure
            $stats = $this->analyzeFormationStructure($tempDir);

            // Parse formation.yaml for version if requested
            $version = null;
            if ($returnVersion) {
                $version = $this->parseFormationYamlVersion($tempDir);
                error_log("📄 Parsed version from yaml: " . ($version ?? '(not found, will use 1.0.0)'));
            }

            // Cleanup
            $this->deleteDirectory($tempDir);

            if ($returnVersion) {
                return [
                    'stats' => $stats,
                    'version' => $version ?? '1.0.0' // Default to 1.0.0
                ];
            }

            return $stats;

        } catch (Exception $e) {
            error_log("❌ Error analyzing formation: " . $e->getMessage());
            @unlink($tempZip);
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            return null;
        }
    }

    /**
     * Analyze formation directory structure for component counts
     *
     * @param string $dir Directory path
     * @return array Component counts
     */
    private function analyzeFormationStructure($dir)
    {
        $stats = [
            'agents' => 0,
            'mcps' => 0,
            'sops' => 0,
            'triggers' => 0,
            'knowledge' => 0,
            'files' => []
        ];

        $files = $this->getFilesRecursive($dir);

        foreach ($files as $file) {
            $relativePath = str_replace($dir . '/', '', $file);
            $stats['files'][] = $relativePath;

            // Count component types based on file patterns
            if (strpos($relativePath, 'agent') !== false && strpos($relativePath, '.yaml') !== false) {
                $stats['agents']++;
            } elseif (strpos($relativePath, 'mcp') !== false || strpos($relativePath, 'server') !== false) {
                $stats['mcps']++;
            } elseif (strpos($relativePath, 'sop') !== false || strpos($relativePath, 'procedure') !== false) {
                $stats['sops']++;
            } elseif (strpos($relativePath, 'trigger') !== false) {
                $stats['triggers']++;
            } elseif (strpos($relativePath, 'knowledge/') !== false && strpos($relativePath, '.md') !== false) {
                // Only count .md files that are in a knowledge/ directory
                $stats['knowledge']++;
            }
        }

        return $stats;
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
     * Parse formation.yaml to get version
     *
     * @param string $dir Directory containing the formation
     * @return string|null Version string or null if not found
     */
    private function parseFormationYamlVersion($dir)
    {
        // Find formation.yaml (could be in subdirectory for GitHub archive)
        $files = $this->getFilesRecursive($dir);
        $yamlPath = null;

        foreach ($files as $file) {
            if (basename($file) === 'formation.yaml' || basename($file) === 'formation.afs') {
                $yamlPath = $file;
                break;
            }
        }

        if (!$yamlPath || !file_exists($yamlPath)) {
            error_log("⚠️ formation.yaml/formation.afs not found in downloaded archive");
            return null;
        }

        try {
            $yamlContent = file_get_contents($yamlPath);

            // Simple regex parsing for version (avoiding yaml parser dependency)
            if (preg_match('/^\s*version:\s*["\']?([0-9]+\.[0-9]+\.[0-9]+)["\']?\s*$/m', $yamlContent, $matches)) {
                return $matches[1];
            }

            error_log("⚠️ version field not found in formation.yaml");
            return null;

        } catch (Exception $e) {
            error_log("❌ Error parsing formation.yaml: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Fill missing days with zero downloads for chart data
     *
     * @param array $data Raw chart data from database
     * @param int $days Number of days to fill (7 or 30)
     * @return array Complete chart data with all days
     */
    private function fillMissingDays($data, $days)
    {
        // Index existing data by day
        $indexed = [];
        foreach ($data as $row) {
            $indexed[$row['day']] = (int)$row['downloads'];
        }

        // Generate all days and fill with zeros where missing
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = [
                'day' => $day,
                'downloads' => $indexed[$day] ?? 0
            ];
        }

        return $result;
    }
}
