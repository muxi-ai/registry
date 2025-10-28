# MUXI Registry - AI Agent Guide

**For AI coding assistants working on the MUXI Registry project**

---

## 🎯 Project Overview

### What is MUXI Registry?

**MUXI Registry is "Docker Hub for AI Formations"** - a lightweight discovery and distribution platform for MUXI AI agent formations.

> [!IMPORTANT]
> This is an important part of the [MUXI tech stack](../MUXI-ARCHITECTURE.md).

**Core Vision:**
- Developers can **instantly share** complete, working AI formations
- One command (`muxi pull @user/formation`) gets you a production-ready setup
- GitHub-backed storage (no heavy infrastructure)
- Lazy/retroactive discovery (any `muxi-*` repo is automatically discoverable)

**Key Innovation: Lazy Discovery**
```
User visits: registry.muxi.org/@user/formation
  ↓
Not in DB? → Fetch from GitHub (github.com/user/muxi-formation)
  ↓
Cache metadata → Display page
  ↓
Next visit: Instant (served from cache)
```

### Tech Stack

- **Backend:** PHP 8.2+ with [Tiny Framework](https://ranaroussi.github.io/tiny/)
- **Database:** SQLite (single file, easy deployment)
- **Frontend:** Alpine.js + HTMX
- **Storage:** GitHub (releases, repos)
- **Styling:** Tailwind CSS

---

## ✅ Phase 2 Complete (2025-10-28)

**Major Milestone**: Registry API is now functionally complete and ready for CLI development!

### Completed Features

**1. Pull Tracking Refactor**:
- ✅ Separated info requests from actual pulls with `?pull=true`
- ✅ Added `:version` syntax for version-specific requests
- ✅ Simplified tracking to daily `downloads` table only
- ✅ Version validation in database

**2. GitHub Helper Refactoring**:
- ✅ Converted from standalone class to Tiny helper (`tiny::github()`)
- ✅ Uses `tiny::http()` instead of direct curl
- ✅ Token management: `setToken()` / `clearToken()`
- ✅ All GitHub API operations centralized

**3. File Upload & Publish Flow**:
- ✅ POST /api/formations/publish with multipart/form-data
- ✅ ZIP extraction and validation
- ✅ formation.yaml parsing with field validation
- ✅ Auto-generate README if missing (basic template, TODO: LLM)
- ✅ Create/verify GitHub repositories
- ✅ Push files via GitHub Contents API
- ✅ Create releases with tags
- ✅ Upload ZIP as release asset
- ✅ Store metadata in formations + versions tables
- ✅ Automatic cleanup of temp files

### Key API Endpoints

```bash
# Info only (no tracking)
GET /api/formations/@user/name

# Specific version info
GET /api/formations/@user/name:1.2.0

# Actual pull (tracks download)
GET /api/formations/@user/name?pull=true

# Version-specific pull
GET /api/formations/@user/name:1.2.0?pull=true

# Publish formation (authenticated)
POST /api/formations/publish
Authorization: Bearer mxr_xxx
Content-Type: multipart/form-data
Body: file=@formation.zip, org=optional-org-name
```

### Stats
- **710 lines** in API controller
- **349 lines** in GitHub helper
- **3 commits** with comprehensive documentation
- **4 new docs**: API-IMPLEMENTATION.md, PULL-TRACKING-REFACTOR.md, PUBLISH-IMPLEMENTATION.md, IMPLEMENTATION-PLAN.md

### Architecture Shift

**Registry is now the gatekeeper**:
- CLI only needs `mxr_` token (simple authentication)
- Registry stores users' GitHub OAuth tokens securely
- Registry handles all GitHub operations (repo creation, releases, uploads)
- CLI becomes simpler: zip and upload

**Next Steps**: CLI development (`muxi pull`, `muxi push`, `muxi search`)!

---

## 🏗️ Architecture

### GitHub-Backed Model

```
CLI (muxi push)
  ↓
Registry creates repo: github.com/user/muxi-formation
  ↓
Push files + create release with bundle.zip
  ↓
Registry caches metadata (SQLite)
  ↓
Anyone can: muxi pull @user/formation
```

**Why GitHub-backed?**
- Zero infrastructure costs
- Native versioning (git tags)
- CDN distribution (releases)
- Familiar developer workflow
- Launch in days, not months

### Repository Naming Convention

| Formation Name | GitHub Repo | Registry URL |
|----------------|-------------|--------------|
| customer-support | `muxi-customer-support` | `@user/customer-support` |
| sentiment-bot | `muxi-sentiment-bot` | `@user/sentiment-bot` |

**Pattern:** All formation repos start with `muxi-` prefix

### URL Structure

```
/                              # Homepage
/@username                    # User profile (special routing via 404)
/@username/formation          # Formation page (special routing via 404)
/dashboard                    # User dashboard (requires auth)
/auth/callback                # GitHub OAuth callback
/search?q=query               # Search formations
```

**Special Routing:**
- URLs starting with `@` are caught by 404 controller
- 404 controller routes to `_profile` or `_formation` controllers
- These controllers handle the `@username` pattern

---

## 📂 Project Structure

```
registry/
├── website/
│   ├── tiny/                 # Tiny framework core
│   ├── app/
│   │   ├── controllers/      # Request handlers
│   │   │   ├── home.php      # Regular controller (auto-routed)
│   │   │   ├── _profile.php  # Special controller (NOT auto-routed)
│   │   │   ├── 404.php       # Handles @username routing
│   │   │   └── auth/
│   │   │       └── callback.php
│   │   ├── middleware/       # Pre-controller logic
│   │   │   └── auth.php
│   │   ├── views/            # HTML templates
│   │   │   ├── layouts/
│   │   │   │   └── default/
│   │   │   │       ├── open.php
│   │   │   │       └── close.php
│   │   │   ├── components/   # Reusable UI elements
│   │   │   │   └── Footer.php
│   │   │   └── home.php      # Page views
│   │   └── models/           # Business logic (future)
│   ├── html/
│   │   ├── index.php         # Entry point
│   │   └── static/           # CSS, JS, images
│   └── data/
│       └── registry.db       # SQLite database
└── docs/
    ├── ALPHA-PRD.md
    ├── IMPLEMENTATION-GUIDE.md
    └── AGENTS.md (this file)
```

---

## 🎨 Tiny Framework Patterns

### 1. Underscore Prefix Convention ⚠️

**CRITICAL: Files starting with `_` are NOT auto-routed**

```php
// app/controllers/_profile.php
// ❌ NOT accessible via /profile or /_profile
// ✅ Can ONLY be called: tiny::controller('_profile', true)
```

**Use cases:**
- Special routing controllers (profile pages via @username)
- Internal/helper controllers
- Controllers that should only be accessible programmatically

### 2. Auto-Routing Logic

```
URL: /                     → app/controllers/home.php
URL: /about                → app/controllers/about.php
URL: /auth/callback        → app/controllers/auth/callback.php
URL: /@username            → 404.php → _profile.php (special routing)
URL: /nonexistent          → app/controllers/404.php
```

**Path Resolution Order:**
1. `{controller}/{section}/{slug}`
2. `{controller}/{section}`
3. `{controller}/index`
4. `{controller}`
5. If none found → `404.php`

### 3. Controller Pattern

```php
<?php
// app/controllers/home.php

class Home extends TinyController
{
    public function get($request, $response)
    {
        // Set data for view
        tiny::data()->title = 'Welcome';
        tiny::data()->formations = Formation::recent(12);

        // Render view: app/views/home.php
        $response->render('home');
    }

    public function post($request, $response)
    {
        // Handle POST requests
        $data = $request->input();
        // ...
        return $response->json(['status' => 'ok']);
    }
}
```

### 4. Special Routing Pattern (404 Handler)

```php
<?php
// app/controllers/404.php

class Class404 extends TinyController
{
    public function get($request, $response)
    {
        $controller = tiny::router()->controller;

        // Route @username URLs
        if (str_starts_with($controller, '@')) {
            // Check if it's a user profile or formation
            $parts = explode('/', $controller);

            if (count($parts) === 1) {
                // @username → profile
                tiny::controller('_profile', true);
            } else {
                // @username/formation → formation page
                tiny::controller('_formation', true);
            }
        }

        // Otherwise show 404 page
        $response->render('errors/404');
    }
}
```

### 5. View Pattern with Layouts

```php
<?php
// app/views/home.php

// Open layout
tiny::layout()->default(
    title: 'MUXI Registry',
    emptyLayout: false
);
?>

<!-- Page content here -->
<h1>Welcome to MUXI Registry</h1>
<p>Discover AI formations...</p>

<?php
// Access data from controller
foreach (tiny::data()->formations as $formation) {
    echo "<div>{$formation->name}</div>";
}
?>

<?php
// Close layout
tiny::layout()->default('/');
?>
```

**Layout Parameters:**
- `title` - Page title (shown in `<title>` tag)
- `emptyLayout` - Set to `true` to skip header/footer
- `robots` - SEO robots meta tag
- `ogImage` - Open Graph image URL
- `scripts` - Additional JS files to load
- `styles` - Additional CSS files to load

### 6. Component Pattern

**Registration (app/views/components/Footer.php):**
```php
<?php
tiny::components()->register('Footer', function (...$props) {
    $year = $props['year'] ?? date('Y');

    return <<<EOF
    <footer>
        <p>&copy; {$year} MUXI Registry</p>
    </footer>
    EOF;
});
```

**Usage (in views or layouts):**
```php
<?php
tiny::components()->require('Footer');
tiny::components()->Footer(['year' => 2024]);
?>
```

### 7. Data Sharing Pattern

```php
// In controller
tiny::data()->username = 'ranaroussi';
tiny::data()->formations = [...];

// In view
echo tiny::data()->username;
foreach (tiny::data()->formations as $f) { ... }
```

### 8. User Authentication

```php
// In middleware (app/middleware/auth.php)
$user = User::findById($userId);
tiny::user($user);  // Set current user

// Access anywhere
if (tiny::user()) {
    echo tiny::user()->github_username;
    echo tiny::user()->registry_username;
}
```

### 9. Router Information

```php
tiny::router()->uri           // /auth/callback
tiny::router()->controller    // auth or @username
tiny::router()->section       // callback or formation-name
tiny::router()->permalink     // Full URL
tiny::router()->query         // Query parameters
```

### 10. Database Access

```php
// Using Tiny's DB wrapper
$user = tiny::db()->getOne('users', ['id' => 1]);
$formations = tiny::db()->getAll('formations', ['user_id' => 1]);

tiny::db()->insert('formations', ['name' => 'test', ...]);
tiny::db()->update('formations', ['name' => 'new'], ['id' => 1]);
tiny::db()->delete('formations', ['id' => 1]);

$lastId = tiny::db()->lastInsertId();
```

---

## 🗄️ Database Schema

### Key Tables

**users** - GitHub users and organizations
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,           -- GitHub username (e.g., muxi-ai)
  registry_username TEXT UNIQUE NOT NULL,  -- Display name (e.g., muxi)
  github_avatar TEXT,
  github_email TEXT,                       -- NULL for orgs
  github_type TEXT DEFAULT 'User',         -- 'User' or 'Organization'
  github_installation_id INTEGER,          -- NULL for orgs
  github_oauth_token TEXT,                 -- NULL for orgs
  is_verified BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);
