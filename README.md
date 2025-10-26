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
- [Getting Started](#getting-started)
- [For Users](#for-users)
- [For Publishers](#for-publishers)
- [Technical Details](#technical-details)
- [Development](#development)
- [Contributing](#contributing)

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

**Backend & Frontend:** [Tiny PHP](https://ranaroussi.github.io/tiny/)
- Lightweight PHP framework
- Handles both API (JSON) and Web (HTML) routes
- Fast, simple, self-hostable

**Database:** SQLite
- Single-file database
- No separate DB server needed
- Perfect for metadata caching

**Storage:** GitHub
- Formation code (repos)
- Versions (git tags)
- Releases (bundles)
- All free!

**Authentication:** GitHub App
- Fine-grained permissions
- Secure OAuth flow
- User controls access

### Project Structure

```
registry/
├── public/
│   ├── index.php              # Entry point
│   └── assets/                # CSS, JS, images
├── app/
│   ├── routes/
│   │   ├── web.php            # HTML pages (/@user/formation)
│   │   └── api.php            # JSON endpoints (/api/*)
│   ├── models/
│   │   ├── User.php           # User model
│   │   ├── Formation.php      # Formation model
│   │   └── Version.php        # Version model
│   ├── views/
│   │   ├── home.php           # Homepage
│   │   ├── formations/
│   │   │   ├── show.php       # Formation page
│   │   │   └── search.php     # Search results
│   │   └── users/
│   │       └── profile.php    # User profile
│   └── lib/
│       ├── GitHub.php         # GitHub API client
│       └── Auth.php           # GitHub App OAuth
├── database/
│   ├── schema.sql             # Database schema
│   └── registry.db            # SQLite database (created on init)
├── config/
│   └── app.php                # Configuration
├── composer.json              # PHP dependencies
└── README.md                  # This file
```

### Database Schema

**Users** (synced from GitHub):
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  github_id INTEGER UNIQUE,
  github_username TEXT UNIQUE,
  github_avatar TEXT,
  created_at DATETIME
);
```

**Formations** (metadata cache):
```sql
CREATE TABLE formations (
  id INTEGER PRIMARY KEY,
  user_id INTEGER,
  name TEXT,
  description TEXT,
  readme_md TEXT,
  latest_version TEXT,
  github_repo TEXT,
  github_stars INTEGER,
  total_downloads INTEGER,
  last_synced_at DATETIME,
  UNIQUE(user_id, name)
);
```

**Versions** (all published versions):
```sql
CREATE TABLE versions (
  id INTEGER PRIMARY KEY,
  formation_id INTEGER,
  version TEXT,
  size_bytes INTEGER,
  download_url TEXT,
  download_count INTEGER,
  published_at DATETIME,
  UNIQUE(formation_id, version)
);
```

See [database/schema.sql](database/schema.sql) for complete schema.

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

### API Endpoints

All API endpoints return JSON.

**Formations:**
```
GET  /api/formations/@:user/:name          # Get formation metadata
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

See [ALPHA-PRD.md](ALPHA-PRD.md) for complete API specification.

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
- PHP 8.2+
- SQLite3
- Composer

**Install:**

```bash
git clone https://github.com/muxi-ai/registry.git
cd registry/

# Install dependencies
composer install

# Create database
sqlite3 database/registry.db < database/schema.sql

# Configure
cp config/app.example.php config/app.php
# Edit config/app.php with your GitHub App credentials

# Run locally
php -S localhost:8080 -t public/
```

**Visit:** http://localhost:8080

### Environment Variables

```bash
# GitHub App (required for auth)
GITHUB_APP_ID=123456
GITHUB_APP_SECRET=abc123...
GITHUB_WEBHOOK_SECRET=secret123

# Database
DB_PATH=database/registry.db

# Optional
REGISTRY_URL=https://registry.muxi.org
```

### Testing

```bash
composer test
```

---

## Contributing

**We welcome contributions!**

### Areas to Help

- 🐛 **Bug fixes** - Find and fix issues
- ✨ **Features** - Implement from [ALPHA-PRD.md](ALPHA-PRD.md)
- 📝 **Documentation** - Improve guides and examples
- 🎨 **UI/UX** - Make the web interface better
- 🧪 **Testing** - Add test coverage

### How to Contribute

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing`)
5. **Open** a Pull Request

### Development Workflow

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## Documentation

- **[ALPHA-PRD.md](ALPHA-PRD.md)** - Complete product requirements and architecture
- **[../cli/docs/REGISTRY.md](../cli/docs/REGISTRY.md)** - CLI command specification
- **[API.md](API.md)** - API documentation (coming soon)

---

## Roadmap

### Alpha (Current)
- [x] GitHub-backed storage
- [x] Public formations only
- [x] Users only (no orgs)
- [x] Full formations only
- [ ] Basic web UI
- [ ] CLI integration
- [ ] Search functionality

### Beta (Next)
- [ ] Private formations
- [ ] Organizations support
- [ ] Partial pulls (agents, MCPs separately)
- [ ] Enhanced search (semantic, tags)
- [ ] Self-hosted option

### Production (Future)
- [ ] Primary S3 storage (GitHub as fallback)
- [ ] Teams and permissions
- [ ] Enterprise features (SSO, audit logs)
- [ ] CI/CD integrations

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
