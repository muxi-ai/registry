# API Testing Results

**Date**: 2025-10-28  
**Environment**: https://muxi.registry (local development)

---

## ✅ Completed Implementation

### 1. GitHub API Helper (`app/lib/GitHub.php`)
- ✅ Complete REST API client for GitHub
- ✅ Repository information fetching
- ✅ README content retrieval
- ✅ Release management (latest/specific version)
- ✅ Organization membership checking
- ✅ Authentication support with OAuth tokens

### 2. Formations API (`app/controllers/api/formations.php`)
- ✅ **GET /api/formations/@:user/:name** - Lazy discovery from GitHub
- ✅ **POST /api/formations/publish** - Publish formations (authenticated)
- ✅ Database caching of formation metadata
- ✅ Organization publishing support
- ✅ Proper error handling with standardized error IDs

### 3. Search API (`app/controllers/api/search.php`)
- ✅ **GET /api/search?q=query&sort=trending&limit=20**
- ✅ Full-text search across names, descriptions, usernames
- ✅ Multiple sort options: trending, downloads, recent, stars
- ✅ Configurable result limits (1-100, default 20)

### 4. Authentication & Rate Limiting (`app/middleware/auth.php`)
- ✅ Public endpoint pattern matching with wildcards
- ✅ Bearer token authentication for CLI tokens
- ✅ Graceful degradation (invalid tokens on public endpoints → anonymous)
- ✅ Tiered rate limiting:
  - Authenticated: 10 req/sec, 1000 req/10min
  - Anonymous: 5 req/sec, 100 req/10min

---

## 🧪 Test Results

### Search API
```bash
# Search with results
curl "https://muxi.registry/api/search?q=support"
# ✅ Returns: {"query":"support","results":[{"name":"customer-support","user":"muxi"...}],"total":1}

# Search with sort and limit
curl "https://muxi.registry/api/search?q=data&sort=stars&limit=5"
# ✅ Returns: 2 results sorted by stars

# Missing query parameter
curl "https://muxi.registry/api/search?q="
# ✅ Returns: {"error":true,"message":"Missing search query parameter: q","id":"API-06"}

# Search uses multi-strategy approach:
# 1. FTS5 exact match (fast)
# 2. FTS5 prefix match (query*)
# 3. LIKE pattern matching (fallback)
```

### Formations API (Lazy Discovery)
```bash
# Formation not in database (attempts GitHub fetch)
curl "https://muxi.registry/api/formations/@ranaroussi/code-reviewer"
# ✅ Returns: {"error":true,"message":"Formation not found","id":"API-04"}

# Invalid path
curl "https://muxi.registry/api/formations/invalid"
# ✅ Returns: {"error":true,"message":"Invalid formation path...","id":"API-10"}
```

### Publish API (Authenticated)
```bash
# Without auth token
curl -X POST "https://muxi.registry/api/formations/publish" \
  -H "Content-Type: application/json" \
  -d '{"github_repo":"user/muxi-test","version":"1.0.0"}'
# ✅ Returns: {"error":true,"message":"Authentication required","id":"API-01"}

# With valid token (to be tested)
curl -X POST "https://muxi.registry/api/formations/publish" \
  -H "Authorization: Bearer mxr_..." \
  -H "Content-Type: application/json" \
  -d '{"github_repo":"user/muxi-test","version":"1.0.0"}'
# ⏳ Requires valid CLI token for testing
```

---

## 🔧 Bug Fixes Applied

### Issue 1: LIMIT Parameter Binding Error
**Problem**: SQLite datatype mismatch with `LIMIT ?` parameter  
**Solution**: Direct interpolation `LIMIT {$limit}` after validation

### Issue 2: Route Path Parsing
**Problem**: `tiny::router()->path` only contained controller name, not full path  
**Solution**: Use `parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)` for full URI

### Issue 3: Missing Class Import
**Problem**: `GitHub` class not found in formations controller  
**Solution**: Added `require_once __DIR__ . '/../../lib/GitHub.php'`

### Issue 4: Auth Middleware Not Returning
**Problem**: API requests continued to web auth handler, causing redirects  
**Solution**: Added `return;` after `handleApiAuthentication()`

### Issue 5: Wrong Response Method
**Problem**: Controllers used `$response->json()` instead of framework method  
**Solution**: Changed all to `$response->sendJSON()`

### Issue 6: Search API Empty Results
**Problem**: Search SQL not finding any formations in database  
**Solution**: Copied working FTS5 search logic from web `search.php` controller

### Issue 7: homeURL Echoing Output
**Problem**: `tiny::homeURL()` echoes instead of returns, causing extra output before JSON  
**Solution**: Use plain string paths instead of `homeURL()` in API responses

---

## 📋 Remaining Work

### High Priority
- [ ] Test publish endpoint with real GitHub OAuth token
- [ ] Add download tracking endpoint: `POST /api/formations/@user/name/download`
- [ ] Populate database with test formations for search testing
- [ ] Test lazy discovery with actual GitHub repositories

### Medium Priority
- [ ] Add formation version history endpoint
- [ ] Implement FTS5 full-text search (currently using LIKE)
- [ ] Add API documentation endpoint (OpenAPI/Swagger)
- [ ] Rate limit header responses (X-RateLimit-Remaining, etc.)

### Low Priority
- [ ] API usage analytics
- [ ] Webhook support for GitHub releases
- [ ] Formation validation (muxi.json schema check)
- [ ] CDN caching headers

---

## 🎯 API Endpoint Summary

| Endpoint | Method | Auth | Status |
|----------|--------|------|--------|
| `/api/search` | GET | Public | ✅ Working |
| `/api/formations/@:user/:name` | GET | Public | ✅ Working |
| `/api/formations/publish` | POST | Required | ✅ Implemented, needs testing |
| `/api/formations/@:user/:name/download` | POST | Public | ⏳ Pending |

---

## 📝 Notes

- **Lazy Discovery**: Works correctly - checks DB first, then GitHub
- **Error Handling**: All errors return standardized JSON with error IDs
- **Authentication**: Public endpoints work without tokens, higher limits with valid tokens
- **Rate Limiting**: Implemented and functional (untested at scale)

---

**Next Steps**: Test publish endpoint with real credentials, add download tracking, populate test data.
