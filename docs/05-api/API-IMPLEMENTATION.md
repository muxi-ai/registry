# API Implementation Guide

**Phase 2: Building the MUXI Registry API**

---

## 🎯 API Endpoints Overview

### Public Endpoints (No Auth Required)
```
GET  /api/formations/@:user/:name    - Get formation metadata (lazy fetch)
GET  /api/search?q=query             - Search formations
POST /api/formations/@:user/:name/:version/download - Track download
```

### Authenticated Endpoints (Requires Bearer Token)
```
POST /api/formations/publish         - Publish formation
GET  /api/formations/@me             - List user's formations
GET  /api/stats/@me                  - User statistics
```

---

## 📦 Endpoint 1: Get Formation (Lazy Discovery)

### `GET /api/formations/@:user/:name[:version][?pull=true]`

**The Core Magic**: Lazy/blind discovery of formations from GitHub

**New in v2.5**: 
- `:version` syntax for specific version info (e.g., `@muxi/customer-support:1.2.0`)
- `?pull=true` query parameter to track downloads (default: info only, no tracking)

#### Flow:
```
1. Parse URL path for optional :version
   ↓
2. Check database for cached metadata
   ↓
3. If found → return cached data (fast path)
   ↓
4. If not found → Try GitHub
   ↓
5. Resolve registry username → GitHub username (handle mappings like @muxi → muxi-ai)
   ↓
6. Fetch repo: github.com/{github_username}/muxi-{formation_name}
   ↓
7. Fetch README, latest release, stats
   ↓
8. Cache in database
   ↓
9. If ?pull=true → track download in downloads table
   ↓
10. Return metadata
```

#### Request Examples:
```bash
# Info only (no tracking)
GET /api/formations/@muxi/customer-support

# Specific version info (no tracking)
GET /api/formations/@muxi/customer-support:1.2.0

# Actual pull (tracks download)
GET /api/formations/@muxi/customer-support?pull=true

# Specific version pull (tracks download)
GET /api/formations/@muxi/customer-support:1.2.0?pull=true
```

#### Response (200 OK):
```json
{
  "name": "customer-support",
  "user": "muxi",
  "description": "AI-powered customer support agent",
  "version": "1.2.0",
  "github_repo": "muxi-ai/muxi-customer-support",
  "github_url": "https://github.com/muxi-ai/muxi-customer-support",
  "install_command": "muxi pull @muxi/customer-support",
  "download_url": "https://github.com/muxi-ai/muxi-customer-support/releases/download/v1.2.0/bundle.zip",
  "stats": {
    "downloads": 1234,
    "downloads_this_week": 89,
    "stars": 42
  },
  "components": {
    "agents": 2,
    "mcps": 1,
    "sops": 3,
    "triggers": 1,
    "knowledge": 2
  },
  "readme_url": "https://raw.githubusercontent.com/muxi-ai/muxi-customer-support/main/README.md",
  "created_at": "2025-01-15T10:30:00Z",
  "updated_at": "2025-10-20T14:22:00Z"
}
```

#### Error Responses:
```json
// 404 - Formation not found (neither in DB nor GitHub)
{
  "error": true,
  "message": "Formation not found",
  "id": "API-04"
}

// 404 - User not found
{
  "error": true,
  "message": "User not found",
  "id": "API-05"
}
```

#### Implementation:
```php
// File: website/app/controllers/api/formations.php

class ApiFormations extends TinyController
{
    public function get($request, $response)
    {
        // Parse route: /api/formations/@user/name
        $username = $request->param('user'); // Remove @ if needed
        $name = $request->param('name');
        
        try {
            $formation = $this->findOrLazyFetch($username, $name);
            return $response->json($formation);
        } catch (NotFoundException $e) {
            return $response->json([
                'error' => true,
                'message' => $e->getMessage(),
                'id' => 'API-04'
            ], 404);
        }
    }
    
    private function findOrLazyFetch($registryUsername, $name)
    {
        // 1. Try database
        $formation = $this->findInDatabase($registryUsername, $name);
        if ($formation) {
            return $this->formatFormationResponse($formation);
        }
        
        // 2. Resolve registry → GitHub username
        $user = $this->resolveUser($registryUsername);
        if (!$user) {
            throw new NotFoundException("User not found");
        }
        
        // 3. Try GitHub
        $github = new GitHub();
        $repoName = "{$user['github_username']}/muxi-{$name}";
        
        try {
            $repo = $github->getRepo($repoName);
            $readme = $github->getReadme($repoName);
            $latestRelease = $github->getLatestRelease($repoName);
        } catch (Exception $e) {
            throw new NotFoundException("Formation not found");
        }
        
        // 4. Cache in database
        $this->cacheFormation($user['id'], $name, $repo, $readme, $latestRelease);
        
        // 5. Return
        $formation = $this->findInDatabase($registryUsername, $name);
        return $this->formatFormationResponse($formation);
    }
}
```