```

**reserved_usernames** - Username mappings (e.g., @muxi → muxi-ai)
```sql
CREATE TABLE reserved_usernames (
  registry_username TEXT PRIMARY KEY,      -- muxi
  github_username TEXT NOT NULL,           -- muxi-ai
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**formations** - Cached formation metadata
```sql
CREATE TABLE formations (
  id INTEGER PRIMARY KEY,
  user_id INTEGER NOT NULL,                -- Owner (user or org)
  published_by_user_id INTEGER,            -- Who published it
  name TEXT NOT NULL,                      -- Without 'muxi-' prefix
  description TEXT,
  readme_md TEXT,                          -- Cached from GitHub
  latest_version TEXT,
  github_repo TEXT NOT NULL,               -- Full: user/muxi-formation
  github_stars INTEGER DEFAULT 0,
  total_downloads INTEGER DEFAULT 0,
  last_synced_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, name),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Username Resolution:**
```
Registry URL: @muxi/customer-support
  ↓
Query: SELECT * FROM users WHERE registry_username = 'muxi'
  ↓
Result: github_username = 'muxi-ai'
  ↓
GitHub Repo: github.com/muxi-ai/muxi-customer-support
```

---

## 🔑 Key Implementation Patterns

### Pattern 1: Lazy Discovery (THE MAGIC ✨)

**The Core Innovation of MUXI Registry**

```php
<?php
// app/models/Formation.php

class Formation
{
    public static function findOrLazyFetch($registryUser, $name)
    {
        // 1. Try database first (fast path)
        $formation = self::findInDatabase($registryUser, $name);
        if ($formation) {
            return $formation;
        }

        // 2. Resolve registry username to GitHub username
        $user = User::findByRegistryUsername($registryUser);
        if (!$user) {
            throw new NotFoundException("User not found");
        }

        // 3. Try GitHub (example: @muxi → muxi-ai → muxi-ai/muxi-formation)
        $github = new GitHub();
        $repoName = "{$user->github_username}/muxi-{$name}";

        try {
            $repo = $github->getRepo($repoName);
        } catch (Exception $e) {
            throw new NotFoundException("Formation not found");
        }

        // 4. Found on GitHub! Fetch metadata
        $readme = $github->getReadme($repoName);
        $latestRelease = $github->getLatestRelease($repoName);

        // 5. Cache in database
        self::cache([
            'user_id' => $user->id,
            'name' => $name,
            'description' => $repo['description'],
            'latest_version' => ltrim($latestRelease['tag_name'], 'v'),
            'readme_md' => $readme,
            'github_repo' => $repoName,
            'github_stars' => $repo['stargazers_count'],
            // ...
        ]);

        // 6. Return cached version
        return self::findInDatabase($registryUser, $name);
    }

    private static function findInDatabase($registryUser, $name)
    {
        return tiny::db()->query(
            "SELECT f.*, u.registry_username, u.github_username
             FROM formations f
             JOIN users u ON f.user_id = u.id
             WHERE u.registry_username = ? AND f.name = ?",
            [$registryUser, $name]
        )->fetch();
    }
}
```

**Why This Works:**
- First visit: Fetches from GitHub, caches in DB
- Subsequent visits: Instant (served from cache)
- No pre-registration needed
- Any `muxi-*` repo is automatically discoverable
- Registry can launch before formations exist!

### Pattern 2: Non-Routable Controllers

```php
<?php
// app/controllers/_profile.php
// NOT accessible via URL - called programmatically only

class Profile extends TinyController
{
    private $username;

    public function __construct()
    {
        // Extract username from @username route
        $this->username = substr(tiny::router()->controller, 1);
        tiny::data()->username = $this->username;
    }

    public function get($request, $response)
    {
        // Load user data using lazy discovery
        $user = User::findByRegistryUsername($this->username);

        if (!$user) {
            // Try GitHub (lazy discovery)
            $user = User::fetchFromGitHub($this->username);
        }

        if (!$user) {
            $response->status(404);
            return $response->render('errors/404');
        }

        tiny::data()->user = $user;
        tiny::data()->formations = Formation::byUser($user->id);

        $response->render('profile/index');
    }
}
```

### Pattern 3: Username Mapping

**Allows @muxi to map to muxi-ai GitHub organization**

```php
<?php
// app/models/User.php

class User
{
    public static function createOrUpdate($githubData, $installationId = null)
    {
        // Check for reserved username mapping
        $reserved = tiny::db()->getOne('reserved_usernames', [
            'github_username' => $githubData['login']
        ]);

        // Use reserved name if exists, otherwise use GitHub username
        $registryUsername = $reserved
            ? $reserved['registry_username']  // muxi-ai → muxi
            : $githubData['login'];           // ranaroussi → ranaroussi

        // Create or update user
        $existing = tiny::db()->getOne('users', [
            'github_id' => $githubData['id']
        ]);

        if ($existing) {
            tiny::db()->update('users', [
                'github_username' => $githubData['login'],
                'registry_username' => $registryUsername,
                'github_avatar' => $githubData['avatar_url'],
                'last_seen_at' => date('Y-m-d H:i:s')
            ], ['id' => $existing['id']]);

            return self::findById($existing['id']);
        }

        tiny::db()->insert('users', [
            'github_id' => $githubData['id'],
            'github_username' => $githubData['login'],
            'registry_username' => $registryUsername,
            'github_avatar' => $githubData['avatar_url'],
            'is_verified' => $reserved ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return self::findById(tiny::db()->lastInsertId());
    }
}
```

### Pattern 4: GitHub API Integration

```php
<?php
// app/lib/GitHub.php

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
}
```

---

## 🚀 Common Tasks

### Task 1: Add a New Page

**1. Create Controller:**
```php
<?php
// app/controllers/browse.php

class Browse extends TinyController
{
    public function get($request, $response)
    {
        $sort = $request->query('sort', 'trending');
        $formations = Formation::browse($sort);

        tiny::data()->formations = $formations;
        tiny::data()->sort = $sort;

        $response->render('browse');
    }
}
```

**2. Create View:**
```php
<?php
// app/views/browse.php

tiny::layout()->default(title: 'Browse Formations', emptyLayout: false);
?>

<h1>Browse Formations</h1>

<div class="formations-grid">
    <?php foreach (tiny::data()->formations as $formation): ?>
        <div class="formation-card">
            <h3>@<?= $formation->user ?>/<?= $formation->name ?></h3>
            <p><?= $formation->description ?></p>
        </div>
    <?php endforeach; ?>
</div>

<?php tiny::layout()->default('/'); ?>
```

**3. Access:** `https://registry.muxi.org/browse`

### Task 2: Add API Endpoint

```php
<?php
// app/controllers/api/formations.php

class ApiFormations extends TinyController
{
    public function get($request, $response)
    {
        // GET /api/formations/@:user/:name
        $user = $request->param('user');
        $name = $request->param('name');

        try {
            $formation = Formation::findOrLazyFetch($user, $name);
            return $response->json($formation);
        } catch (NotFoundException $e) {
            return $response->json(['error' => 'Formation not found'], 404);
        }
    }

    public function post($request, $response)
    {
        // POST /api/formations/publish
        // Requires authentication
        $token = $request->header('Authorization');
        $user = Auth::verifyToken($token);

        if (!$user) {
            return $response->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->input();
        $formation = Formation::fetchAndCache(
            $data['github_repo'],
            $data['version']
        );

        return $response->json([
            'status' => 'ok',
            'formation' => $formation
        ]);
    }
}
```

### Task 3: Add Middleware

```php
<?php
// app/middleware/auth.php

class AuthMiddleware
{
    private $allowedPaths = [
        '/',
        '/search',
        '/browse',
        // API endpoints (no auth required for read)
        '/api/formations',
    ];

    public function handle(): void
    {
        // Skip auth for allowed paths
        $path = tiny::router()->uri;
        foreach ($this->allowedPaths as $allowed) {
            if (str_starts_with($path, $allowed)) {
                return;
            }
        }

        // Check session
        $cookie = tiny::cookie('user');
        if (!$cookie->exists) {
            tiny::redirect('/auth/login');
            return;
        }

        // Load user
        $userId = decrypt($cookie->data['hash']);
        $user = tiny::db()->getOne('users', ['id' => $userId]);

        if (!$user) {
            tiny::redirect('/auth/login');
            return;
        }

        tiny::user($user);
    }
}
```

**Register middleware:**
```php
// app/middleware.php
tiny::middleware('auth');
```

### Task 4: Add Component

```php
<?php
// app/views/components/FormationCard.php

tiny::components()->register('FormationCard', function (...$props) {
    $formation = $props['formation'] ?? null;
    if (!$formation) return '';

    $showStats = $props['showStats'] ?? true;

    return <<<EOF
    <div class="formation-card">
        <h3>
            <a href="/@{$formation->user}/{$formation->name}">
                @{$formation->user}/{$formation->name}
            </a>
        </h3>
        <p>{$formation->description}</p>

        {$showStats ? "
        <div class='stats'>
            <span>⬇ {$formation->total_downloads} pulls</span>
            <span>⭐ {$formation->github_stars} stars</span>
        </div>
        " : ''}
    </div>
    EOF;
});
```

**Usage:**
```php
<?php
tiny::components()->require('FormationCard');
tiny::components()->FormationCard([
    'formation' => $formation,
    'showStats' => true
]);
?>
```

---

## 🐛 Common Gotchas

### 1. Underscore Files Are Not Routed ⚠️

```php
// ❌ WRONG: Trying to access /_profile
// This will 404 even if file exists

// ✅ CORRECT: Call from 404 controller
tiny::controller('_profile', true);
```

### 2. Class Names Are Transformed

```php
// File: 404.php → Class: Class404
// File: auth/callback.php → Class: AuthCallback
// File: _profile.php → Class: Profile
```

### 3. Layout Must Be Opened AND Closed

```php
// ❌ WRONG: Missing close
tiny::layout()->default(title: 'Test');
<h1>Content</h1>

// ✅ CORRECT
tiny::layout()->default(title: 'Test', emptyLayout: false);
<h1>Content</h1>
tiny::layout()->default('/');
```

### 4. Middleware Runs Before Controllers

```php
// Middleware executes first
// Can redirect before controller runs
// Can set tiny::user() for controller to access
```

### 5. Database Returns Arrays, Not Objects

```php
// getOne returns associative array
$user = tiny::db()->getOne('users', ['id' => 1]);
echo $user['github_username'];  // ✅ Correct
echo $user->github_username;    // ❌ Wrong
```

To use objects, convert or use a model wrapper.

---

## 📝 Code Style Guide

### PHP

```php
<?php
// PSR-12 style
// Opening PHP tag always on first line

class MyController extends TinyController
{
    // 4 spaces indentation
    public function get($request, $response)
    {
        // Method body
    }
}

// Array syntax
$data = [
    'key' => 'value',
    'items' => [1, 2, 3]
];

// String interpolation in double quotes
echo "Hello, {$user['name']}!";

// Heredoc for multi-line HTML
return <<<EOF
<div>
    <p>Content</p>
</div>
EOF;
```

### HTML/Views

```php
<?php tiny::layout()->default(title: 'Page', emptyLayout: false); ?>

<!-- Semantic HTML -->
<article>
    <header>
        <h1>Title</h1>
    </header>

    <section>
        <!-- Content -->
    </section>

    <footer>
        <!-- Meta info -->
    </footer>
</article>

<!-- Escape output -->
<?php echo htmlspecialchars($userInput); ?>

<!-- Short echo syntax for variables -->
<?= $safeVariable ?>

<?php tiny::layout()->default('/'); ?>
```

---

## 🔍 Quick Reference

### Tiny Framework Methods

```php
// Data
tiny::data()->key = 'value'
tiny::data()->key

// User
tiny::user($user)
tiny::user()
tiny::user()->property

// Router
tiny::router()->uri
tiny::router()->controller
tiny::router()->section
tiny::router()->query

// Database
tiny::db()->getOne('table', ['id' => 1])
tiny::db()->getAll('table', ['status' => 'active'])
tiny::db()->insert('table', $data)
tiny::db()->update('table', $data, ['id' => 1])
tiny::db()->delete('table', ['id' => 1])
tiny::db()->lastInsertId()

// URLs
tiny::homeURL('/path')
tiny::staticURL('/css/file.css')

// Layouts
tiny::layout()->default(...)
tiny::layout()->props('key')

// Components
tiny::components()->require('Name')
tiny::components()->ComponentName()

// Cache
tiny::cache()->get('key')
tiny::cache()->set('key', 'value', 3600)
tiny::cache()->remember('key', 3600, fn() => ...)

// Redirects
tiny::redirect('/path')

// Controllers
tiny::controller('file', $die = false)
tiny::render('view', $die = false)
```

### Common Queries

```sql
-- Find formation by registry username
SELECT f.*, u.registry_username, u.github_username
FROM formations f
JOIN users u ON f.user_id = u.id
WHERE u.registry_username = ? AND f.name = ?

-- Search formations
SELECT * FROM formations
WHERE name LIKE ? OR description LIKE ?
ORDER BY github_stars DESC
LIMIT ?

-- Recent formations
SELECT * FROM formations
ORDER BY published_at DESC
LIMIT ?

-- Increment downloads
UPDATE formations
SET total_downloads = total_downloads + 1
WHERE user_id = (
    SELECT id FROM users WHERE registry_username = ?
) AND name = ?
```

---

## 📚 Further Reading

- **ALPHA-PRD.md** - Product vision, user flows, architecture
- **IMPLEMENTATION-GUIDE.md** - Detailed implementation steps, models, endpoints
- **TINY_FRAMEWORK_NOTES.md** - Framework deep-dive, patterns, gotchas
- **VIEWS_LAYOUTS_COMPONENTS.md** - View system guide, examples
- **Tiny Framework Docs** - `/website/tiny/docs/`

---

## 🎯 Development Workflow

### Starting Work

1. **Read the task requirements**
2. **Check which files are involved** (controllers, views, models)
3. **Review related patterns** in this guide
4. **Implement following Tiny conventions**
5. **Test the flow end-to-end**

### Common Development Cycle

```bash
# 1. Edit files
# 2. Refresh browser (no build step!)
# 3. Check PHP errors in browser/logs
# 4. Iterate

# Database changes
sqlite3 website/data/registry.db < schema.sql

# View logs (if using PHP built-in server)
php -S localhost:8000 -t website/html
```

### Testing Checklist

- [ ] Page loads without PHP errors
- [ ] Layout renders correctly (header/footer)
- [ ] Data displays correctly
- [ ] Links work
- [ ] Authentication flow works (if applicable)
- [ ] Database queries execute properly
- [ ] Responsive on mobile

---

## 💡 Pro Tips for AI Agents

1. **Always use underscore prefix** for non-routable controllers
2. **Remember username mapping** (@muxi → muxi-ai)
3. **Lazy discovery is the core pattern** - cache on first request
4. **Views need layout open/close** - don't forget!
5. **Database returns arrays** - convert to objects if needed
6. **Middleware runs before controllers** - auth happens here
7. **Special routing via 404** - handle @username patterns
8. **Check existing patterns** before inventing new ones

---

**This guide is a living document. Update it as you learn more patterns and conventions!**
