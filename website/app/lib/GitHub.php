<?php

declare(strict_types=1);

/**
 * GitHub API Client
 * 
 * Handles interactions with GitHub REST API for repository, release, and README operations.
 */
class GitHub
{
    private $token;
    private $baseUrl = 'https://api.github.com';
    
    /**
     * Initialize GitHub API client
     *
     * @param string|null $token Personal access token or OAuth token (optional)
     */
    public function __construct($token = null)
    {
        $this->token = $token ?? getenv('GITHUB_TOKEN');
    }
    
    /**
     * Make HTTP request to GitHub API
     *
     * @param string $endpoint API endpoint (e.g., /repos/user/repo)
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array|null $data Request body data
     * @return array Response data as associative array
     * @throws Exception on HTTP errors
     */
    private function request($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Accept: application/vnd.github+json',
            'User-Agent: MUXI-Registry',
            'X-GitHub-Api-Version: 2022-11-28'
        ];
        
        if ($this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("GitHub API error: HTTP $httpCode - $response");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get repository information
     *
     * @param string $repo Repository in format "owner/repo"
     * @return array Repository data
     * @throws Exception if repository not found
     */
    public function getRepo($repo)
    {
        return $this->request("/repos/$repo");
    }
    
    /**
     * Get repository README content
     *
     * @param string $repo Repository in format "owner/repo"
     * @return string README content (decoded from base64)
     * @throws Exception if README not found
     */
    public function getReadme($repo)
    {
        $data = $this->request("/repos/$repo/readme");
        return base64_decode($data['content']);
    }
    
    /**
     * Get latest release
     *
     * @param string $repo Repository in format "owner/repo"
     * @return array Release data
     * @throws Exception if no releases found
     */
    public function getLatestRelease($repo)
    {
        return $this->request("/repos/$repo/releases/latest");
    }
    
    /**
     * Get specific release by tag
     *
     * @param string $repo Repository in format "owner/repo"
     * @param string $version Version tag (with or without 'v' prefix)
     * @return array Release data
     * @throws Exception if release not found
     */
    public function getRelease($repo, $version)
    {
        $tag = strpos($version, 'v') === 0 ? $version : "v{$version}";
        return $this->request("/repos/$repo/releases/tags/{$tag}");
    }
    
    /**
     * Check if user has push permission to repository
     *
     * @param string $repo Repository in format "owner/repo"
     * @param string $username GitHub username to check
     * @return bool True if user has push access
     */
    public function checkRepoPermission($repo, $username)
    {
        try {
            // Check if user is a collaborator with push access
            $collab = $this->request("/repos/$repo/collaborators/{$username}/permission");
            $permission = $collab['permission'] ?? '';
            
            return in_array($permission, ['admin', 'write', 'maintain']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if repository exists
     *
     * @param string $repo Repository in format "owner/repo"
     * @return bool True if repository exists
     */
    public function repoExists($repo)
    {
        try {
            $this->getRepo($repo);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all releases for a repository
     *
     * @param string $repo Repository in format "owner/repo"
     * @param int $limit Maximum number of releases to return
     * @return array Array of release data
     */
    public function getReleases($repo, $limit = 10)
    {
        return $this->request("/repos/$repo/releases?per_page={$limit}");
    }
    
    /**
     * Check if user is member of organization
     *
     * @param string $org Organization name
     * @param string $username GitHub username to check
     * @return bool True if user is member of organization
     */
    public function isOrgMember($org, $username)
    {
        try {
            // Check org membership
            $this->request("/orgs/{$org}/members/{$username}");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
