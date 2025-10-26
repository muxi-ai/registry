# MUXI Registry - Alpha PRD

**Version:** Alpha (v0.1.0)  
**Status:** Planning  
**Last Updated:** 2025-01-15

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [The Big Idea](#the-big-idea)
3. [Alpha Scope](#alpha-scope)
4. [Technical Architecture](#technical-architecture)
5. [User Flows](#user-flows)
6. [GitHub App Integration](#github-app-integration)
7. [Registry Components](#registry-components)
8. [CLI Integration](#cli-integration)
9. [Web UI](#web-ui)
10. [Migration Path](#migration-path)
11. [Success Metrics](#success-metrics)
12. [Open Questions](#open-questions)

---

## Executive Summary

### Vision

**MUXI Registry is Docker Hub for AI formations** - a place where developers can instantly share and discover complete, working AI agent formations.

### Alpha Strategy: GitHub-Backed Registry

Instead of building heavy infrastructure upfront, we're **using GitHub as the storage backend** and building a lightweight UX/discovery layer on top.

**The genius:**
- ✅ Launch in **days**, not months
- ✅ Zero infrastructure costs (GitHub hosts everything)
- ✅ Native git workflows (devs can edit with git)
- ✅ Validate the concept before heavy investment
- ✅ **Lazy/retroactive registration** - any `muxi-*` repo is automatically discoverable

### The Developer Experience

```bash
# First time user trying MUXI
muxi pull @muxi/customer-support

# Downloads, extracts, and you're running a complete formation
# No reading docs, no configuration, just instant value

# Developer publishing their own
cd my-formation/
muxi login                    # GitHub OAuth via browser
muxi push                     # Creates repo, publishes formation

# Published!
# registry.muxi.org/@username/my-formation
```

---

## The Big Idea

### The Problem

New developers trying to learn MUXI face:
- Empty starting point (no examples)
- Complex setup (what agents? what MCPs?)
- No community (can't discover others' work)
- Reinventing the wheel (everyone builds the same things)

### The Solution

**One command to a working formation:**

```bash
muxi pull @muxi/customer-support
```

This downloads a complete, production-ready customer support formation with:
- Multiple specialized agents
- Pre-configured MCPs (Zendesk, Slack, etc.)
- Standard operating procedures
- Event triggers
- Documentation

**Instant onboarding. Instant value.**

### Why GitHub-Backed?

Traditional registry approach:
- Build storage layer (S3, database)
- Build upload/download system
- Build version management
- Build access control
- Build web UI
- **Months of work before anyone can use it**

GitHub-backed approach:
- GitHub = storage, versioning, releases, CDN
- We build = thin UX layer, discovery, metadata
- **Ship in a week, iterate based on real usage**

---

## Alpha Scope

### What's IN (MVP)

**Core Features:**
- ✅ Push formations to GitHub (auto-creates `muxi-*` repos)
- ✅ Pull formations from GitHub (auto-downloads releases)
- ✅ GitHub App for secure, fine-grained permissions
- ✅ Lazy/retroactive discovery (any `muxi-*` repo works)
- ✅ Web UI for browsing formations
- ✅ Search (simple text search)
- ✅ User profiles (pulled from GitHub)
- ✅ Download/pull statistics

**Technical:**
- ✅ GitHub OAuth authentication (via GitHub App)
- ✅ Lightweight metadata DB (SQLite)
- ✅ PHP + Alpine.js + HTMX web UI
- ✅ Go API (minimal, just metadata)

### What's OUT (Future)

**Phase 2+:**
- ❌ Private formations (everything is public in alpha)
- ❌ Organizations/teams (users only)
- ❌ Partial pulls (agents, MCPs separately)
- ❌ Dependencies/resolution
- ❌ Self-hosted registry
- ❌ Direct S3 uploads (GitHub only)
- ❌ Semantic/vector search
- ❌ Stars/favorites system
- ❌ Comments/reviews
- ❌ CI/CD integrations

### Design Constraints

1. **Public-only** - Everything is public, no private repos (simplifies auth, legal)
2. **Users-only** - No organizations or teams (simplifies permissions)
3. **Full formations only** - No partial pulls of agents/MCPs (simplifies bundling)
4. **GitHub as source of truth** - Registry is just a cache/index
5. **Read authentication optional** - Anyone can pull, auth only needed for push

---

## Technical Architecture

### The GitHub-Backed Model

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   Developer  │         │   Registry   │         │    GitHub    │
│     (CLI)    │         │   (Metadata) │         │   (Storage)  │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │ 1. muxi push           │                        │
       │─────────────────────────>                       │
       │                        │                        │
       │                        │ 2. Create repo         │
       │                        │ "muxi-formation"       │
       │                        │───────────────────────>│
       │                        │                        │
       │                        │ 3. Push code + tag     │
       │                        │───────────────────────>│
       │                        │                        │
       │                        │ 4. Create release      │
       │                        │   + upload bundle.zip  │
       │                        │───────────────────────>│
       │                        │                        │
       │                        │ 5. Cache metadata      │
       │                        │ (in SQLite)            │
       │                        │                        │
       │ 6. Published!          │                        │
       │<─────────────────────────                       │
       │                        │                        │
       │                        │                        │
       │ 7. muxi pull @user/form                         │
       │─────────────────────────>                       │
       │                        │                        │
       │                        │ 8. Query metadata      │
       │ 9. Repo info + URL     │   (SQLite)             │
       │<─────────────────────────                       │
       │                        │                        │
       │ 10. Download bundle.zip                         │
       │────────────────────────────────────────────────>│
       │                        │                        │
       │ 11. bundle.zip         │                        │
       │<────────────────────────────────────────────────│
       │                        │                        │
       │                        │ 12. Record download    │
       │─────────────────────────> (increment counter)   │
```

### Repository Naming Convention

**Pattern:** `muxi-{formation-name}`

**Examples:**
```
github.com/ranaroussi/muxi-customer-support
github.com/somedev/muxi-sentiment-analyzer
github.com/muxi/muxi-data-processor
```

**Why the prefix?**
- ✅ Clear identification (it's a MUXI formation)
- ✅ Easy discovery (search GitHub for "muxi-")
- ✅ Avoids conflicts with existing projects
- ✅ Groups formations together in repo lists

**Alternative considered:** `_muxi-` prefix (underscore) for uniqueness, but `muxi-` is more conventional.

### URL Structure

**Registry URLs:**
```
registry.muxi.org/@username                    # User profile
registry.muxi.org/@username/formation-name     # Formation page
registry.muxi.org/browse                       # Browse all
registry.muxi.org/search?q=customer            # Search
```

**Why `@` prefix?**
- ✅ Distinguishes user pages from static pages
- ✅ Familiar pattern (Twitter, GitHub gists, etc.)
- ✅ Prevents conflicts with routes like `/support`, `/docs`, `/about`

**Reserved usernames:**
```
admin, support, api, docs, help, about, blog,
terms, privacy, contact, login, logout, auth,
register, signup, dashboard, settings, browse,
search, explore, trending, new, popular, muxi,
official, team, staff
```

### CLI Naming

**Full formation pull:**
```bash
muxi pull @ranaroussi/customer-support           # Latest version
muxi pull @ranaroussi/customer-support:1.0.0     # Specific version
```

**Mapping:**
```
@ranaroussi/customer-support  →  github.com/ranaroussi/muxi-customer-support
```

---

## GitHub App Integration

### Why GitHub App (Not OAuth)?

**Traditional OAuth with `repo` scope:**
- ❌ Full access to ALL repos (public + private)
- ❌ Can read secrets, delete repos, force push
- ❌ All-or-nothing access
- ❌ Users rightfully nervous

**GitHub App (fine-grained permissions):**
- ✅ Only access repos user explicitly grants
- ✅ Can create new repos with permission
- ✅ Clear, minimal permissions
- ✅ User can revoke per-repo access anytime
- ✅ Better audit trail ("via App" commits)

### Required Permissions

```yaml
GitHub App: "MUXI Registry"

Repository permissions:
  - Contents: Read & Write        # Push code, create releases
  - Metadata: Read                # Read repo info
  
Account permissions:
  - Administration: Read & Write  # Create new repos
  
User permissions:
  - Email addresses: Read         # User identity
```

**That's it!** No access to:
- ❌ Existing repos (unless granted)
- ❌ Secrets
- ❌ Repository deletion
- ❌ Webhooks, deploy keys, etc.

### Installation Flow

```bash
$ muxi login

Opening browser to install MUXI GitHub App...
→ https://github.com/apps/muxi-registry/installations/new

# User sees GitHub's install screen:
┌─────────────────────────────────────────────────┐
│  MUXI Registry wants to:                        │
│                                                 │
│  ✓ Create repositories on your behalf          │
│  ✓ Read and write code in MUXI formations      │
│                                                 │
│  Repository access:                             │
│  ○ All repositories (not recommended)           │
│  ● Only select repositories                     │
│                                                 │
│  [Install]  [Cancel]                            │
└─────────────────────────────────────────────────┘

# User installs, GitHub redirects:
→ registry.muxi.org/auth/callback?installation_id=12345&code=abc...

# Registry exchanges code for installation token
# Shows success message in browser
# CLI polls registry API for token confirmation

✓ Authenticated as @ranaroussi
  Token saved to ~/.muxi/credentials.json
```

---

## User Flows

### Flow 1: First-Time User (Pull)

**Goal:** Try MUXI in 30 seconds

```bash
# No account needed!
muxi pull @muxi/customer-support

# CLI:
→ Querying registry.muxi.org for @muxi/customer-support...
→ Found! Latest version: 1.2.0
→ Downloading from github.com/muxi/muxi-customer-support/releases/download/v1.2.0/bundle.zip
→ Extracting formation...
✓ Downloaded @muxi/customer-support v1.2.0

# Ready to deploy:
cd customer-support/
muxi deploy --profile local
```

**No registration. No login. Just instant value.**

### Flow 2: Developer Publishing (First Time)

**Goal:** Publish a formation in 2 minutes

```bash
cd my-formation/

# 1. Login (one-time)
$ muxi login
Opening browser to install MUXI GitHub App...
✓ Authenticated as @ranaroussi

# 2. Ensure formation.yaml has version
$ cat formation.yaml
formation:
  id: customer-support
  version: "1.0.0"
  ...

# 3. Push
$ muxi push

Reading formation.yaml...
→ Formation: customer-support v1.0.0

This will create a PUBLIC repository:
  github.com/ranaroussi/muxi-customer-support

Continue? [Y/n] y

Creating repository...
✓ Repository created
✓ Pushing formation files
✓ Creating tag v1.0.0
✓ Creating release with bundle.zip
✓ Notifying registry

✓ Published @ranaroussi/customer-support v1.0.0!

View at: registry.muxi.org/@ranaroussi/customer-support
GitHub: github.com/ranaroussi/muxi-customer-support
```

### Flow 3: Publishing Update

**Goal:** Publish new version in 30 seconds

```bash
cd my-formation/

# Update version in formation.yaml
# (edit files, commit changes locally - optional)

$ muxi push

Reading formation.yaml...
→ Formation: customer-support v1.1.0

Repository exists: github.com/ranaroussi/muxi-customer-support
Checking if v1.1.0 already exists...
✗ Not found (good!)

✓ Pushing updates
✓ Creating tag v1.1.0
✓ Creating release
✓ Notifying registry

✓ Published @ranaroussi/customer-support v1.1.0!
```

### Flow 4: Lazy Discovery (The Magic)

**User visits registry URL for unregistered formation:**

```
User types in browser:
→ registry.muxi.org/@somedev/cool-formation

Registry:
→ SELECT * FROM formations WHERE user='somedev' AND name='cool-formation'
→ Not found in database!

→ Try GitHub: GET api.github.com/repos/somedev/muxi-cool-formation
→ Repo exists! ✓

→ Fetch: README.md, formation.yaml, latest release
→ INSERT INTO formations (user, name, version, ...)
→ Render page with fetched data

# Next visit: served from cached metadata
```

**This means:**
- Anyone can create `muxi-*` repos on GitHub
- Registry automatically discovers them
- No explicit registration needed
- Works even before registry exists!

---

## Registry Components

### 1. Database (SQLite)

**Why SQLite?**
- ✅ Single file, easy deployment
- ✅ No separate database server
- ✅ Perfect for metadata (reads >>> writes)
- ✅ WAL mode handles concurrency
- ✅ Easy self-hosted path later

**Schema:**

```sql
-- Users (synced from GitHub)
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,           -- Actual GitHub username (e.g., muxi-ai)
  registry_username TEXT UNIQUE NOT NULL,  -- Display name on registry (e.g., muxi)
  github_avatar TEXT,
  is_verified BOOLEAN DEFAULT 0,           -- Official/verified account badge
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);

-- Reserved usernames (allows @muxi to map to muxi-ai GitHub org)
CREATE TABLE reserved_usernames (
  registry_username TEXT PRIMARY KEY,
  github_username TEXT NOT NULL,           -- Which GitHub account owns this
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Pre-seed official mappings
INSERT INTO reserved_usernames VALUES 
  ('muxi', 'muxi-ai', datetime('now')),
  ('admin', 'muxi-ai', datetime('now')),
  ('support', 'muxi-ai', datetime('now')),
  ('official', 'muxi-ai', datetime('now'));

-- Formations (metadata cache)
CREATE TABLE formations (
  id INTEGER PRIMARY KEY,
  user_id INTEGER NOT NULL,
  name TEXT NOT NULL,                  -- Without 'muxi-' prefix
  description TEXT,
  readme_md TEXT,                      -- Cached from GitHub
  latest_version TEXT,
  license TEXT,
  github_repo TEXT NOT NULL,           -- Full repo name (e.g., muxi-ai/muxi-customer-support)
  github_stars INTEGER DEFAULT 0,
  total_downloads INTEGER DEFAULT 0,
  is_public BOOLEAN DEFAULT 1,         -- For future
  last_synced_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, name),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Versions (tracks all published versions)
CREATE TABLE versions (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER NOT NULL,
  version TEXT NOT NULL,               -- Semver: 1.0.0
  release_notes TEXT,                  -- From GitHub release body
  size_bytes INTEGER,
  sha256 TEXT,                         -- Bundle hash
  download_url TEXT,                   -- GitHub release asset URL
  download_count INTEGER DEFAULT 0,
  published_at DATETIME,
  UNIQUE(formation_id, version),
  FOREIGN KEY (formation_id) REFERENCES formations(id)
);

-- Stats JSON (component counts, extracted from formation.yaml)
CREATE TABLE formation_stats (
  id INTEGER PRIMARY KEY,
  version_id INTEGER NOT NULL,
  agents_count INTEGER DEFAULT 0,
  mcps_count INTEGER DEFAULT 0,
  sops_count INTEGER DEFAULT 0,
  triggers_count INTEGER DEFAULT 0,
  knowledge_count INTEGER DEFAULT 0,
  stats_json TEXT,                     -- Full JSON blob
  FOREIGN KEY (version_id) REFERENCES versions(id)
);

-- Tokens (CLI authentication)
CREATE TABLE tokens (
  id INTEGER PRIMARY KEY,
  user_id INTEGER NOT NULL,
  token_hash TEXT UNIQUE NOT NULL,     -- SHA256 hash
  name TEXT,                           -- "My Laptop", "CI/CD"
  github_installation_id INTEGER,      -- GitHub App installation ID
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_formations_user ON formations(user_id);
CREATE INDEX idx_formations_name ON formations(name);
CREATE INDEX idx_formations_downloads ON formations(total_downloads DESC);
CREATE INDEX idx_versions_formation ON versions(formation_id);
CREATE INDEX idx_tokens_user ON tokens(user_id);
CREATE INDEX idx_tokens_hash ON tokens(token_hash);
```

### 2. API & Web (Full PHP Stack with Tiny)

**Why Full PHP (not Go API + PHP UI)?**
- ✅ **Single codebase** - API + Web in one framework
- ✅ **Simpler deployment** - Drop files on any PHP host  
- ✅ **Easier self-hosting** - Just needs PHP 8.2+ and SQLite
- ✅ **Tiny framework** - Built for exactly this use case
- ✅ **Less complexity** - No language/framework boundaries
- ✅ **Fast iteration** - One paradigm, one stack

**Technology:** [Tiny PHP Framework](https://ranaroussi.github.io/tiny/)
- Lightweight, fast, built for this
- Handles both JSON API endpoints and HTML pages
- Alpine.js + HTMX for interactivity
- Single `index.php` entry point

**Endpoints (API returns JSON, Web returns HTML):**

```
# Authentication
POST   /api/auth/begin              # Start GitHub OAuth flow
GET    /api/auth/callback           # GitHub OAuth callback
GET    /api/auth/status             # Check auth status
POST   /api/auth/logout             # Invalidate token

# Formations
GET    /api/formations/@:user/:name            # Get formation metadata (or lazy-fetch)
POST   /api/formations/publish                 # CLI notifies of new publish
GET    /api/formations/@:user/:name/versions   # List all versions
GET    /api/formations/@:user/:name/:version   # Get specific version

# Downloads
POST   /api/formations/@:user/:name/:version/download  # Record download (increments counter)

# User
GET    /api/users/@:username         # Get user profile + formations list
GET    /api/users/@:username/stats   # User statistics

# Search & Discovery
GET    /api/search?q=:query          # Search formations
GET    /api/browse?sort=:field       # Browse all (trending, new, popular)

# Admin (future)
DELETE /api/formations/@:user/:name/:version  # Delete version
```

### 3. Routing (Same Framework)

**API Routes (routes/api.php - return JSON):**
```php
Route::get('/api/formations/@:user/:name', function($user, $name) {
    $formation = Formation::findOrLazyFetch($user, $name);
    return json($formation);
});

Route::post('/api/formations/publish', function() {
    // CLI notification
    Auth::requireToken();
    $data = input();
    Formation::register($data);
    return json(['status' => 'ok']);
});
```

**Web Routes (routes/web.php - return HTML):**
```php
Route::get('/@:user/:name', function($user, $name) {
    $formation = Formation::findOrLazyFetch($user, $name);
    return view('formations/show', compact('formation'));
});

Route::get('/search', function() {
    $results = Formation::search(input('q'));
    return view('formations/search', compact('results'));
});
```

**Pages:**

```
/                              # Homepage (featured, trending, search)
/@:username                    # User profile
/@:username/:formation         # Formation page (README, stats, install)
/browse                        # Browse all formations
/search?q=:query               # Search results
/login                         # GitHub OAuth start
/logout                        # Clear session
/docs                          # Documentation
/support                       # Help center
```

**Formation Page Template:**

```html
<!-- registry.muxi.org/@ranaroussi/customer-support -->

<div class="formation-header">
  <h1>@ranaroussi/customer-support</h1>
  <span class="version">v1.2.0</span>
  
  <div class="stats">
    <span>⬇ 1,234 pulls</span>
    <span>⭐ 45 stars</span>
    <span>📦 2.1 MB</span>
  </div>
</div>

<div class="install-box">
  <h3>🚀 Installation</h3>
  <pre><code>muxi pull @ranaroussi/customer-support</code></pre>
  <button onclick="copyToClipboard()">Copy</button>
</div>

<div class="components">
  <h3>📊 Contains</h3>
  <ul>
    <li>3 agents</li>
    <li>2 MCPs (Zendesk, Slack)</li>
    <li>1 SOP</li>
    <li>2 triggers</li>
  </ul>
</div>

<div class="links">
  <a href="https://github.com/ranaroussi/muxi-customer-support">View on GitHub</a>
  <a href="https://github.com/ranaroussi/muxi-customer-support/issues">Report Issue</a>
</div>

<div class="readme">
  <!-- README.md rendered as Markdown -->
  <?php echo renderMarkdown($formation->readme_md); ?>
</div>
```

### 4. Storage (GitHub)

**GitHub Repo Structure:**

```
github.com/ranaroussi/muxi-customer-support/
├── formation.yaml              # Formation manifest
├── README.md                   # Homepage content
├── agents/                     # Agent definitions
│   ├── escalation.yaml
│   ├── sentiment.yaml
│   └── router.yaml
├── mcps/                       # MCP configurations
│   ├── zendesk.yaml
│   └── slack.yaml
├── sops/                       # Standard operating procedures
│   └── escalation.yaml
├── triggers/                   # Event triggers
│   ├── new-ticket.yaml
│   └── urgent.yaml
├── knowledge/                  # Knowledge bases
│   └── company-docs/
└── secrets.enc.example         # Example secrets file

# Releases:
- v1.0.0 → bundle.zip (all files packaged)
- v1.1.0 → bundle.zip
- v1.2.0 → bundle.zip (latest)
```

**Tags:** Semver tags (`v1.0.0`, `v1.1.0`, etc.)

**Releases:** Each tag gets a GitHub release with attached `bundle.zip`

---

## CLI Integration

### Commands

**See:** `../cli/docs/REGISTRY.md` for full CLI specification

**Summary:**

```bash
# Authentication
muxi login                              # Install GitHub App, authenticate

# Publishing
muxi push                               # Publish from current directory
muxi push --tag 1.0.0                   # Override version (future)

# Installing
muxi pull @user/formation               # Pull latest
muxi pull @user/formation:1.0.0         # Pull specific version

# Discovery
muxi search "customer support"          # Search formations
muxi show @user/formation               # Show info in terminal
```

### Configuration Files

**`~/.muxi/credentials.json`** (Created by `muxi login`)

```json
{
  "registry": {
    "token": "mxr_abc123...",
    "github_installation_id": 12345,
    "github_username": "ranaroussi",
    "expires_at": "2025-12-31T23:59:59Z"
  }
}
```

**`formation.yaml`** (Required for push)

```yaml
formation:
  id: customer-support
  name: "Customer Support Formation"
  version: "1.0.0"          # REQUIRED for muxi push
  description: "AI-powered customer support with escalation"
  author: "Ran Aroussi"
  license: "MIT"
  
  runtime:
    type: python
    version: "3.10"
```

### Push Flow (Detailed)

```bash
$ cd my-formation/
$ muxi push

# Step 1: Validate
→ Checking formation.yaml exists... ✓
→ Reading formation.yaml... ✓
→ Formation: customer-support v1.0.0
→ Validating formation structure... ✓

# Step 2: Check authentication
→ Checking authentication... ✓
→ Authenticated as @ranaroussi

# Step 3: Check repo existence
→ Checking github.com/ranaroussi/muxi-customer-support...
→ Repository doesn't exist

# Step 4: Confirm creation
┌─────────────────────────────────────────────────┐
│  MUXI will create a new PUBLIC repository:      │
│                                                 │
│  📦 github.com/ranaroussi/muxi-customer-support │
│                                                 │
│  This repository will:                          │
│  • Be publicly visible on GitHub                │
│  • Contain your formation source code           │
│  • Be managed by MUXI GitHub App                │
│  • Be used for distribution                     │
│                                                 │
│  Continue? [Y/n]                                │
└─────────────────────────────────────────────────┘

y

# Step 5: Create repo
→ Creating repository via GitHub API... ✓

# Step 6: Bundle formation
→ Bundling formation files...
  • formation.yaml
  • README.md
  • agents/ (3 files)
  • mcps/ (2 files)
  • sops/ (1 file)
  • triggers/ (2 files)
  • knowledge/ (excluded, too large - future)
→ Created bundle.zip (2.1 MB) ✓

# Step 7: Push to GitHub
→ Initializing git repository... ✓
→ Adding files... ✓
→ Committing... ✓
→ Adding remote... ✓
→ Pushing to github.com/ranaroussi/muxi-customer-support... ✓

# Step 8: Create tag
→ Creating tag v1.0.0... ✓
→ Pushing tag... ✓

# Step 9: Create release
→ Creating GitHub release v1.0.0... ✓
→ Uploading bundle.zip... ✓

# Step 10: Notify registry
→ Notifying registry.muxi.org... ✓
→ Registry cached metadata ✓

# Done!
✓ Published @ranaroussi/customer-support v1.0.0!

View at:
  Registry: https://registry.muxi.org/@ranaroussi/customer-support
  GitHub:   https://github.com/ranaroussi/muxi-customer-support

Share with:
  muxi pull @ranaroussi/customer-support
```

---

## Web UI

### Technology

- **Backend:** PHP 8.2+ (with Tiny framework)
- **Frontend:** Alpine.js 3.x + HTMX 1.x
- **Styling:** Tailwind CSS or custom minimal CSS
- **Markdown:** CommonMark parser for README rendering
- **Syntax Highlighting:** Highlight.js for code blocks

### Key Pages

#### 1. Homepage (`/`)

**Sections:**
- Hero with search bar
- Featured formations (curated by @muxi)
- Trending (most pulls this week)
- Recently added (newest formations)
- Categories (Support, Analytics, Integration, etc.)

#### 2. Formation Page (`/@user/formation`)

**Sections:**
- Header (name, version, stats)
- Installation instructions (code snippet)
- Component breakdown (agents, MCPs, etc.)
- Links (GitHub, issues)
- README (rendered Markdown)
- Version history (dropdown or tab)

#### 3. User Profile (`/@username`)

**Sections:**
- User info (avatar, username from GitHub)
- Total stats (formations count, total pulls)
- List of formations (with stats each)

#### 4. Browse (`/browse`)

**Features:**
- Filter by: newest, trending, most pulls, name
- Pagination
- Grid or list view

#### 5. Search (`/search?q=query`)

**Features:**
- Text search (name, description, README content)
- Results sorted by relevance
- Highlight matching terms

---

## Migration Path

### Alpha → Beta → Production

**Alpha (Current Plan):**
- GitHub-backed storage
- Public formations only
- Users only (no orgs)
- Full formations only (no partial pulls)
- Basic metadata DB

**Beta (Phase 2):**
- Hybrid model: GitHub OR direct upload
- Private formations support
- Organizations support
- Partial pulls (agents, MCPs separately)
- Enhanced search (tags, categories)

**Production (Phase 3):**
- Primary storage: S3
- GitHub repos still work (backwards compat)
- Teams, fine-grained permissions
- Semantic/vector search
- Self-hosted registry option
- Enterprise features (SSO, audit logs)

### Backwards Compatibility

**All formations published in alpha will continue to work:**
- GitHub repos never go away
- Registry URLs stay the same
- `muxi pull @user/formation` always works
- Migration to new storage is opt-in

---

## Success Metrics

### Launch Goals (First 30 days)

**Adoption:**
- [ ] 50+ formations published
- [ ] 10+ active publishers
- [ ] 500+ total pulls
- [ ] 100+ unique CLI users

**Technical:**
- [ ] 99% uptime
- [ ] < 2s average page load
- [ ] < 5s average pull time
- [ ] Zero data loss events

**Community:**
- [ ] Official @muxi formations published (5+ examples)
- [ ] Community formations (non-@muxi) published
- [ ] First GitHub issue/PR on a formation repo
- [ ] First community contribution to official formation

### Success Indicators

**Engagement:**
- Users publishing multiple formations
- Users updating their formations (new versions)
- Users discovering formations via search (not just direct links)
- GitHub stars on formation repos

**Quality:**
- Formations with good READMEs
- Formations with multiple versions (iteration)
- Formations with issue/PR activity on GitHub

**Validation:**
- Feature requests for private formations (validates enterprise need)
- Feature requests for orgs (validates team use case)
- Feature requests for partial pulls (validates granularity need)

---

## Open Questions

### Product

1. **Should we curate "featured" formations?** 
   - Pros: Quality signal, discovery
   - Cons: Who decides? Bias concerns

2. **Version selection UX:** 
   - Default to `latest`? Or show version picker?
   - How to handle breaking changes?

3. **Formation updates:**
   - Should we notify users when formations they've pulled have updates?
   - Email notifications? CLI warnings?

4. **README requirements:**
   - Should we enforce README.md for published formations?
   - Template/checklist?

### Technical

5. **Bundle size limits:**
   - Should we limit formation bundle size? (e.g., 50MB max)
   - How to handle large knowledge bases?

6. **Sync frequency:**
   - How often to sync GitHub stars/releases to registry DB?
   - On-demand when page visited? Cron job?

7. **Rate limiting:**
   - Should we rate-limit pulls to prevent abuse?
   - GitHub has its own rate limits (60/hr anonymous, 5000/hr authed)

8. **Cache invalidation:**
   - When to invalidate cached README/metadata?
   - User-triggered "refresh" button?

### Legal/Policy

9. **Content policy:**
   - Do we need terms of service?
   - Content moderation? (malicious code, offensive names)
   - DMCA process?

10. **GitHub dependency:**
    - What if GitHub changes API/terms?
    - Backup plan?

---

## Appendix

### Reserved Usernames

```
admin, support, api, docs, help, about, blog,
terms, privacy, contact, login, logout, auth,
register, signup, dashboard, settings, browse,
search, explore, trending, new, popular, muxi,
official, team, staff, status, cdn, assets,
static, www, app, web, mail, ftp, smtp, root
```

### Repo Naming Examples

| Formation Name | Repo Name | Registry URL |
|----------------|-----------|--------------|
| customer-support | `muxi-customer-support` | `@ranaroussi/customer-support` |
| sentiment-bot | `muxi-sentiment-bot` | `@ranaroussi/sentiment-bot` |
| data-analyzer | `muxi-data-analyzer` | `@ranaroussi/data-analyzer` |

### GitHub API Endpoints Used

```
# Repository creation
POST /user/repos

# Release creation
POST /repos/:owner/:repo/releases

# Release asset upload
POST /repos/:owner/:repo/releases/:id/assets

# Repository info
GET /repos/:owner/:repo

# Releases list
GET /repos/:owner/:repo/releases

# README fetch
GET /repos/:owner/:repo/readme
```

---

**End of Alpha PRD**

Next steps:
1. Review and approve PRD
2. Create GitHub App
3. Implement CLI commands (see `../cli/docs/REGISTRY.md`)
4. Build registry API
5. Build registry web UI
6. Launch! 🚀
