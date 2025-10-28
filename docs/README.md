# MUXI Registry Documentation

**Docker Hub for AI Formations** - A lightweight discovery and distribution platform for MUXI AI agent formations.

---

## 📚 Documentation Structure

### [01. Overview](./01-overview/)
Core concepts and project vision
- **[ALPHA-PRD.md](./01-overview/ALPHA-PRD.md)** - Product requirements, user flows, architecture decisions
- **[AGENTS.md](./01-overview/AGENTS.md)** - Guide for AI coding assistants working on this project

### [02. Setup](./02-setup/)
Getting started and authentication setup
- **[GITHUB-APP-SETUP.md](./02-setup/GITHUB-APP-SETUP.md)** - Step-by-step GitHub App configuration
- **[GITHUB-APP-DESCRIPTION.md](./02-setup/GITHUB-APP-DESCRIPTION.md)** - GitHub App public description and permissions
- **[AUTH_FLOW.md](./02-setup/AUTH_FLOW.md)** - Authentication flow documentation

### [03. Implementation](./03-implementation/)
Development guides and status tracking
- **[IMPLEMENTATION-GUIDE.md](./03-implementation/IMPLEMENTATION-GUIDE.md)** - Detailed implementation steps
- **[IMPLEMENTATION_STATUS.md](./03-implementation/IMPLEMENTATION_STATUS.md)** - Current progress tracker
- **[PHASE1-COMPLETE.md](./03-implementation/PHASE1-COMPLETE.md)** - Phase 1 completion summary
- **[TINY_FRAMEWORK_NOTES.md](./03-implementation/TINY_FRAMEWORK_NOTES.md)** - Tiny PHP framework patterns and conventions

### [04. Features](./04-features/)
Feature-specific implementation guides
- **[DOWNLOADS-TRACKING.md](./04-features/DOWNLOADS-TRACKING.md)** - Download tracking system and trending algorithm
- **[DOWNLOADS-CHART.md](./04-features/DOWNLOADS-CHART.md)** - Chart visualization implementation
- **[VIEWS_LAYOUTS_COMPONENTS.md](./04-features/VIEWS_LAYOUTS_COMPONENTS.md)** - Frontend view system guide
- **[USER_MESSAGING.md](./04-features/USER_MESSAGING.md)** - User communication guidelines

### [05. API](./05-api/)
API and CLI documentation
- **[CLI-API-SCOPE.md](./05-api/CLI-API-SCOPE.md)** - Complete CLI and API design specification
- **[CLI-AUTH-TESTING.md](./05-api/CLI-AUTH-TESTING.md)** - Authentication testing guide and security decisions
- **[API-IMPLEMENTATION.md](./05-api/API-IMPLEMENTATION.md)** - API implementation guide with request/response examples
- **[PULL-TRACKING-REFACTOR.md](./05-api/PULL-TRACKING-REFACTOR.md)** - Pull tracking refactor documentation
- **[PUBLISH-IMPLEMENTATION.md](./05-api/PUBLISH-IMPLEMENTATION.md)** - File upload and publish flow implementation
- **[IMPLEMENTATION-PLAN.md](./05-api/IMPLEMENTATION-PLAN.md)** - Detailed implementation plan for Phase 2.5

---

## 🚀 Quick Start

1. **Read the vision**: Start with [ALPHA-PRD.md](./01-overview/ALPHA-PRD.md)
2. **Setup GitHub App**: Follow [GITHUB-APP-SETUP.md](./02-setup/GITHUB-APP-SETUP.md)
3. **AI Assistants**: Read [AGENTS.md](./01-overview/AGENTS.md) for project conventions
4. **Implementation**: Use [IMPLEMENTATION-GUIDE.md](./03-implementation/IMPLEMENTATION-GUIDE.md)

---

## 🏗️ Architecture Summary

**Core Innovation: Lazy Discovery**
```
User visits: registry.muxi.org/@user/formation
  ↓
Not in DB? → Fetch from GitHub (github.com/user/muxi-formation)
  ↓
Cache metadata → Display page
  ↓
Next visit: Instant (served from cache)
```

**Tech Stack:**
- Backend: PHP 8.2+ with Tiny Framework
- Database: SQLite
- Frontend: Alpine.js + HTMX
- Storage: GitHub (releases, repos)
- Charts: Chart.js

**Key Features:**
- GitHub-backed storage (zero infrastructure)
- Lazy/blind discovery (any `muxi-*` repo is discoverable)
- Download tracking with trending algorithm
- CLI authentication with smart rate limiting
- Public vs authenticated API endpoints

---

## 📖 Phase Status

### ✅ Phase 1: Web UI (COMPLETE)
- Homepage with trending/popular/recent formations
- User profiles (@username pages)
- Formation detail pages with charts
- Search and browse functionality
- GitHub OAuth + App installation
- Download tracking and visualization

### ✅ Phase 2: API & CLI Integration (COMPLETE)
- CLI authentication flow ✅
- Public API endpoints ✅
  - GET /api/formations/@user/name[:version][?pull=true]
  - GET /api/search
- Authenticated API endpoints ✅
  - POST /api/formations/publish (file upload)
- Pull tracking refactor ✅
  - Separate info vs pull requests
  - Version-specific syntax (:version)
  - Download tracking with ?pull=true
- GitHub helper refactor ✅
  - Tiny helper with tiny::http()
  - Token management
- File upload processing ✅
  - ZIP extraction and validation
  - formation.yaml parsing
  - README auto-generation
- GitHub operations ✅
  - Repository creation
  - File pushing (Contents API)
  - Release creation
  - Asset uploads
- Database storage ✅
  - Formation metadata
  - Version tracking

### 📋 Phase 3: CLI Development & Enhancements (NEXT)
- CLI tool development (muxi pull, muxi push, muxi search)
- End-to-end testing with real formations
- LLM-generated comprehensive READMEs
- Async processing with progress tracking
- Smart file diff (only update changed files)
- Formation structure validation
- Categories/tags with LLM auto-categorization
- Organization publishing with enhanced permissions
- Token management UI
- Private formations support

---

## 🔗 Related Resources

- **Main README**: [../README.md](../README.md)
- **Database Schema**: [../website/schema.sql](../website/schema.sql)
- **Test Data**: [../website/test-data.sql](../website/test-data.sql)

---

## 🤝 Contributing

This is an internal project. For development guidelines:
1. Read [AGENTS.md](./01-overview/AGENTS.md) for coding conventions
2. Follow patterns in [TINY_FRAMEWORK_NOTES.md](./03-implementation/TINY_FRAMEWORK_NOTES.md)
3. Check [IMPLEMENTATION_STATUS.md](./03-implementation/IMPLEMENTATION_STATUS.md) for current work

---

**Last Updated**: 2025-10-28  
**Current Phase**: Phase 2 Complete - Ready for CLI Development