---

## 🔍 Endpoint 2: Search Formations

### `GET /api/search?q=query&sort=trending&limit=20`

**Full-text search** across formation names, descriptions, and tags.

#### Parameters:
- `q` (required): Search query
- `sort` (optional): `trending` | `downloads` | `recent` | `stars` (default: `trending`)
- `limit` (optional): 1-100 (default: 20)

#### Request:
```bash
GET /api/search?q=customer+support&sort=trending&limit=10
```

#### Response (200 OK):
```json
{
  "query": "customer support",
  "results": [
    {
      "name": "customer-support",
      "user": "muxi",
      "description": "AI-powered customer support agent",
      "version": "1.2.0",
      "downloads": 1234,
      "downloads_this_week": 89,
      "github_repo": "muxi-ai/muxi-customer-support"
    },
    {
      "name": "support-bot",
      "user": "acme",
      "description": "Customer support automation",
      "version": "2.0.1",
      "downloads": 567,
      "downloads_this_week": 23,
      "github_repo": "acme/muxi-support-bot"
    }
  ],
  "total": 2,
  "limit": 10,
  "sort": "trending"
}
```

#### Implementation:
```php
// File: website/app/controllers/api/search.php

class ApiSearch extends TinyController
{
    public function get($request, $response)
    {
        $query = $request->query('q');
        if (!$query) {
            return $response->json([
                'error' => true,
                'message': 'Missing search query',
                'id' => 'API-06'
            ], 400);
        }
        
        $sort = $request->query('sort', 'trending');
        $limit = min((int)$request->query('limit', 20), 100);
        
        $results = $this->searchFormations($query, $sort, $limit);
        
        return $response->json([
            'query' => $query,
            'results' => $results,
            'total' => count($results),
            'limit' => $limit,
            'sort' => $sort
        ]);
    }
    
    private function searchFormations($query, $sort, $limit)
    {
        // Use FTS5 full-text search
        $orderBy = match($sort) {
            'trending' => 'downloads_7d DESC, github_stars DESC',
            'downloads' => 'total_downloads DESC',
            'recent' => 'published_at DESC',
            'stars' => 'github_stars DESC',
            default => 'downloads_7d DESC'
        };
        
        return tiny::db()->query("
            SELECT 
                f.name,
                u.registry_username as user,
                f.description,
                f.latest_version as version,
                f.total_downloads as downloads,
                f.github_repo,
                COALESCE(SUM(d.download_count), 0) as downloads_this_week
            FROM formations f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN downloads d 
                ON f.id = d.formation_id 
                AND d.day >= DATE('now', '-7 days')
            WHERE f.name LIKE ? OR f.description LIKE ?
            GROUP BY f.id
            ORDER BY {$orderBy}
            LIMIT ?
        ", [
            "%{$query}%",
            "%{$query}%",
            $limit
        ])->fetchAll();
    }
}
```

---

## 📤 Endpoint 3: Publish Formation

### `POST /api/formations/publish`

**Authenticated endpoint**: Publish or update a formation from GitHub repo.

#### Authentication:
```
Authorization: Bearer mxr_your_token_here
```

#### Request Body:
```json
{
  "github_repo": "user/muxi-formation-name",
  "version": "1.0.0"
}
```

#### Response (200 OK):
```json
{
  "status": "ok",
  "message": "Formation published successfully",
  "formation": {
    "name": "formation-name",
    "user": "user",
    "version": "1.0.0",
    "url": "https://registry.muxi.org/@user/formation-name"
  }
}
```

#### Error Responses:
```json
// 401 - Not authenticated
{
  "error": true,
  "message": "Authentication required",
  "id": "API-01"
}

// 403 - Not authorized (not repo owner)
{
  "error": true,
  "message": "You don't have permission to publish this formation",
  "id": "API-07"
}

// 400 - Invalid repo name
{
  "error": true,
  "message": "Invalid repository name format",
  "id": "API-08"
}

// 404 - Repo not found
{
  "error": true,
  "message": "Repository not found on GitHub",
  "id": "API-09"
}
```

