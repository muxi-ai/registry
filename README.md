# MUXI Registry

**Share and discover complete AI agent formations**

[![License](https://img.shields.io/badge/license-Apache%202.0-blue.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-production-green.svg)](https://registry.muxi.org)

```bash
muxi pull @muxi/customer-support  # Get a complete formation in seconds
```

---

## What Is This?

**MUXI Registry is like npm or Docker Hub, but for AI formations.**

Stop building AI agents from scratch. Install complete, production-ready formations built by the community:

```bash
muxi pull @muxi/customer-support    # Complete customer support system
muxi pull @dev/code-reviewer         # Automated code review agent
muxi pull @company/sales-assistant   # Sales automation formation
```

Each formation includes everything you need:
- ✅ Pre-configured AI agents
- ✅ Integrated tools (MCPs)
- ✅ Standard operating procedures
- ✅ Automation triggers
- ✅ Knowledge bases

**No setup. No configuration. Just pull and deploy.**

---

## Quick Start

### Using Formations

```bash
# Search for formations
muxi search "customer support"

# Install a formation
muxi pull @muxi/customer-support

# Install specific version
muxi pull @muxi/customer-support:1.2.0
```

**Browse formations at:** [registry.muxi.org](https://registry.muxi.org)

### Publishing Formations

```bash
# 1. Authenticate (one-time)
muxi login

# 2. Add version to formation.yaml
# formation:
#   id: my-formation
#   version: "1.0.0"

# 3. Publish
muxi push
```

Your formation is now at `@yourname/my-formation` 🎉

**Features:**
- ✅ Auto-generated README with LLM
- ✅ Semantic versioning
- ✅ Organization support
- ✅ GitHub-backed (free hosting)
- ✅ Instant discovery

---

## How It Works

**GitHub-Backed Storage**

Formations are stored on GitHub repositories:

```
muxi push → Creates github.com/yourname/muxi-formation-name
          → Registry caches metadata for search
          → Anyone can: muxi pull @yourname/formation-name
```

**Lazy Discovery**

Any `muxi-*` GitHub repo is automatically discoverable. No registration needed!

---

## Features

### Smart Search
- Multi-strategy search (FTS5, fuzzy matching)
- Typo correction ("custmer" → "customer")
- Trending and popular rankings

### LLM-Powered
- Auto-generates comprehensive READMEs
- Analyzes formation structure
- Categorizes for better discovery

### Production Ready
- Rate limiting and security
- Download tracking and analytics
- Version management
- Organization publishing

---

## Documentation

**For Users:**
- 📚 [Browse formations](https://registry.muxi.org)
- 🔍 [Search API](docs/ARCHITECTURE.md#api-endpoints)

**For Publishers:**
- 📦 [Publishing Guide](AGENTS.md#for-publishers)
- 🏢 [Organization Publishing](AGENTS.md#organization-publishing)

**For Developers:**
- 🏗️ [Architecture](docs/ARCHITECTURE.md) - Technical details
- 🤖 [Developer Guide](AGENTS.md) - Contributing guide
- 🔌 [API Reference](docs/ARCHITECTURE.md#api-endpoints)
- 🛠️ [Setup & Development](docs/ARCHITECTURE.md#development-setup)

---

## Contributing

We welcome contributions! See [AGENTS.md](AGENTS.md#contributing) for:
- How to contribute
- Code style guidelines
- Development workflow
- Areas where we need help

---

## Self-Hosting

Want to run your own registry?

```bash
git clone https://github.com/muxi-ai/registry.git
cd registry/
php -S localhost:8080 -t website/html/
```

See [docs/ARCHITECTURE.md#development-setup](docs/ARCHITECTURE.md#development-setup) for complete setup guide.

---

## Links

- **Website:** [muxi.org](https://muxi.org)
- **Registry:** [registry.muxi.org](https://registry.muxi.org)
- **Documentation:** [muxi.org/docs](https://muxi.org/docs)
- **GitHub:** [github.com/muxi-ai](https://github.com/muxi-ai)

---

## Support

- 💬 [GitHub Discussions](https://muxi.org/community)
- 🐛 [Report Issues](https://github.com/muxi-ai/registry/issues)
- 📖 [Read the Docs](docs/ARCHITECTURE.md)

---

## License

Apache License 2.0 - See [LICENSE](LICENSE) for details.

---

**Made with ❤️ by the MUXI team**
