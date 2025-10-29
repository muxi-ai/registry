# MUXI Registry - Technical Architecture

**Complete technical documentation for developers and contributors**

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Core Patterns](#core-patterns)
- [API Endpoints](#api-endpoints)
- [GitHub Integration](#github-integration)
- [Development Setup](#development-setup)
- [Testing](#testing)

---

## Architecture Overview

### GitHub-Backed Storage Model

Instead of building heavy infrastructure, we use **GitHub as the storage backend** and provide a lightweight discovery layer on top.

```
Developer                    Registry                   GitHub
    |                           |                         |
    | muxi push                 |                         |
    |-------------------------->|                         |
    |                           |                         |
    |              Creates: github.com/user/muxi-formation-name
    |                           |------------------------>|
    |                           |                         |
    |                           | Caches metadata         |
    |                           | (SQLite)                |
    |                           |                         |
    |                                                     |
    | (Later, someone pulls)                              |
    |                                                     |
    | muxi pull @user/formation                           |
    |-------------------------->|                         |
    |                           |                         |
    |                      Returns metadata               |
    |<--------------------------|                         |
    |                                                     |
    | Downloads bundle.zip directly                       |
    |-------------------------------------------------->  |
    |                                                     |
```

**Key Benefits:**
- ✅ **Free hosting** - GitHub stores everything
- ✅ **Git-native** - Edit formations with git
- ✅ **Fast launch** - No heavy infrastructure needed
- ✅ **Transparent** - All code is public on GitHub

### The Lazy Discovery Pattern

**The core innovation of MUXI Registry** - formations are discovered on-demand, not pre-registered.

**How it works:**
1. User visits: `registry.muxi.org/@user/formation`
2. Registry checks database (cache)
3. If not found, queries GitHub API: `github.com/user/muxi-formation`
4. If repo exists:
   - Fetch metadata (README, releases, stars)
   - Cache in database
   - Display page
5. Next visit: Instant (served from cache)

**Benefits:**
- ✅ No pre-registration required
- ✅ Works retroactively (any `muxi-*` repo is discoverable)
- ✅ Registry can launch before formations exist
- ✅ Self-service publishing workflow
- ✅ Always syncs with GitHub (source of truth)

**Example:**
```
User visits: registry.muxi.org/@yourname/cool-formation

Registry:
→ Not in database, check GitHub...
→ Found github.com/yourname/muxi-cool-formation!
→ Cache metadata and show page
```

No explicit registration needed! 🎉

---

## Technology Stack

**Backend:** [Tiny PHP Framework](https://ranaroussi.github.io/tiny/)
- Lightweight PHP framework (single codebase for API + Web)
- MVC architecture with auto-routing
- Built-in layout and component system
- Handles both JSON API endpoints and HTML pages
- Fast, simple, self-hostable

**Frontend:** Alpine.js + HTMX
- Alpine.js for reactive components
- HTMX for dynamic content
- Tailwind CSS for styling
- No build step required

**Database:** SQLite
- Single-file database (`website/data/registry.db`)
- No separate DB server needed
- WAL mode for better concurrency
- Perfect for metadata caching
- FTS5 full-text search enabled

**Storage:** GitHub
- Formation code (git repositories)
- Versions (git tags + releases)
- Distribution (bundle.zip assets)
- Zero hosting costs

**Authentication:** GitHub App (OAuth)
- Fine-grained repository permissions
- Users control which repos the app can access
- Secure token-based CLI authentication
- No access to existing repos unless granted

---

## Project Structure

```
registry/
├── website/                   # Main application
│   ├── tiny/                  # Tiny framework core
│   │   └── tiny.php           # Framework engine
│   ├── app/
│   │   ├── controllers/       # Request handlers
│   │   │   ├── home.php       # Homepage controller
│   │   │   ├── dashboard.php  # User dashboard
│   │   │   ├── _profile.php   # User profile (special routing)
│   │   │   ├── _formation.php # Formation page (special routing)
│   │   │   ├── 404.php        # 404 handler (@username routing)
│   │   │   ├── search.php     # Search page
│   │   │   ├── browse.php     # Browse page
│   │   │   └── auth/
│   │   │       └── callback.php  # GitHub OAuth callback
│   │   ├── middleware/        # Pre-controller logic
│   │   │   └── auth.php       # Authentication & rate limiting
│   │   ├── views/             # HTML templates
│   │   │   ├── layouts/
│   │   │   │   └── default/
│   │   │   │       ├── open.php  # Layout header
│   │   │   │       └── close.php # Layout footer
│   │   │   ├── components/    # Reusable UI elements
│   │   │   │   ├── Footer.php
│   │   │   │   └── Toast.php
│   │   │   ├── home.php       # Homepage view
│   │   │   ├── dashboard.php  # Dashboard view
│   │   │   └── profile/
│   │   │       └── index.php  # Profile view
│   │   ├── models/            # Business logic
│   │   │   ├── User.php
│   │   │   ├── Formation.php
│   │   │   └── Search.php
│   │   └── helpers.php        # Helper functions
│   ├── html/
│   │   ├── index.php          # Entry point
│   │   └── static/            # Static assets
│   │       ├── css/
│   │       ├── js/
│   │       └── images/
│   └── registry.db            # SQLite database
├── docs/                      # Documentation
│   ├── ALPHA-PRD.md           # Product requirements
│   ├── IMPLEMENTATION-GUIDE.md # Implementation guide
│   ├── ARCHITECTURE.md        # This file
│   ├── TINY_FRAMEWORK_NOTES.md # Framework patterns
│   ├── VIEWS_LAYOUTS_COMPONENTS.md # View system guide
│   └── AGENTS.md              # AI agent guide
└── README.md                  # User-facing readme
```

### Key Directories

**`website/app/controllers/`** - Request handlers
- Files starting with `_` are NOT auto-routed (special routing only)
- Example: `_profile.php` handles `/@username` URLs via 404 routing

**`website/app/views/`** - HTML templates
- Uses Tiny's layout system (open.php + close.php)
- Components are reusable UI elements

**`website/app/middleware/`** - Pre-controller logic
- `auth.php` runs before all protected routes
- Handles authentication and rate limiting

**`website/html/`** - Web root
- Only `index.php` and `static/` folder here

**`website/app/models/`** - Business logic
- User.php - User and organization management
- Formation.php - Formation CRUD and lazy discovery
- Search.php - Multi-strategy search implementation

---

## Database Schema

### Core Tables

**users** - GitHub users and organizations
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,           -- GitHub username (e.g., muxi-ai)
  registry_username TEXT UNIQUE NOT NULL,  -- Display name (e.g., muxi)
  github_avatar TEXT,
  github_email TEXT,                       -- NULL for organizations
  github_type TEXT DEFAULT 'User',         -- 'User' or 'Organization'
  github_oauth_token TEXT,                 -- ENCRYPTED
  github_refresh_token TEXT,               -- ENCRYPTED
  github_token_expires_at DATETIME,
  is_verified BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);
```

**reserved_usernames** - Username mappings
```sql
CREATE TABLE reserved_usernames (
  registry_username TEXT PRIMARY KEY,      -- e.g., "muxi"
  github_username TEXT NOT NULL,           -- e.g., "muxi-ai"
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Example: Maps @muxi → muxi-ai GitHub organization
INSERT INTO reserved_usernames VALUES ('muxi', 'muxi-ai', datetime('now'));
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
  github_repo TEXT NOT NULL,               -- Full: owner/muxi-name
  github_stars INTEGER DEFAULT 0,
  total_downloads INTEGER DEFAULT 0,
  is_public BOOLEAN DEFAULT 1,
  last_synced_at DATETIME,
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, name),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (published_by_user_id) REFERENCES users(id)
);
```

**versions** - All published versions
```sql
CREATE TABLE versions (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,                   -- Semver: 1.0.0
  release_notes TEXT,
  size_bytes INTEGER,
  sha256 TEXT,
  download_url TEXT,                       -- GitHub release asset URL
  download_count INTEGER DEFAULT 0,
  published_at DATETIME,
  UNIQUE(formation_id, version),
  FOREIGN KEY (formation_id) REFERENCES formations(id)
);
```

**formation_stats** - Component counts per version
```sql
CREATE TABLE formation_stats (
  id INTEGER PRIMARY KEY,
  version_id INTEGER NOT NULL,
  agents_count INTEGER DEFAULT 0,
  mcps_count INTEGER DEFAULT 0,
  sops_count INTEGER DEFAULT 0,
  triggers_count INTEGER DEFAULT 0,
  knowledge_count INTEGER DEFAULT 0,
  stats_json TEXT,                         -- Full JSON with details
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (version_id) REFERENCES versions(id)
);
```

**downloads** - Daily download tracking
```sql
CREATE TABLE downloads (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER NOT NULL,
  version_id INTEGER,                      -- NULL for latest
  day DATE NOT NULL,
  download_count INTEGER DEFAULT 0,
  UNIQUE(formation_id, version_id, day),
  FOREIGN KEY (formation_id) REFERENCES formations(id),
  FOREIGN KEY (version_id) REFERENCES versions(id)
);
```

**formations_fts** - Full-text search index
```sql
CREATE VIRTUAL TABLE formations_fts USING fts5(
  name,
  description,
  readme_md,
  content='formations',
  content_rowid='id'
);
```

### Username Resolution Example

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

## Core Patterns

### 1. Special Routing via 404

URLs starting with `@` (like `/@username`) are handled via a special routing pattern:

1. URL doesn't match any controller → `404.php` controller executes
2. `404.php` checks if URL starts with `@`
3. If yes, routes to `_profile.php` or `_formation.php` controller
4. These controllers handle the dynamic routing

**Example:**
- `/@muxi` → `_profile.php` (user profile)
- `/@muxi/customer-support` → `_formation.php` (formation page)

This pattern allows clean URLs without database lookups for routing.

### 2. Multi-Strategy Search

The search system uses multiple strategies for best results:

1. **FTS5 Exact Match** - Fast, indexed full-text search
2. **FTS5 Prefix Match** - Partial word matching (`cust*` matches `customer`)
3. **LIKE Pattern Fallback** - Fuzzy search when FTS5 fails
4. **Levenshtein Distance** - Typo correction for "did you mean"

**Trending Score Algorithm:**
```php
// Recent 3 days: weight 3x
// Days 4-7: weight 1x
// Add 10% of GitHub stars as tiebreaker
$score = ($recent_downloads * 3) + ($older_downloads * 1) + ($stars * 0.1);
```

### 3. Rate Limiting

**API Rate Limits:**
- Anonymous: 5 requests/second
- Authenticated: 10 requests/second
- Uploads: 1/minute, max 10/hour (stricter for expensive operations)

**Implementation:** Uses Tiny's rate limiter with in-memory tracking.

### 4. LLM README Generation

When publishing without a README, the system:
1. Analyzes formation structure (agents, MCPs, SOPs, etc.)
2. Sends structured prompt to OpenAI API
3. Generates comprehensive README with usage examples
4. Extracts categories for better search/discovery
5. Falls back to basic template if LLM fails

---

## API Endpoints

All API endpoints return JSON.

### Formations

```
GET  /api/formations/@:user/:name
  → Get formation metadata (lazy discovery enabled)
  → Response: { name, description, version, stats, download_url }

GET  /api/formations/@:user/:name:version
  → Get specific version
  → Response: Same as above, for specific version

GET  /api/formations/@:user/:name/versions
  → List all versions
  → Response: [{ version, published_at, downloads }, ...]

POST /api/formations/publish
  → Publish formation (authenticated)
  → Headers: Authorization: Bearer mxr_xxx
  → Body: multipart/form-data with file, optional org parameter
  → Response: { success, formation_url, github_repo }

GET  /api/formations/@:user/:name?pull=true
  → Record download (increments counter)
  → Response: Same as GET without pull
```

### Search & Browse

```
GET  /api/search?q=query&sort=trending&limit=20
  → Search formations
  → sort: trending|downloads|recent|stars
  → Response: { results: [...], total, query }

GET  /api/browse?sort=downloads&limit=20
  → Browse all formations
  → sort: downloads|stars|recent
  → Response: { formations: [...], total }
```

### Health Check

```
GET  /api/health
  → System health check
  → Response: { status: "ok"|"degraded", checks: {...} }
  → Returns 200 (ok) or 503 (degraded)
```

### Authentication

```
GET  /auth
  → Redirects to GitHub OAuth
  
GET  /auth/callback
  → GitHub OAuth callback
  → Sets session and returns to dashboard
```

---

## GitHub Integration

### Repository Naming

- All formation repos use `muxi-` prefix
- Example: `github.com/username/muxi-formation-name`
- Maps to: `@username/formation-name` in registry

### Versioning

- Git tags: `v1.0.0`, `v1.1.0`, etc.
- Follows semantic versioning (enforced)
- Each tag gets a GitHub release with `bundle.zip` asset
- Registry caches version metadata

### Publishing Flow

1. **CLI uploads ZIP** to `/api/formations/publish`
2. **Registry validates** ZIP contents, size, formation.yaml
3. **Creates/verifies GitHub repo** `user/muxi-formation-name`
4. **Pushes files** via GitHub Contents API
5. **Creates release** with version tag
6. **Uploads ZIP** as release asset
7. **Stores metadata** in database (formations + versions tables)
8. **Analyzes structure** and stores stats (agents, MCPs, etc.)
9. **Generates README** if missing (via LLM)

### GitHub App Permissions

- **Repository permissions:** 
  - Contents: Read & Write (push files)
  - Metadata: Read (repo info)
- **User permissions:**
  - Email: Read (for user info)
  - Profile: Read (avatar, username)
- **No access** to existing repos unless explicitly granted

---

## Development Setup

### Requirements

- PHP 8.2+ with extensions: `sqlite3`, `curl`, `json`, `mbstring`
- SQLite3 CLI (for database management)
- Git 2.0+

### Quick Start

```bash
# Clone repository
git clone https://github.com/muxi-ai/registry.git
cd registry/

# Run development server (database auto-created)
php -S localhost:8080 -t website/html/

# Visit: http://localhost:8080
```

### Environment Variables

```bash
# GitHub App (required for publishing)
GITHUB_APP_ID=your_app_id
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
GITHUB_WEBHOOK_SECRET=your_webhook_secret

# OpenAI (optional, for README generation)
OPENAI_API_KEY=your_openai_api_key

# Encryption (required for token storage)
CRYPTO_SECRET=<your-32-character-random-string>

# Database (optional, defaults to website/registry.db)
DB_PATH=/path/to/registry.db

# Debug mode (optional)
DEBUG=true
```

### Tiny Framework Routing

The framework uses auto-routing:
- `/` → `website/app/controllers/home.php`
- `/dashboard` → `website/app/controllers/dashboard.php`
- `/auth/callback` → `website/app/controllers/auth/callback.php`
- `/@username` → 404 handler → `_profile.php` (special routing)

**Files starting with `_` are NOT auto-routed** - they're called programmatically.

### Development Workflow

1. **Edit files** - No build step required!
2. **Refresh browser** - Changes are immediate
3. **Check errors** - PHP errors show in browser (when DEBUG=true)
4. **Database changes** - Use SQLite CLI or DB browser

### Common Tasks

**View database:**
```bash
sqlite3 website/registry.db
sqlite> .tables
sqlite> SELECT * FROM users;
sqlite> SELECT * FROM formations;
```

**Clear cache:**
```bash
rm -rf website/data/cache/*
```

**Check logs:**
```bash
# Tiny uses error_log() which goes to PHP error log
tail -f /var/log/php_errors.log
```

---

## Testing

### Manual Testing

```bash
# Test API endpoints
curl http://localhost:8080/api/search?q=customer

# Test formation page
curl http://localhost:8080/api/formations/@muxi/test

# Test health check
curl http://localhost:8080/api/health
```

### PHP Syntax Check

```bash
find website/app -name "*.php" -exec php -l {} \;
```

### Database Tests

```bash
# Test connection
php -r "new PDO('sqlite:website/registry.db');"

# Test FTS5 search
sqlite3 website/registry.db "SELECT * FROM formations_fts WHERE formations_fts MATCH 'customer';"
```

### Security Testing Checklist

- [ ] Test ZIP with path traversal attempts (`../../etc/passwd`)
- [ ] Test ZIP with absolute paths (`/etc/passwd`)
- [ ] Test oversized ZIP files (>50MB)
- [ ] Test invalid formation IDs (special chars, XSS attempts)
- [ ] Test rate limiting (>10 uploads/hour)
- [ ] Verify security events logged
- [ ] Test health check endpoint

---

## Security Measures

### Implemented Protections

1. **ZIP Slip Prevention** - Path traversal validation before extraction
2. **File Size Limits** - 50MB uploads, 100MB extracted
3. **Input Validation** - Regex validation on all user inputs
4. **Rate Limiting** - Per-user and per-IP rate limits
5. **SQL Injection Protection** - Parameterized queries throughout
6. **XSS Prevention** - HTML sanitization on all outputs
7. **Token Encryption** - GitHub OAuth tokens encrypted at rest
8. **Secure Temp Cleanup** - Robust cleanup with fallback

### Security Event Logging

All security events are logged via `tiny::log()`:
- Path traversal attempts
- ZIP validation failures
- Rate limit violations
- Formation publish errors
- Authentication failures

---

## Performance Optimizations

### Implemented

- **SQLite WAL mode** - Better concurrency for reads/writes
- **FTS5 indexing** - Fast full-text search
- **Metadata caching** - Database cache for GitHub data
- **Lazy discovery** - On-demand fetching from GitHub

### Future Optimizations

- N+1 query resolution (eager loading)
- Redis cache layer
- CDN for static assets
- Async job processing for expensive operations

---

## Related Documentation

- **[AGENTS.md](../AGENTS.md)** - AI agent development guide
- **[IMPLEMENTATION-GUIDE.md](IMPLEMENTATION-GUIDE.md)** - Step-by-step implementation
- **[TINY_FRAMEWORK_NOTES.md](TINY_FRAMEWORK_NOTES.md)** - Framework deep dive
- **[CLI API Documentation](../../cli/docs/REGISTRY.md)** - CLI integration spec

---

**Last Updated:** 2025-10-29  
**Version:** 1.0  
**Status:** Production Ready