#### Implementation:
```php
// File: website/app/controllers/api/formations.php

class ApiFormations extends TinyController
{
    public function post($request, $response)
    {
        // Authentication checked by middleware
        $user = tiny::user();
        if (!$user) {
            return $response->json([
                'error' => true,
                'message' => 'Authentication required',
                'id' => 'API-01'
            ], 401);
        }
        
        $data = $request->input();
        $githubRepo = $data['github_repo'] ?? '';
        $version = $data['version'] ?? null;
        
        // Validate repo format
        if (!preg_match('/^[\w-]+\/muxi-[\w-]+$/', $githubRepo)) {
            return $response->json([
                'error' => true,
                'message' => 'Invalid repository name format. Must be: user/muxi-name',
                'id' => 'API-08'
            ], 400);
        }
        
        // Check ownership
        list($repoOwner, $repoName) = explode('/', $githubRepo);
        if ($repoOwner !== $user->github_username) {
            // Check if it's an org the user has access to
            if (!$this->canPublishToOrg($user, $repoOwner)) {
                return $response->json([
                    'error' => true,
                    'message' => 'You don\'t have permission to publish this formation',
                    'id' => 'API-07'
                ], 403);
            }
        }
        
        // Fetch from GitHub
        $github = new GitHub($user->github_oauth_token);
        try {
            $repo = $github->getRepo($githubRepo);
            $readme = $github->getReadme($githubRepo);
            $release = $version 
                ? $github->getRelease($githubRepo, $version)
                : $github->getLatestRelease($githubRepo);
        } catch (Exception $e) {
            return $response->json([
                'error' => true,
                'message' => 'Repository not found on GitHub',
                'id' => 'API-09'
            ], 404);
        }
        
        // Extract formation name (remove muxi- prefix)
        $formationName = str_replace('muxi-', '', $repoName);
        
        // Create or update formation
        $formation = $this->createOrUpdateFormation(
            $user->id,
            $formationName,
            $repo,
            $readme,
            $release
        );
        
        return $response->json([
            'status' => 'ok',
            'message' => 'Formation published successfully',
            'formation' => [
                'name' => $formationName,
                'user' => $user->registry_username,
                'version' => $release['tag_name'],
                'url' => tiny::homeURL("/@{$user->registry_username}/{$formationName}")
            ]
        ]);
    }
}
```

---

## 🔧 Shared Helper: GitHub API Client

Create a reusable GitHub API client:

```php
// File: website/app/lib/GitHub.php

class GitHub
{
    private $token;
    private $baseUrl = 'https://api.github.com';
    
    public function __construct($token = null)
    {
        $this->token = $token ?? getenv('GITHUB_TOKEN');
    }
    
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
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("GitHub API error: HTTP $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    public function getRepo($repo)
    {
        return $this->request("/repos/$repo");
    }
    
    public function getReadme($repo)
    {
        $data = $this->request("/repos/$repo/readme");
        return base64_decode($data['content']);
    }
    
    public function getLatestRelease($repo)
    {
        return $this->request("/repos/$repo/releases/latest");
    }
    
    public function getRelease($repo, $version)
    {
        $tag = strpos($version, 'v') === 0 ? $version : "v{$version}";
        return $this->request("/repos/$repo/releases/tags/{$tag}");
    }
    
    public function checkRepoPermission($repo, $username)
    {
        try {
            $collaborators = $this->request("/repos/$repo/collaborators");
            foreach ($collaborators as $collab) {
                if ($collab['login'] === $username && 
                    in_array($collab['permissions']['push'], [true, 'true'])) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
```

---

## 🧪 Testing

### Test GET Formation (Lazy Discovery)
```bash
# Already cached
curl http://localhost:8000/api/formations/@ranaroussi/code-reviewer

# Not cached (lazy fetch from GitHub)
curl http://localhost:8000/api/formations/@newuser/new-formation

# Not found
curl http://localhost:8000/api/formations/@fake/nonexistent
```

### Test Search
```bash
curl "http://localhost:8000/api/search?q=customer&sort=trending&limit=5"
```

### Test Publish (Authenticated)
```bash
TOKEN="mxr_your_token_here"

curl -X POST http://localhost:8000/api/formations/publish \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "github_repo": "user/muxi-my-formation",
    "version": "1.0.0"
  }'
```

---

## ✅ Implementation Checklist

- [ ] Create `/api/formations` controller
- [ ] Implement GET (lazy discovery)
- [ ] Implement POST (publish)
- [ ] Create `/api/search` controller
- [ ] Create GitHub API helper class
- [ ] Add error handling
- [ ] Test all endpoints
- [ ] Document rate limits
- [ ] Add logging

---

**Next**: Build CLI tool that consumes these APIs
