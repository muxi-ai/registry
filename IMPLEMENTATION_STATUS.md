# MUXI Registry - Implementation Status

**Last Updated:** 2025-10-27  
**Architecture:** Tiny PHP Framework + SQLite

---

## 📋 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [What's Implemented](#whats-implemented)
3. [What's Still Needed](#whats-still-needed)
4. [Code Structure](#code-structure)
5. [Authentication Flow](#authentication-flow)
6. [Key Files Reference](#key-files-reference)

---

## Architecture Overview

### The Tiny Framework Pattern

MUXI Registry is built on the **Tiny PHP Framework**, which follows a convention-over-configuration MVC architecture:

**Request Lifecycle:**
```
HTTP Request
    ↓
Router (URL → Controller mapping)
    ↓
Middleware (AuthMiddleware - checks authentication)
    ↓
Controller (coordinates logic)
    ↓
Model (business logic, database operations)
    ↓
View (renders HTML)
    ↓
HTTP Response
```

### Key Conventions

1. **Auto-Routing:**
   - `/` → `app/controllers/home.php`
   - `/auth` → `app/controllers/auth/index.php`
   - `/auth/callback` → `app/controllers/auth/callback.php`
   - `/@username` → Special handling via 404 catch-all

2. **Controller Methods:**
   - `get($request, $response)` - Handles GET requests
   - `post($request, $response)` - Handles POST requests
   - `patch($request, $response)` - Handles PATCH requests
   - `delete($request, $response)` - Handles DELETE requests

3. **View Rendering:**
   - `$response->render('view/path')` - Renders `app/views/view/path.php`
   - `tiny::render()` - Renders default view matching controller path

4. **Model Access:**
   - `tiny::model('user')` - Loads `app/models/user.php`

5. **Middleware:**
   - Defined in `app/middleware.php`
   - Runs before every controller
   - Handles authentication, CSRF, etc.

---

## What's Implemented

### ✅ 1. User Authentication (GitHub OAuth + App)

**Status:** COMPLETE

**Components:**
- OAuth initiation (`/auth` → GitHub)
- OAuth callback handler (`/auth/callback`)
- GitHub user fetching (profile + email)
- User creation/update in database
- Session management (encrypted cookies)
- Installation flow handling

**How it works:**

1. **User initiates login:**
   ```
   User clicks "Login with GitHub"
     → Redirects to /auth (auth/index.php)
     → Generates CSRF state token
     → Redirects to GitHub OAuth:
       github.com/login/oauth/authorize?
         client_id=...
         &scope=public_repo,user:email
         &state=CSRF_TOKEN
   ```

2. **GitHub redirects back:**
   ```
   GitHub authorizes user
     → Redirects to /auth/callback?code=ABC&installation_id=12345&state=TOKEN
     → Validates CSRF token
     → Exchanges code for OAuth access token
     → Fetches user profile from GitHub
     → Creates/updates user in database
     → Encrypts OAuth token and stores it
     → Creates session cookie (encrypted user ID)
   ```

3. **Installation handling:**
   ```
   If installation_id is present:
     → User installed app during OAuth
     → Store installation_id in database
     → Redirect to dashboard
   
   If installation_id is NOT present:
     → User only did OAuth (no app install)
     → Redirect to /auth/install page
     → Show "Install GitHub App" prompt
   ```

**Files:**
- **Controllers:**
  - `app/controllers/auth/index.php` - OAuth initiation
  - `app/controllers/auth/callback.php` - OAuth callback handler
  - `app/controllers/auth/login.php` - Redirect to /auth
  - `app/controllers/auth/install.php` - Show install prompt
  - `app/controllers/auth/error.php` - Error display
  
- **Model:**
  - `app/models/user.php` - User CRUD operations
  
- **Views:**
  - `app/views/auth/install.php` - Install prompt UI
  - `app/views/auth/error.php` - Error display UI

**Database Fields Populated:**
```sql
users:
  ✅ github_id
  ✅ github_username (e.g., "ranaroussi")
  ✅ registry_username (e.g., "ranaroussi" or "muxi" from reserved)
  ✅ github_avatar
  ✅ github_email
  ✅ github_type (User or Organization)
  ✅ github_installation_id (nullable, set when app installed)
  ✅ github_oauth_token (encrypted)
  ✅ first_name
  ✅ last_name
  ✅ company
  ✅ bio
  ✅ twitter_username
  ✅ is_verified
  ✅ created_at
  ✅ last_seen_at
```

---

### ✅ 2. Session Management

**Status:** COMPLETE

**Components:**
- Encrypted cookie storage
- Session creation/destruction
- User session validation
- Automatic user data loading

**How it works:**

**Cookie Structure:**
```json
{
  "hash": "encrypted_user_id"
}
```

**Session Flow:**
```
1. User logs in
   → createOrUpdateUser() creates user record
   → setSession(userId) encrypts user ID
   → Sets "user" cookie with encrypted hash

2. User makes request
   → AuthMiddleware reads cookie
   → Decrypts hash to get user ID
   → Loads full user data from database
   → Sets tiny::user() with complete user object
   → Caches user data (1 hour TTL)

3. User logs out
   → destroySession() deletes cookie
   → User data cleared
```

**Files:**
- `app/models/user.php::setSession()`
- `app/models/user.php::getSession()`
- `app/models/user.php::destroySession()`
- `app/models/user.php::encryptUserHash()`
- `app/models/user.php::decryptUserHash()`

---

### ✅ 3. Authentication Middleware

**Status:** COMPLETE

**Components:**
- Path-based access control
- Web authentication (cookie-based)
- API authentication (Bearer token)
- Rate limiting for API requests
- Automatic user data loading

**How it works:**

**Access Control Modes:**

```php
// Current mode: "disallowed" (whitelist protected paths)
// Protected paths: account/*
// All other paths are public

// Alternative mode: "allowed" (whitelist public paths)
// Public paths: auth, api, webhooks, rpc, 404
// All other paths require authentication
```

**Request Flow:**
```
1. Every HTTP request hits AuthMiddleware first

2. Middleware checks path:
   → /auth/* → Public (ALLOWED)
   → /api/* → API auth required (Bearer token)
   → /* → Check ACCESS_MODE setting

3. For web requests:
   → Reads "user" cookie
   → Decrypts hash to get user ID
   → Validates user exists in database
   → Loads full user data (with caching)
   → Sets tiny::user() globally

4. For API requests:
   → Reads Authorization header
   → Decrypts Bearer token to get user ID
   → Validates user exists
   → Rate limits requests (10/sec, 1000/10min)
   → Sets tiny::user() globally

5. If authentication fails:
   → Web: Redirect to /auth/login
   → API: Return 401 JSON error
```

**Files:**
- `app/middleware/auth.php` - Main middleware class
- `app/middleware.php` - Middleware registration

---

### ✅ 4. Profile URLs (@ Catch-All)

**Status:** COMPLETE

**Components:**
- 404 handler with special routing
- Profile controller
- Username extraction from URL

**How it works:**

**URL Pattern:** `/@username`

**Flow:**
```
1. User visits /@ranaroussi

2. Router doesn't find controller
   → Triggers 404 handler
   → app/controllers/404.php::get()

3. 404 handler checks URL:
   → if (starts_with('@')) {
       → Route to _profile controller
       → tiny::controller('_profile', true)
     }

4. Profile controller (_profile.php):
   → Extracts username from route
   → controller = "@ranaroussi"
   → username = substr(controller, 1) = "ranaroussi"
   → Sets tiny::data()->username
   → Renders app/views/profile/index.php

5. View displays:
   → "Profile: @ranaroussi"
   → (Currently just a placeholder)
```

**Files:**
- `app/controllers/404.php` - Catches all 404s, routes @ URLs
- `app/controllers/_profile.php` - Handles profile rendering
- `app/views/profile/index.php` - Profile page UI

**Important:** Profile controller starts with underscore (`_profile`) which means:
- ✅ Can be called programmatically: `tiny::controller('_profile', true)`
- ❌ Cannot be accessed directly via URL: `/profile` or `/_profile` won't work
- This prevents direct access and only allows routing through 404 handler

---

### ✅ 5. Home Page

**Status:** BASIC IMPLEMENTATION

**Components:**
- Simple home page controller
- Basic welcome view
- User detection (shows username if logged in)

**Files:**
- `app/controllers/home.php`
- `app/views/home.php`

---

### ✅ 6. Database Schema

**Status:** COMPLETE (for Phase 1)

**Tables:**
- ✅ `users` - User accounts (including orgs)
- ✅ `reserved_usernames` - Username mappings
- ✅ `formations` - Formation metadata
- ✅ `versions` - Formation versions
- ✅ `formation_stats` - Component statistics
- ✅ `tokens` - CLI authentication tokens
- ✅ `formations_fts` - Full-text search
- ✅ `formations_spellfix` - Fuzzy search

**File:** `database/schema.sql`

---

## What's Still Needed

### 🚧 7. Formation Discovery & Display

**Priority:** HIGH  
**Status:** NOT STARTED

**What needs to be built:**

1. **Formation Page Controller** (`app/controllers/_formation.php`)
   ```php
   // URL: /@username/formation-name
   // Similar to profile controller
   // Extract username and formation name from route
   ```

2. **Formation Model** (`app/models/formation.php`)
   ```php
   // Core methods:
   - findOrLazyFetch($username, $formationName)
   - fetchFromGitHub($githubRepo)
   - cacheFormation($formationData)
   - getVersions($formationId)
   - recordDownload($formationId, $version)
   - search($query, $filters)
   ```

3. **Formation View** (`app/views/formation/show.php`)
   ```html
   - Header with name, version, stats
   - Installation instructions
   - Component breakdown
   - Links to GitHub
   - Rendered README
   - Version history
   ```

4. **404 Handler Update**
   ```php
   // Enhance to handle both:
   // /@username → profile
   // /@username/formation → formation
   
   if (str_starts_with($controller, '@')) {
       if (str_contains($controller, '/')) {
           // Has slash → formation page
           // Use _formation so it's not directly accessible
           tiny::controller('_formation', true);
       } else {
           // No slash → profile page
           tiny::controller('_profile', true);
       }
   }
   ```

**Note:** Both `_profile` and `_formation` controllers should start with underscore to prevent direct URL access. They should only be accessible through the 404 routing mechanism.

**Dependencies:**
- GitHub API integration helper
- Markdown parser for README
- Formation metadata cache system

---

### 🚧 8. Browse & Search

**Priority:** HIGH  
**Status:** NOT STARTED

**What needs to be built:**

1. **Browse Controller** (`app/controllers/browse.php`)
   ```php
   - List all formations
   - Pagination
   - Sorting (trending, newest, most pulls, stars)
   - Filtering by tags/categories (future)
   ```

2. **Search Controller** (`app/controllers/search.php`)
   ```php
   - Text search across formations
   - Use FTS5 + Spellfix1 for fuzzy matching
   - Highlight search terms
   - Pagination
   ```

3. **Views:**
   - `app/views/browse.php` - Browse grid/list
   - `app/views/search.php` - Search results

---

### 🚧 9. API Endpoints

**Priority:** HIGH  
**Status:** NOT STARTED

**What needs to be built:**

All API endpoints in `app/controllers/api/`:

1. **`api/formations.php`**
   ```php
   GET /api/formations/@:user/:name
   - Return formation metadata (JSON)
   - Lazy discovery if not in DB
   ```

2. **`api/formations/publish.php`**
   ```php
   POST /api/formations/publish
   - CLI notification of new publish
   - Requires Bearer token authentication
   - Validate formation metadata
   - Cache in database
   ```

3. **`api/formations/download.php`**
   ```php
   POST /api/formations/@:user/:name/:version/download
   - Record download stat (anonymous)
   - Increment counters
   ```

4. **`api/search.php`**
   ```php
   GET /api/search?q=query
   - Search formations
   - Return JSON results
   ```

5. **`api/users.php`**
   ```php
   GET /api/users/@:username
   - Return user profile + formations
   - JSON format
   ```

**Note:** API authentication already handled by AuthMiddleware (Bearer token)

---

### 🚧 10. GitHub Integration Helper

**Priority:** HIGH  
**Status:** NOT STARTED

**What needs to be built:**

Create `app/helpers/github.php`:

```php
class GitHubHelper {
    // API calls using user's OAuth token
    
    - getRepo($owner, $repo, $token)
    - getReadme($owner, $repo, $token)
    - getLatestRelease($owner, $repo, $token)
    - getReleases($owner, $repo, $token)
    - getFile($owner, $repo, $path, $token)
    - createRepo($name, $description, $token)
    - createRelease($owner, $repo, $tag, $body, $token)
    - uploadReleaseAsset($releaseId, $filePath, $token)
    
    // Handle rate limiting
    // Handle API errors
    // Cache responses where appropriate
}
```

**Usage:**
```php
$github = new GitHubHelper();
$oauthToken = tiny::model('user')->getGitHubAccessTokenByUserId($userId);
$repo = $github->getRepo('ranaroussi', 'muxi-customer-support', $oauthToken);
```

---

### 🚧 11. Formation Model (Full Implementation)

**Priority:** HIGH  
**Status:** NOT STARTED

**Location:** `app/models/formation.php`

**Key Methods Needed:**

```php
class FormationModel extends TinyModel {
    
    // Discovery
    public function findOrLazyFetch($registryUsername, $formationName);
    public function findInDatabase($registryUsername, $formationName);
    public function resolveRegistryUsername($registryUsername);
    
    // GitHub Integration
    public function fetchFromGitHub($githubRepo, $userToken);
    public function cacheFormation($formationData);
    public function syncFromGitHub($formationId, $userToken);
    
    // Queries
    public function getFormation($registryUsername, $formationName);
    public function getVersions($formationId);
    public function getLatestVersion($formationId);
    public function getUserFormations($userId);
    
    // Statistics
    public function recordDownload($formationId, $version);
    public function getStats($formationId);
    
    // Search
    public function search($query, $options = []);
    public function searchWithFuzzy($query);
    public function recent($limit = 20);
    public function trending($limit = 20);
    
    // Publishing (for API)
    public function registerFormation($githubRepo, $version, $publisherUserId);
    public function createOrgUser($githubOrgName);
}
```

---

### 🚧 12. CLI Token Management

**Priority:** MEDIUM  
**Status:** PARTIAL

**What exists:**
- `tokens` table in database
- Token generation logic in auth flow (commented out?)

**What needs to be built:**

1. **Token Generation:**
   ```php
   // In auth callback or install completion
   $cliToken = $this->generateCliToken($userId);
   // Format: mxr_[encrypted_data]
   ```

2. **Token Storage View:**
   - Show token to user ONCE after auth
   - "Copy to clipboard" button
   - Warning: "This token will only be shown once"

3. **Token Validation API:**
   ```php
   // Already handled by AuthMiddleware
   // Bearer token → decrypt → get user_id
   // Use for CLI authentication
   ```

4. **Token Management Page:**
   - List user's tokens
   - Revoke tokens
   - Create new tokens
   - Last used timestamp

---

### 🚧 13. Organization Support

**Priority:** MEDIUM  
**Status:** SCHEMA READY

**What's ready:**
- Database schema supports orgs
- `github_type` field (User/Organization)
- `published_by_user_id` tracking

**What needs to be built:**

1. **Organization Discovery:**
   ```php
   // When formation published to org:
   - Check if org exists in users table
   - If not, create "ghost" user record:
     - github_type = 'Organization'
     - github_oauth_token = NULL
     - github_installation_id = NULL
   - Link formation to org user_id
   - Store publisher in published_by_user_id
   ```

2. **Permission Checking:**
   ```php
   // Verify user can publish to org
   - Use user's OAuth token
   - Check GitHub API for org membership
   - Verify repo creation permission
   ```

3. **Org Profile Pages:**
   - Similar to user profiles
   - Show org formations
   - Show org members (from GitHub API)
   - Indicate it's an organization

---

### 🚧 14. Markdown Rendering

**Priority:** HIGH  
**Status:** NOT STARTED

**What needs to be built:**

Install and integrate markdown parser:

```bash
composer require league/commonmark
```

Create helper in `app/helpers/markdown.php`:

```php
function renderMarkdown($markdown) {
    $converter = new CommonMark\MarkdownConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]);
    return $converter->convert($markdown);
}
```

Use in formation view:
```php
<div class="readme">
    <?php echo renderMarkdown($formation->readme_md); ?>
</div>
```

---

### 🚧 15. Reserved Usernames System

**Priority:** LOW  
**Status:** SCHEMA READY

**What's ready:**
- `reserved_usernames` table
- Example mappings (muxi → muxi-ai)

**What needs to be built:**

1. **Username Resolution:**
   ```php
   // In FormationModel::findOrLazyFetch()
   
   // Check if username is reserved
   $reserved = DB::getOne('reserved_usernames', [
       'registry_username' => $registryUsername
   ]);
   
   $githubUsername = $reserved 
       ? $reserved['github_username']  // muxi → muxi-ai
       : $registryUsername;             // ranaroussi → ranaroussi
   ```

2. **Admin Interface:**
   - Add/remove reserved mappings
   - Verify GitHub account ownership
   - Set verified badges

---

### 🚧 16. Error Pages

**Priority:** LOW  
**Status:** PARTIAL

**What exists:**
- `app/controllers/404.php` (routes @ URLs)
- `app/views/404.php`
- `app/views/500.php`

**What needs enhancement:**
- Better 404 page design
- Suggestions for similar formations
- Search box on 404 page

---

### 🚧 17. User Dashboard

**Priority:** MEDIUM  
**Status:** NOT STARTED

**What needs to be built:**

Controller: `app/controllers/dashboard.php`

```php
// Show user's:
- Published formations
- Installation status
- CLI tokens
- Profile settings
- GitHub integration status
```

---

## Code Structure

### Current File Count: 41 PHP files in `/app`

```
/app
├── controllers/ (12 files)
│   ├── home.php
│   ├── 404.php
│   ├── _profile.php
│   ├── auth/
│   │   ├── index.php (OAuth start)
│   │   ├── callback.php (OAuth callback)
│   │   ├── login.php (redirect)
│   │   ├── install.php (install prompt)
│   │   └── error.php (error display)
│   ├── rpc/
│   ├── webhooks/
│   ├── sitemap.xml.php
│   ├── llm.txt.php
│   └── test-scheduler.php
│
├── models/ (2 files)
│   ├── user.php ✅ COMPLETE
│   └── _example.php
│
├── views/ (18 files)
│   ├── home.php
│   ├── 404.php
│   ├── 500.php
│   ├── unauthorized.php
│   ├── auth/
│   │   ├── install.php
│   │   └── error.php
│   ├── profile/
│   │   └── index.php
│   ├── components/
│   │   └── (8 component files)
│   └── layouts/
│       └── default/
│           ├── open.php
│           └── close.php
│
├── middleware/ (2 files)
│   ├── auth.php ✅ COMPLETE
│   └── version.php
│
├── middleware.php (registers middleware)
├── common.php (common functions)
└── scheduler.php (job scheduler)
```

---

## Authentication Flow (Complete Implementation)

### Visual Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ USER VISITS SITE                                            │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ AuthMiddleware::handle()                                    │
│ - Checks cookie for user session                            │
│ - If authenticated: Loads user data                         │
│ - If not authenticated: Allows public paths, blocks others  │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
        ┌────────┴────────┐
        │  Authenticated? │
        └────────┬────────┘
                 │
        ┌────────┴────────┐
        │                 │
        NO                YES
        │                 │
        ▼                 ▼
┌───────────────┐  ┌──────────────┐
│ Public Path?  │  │ Continue to  │
└───┬───────────┘  │ Controller   │
    │              └──────────────┘
┌───┴────┐
│        │
YES      NO
│        │
│        ▼
│   ┌──────────────────┐
│   │ Redirect to      │
│   │ /auth/login      │
│   └──────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ USER CLICKS "LOGIN WITH GITHUB"                             │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ auth/index.php::get()                                       │
│ 1. Generate CSRF state token                                │
│ 2. Store in flash session                                   │
│ 3. Build GitHub OAuth URL with:                             │
│    - client_id                                              │
│    - redirect_uri=/auth/callback                            │
│    - scope=public_repo,user:email                           │
│    - state=CSRF_TOKEN                                       │
│ 4. Redirect to GitHub                                       │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ GITHUB OAUTH SCREEN                                         │
│ - User sees: "Authorize MUXI Registry"                      │
│ - Permissions: public_repo, user:email                      │
│ - Optional: Install app checkbox                            │
│ - User clicks "Authorize"                                   │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ GITHUB REDIRECTS BACK                                       │
│ URL: /auth/callback?code=ABC&installation_id=123&state=XYZ  │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ auth/callback.php::get()                                    │
│                                                             │
│ 1. VALIDATE CSRF STATE                                     │
│    - Get state from URL                                     │
│    - Get stored state from flash session                    │
│    - Compare: if different, redirect to /auth/error        │
│                                                             │
│ 2. EXCHANGE CODE FOR TOKEN                                 │
│    - POST to github.com/login/oauth/access_token           │
│    - Send: client_id, client_secret, code                  │
│    - Receive: access_token                                 │
│                                                             │
│ 3. FETCH USER INFO                                         │
│    - GET /user (with access token)                         │
│    - GET /user/emails                                      │
│    - Parse name into first/last                            │
│                                                             │
│ 4. CREATE/UPDATE USER                                      │
│    - Check if user exists (by github_id)                   │
│    - If exists: UPDATE user data                           │
│    - If new: INSERT user record                            │
│    - Store encrypted OAuth token                           │
│    - Store installation_id (if present)                    │
│                                                             │
│ 5. CREATE SESSION                                          │
│    - Encrypt user ID                                       │
│    - Set "user" cookie with hash                           │
│                                                             │
│ 6. REDIRECT                                                │
│    - If installation_id: Go to /auth/login (success)       │
│    - If no installation_id: Go to /auth/install            │
└────────────────┬────────────────────────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        │           installation_id?
        │                 │
        NO                YES
        │                 │
        ▼                 ▼
┌───────────────┐  ┌──────────────┐
│ /auth/install │  │ /auth/login  │
│               │  │ (or redirect │
│ Show prompt:  │  │ to return    │
│ "Install App" │  │ URL)         │
│               │  │              │
│ User clicks   │  └──────────────┘
│ "Install App" │         │
│ → GitHub App  │         │
│   Install     │         │
│   Page        │         │
│               │         │
│ After install │         │
│ → Callback    │         │
│   with        │         │
│   installation│         │
│   _id         │         │
└───────┬───────┘         │
        │                 │
        └────────┬────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ USER IS NOW AUTHENTICATED                                   │
│ - User record in database                                   │
│ - Session cookie set                                        │
│ - OAuth token stored (encrypted)                            │
│ - Installation ID stored (if installed)                     │
│                                                             │
│ On subsequent requests:                                     │
│ - AuthMiddleware reads cookie                               │
│ - Decrypts hash → gets user ID                             │
│ - Loads user data from DB                                  │
│ - Sets tiny::user() globally                               │
│ - User can access authenticated pages                       │
└─────────────────────────────────────────────────────────────┘
```

### Key Security Features

1. **CSRF Protection:**
   - Random state token generated
   - Stored in session
   - Validated on callback
   - Prevents replay attacks

2. **Token Encryption:**
   - OAuth tokens encrypted before storage
   - Uses CRYPTO_SECRET from environment
   - Decrypted only when needed for API calls

3. **Session Security:**
   - User ID encrypted in cookie
   - Cookie sent as HttpOnly (prevents XSS)
   - Session data cached (1 hour TTL)
   - Invalid sessions redirect to login

4. **Rate Limiting:**
   - API requests: 10/second, 1000/10 minutes
   - Per-user basis
   - Prevents abuse

---

## Key Files Reference

### Controllers
| File | Purpose | Status |
|------|---------|--------|
| `app/controllers/home.php` | Homepage | ✅ Basic |
| `app/controllers/404.php` | Routes @ URLs to profiles | ✅ Complete |
| `app/controllers/_profile.php` | User profile pages | ✅ Basic |
| `app/controllers/auth/index.php` | OAuth initiation | ✅ Complete |
| `app/controllers/auth/callback.php` | OAuth callback | ✅ Complete |
| `app/controllers/auth/install.php` | App install prompt | ✅ Complete |
| `app/controllers/auth/error.php` | Auth error display | ✅ Complete |

### Models
| File | Purpose | Status |
|------|---------|--------|
| `app/models/user.php` | User management | ✅ Complete |
| `app/models/formation.php` | Formation management | ❌ TODO |

### Middleware
| File | Purpose | Status |
|------|---------|--------|
| `app/middleware/auth.php` | Authentication | ✅ Complete |

### Views
| File | Purpose | Status |
|------|---------|--------|
| `app/views/home.php` | Homepage template | ✅ Basic |
| `app/views/profile/index.php` | Profile template | ✅ Basic |
| `app/views/auth/install.php` | Install prompt | ✅ Complete |
| `app/views/auth/error.php` | Error display | ✅ Complete |

### Database
| File | Purpose | Status |
|------|---------|--------|
| `database/schema.sql` | Complete schema | ✅ Complete |

---

## Next Steps Priority

### Phase 1: Core Formation Features (Critical for Launch)

1. **Formation Discovery** (HIGH)
   - Formation model implementation
   - GitHub API integration helper
   - Lazy fetch from GitHub
   - Metadata caching

2. **Formation Display** (HIGH)
   - Formation page controller
   - Formation page view
   - Markdown rendering
   - 404 routing enhancement

3. **API Endpoints** (HIGH)
   - GET /api/formations/@user/name
   - POST /api/formations/publish
   - POST /api/formations/@user/name/version/download

4. **Search & Browse** (HIGH)
   - Browse controller
   - Search controller
   - FTS5 integration

### Phase 2: Polish & Features

5. **CLI Token Management** (MEDIUM)
   - Token display after auth
   - Token management page
   - Token revocation

6. **User Dashboard** (MEDIUM)
   - Dashboard controller
   - User's formations list
   - Settings

7. **Organization Support** (MEDIUM)
   - Org discovery logic
   - Permission checking
   - Org profiles

### Phase 3: Enhancement

8. **Reserved Usernames** (LOW)
   - Username resolution
   - Admin interface

9. **Better Error Pages** (LOW)
   - Enhanced 404 design
   - Search suggestions

---

## Development Guidelines

### Adding a New Controller

1. Create file in `app/controllers/`
2. Extend `TinyController`
3. Implement HTTP method handlers (`get`, `post`, etc.)
4. Access via URL matching file path

Example:
```php
<?php
class MyController extends TinyController {
    public function get($request, $response) {
        $response->render('my/view');
    }
}
```

### Adding a New Model

1. Create file in `app/models/`
2. Extend `TinyModel`
3. Implement business logic methods
4. Access via `tiny::model('modelname')`

Example:
```php
<?php
class MyModel extends TinyModel {
    public function getData($id) {
        return tiny::db()->getOne('table', ['id' => $id]);
    }
}
```

### Adding a New View

1. Create file in `app/views/`
2. Use `tiny::layout()->default()` for layout
3. Access user via `tiny::user()`
4. Access data via `tiny::data()->`

Example:
```php
<?php tiny::layout()->default(title: 'Page Title'); ?>
<h1>Content</h1>
<?php if (tiny::user()): ?>
    <p>Logged in as <?php echo tiny::user()->username; ?></p>
<?php endif; ?>
<?php tiny::layout()->default('/'); ?>
```

---

## Useful Tiny Framework Methods

### Global Access
```php
tiny::user()           // Current authenticated user object
tiny::user(['id' => 1])  // Set user data
tiny::data()           // Global data store for views
tiny::router()         // Router information
tiny::db()             // Database connection
tiny::model('name')    // Load model
tiny::flash('key')     // Flash messages
tiny::cookie('name')   // Cookie management
tiny::cache()          // Cache operations
```

### Controller Methods
```php
$response->render('view/path')  // Render view
$response->json(['data' => 'value'])  // JSON response
tiny::redirect('/path')  // Redirect
tiny::homeURL('/path')   // Generate absolute URL
```

### Database
```php
tiny::db()->getOne('table', ['id' => 1])  // Get single row
tiny::db()->getAll('table', ['status' => 'active'])  // Get multiple
tiny::db()->insert('table', ['data' => 'value'])  // Insert
tiny::db()->update('table', ['data' => 'new'], ['id' => 1])  // Update
tiny::db()->delete('table', ['id' => 1])  // Delete
tiny::db()->lastInsertId()  // Last inserted ID
```

---

**Document maintained by: Development Team**  
**For questions:** See Tiny framework docs in `/tiny/docs/`
