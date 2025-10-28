# MUXI Registry - Implementation Guide

**Quick start guide for building the alpha registry**

---

## 📋 Table of Contents

- [Overview](#overview)
- [Phase 1: Proof of Concept](#phase-1-proof-of-concept)
- [Core API Endpoints](#core-api-endpoints)
- [Web Pages](#web-pages)
- [Key Models](#key-models)
- [Database Queries](#database-queries)
- [GitHub API Integration](#github-api-integration)
- [Phase 2: Complete Alpha](#phase-2-complete-alpha)

---

## Overview

**Goal:** Build a GitHub-backed formation registry using Tiny PHP framework.

**Strategy:** Start with 2 endpoints + 1 page to prove the concept, then expand.

**Tech Stack:**
- PHP 8.2+ with Tiny framework
- SQLite database
- GitHub API for storage
- Alpine.js + HTMX for UI

---

## Phase 1: Proof of Concept

**Build these first to validate the approach:**

1. ✅ `GET /api/formations/@:user/:name` - Lazy discovery API
2. ✅ `GET /@:user/:name` - Formation page (HTML)
3. ✅ `GET /` - Homepage with basic info

**This proves:**
- Lazy GitHub discovery works
- Database caching works
- Same model serves both API (JSON) and Web (HTML)
- GitHub API integration works

---

## Core API Endpoints

### 1. Formation Discovery (CRITICAL - Build First)

#### `GET /api/formations/@:user/:name`

**Purpose:** Return formation metadata with lazy GitHub discovery (the magic!)

**Logic:**

```
1. Check database for formation
   ├─ Found? → Return cached data
   └─ Not found?
      ├─ Query GitHub API for github.com/:user/muxi-:name
      ├─ Found on GitHub?
      │  ├─ Fetch: README, latest release, stars
      │  ├─ Cache in database
      │  └─ Return formation data
      └─ Not found? → 404 error
```

**Response (JSON):**

```json
{
  "user": "ranaroussi",
  "name": "customer-support",
  "latest_version": "1.2.0",
  "description": "AI-powered customer support with escalation",
  "github_repo": "ranaroussi/muxi-customer-support",
  "github_stars": 45,
  "total_downloads": 1234,
  "size_bytes": 29593,
  "download_url": "https://github.com/ranaroussi/muxi-customer-support/releases/download/v1.2.0/bundle.zip",
  "published_at": "2025-01-10T10:30:00Z"
}
```

**Implementation (routes/api.php):**

```php
Route::get('/api/formations/@:user/:name', function($user, $name) {
    try {
        $formation = Formation::findOrLazyFetch($user, $name);
        return json($formation);
    } catch (NotFoundException $e) {
        return json(['error' => 'Formation not found'], 404);
    } catch (Exception $e) {
        return json(['error' => 'Server error'], 500);
    }
});
```

**Used by CLI:** `muxi pull @user/formation` calls this first

---

### 2. Publish Notification

#### `POST /api/formations/publish`

**Purpose:** CLI notifies registry after publishing to GitHub

**Requires:** Authentication token (Bearer token in header)

**Request (JSON):**

```json
{
  "github_repo": "ranaroussi/muxi-customer-support",
  "version": "1.0.0",
  "formation_id": "customer-support"
}
```

**Logic:**

```
1. Verify auth token (from muxi login)
2. Fetch formation metadata from GitHub
3. Cache in database (or update if exists)
4. Return success
```

**Response:**

```json
{
  "status": "ok",
  "formation": {
    "user": "ranaroussi",
    "name": "customer-support",
    "version": "1.0.0",
    "url": "https://registry.muxi.org/@ranaroussi/customer-support"
  }
}
```

**Implementation:**

```php
Route::post('/api/formations/publish', function() {
    // Check auth
    $token = Auth::getBearerToken();
    $user = Auth::verifyToken($token);

    if (!$user) {
        return json(['error' => 'Unauthorized'], 401);
    }

    // Get data
    $data = json_decode(file_get_contents('php://input'), true);

    // Fetch from GitHub and cache
    $formation = Formation::fetchAndCache(
        $data['github_repo'],
        $data['version']
    );

    return json([
        'status' => 'ok',
        'formation' => $formation
    ]);
});
```

**Used by CLI:** After `muxi push` creates GitHub release

---

### 3. Search

#### `GET /api/search?q=customer+support&limit=20`

**Purpose:** Search formations by text query

**Parameters:**
- `q` - Search query (required)
- `limit` - Results limit (default: 20, max: 100)
- `sort` - Sort by: `relevance`, `downloads`, `stars`, `recent` (default: `relevance`)

**Response:**

```json
{
  "query": "customer support",
  "results": [
    {
      "user": "ranaroussi",
      "name": "customer-support",
      "description": "AI-powered customer support...",
      "latest_version": "1.2.0",
      "downloads": 1234,
      "stars": 45
    },
    // ... more results
  ],
  "total": 3
}
```

**Implementation:**

```php
Route::get('/api/search', function() {
    $query = $_GET['q'] ?? '';
    $limit = min((int)($_GET['limit'] ?? 20), 100);
    $sort = $_GET['sort'] ?? 'relevance';

    if (empty($query)) {
        return json(['error' => 'Query required'], 400);
    }

    $results = Formation::search($query, $limit, $sort);

    return json([
        'query' => $query,
        'results' => $results,
        'total' => count($results)
    ]);
});
```

**Used by CLI:** `muxi search "query"`

---

### 4. Download Tracking

#### `POST /api/formations/@:user/:name/:version/download`

**Purpose:** Track downloads for analytics (increments counter)

**No auth required** - anonymous tracking

**Request:** Empty POST

**Response:**

```json
{
  "status": "ok"
}
```

**Implementation:**

```php
Route::post('/api/formations/@:user/:name/:version/download', function($user, $name, $version) {
    Formation::recordDownload($user, $name, $version);
    return json(['status' => 'ok']);
});
```

**Used by CLI:** After `muxi pull` downloads bundle.zip

---

### 5. Authentication Callback

#### `GET /api/auth/callback?code=...&installation_id=...`

**Purpose:** Handle GitHub OAuth callback after app installation

**Flow:**

```
1. User runs: muxi login
2. Opens: https://github.com/apps/muxi-registry/installations/new
3. User installs app
4. GitHub redirects to: registry.muxi.org/auth/callback?code=ABC&installation_id=12345
5. Registry exchanges code for token
6. Creates user record (if new)
7. Generates CLI token
8. Shows success page with token
```

**Response (HTML page):**

```html
<div class="auth-success">
    <h1>✓ Authentication Successful</h1>
    <p>You're logged in as <strong>@ranaroussi</strong></p>

    <div class="token-box">
        <code id="token">mxr_abc123def456...</code>
        <button onclick="copyToken()">Copy Token</button>
    </div>

    <p>Return to your terminal to continue.</p>
</div>
```

**Implementation:**

```php
Route::get('/auth/callback', function() {
    $code = $_GET['code'] ?? null;
    $installationId = $_GET['installation_id'] ?? null;

    if (!$code) {
        return view('auth/error', ['message' => 'Missing code']);
    }

    // Exchange code for GitHub token
    $github = new GitHubAuth();
    $accessToken = $github->exchangeCode($code);

    // Get user info
    $githubUser = $github->getUser($accessToken);

    // Create/update user
    $user = User::createOrUpdate([
        'github_id' => $githubUser['id'],
        'github_username' => $githubUser['login'],
        'github_avatar' => $githubUser['avatar_url'],
        'github_installation_id' => $installationId
    ]);

    // Generate CLI token
    $token = Auth::generateToken($user->id);

    return view('auth/success', [
        'username' => $user->github_username,
        'token' => $token
    ]);
});
```

**Used by CLI:** Polls this endpoint or receives callback

---

## Web Pages

### 1. Formation Page (Build First)

#### `GET /@:user/:name`

**Purpose:** Display formation details with README

**Uses same logic as API** - `Formation::findOrLazyFetch()`

**Template (views/formations/show.php):**

```php
<!DOCTYPE html>
<html>
<head>
    <title>@<?= $formation->user ?>/<?= $formation->name ?> - MUXI Registry</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="formation-header">
            <h1>@<?= $formation->user ?>/<?= $formation->name ?></h1>
            <span class="version">v<?= $formation->latest_version ?></span>
        </div>

        <!-- Description -->
        <p class="description"><?= esc($formation->description) ?></p>

        <!-- Stats -->
        <div class="stats">
            <span>⬇ <?= number_format($formation->total_downloads) ?> pulls</span>
            <span>⭐ <?= $formation->github_stars ?> stars</span>
            <span>📦 <?= formatBytes($formation->size_bytes) ?></span>
            <span>🕒 <?= timeAgo($formation->published_at) ?></span>
        </div>

        <!-- Install Box -->
        <div class="install-box">
            <h3>🚀 Installation</h3>
            <pre><code>muxi pull @<?= $formation->user ?>/<?= $formation->name ?></code></pre>
            <button onclick="copyInstallCommand()">Copy</button>
        </div>

        <!-- Links -->
        <div class="links">
            <a href="https://github.com/<?= $formation->github_repo ?>" target="_blank">
                📦 View on GitHub
            </a>
            <a href="https://github.com/<?= $formation->github_repo ?>/issues" target="_blank">
                🐛 Report Issue
            </a>
        </div>

        <!-- README -->
        <div class="readme">
            <?= parseMarkdown($formation->readme_md) ?>
        </div>
    </div>

    <script>
        function copyInstallCommand() {
            const code = 'muxi pull @<?= $formation->user ?>/<?= $formation->name ?>';
            navigator.clipboard.writeText(code);
            alert('Copied to clipboard!');
        }
    </script>
</body>
</html>
```

**Implementation (routes/web.php):**

```php
Route::get('/@:user/:name', function($user, $name) {
    try {
        $formation = Formation::findOrLazyFetch($user, $name);
        return view('formations/show', compact('formation'));
    } catch (NotFoundException $e) {
        return view('errors/404', ['message' => 'Formation not found']);
    }
});
```

---

### 2. Homepage

#### `GET /`

**Purpose:** Landing page with recent formations and search

**Template (views/home.php):**

```php
<!DOCTYPE html>
<html>
<head>
    <title>MUXI Registry - Docker Hub for AI Formations</title>
</head>
<body>
    <div class="hero">
        <h1>MUXI Registry</h1>
        <p>Docker Hub for AI Formations</p>

        <!-- Search Box -->
        <form action="/search" method="get" class="search-form">
            <input type="text" name="q" placeholder="Search formations..." />
            <button type="submit">Search</button>
        </form>

        <!-- Quick Start -->
        <div class="quick-start">
            <h3>Get started in seconds</h3>
            <pre><code>muxi pull @muxi/customer-support</code></pre>
        </div>
    </div>

    <!-- Recent Formations -->
    <div class="container">
        <h2>Recently Added</h2>
        <div class="formation-grid">
            <?php foreach ($recent as $formation): ?>
                <div class="formation-card">
                    <h3>
                        <a href="/@<?= $formation->user ?>/<?= $formation->name ?>">
                            @<?= $formation->user ?>/<?= $formation->name ?>
                        </a>
                    </h3>
                    <p><?= $formation->description ?></p>
                    <div class="stats">
                        <span>⬇ <?= $formation->total_downloads ?></span>
                        <span>⭐ <?= $formation->github_stars ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
```

**Implementation:**

```php
Route::get('/', function() {
    $recent = Formation::recent(12);
    return view('home', compact('recent'));
});
```

---

### 3. Search Results Page

#### `GET /search?q=query`

**Template (views/formations/search.php):**

```php
<div class="container">
    <h1>Search Results for "<?= esc($_GET['q']) ?>"</h1>

    <?php if (empty($results)): ?>
        <p>No formations found. Try a different search term.</p>
    <?php else: ?>
        <p>Found <?= count($results) ?> formations</p>

        <div class="formation-list">
            <?php foreach ($results as $formation): ?>
                <div class="formation-item">
                    <h3>
                        <a href="/@<?= $formation->user ?>/<?= $formation->name ?>">
                            @<?= $formation->user ?>/<?= $formation->name ?>
                        </a>
                    </h3>
                    <p><?= $formation->description ?></p>
                    <code>muxi pull @<?= $formation->user ?>/<?= $formation->name ?></code>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
```

**Implementation:**

```php
Route::get('/search', function() {
    $query = $_GET['q'] ?? '';
    $results = empty($query) ? [] : Formation::search($query);
    return view('formations/search', compact('results', 'query'));
});
```

---

## Key Models

### Formation Model (app/models/Formation.php)

```php
<?php

class Formation {
    /**
     * The magic lazy discovery method
     * Checks DB first, then falls back to GitHub
     *
     * @param string $registryUser - Registry username (e.g., "muxi")
     * @param string $name - Formation name (e.g., "customer-support")
     */
    public static function findOrLazyFetch($registryUser, $name) {
        // 1. Try database first
        $formation = self::findInDatabase($registryUser, $name);

        if ($formation) {
            return $formation;
        }

        // 2. Resolve registry username to GitHub username
        $user = User::findByRegistryUsername($registryUser);

        if (!$user) {
            throw new NotFoundException("User not found");
        }

        // 3. Not in DB, try GitHub using GITHUB username
        // Example: @muxi (registry) → muxi-ai (GitHub) → muxi-ai/muxi-customer-support
        $github = new GitHub();
        $repoName = "{$user->github_username}/muxi-{$name}";

        try {
            $repo = $github->getRepo($repoName);
        } catch (Exception $e) {
            throw new NotFoundException("Formation not found");
        }

        // 3. Found on GitHub! Fetch metadata
        $readme = $github->getReadme($repoName);
        $latestRelease = $github->getLatestRelease($repoName);

        // 4. Parse formation.yaml from repo
        $formationYaml = $github->getFile($repoName, 'formation.yaml');
        $formationData = yaml_parse($formationYaml);

        // 5. Cache in database (using user_id, not username)
        $formationId = self::cache([
            'user_id' => $user->id,  // Links to users table
            'name' => $name,
            'description' => $formationData['formation']['description'] ?? $repo['description'],
            'latest_version' => ltrim($latestRelease['tag_name'], 'v'),
            'readme_md' => $readme,
            'github_repo' => $repoName,  // Full repo: muxi-ai/muxi-customer-support
            'github_stars' => $repo['stargazers_count'],
            'size_bytes' => $latestRelease['assets'][0]['size'] ?? 0,
            'published_at' => $latestRelease['published_at'],
            'last_synced_at' => date('Y-m-d H:i:s')
        ]);

        // 6. Return cached version
        return self::findInDatabase($registryUser, $name);
    }

    /**
     * Find formation in database by registry username
     */
    private static function findInDatabase($registryUser, $name) {
        $db = Database::getInstance();

        // Join with users table to resolve registry username
        $formation = $db->query(
            "SELECT f.*, u.registry_username, u.github_username, u.github_avatar
             FROM formations f
             JOIN users u ON f.user_id = u.id
             WHERE u.registry_username = ? AND f.name = ?",
            [$registryUser, $name]
        )->fetch();

        return $formation ?: null;
    }

    private static function cache($data) {
        $db = Database::getInstance();

        // Check if exists
        $existing = $db->query(
            "SELECT id FROM formations WHERE user_id = ? AND name = ?",
            [$data['user_id'], $data['name']]
        )->fetch();

        if ($existing) {
            // Update
            $db->query(
                "UPDATE formations SET
                    description = ?,
                    latest_version = ?,
                    readme_md = ?,
                    github_stars = ?,
                    size_bytes = ?,
                    published_at = ?,
                    last_synced_at = ?
                WHERE id = ?",
                [
                    $data['description'],
                    $data['latest_version'],
                    $data['readme_md'],
                    $data['github_stars'],
                    $data['size_bytes'],
                    $data['published_at'],
                    $data['last_synced_at'],
                    $existing['id']
                ]
            );
            return $existing['id'];
        } else {
            // Insert
            $db->query(
                "INSERT INTO formations (
                    user_id, name, description, latest_version,
                    readme_md, github_repo, github_stars, size_bytes,
                    published_at, last_synced_at, total_downloads
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)",
                [
                    $data['user_id'],
                    $data['name'],
                    $data['description'],
                    $data['latest_version'],
                    $data['readme_md'],
                    $data['github_repo'],
                    $data['github_stars'],
                    $data['size_bytes'],
                    $data['published_at'],
                    $data['last_synced_at']
                ]
            );
            return $db->lastInsertId();
        }
    }

    public static function search($query, $limit = 20, $sort = 'relevance') {
        $db = Database::getInstance();

        $orderBy = match($sort) {
            'downloads' => 'total_downloads DESC',
            'stars' => 'github_stars DESC',
            'recent' => 'published_at DESC',
            default => 'github_stars DESC' // relevance approximation
        };

        return $db->query(
            "SELECT * FROM formations
            WHERE name LIKE ? OR description LIKE ? OR readme_md LIKE ?
            ORDER BY $orderBy
            LIMIT ?",
            ["%$query%", "%$query%", "%$query%", $limit]
        )->fetchAll();
    }

    public static function recent($limit = 10) {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM formations ORDER BY published_at DESC LIMIT ?",
            [$limit]
        )->fetchAll();
    }

    public static function recordDownload($registryUser, $name, $version) {
        $db = Database::getInstance();

        // Increment total downloads (join with users to resolve username)
        $db->query(
            "UPDATE formations
            SET total_downloads = total_downloads + 1
            WHERE user_id = (
                SELECT id FROM users WHERE registry_username = ?
            ) AND name = ?",
            [$registryUser, $name]
        );

        // Also track version-specific downloads (if you have versions table)
        // ...
    }
}
```

---

### User Model (app/models/User.php)

```php
<?php

class User {
    /**
     * Find user by registry username
     */
    public static function findByRegistryUsername($username) {
        $db = Database::getInstance();

        return $db->query(
            "SELECT * FROM users WHERE registry_username = ?",
            [$username]
        )->fetch();
    }

    /**
     * Create or update user from GitHub OAuth data
     * Handles username mapping for reserved names
     */
    public static function createOrUpdate($githubData, $installationId = null) {
        $db = Database::getInstance();

        // Check if this GitHub account has a reserved registry username
        $reserved = $db->query(
            "SELECT registry_username FROM reserved_usernames WHERE github_username = ?",
            [$githubData['login']]
        )->fetch();

        // Use reserved name if exists, otherwise use GitHub username
        $registryUsername = $reserved
            ? $reserved['registry_username']  // e.g., "muxi" for muxi-ai
            : $githubData['login'];           // e.g., "ranaroussi"

        // Check if user exists
        $existing = $db->query(
            "SELECT id FROM users WHERE github_id = ?",
            [$githubData['id']]
        )->fetch();

        if ($existing) {
            // Update existing user
            $db->query(
                "UPDATE users SET
                    github_username = ?,
                    registry_username = ?,
                    github_avatar = ?,
                    last_seen_at = ?
                WHERE github_id = ?",
                [
                    $githubData['login'],
                    $registryUsername,
                    $githubData['avatar_url'],
                    date('Y-m-d H:i:s'),
                    $githubData['id']
                ]
            );

            $userId = $existing['id'];
        } else {
            // Create new user
            $db->query(
                "INSERT INTO users (
                    github_id, github_username, registry_username,
                    github_avatar, is_verified, created_at
                ) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $githubData['id'],
                    $githubData['login'],
                    $registryUsername,
                    $githubData['avatar_url'],
                    $reserved ? 1 : 0,  // Auto-verify reserved accounts
                    date('Y-m-d H:i:s')
                ]
            );

            $userId = $db->lastInsertId();
        }

        return self::findById($userId);
    }

    public static function findById($id) {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
    }
}
```

---

## GitHub API Integration

### GitHub API Helper (app/lib/GitHub.php)

```php
<?php

class GitHub {
    private $token;
    private $baseUrl = 'https://api.github.com';

    public function __construct($token = null) {
        $this->token = $token ?? getenv('GITHUB_TOKEN');
    }

    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Accept: application/vnd.github+json',
            'User-Agent: MUXI-Registry'
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

    public function getRepo($repo) {
        return $this->request("/repos/$repo");
    }

    public function getReadme($repo) {
        $data = $this->request("/repos/$repo/readme");
        // Content is base64 encoded
        return base64_decode($data['content']);
    }

    public function getLatestRelease($repo) {
        return $this->request("/repos/$repo/releases/latest");
    }

    public function getFile($repo, $path, $ref = 'main') {
        $data = $this->request("/repos/$repo/contents/$path?ref=$ref");
        return base64_decode($data['content']);
    }

    public function getReleases($repo) {
        return $this->request("/repos/$repo/releases");
    }
}
```

---

## Database Queries

### Common Queries

**Find formation by registry username:**

```sql
SELECT f.*, u.registry_username, u.github_username, u.github_avatar
FROM formations f
JOIN users u ON f.user_id = u.id
WHERE u.registry_username = ? AND f.name = ?
```

**Example:**

```sql
-- User requests: @muxi/customer-support
-- Query resolves: muxi → muxi-ai → github.com/muxi-ai/muxi-customer-support
```

**Search formations:**

```sql
SELECT * FROM formations
WHERE name LIKE ? OR description LIKE ? OR readme_md LIKE ?
ORDER BY github_stars DESC
LIMIT ?
```

**Recent formations:**

```sql
SELECT * FROM formations
ORDER BY published_at DESC
LIMIT ?
```

**Increment downloads:**

```sql
UPDATE formations
SET total_downloads = total_downloads + 1
WHERE user_id = (
    SELECT id FROM users WHERE registry_username = ?
) AND name = ?
```

**Cache formation:**

```sql
INSERT INTO formations (
    user_id, name, description, latest_version,
    readme_md, github_repo, github_stars, size_bytes,
    published_at, last_synced_at, total_downloads
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
ON CONFLICT(user_id, name) DO UPDATE SET
    description = excluded.description,
    latest_version = excluded.latest_version,
    readme_md = excluded.readme_md,
    github_stars = excluded.github_stars,
    size_bytes = excluded.size_bytes,
    last_synced_at = excluded.last_synced_at
```

**Resolve registry username to GitHub username:**

```sql
-- Check for reserved mapping first
SELECT u.github_username
FROM users u
WHERE u.registry_username = ?

-- Example: @muxi → muxi-ai
```

**Find user by GitHub username:**

```sql
SELECT * FROM users WHERE github_username = ?
```

---

## Phase 2: Complete Alpha

**After Phase 1 works, add:**

### Additional Endpoints
- [ ] `POST /api/auth/begin` - Initiate OAuth flow
- [ ] `GET /api/users/@:username` - User profile API
- [ ] `GET /api/formations/@:user/:name/versions` - List all versions

### Additional Pages
- [ ] User profile page (`/@:username`)
- [ ] Browse page (`/browse`)
- [ ] Auth success/error pages

### Features
- [ ] Markdown rendering (CommonMark library)
- [ ] Syntax highlighting in README
- [ ] Copy-to-clipboard functionality
- [ ] Pagination for search/browse
- [ ] Basic analytics dashboard (admin only)

### Polish
- [ ] CSS styling (Tailwind or custom)
- [ ] Mobile responsive
- [ ] Error pages (404, 500)
- [ ] Rate limiting
- [ ] Caching headers

---

## Testing Checklist

**Phase 1 Testing:**

- [ ] **Username Mapping**
  - [ ] Seed `reserved_usernames` table with `('muxi', 'muxi-ai')`
  - [ ] Login as `muxi-ai` GitHub user
  - [ ] Verify creates user with `registry_username = 'muxi'`
  - [ ] Visit `/@muxi` shows correct profile (not `/@muxi-ai`)

- [ ] **Formation Discovery**
  - [ ] Visit `/@muxi/customer-support` (doesn't exist in DB)
    - Should resolve: muxi → muxi-ai
    - Should fetch from `github.com/muxi-ai/muxi-customer-support`
    - Should cache in DB with correct user_id
    - Should display formation page with `@muxi` branding

- [ ] **Caching**
  - [ ] Visit `/@muxi/customer-support` again
    - Should serve from DB (fast)
    - README should render
    - GitHub link shows `muxi-ai/muxi-customer-support`

- [ ] **API Consistency**
  - [ ] Call `/api/formations/@muxi/customer-support`
    - Should return JSON
    - Same data as web page
    - `github_repo` shows `muxi-ai/muxi-customer-support`

- [ ] **Error Handling**
  - [ ] Visit `/@fake/doesntexist`
    - Should show 404

  - [ ] Visit `/@muxi/doesntexist`
    - User exists, formation doesn't
    - Should try GitHub, then 404

- [ ] **Search**
  - [ ] Search works
    - `/search?q=customer` finds formations
    - Results show `@muxi` (not `@muxi-ai`)

---

## Deployment Checklist

- [ ] Set `GITHUB_TOKEN` environment variable
- [ ] Create SQLite database from schema
- [ ] Set proper file permissions (DB writable)
- [ ] Configure web server (Apache/Nginx)
- [ ] Test on production domain
- [ ] Set up SSL/HTTPS

---

**Ready to build! Start with Phase 1, then expand.** 🚀
