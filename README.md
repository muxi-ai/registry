# MUXI Registry

**Docker Hub for AI Formations** - Share, discover, and deploy complete AI agent formations with a single command.

[![License](https://img.shields.io/badge/license-Elastic%202.0-blue.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-alpha-orange.svg)](https://github.com/muxi-ai/registry/releases)

```bash
# Install any formation in seconds
muxi pull @muxi/customer-support
```

---

## 📖 Table of Contents

- [What Is This?](#what-is-this)
- [How It Works](#how-it-works)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [For Users](#for-users)
- [For Publishers](#for-publishers)
- [Technical Details](#technical-details)
- [Development](#development)
- [Contributing](#contributing)
- [Documentation](#documentation)

---

## What Is This?

**MUXI Registry is like Docker Hub, but for AI formations.**

Instead of building formations from scratch, you can:
- 🚀 **Install complete formations** with one command
- 🔍 **Discover formations** published by the community
- 📦 **Publish your own** formations for others to use
- 🌐 **Browse at** [registry.muxi.org](https://registry.muxi.org) (coming soon)

### The Problem

New MUXI users face:
- Empty starting point (no examples)
- Complex setup (what agents? what MCPs?)
- No community (can't discover others' work)
- Reinventing the wheel (everyone builds the same things)

### The Solution

**One command to a working formation:**

```bash
muxi pull @muxi/customer-support
cd customer-support/
muxi deploy
```

You now have a complete, production-ready customer support formation with agents, MCPs, SOPs, and triggers. **Instant value.**

---

## How It Works

### The GitHub-Backed Model

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

### Lazy Discovery (The Magic)

**Any `muxi-*` GitHub repo is automatically discoverable.**

Even if you create a formation repo manually on GitHub, the registry will find it when someone visits:

```
User visits: registry.muxi.org/@yourname/cool-formation

Registry:
→ Not in database, check GitHub...
→ Found github.com/yourname/muxi-cool-formation!
→ Cache metadata and show page
```

No explicit registration needed! 🎉

---

## Architecture

### Technology Stack

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
│   │   │   └── auth/
│   │   │       ├── callback.php  # GitHub OAuth callback
│   │   │       └── install.php   # App installation flow
│   │   ├── middleware/        # Pre-controller logic
│   │   │   └── auth.php       # Authentication middleware
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
│   │   ├── models/            # Business logic (future)
│   │   │   ├── User.php
│   │   │   └── Formation.php
│   │   └── helpers.php        # Helper functions
│   ├── html/
│   │   ├── index.php          # Entry point
│   │   └── static/            # Static assets
│   │       ├── css/
│   │       ├── js/
│   │       └── images/
│   └── data/
│       └── registry.db        # SQLite database
├── docs/                      # Documentation
│   ├── ALPHA-PRD.md           # Product requirements
│   ├── IMPLEMENTATION-GUIDE.md # Implementation guide
│   ├── TINY_FRAMEWORK_NOTES.md # Framework patterns
│   ├── VIEWS_LAYOUTS_COMPONENTS.md # View system guide
│   └── AGENTS.md              # AI agent guide
└── README.md                  # This file
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

**`website/html/`** - Web root
- Only `index.php` and `static/` folder here

### Database Schema

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
  github_installation_id INTEGER,          -- NULL for organizations
  github_oauth_token TEXT,                 -- NULL for organizations
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
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, name),
  FOREIGN KEY (user_id) REFERENCES users(id)
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

**Example: Username Resolution**
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

## Getting Started

### For Users

**Install formations with zero setup:**

```bash
# No authentication needed!
muxi pull @muxi/customer-support
muxi pull @somedev/sentiment-analyzer
```

**Search for formations:**

```bash
muxi search "customer support"
```

**Browse at:** [registry.muxi.org](https://registry.muxi.org)

### For Publishers

**Publish your formation:**

```bash
# 1. Authenticate (one-time)
muxi login

# 2. Ensure formation.yaml has version
cat formation.yaml
# formation:
#   id: my-formation
#   version: "1.0.0"  # Required!

# 3. Push
muxi push

# Published to:
# - GitHub: github.com/yourname/muxi-my-formation
# - Registry: registry.muxi.org/@yourname/my-formation
```

**Update your formation:**

```bash
# Edit formation, update version in formation.yaml
muxi push  # Creates new release
```

---

## Technical Details

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

### Special Routing via 404

URLs starting with `@` (like `/@username`) are handled via a special routing pattern:

1. URL doesn't match any controller → `404.php` controller executes
2. `404.php` checks if URL starts with `@`
3. If yes, routes to `_profile.php` or `_formation.php` controller
4. These controllers handle the dynamic routing

**Example:**
- `/@muxi` → `_profile.php` (user profile)
- `/@muxi/customer-support` → `_formation.php` (formation page)

This pattern allows clean URLs without database lookups for routing.

### API Endpoints

All API endpoints return JSON.

**Formations:**
```
GET  /api/formations/@:user/:name          # Get formation metadata (lazy discovery)
GET  /api/formations/@:user/:name/versions # List all versions
POST /api/formations/publish               # CLI publish notification
POST /api/formations/@:user/:name/:version/download  # Record download
```

**Search:**
```
GET  /api/search?q=query&limit=20         # Search formations
GET  /api/browse?sort=downloads           # Browse all
```

**Authentication:**
```
POST /api/auth/begin                      # Start GitHub OAuth
GET  /api/auth/callback                   # OAuth callback
GET  /api/auth/status                     # Check auth status
```

See [ALPHA-PRD.md](ALPHA-PRD.md) and [IMPLEMENTATION-GUIDE.md](IMPLEMENTATION-GUIDE.md) for complete API specification.

### GitHub Integration

**Repository Naming:**
- All formation repos use `muxi-` prefix
- Example: `github.com/username/muxi-formation-name`
- Maps to: `@username/formation-name` in registry

**Versioning:**
- Git tags: `v1.0.0`, `v1.1.0`, etc.
- Follows semantic versioning
- Each tag gets a GitHub release with `bundle.zip`

**GitHub App:**
- Fine-grained permissions (only formation repos)
- User controls access per repository
- Secure OAuth flow

### CLI Integration

The MUXI CLI handles all registry interactions.

**Commands:**
- `muxi login` - GitHub App authentication
- `muxi push` - Publish formation (creates repo, tags, releases)
- `muxi pull @user/formation` - Download formation
- `muxi search "query"` - Search formations
- `muxi show @user/formation` - View details

See [../cli/docs/REGISTRY.md](../cli/docs/REGISTRY.md) for CLI specification.

---

## Development

### Local Setup

**Requirements:**
- PHP 8.2+ with extensions: `sqlite3`, `curl`, `json`, `mbstring`
- SQLite3 CLI (for database setup)
- Composer (optional, if using dependencies)

**Quick Start:**

```bash
# Clone repository
git clone https://github.com/muxi-ai/registry.git
cd registry/

# Create database
cd website/data/
sqlite3 registry.db

# In SQLite prompt, create schema:
sqlite> -- Paste schema from docs/database-schema.sql

# Or import from file:
# sqlite3 registry.db < ../../docs/database-schema.sql

cd ../..

# Configure GitHub App (optional for development)
# Create .env file or set environment variables
export GITHUB_APP_ID=your_app_id
export GITHUB_APP_SECRET=your_app_secret
export GITHUB_CLIENT_ID=your_client_id
export GITHUB_CLIENT_SECRET=your_client_secret

# Run development server
cd website/html/
php -S localhost:8080

# Or from root:
php -S localhost:8080 -t website/html/
```

**Visit:** http://localhost:8080

### Tiny Framework Routing

The framework uses auto-routing:
- `/` → `website/app/controllers/home.php`
- `/dashboard` → `website/app/controllers/dashboard.php`
- `/auth/callback` → `website/app/controllers/auth/callback.php`
- `/@username` → 404 handler → `_profile.php` (special routing)

**Files starting with `_` are NOT auto-routed** - they're called programmatically.

### Environment Variables

```bash
# GitHub App (required for publishing)
GITHUB_APP_ID=123456
GITHUB_CLIENT_ID=Iv1.abc123...
GITHUB_CLIENT_SECRET=abc123...
GITHUB_APP_PRIVATE_KEY_PATH=/path/to/private-key.pem

# Database (optional, defaults to website/data/registry.db)
DB_PATH=website/data/registry.db

# Registry URL (optional, defaults to current domain)
REGISTRY_URL=https://registry.muxi.org

# Debug mode (optional)
DEBUG=true
```

### Development Workflow

1. **Edit files** - No build step required!
2. **Refresh browser** - Changes are immediate
3. **Check errors** - PHP errors show in browser (when DEBUG=true)
4. **Database changes** - Use SQLite CLI or DB browser

### Common Tasks

**View database:**
```bash
sqlite3 website/data/registry.db
sqlite> .tables
sqlite> SELECT * FROM users;
```

**Clear cache:**
```bash
rm -rf website/data/cache/*
```

**View routes:**
```bash
# Routes are auto-generated from controller files
ls -R website/app/controllers/
```

### Testing

```bash
# Run PHP syntax check
find website/app -name "*.php" -exec php -l {} \;

# Test database connection
php -r "new PDO('sqlite:website/data/registry.db');"

# Test GitHub API (requires token)
export GITHUB_TOKEN=your_token
php website/app/test/github-api-test.php
```

---

## Contributing

**We welcome contributions!** Whether you're fixing bugs, adding features, or improving documentation, your help is appreciated.

### 🚀 Quick Start for Contributors

1. **Read the docs** - Start with [AGENTS.md](AGENTS.md) for a comprehensive guide
2. **Set up locally** - Follow the [Development](#development) section above
3. **Pick an issue** - Check [GitHub Issues](https://github.com/muxi-ai/registry/issues)
4. **Ask questions** - Open a discussion or comment on issues

### 🎯 Areas to Help

**High Priority:**
- 🐛 **Bug fixes** - Find and fix issues (check issues tagged `bug`)
- ✨ **Alpha MVP features** - See [Roadmap](#roadmap) Phase 2 items
- 📝 **Documentation** - Improve guides, add examples
- 🧪 **Testing** - Add tests for existing code

**Medium Priority:**
- 🎨 **UI/UX improvements** - Better layouts, components, interactions
- 🔍 **Search functionality** - Implement text search for formations
- 📊 **Analytics** - Formation stats, user dashboards
- 🐳 **Self-hosting guide** - Documentation for deploying your own registry

**Good First Issues:**
- Add helper functions to `website/app/helpers.php`
- Create reusable UI components in `website/app/views/components/`
- Improve error messages and user feedback
- Write documentation for existing features

### 📝 How to Contribute

1. **Fork** the repository
2. **Create** a feature branch:
   ```bash
   git checkout -b feature/add-search-function
   # or
   git checkout -b fix/formation-cache-bug
   ```
3. **Make your changes**
   - Follow existing code style
   - Add comments for complex logic
   - Test your changes locally
4. **Commit** with clear messages:
   ```bash
   git commit -m "Add text search for formations"
   git commit -m "Fix cache invalidation bug in Formation model"
   ```
5. **Push** to your fork:
   ```bash
   git push origin feature/add-search-function
   ```
6. **Open a Pull Request**
   - Describe what you changed and why
   - Reference any related issues
   - Add screenshots for UI changes

### 🎨 Code Style Guidelines

**PHP:**
- Follow PSR-12 coding standard
- Use 4 spaces for indentation
- Type hint function parameters and return types when possible
- Add PHPDoc comments for public methods

**HTML/Views:**
- Use semantic HTML5 elements
- Keep views simple (logic in controllers/models)
- Escape output: `<?= htmlspecialchars($var) ?>`

**JavaScript:**
- Use Alpine.js for reactivity
- Keep JavaScript minimal and readable
- Prefer declarative over imperative code

### 🔍 Before Submitting

- [ ] Code follows existing patterns (check [AGENTS.md](AGENTS.md))
- [ ] No PHP syntax errors (`php -l file.php`)
- [ ] Tested locally (manual testing is fine for now)
- [ ] Commit messages are clear and descriptive
- [ ] No sensitive data (tokens, passwords) in code

### 📚 Resources for Contributors

- **[AGENTS.md](AGENTS.md)** - Comprehensive guide for AI agents and developers
- **[IMPLEMENTATION-GUIDE.md](IMPLEMENTATION-GUIDE.md)** - Detailed implementation patterns
- **[TINY_FRAMEWORK_NOTES.md](TINY_FRAMEWORK_NOTES.md)** - Framework conventions
- **[Tiny Framework Docs](https://ranaroussi.github.io/tiny/)** - Official framework documentation

### 💬 Questions?

- Open a [GitHub Discussion](https://github.com/muxi-ai/registry/discussions)
- Comment on relevant issues
- Check existing documentation first

---

**Thank you for contributing to MUXI Registry!** 🙌

---

## Documentation

### Core Documentation

- **[ALPHA-PRD.md](ALPHA-PRD.md)** - Complete product requirements, user flows, and architecture decisions
- **[IMPLEMENTATION-GUIDE.md](IMPLEMENTATION-GUIDE.md)** - Detailed implementation guide with code examples and patterns
- **[AGENTS.md](AGENTS.md)** - AI agent guide for contributing to the project

### Framework & Patterns

- **[TINY_FRAMEWORK_NOTES.md](TINY_FRAMEWORK_NOTES.md)** - Deep dive into Tiny framework patterns and conventions
- **[VIEWS_LAYOUTS_COMPONENTS.md](VIEWS_LAYOUTS_COMPONENTS.md)** - Complete guide to the view system

### Additional Resources

- **[GITHUB-APP-SETUP.md](GITHUB-APP-SETUP.md)** - How to set up the GitHub App
- **[AUTH_FLOW.md](AUTH_FLOW.md)** - GitHub OAuth authentication flow
- **[USER_MESSAGING.md](USER_MESSAGING.md)** - User-facing messaging guidelines

### External Documentation

- **[Tiny Framework Docs](https://ranaroussi.github.io/tiny/)** - Official framework documentation
- **CLI Registry Docs** - `../cli/docs/REGISTRY.md` (CLI command specification)

---

## Roadmap

### ✅ Phase 1: Core Infrastructure (Complete)
- [x] GitHub-backed storage architecture
- [x] Lazy discovery pattern implementation
- [x] Database schema (users, formations, versions)
- [x] Username mapping system (reserved_usernames)
- [x] Tiny framework integration
- [x] Special routing (@username URLs)

### 🚧 Phase 2: Alpha MVP (In Progress)
- [x] GitHub App OAuth authentication
- [x] User dashboard (basic)
- [ ] Formation page with README rendering
- [ ] User profile pages
- [ ] Search functionality (text search)
- [ ] Browse page (trending, recent, popular)
- [ ] CLI integration (`muxi pull`, `muxi push`)

### 📋 Phase 3: Beta Features (Planned)
- [ ] Organizations support (publishing to orgs)
- [ ] Private formations
- [ ] Markdown rendering with syntax highlighting
- [ ] Enhanced search (tags, categories)
- [ ] Formation statistics dashboard
- [ ] Version history and changelog
- [ ] Copy-to-clipboard for install commands

### 🔮 Phase 4: Production (Future)
- [ ] Hybrid storage (S3 + GitHub)
- [ ] Teams and fine-grained permissions
- [ ] Semantic/vector search
- [ ] Self-hosted registry option
- [ ] Enterprise features (SSO, audit logs)
- [ ] CI/CD integrations
- [ ] Formation dependencies and resolution

---

## License

Elastic License 2.0 - See [LICENSE](LICENSE) for details.

---

## Links

- **Website:** https://muxi.org
- **Registry:** https://registry.muxi.org (coming soon)
- **Documentation:** https://docs.muxi.org
- **GitHub Org:** https://github.com/muxi-ai

---

## Support

- **Issues:** [GitHub Issues](https://github.com/muxi-ai/registry/issues)
- **Discussions:** [GitHub Discussions](https://github.com/muxi-ai/registry/discussions)
- **Discord:** [Join our community](https://discord.gg/muxi) (coming soon)

---

**Made with ❤️ by the MUXI team**
