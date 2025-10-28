# CLI Authentication - Testing Guide

## ✅ What Was Implemented

### 1. CLI Login Flow
- **URL**: `/auth/cli/authorize`
- **Flow**:
  1. User visits `/auth/cli/authorize`
  2. If logged in + has installation → redirect to `/auth/cli/token`
  3. Otherwise → set `auth_mode=cli` cookie → show install button
  4. OAuth flow completes → callback checks `auth_mode` cookie
  5. If `auth_mode=cli` → redirect to `/auth/cli/token`
  6. Token generated and displayed with copy button

### 2. Token Display (`/auth/cli/token`)
- ✅ Large, copyable input field
- ✅ One-click copy button with success feedback
- ✅ Security warning (yellow banner)
- ✅ Token format: `mxr_{60-char-nanoid}`

### 3. Token Storage
```sql
CREATE TABLE cli_tokens (
  user_id INTEGER,
  token_hash TEXT,          -- Encrypted token
  name TEXT,                -- "CLI Token"
  expires_at DATETIME,      -- Currently: 2099 (never expire)
  last_used_at DATETIME
);
```

### 4. Public API Endpoints
```php
// No authentication required:
GET  /api/formations/*          // Get formation metadata
GET  /api/search                // Search formations
POST /api/formations/*/download // Track anonymous downloads

// Authentication required (all others):
POST /api/formations/publish
GET  /api/formations/@me
etc.
```

### 5. Auth Middleware
- ✅ Checks `PUBLIC_API_ENDPOINTS` pattern matching
- ✅ Validates token via `getUserByCliToken()`
- ✅ Updates `last_used_at` timestamp
- ✅ Returns 401 (not 400) for auth failures
- ✅ **Smart Rate Limiting**:
  - **Authenticated users** (with Bearer token): 10 req/sec, 1000 req/10min (by user_id)
  - **Anonymous users** (public endpoints only): 5 req/sec, 100 req/10min (by IP)
  - CLI should send Bearer token even for public endpoints to get higher limits

---

## 🧪 Testing Steps

### Test 1: CLI Login Flow (Manual)

```bash
# 1. Visit the authorize page (logged out)
open https://registry.muxi.org/auth/cli/authorize

# Expected: Sign in button shown

# 2. Sign in with GitHub
# Expected: OAuth flow → redirects to /auth/cli/token

# 3. Token displayed
# Expected: 
# - Token shown in copyable input field
# - "mxr_" prefix visible
# - Copy button works
# - Security warning displayed
```

### Test 2: Public API Access (No Token - Lower Rate Limits)

```bash
# Should work WITHOUT token (but with lower rate limits):

# Get formation
curl https://registry.muxi.org/api/formations/@user/formation
# Expected: 200 OK with formation data
# Rate limit: 5 req/sec, 100 req/10min (by IP)

# Search
curl https://registry.muxi.org/api/search?q=customer
# Expected: 200 OK with search results

# Track download
curl -X POST https://registry.muxi.org/api/formations/@user/formation/1.0.0/download
# Expected: 200 OK (or appropriate response)
```

### Test 2b: Public API Access (With Token - Higher Rate Limits)

```bash
TOKEN="mxr_your_token_here"

# CLI should send Bearer token even for public endpoints to get higher limits:

# Get formation (with token)
curl -H "Authorization: Bearer $TOKEN" \
  https://registry.muxi.org/api/formations/@user/formation
# Expected: 200 OK with formation data
# Rate limit: 10 req/sec, 1000 req/10min (by user_id) ✨ Much higher!

# Search (with token)
curl -H "Authorization: Bearer $TOKEN" \
  https://registry.muxi.org/api/search?q=customer
# Expected: 200 OK with search results
# Rate limit: 10 req/sec, 1000 req/10min (by user_id)
```

### Test 3: Authenticated API Access (With Token)

```bash
# Generate a token first via web UI

TOKEN="mxr_your_token_here"

# Should work WITH token:
curl -H "Authorization: Bearer $TOKEN" \
  https://registry.muxi.org/api/formations/publish \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"github_repo": "user/muxi-formation"}'
# Expected: 200 OK (or appropriate response based on endpoint implementation)

# Should FAIL without token:
curl https://registry.muxi.org/api/formations/publish \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"github_repo": "user/muxi-formation"}'
# Expected: 401 Unauthorized
```

### Test 4: Invalid Token Behavior

```bash
# Test 4a: Invalid token on PUBLIC endpoint (graceful degradation)
curl -H "Authorization: Bearer invalid_token_abc123" \
  https://registry.muxi.org/api/formations/@user/formation
# Expected: 200 OK (treated as anonymous, lower rate limits)
# No error - gracefully falls back to anonymous access

# Test 4b: Invalid token on PRIVATE endpoint (strict validation)
curl -H "Authorization: Bearer invalid_token_abc123" \
  https://registry.muxi.org/api/formations/publish \
  -X POST
# Expected: 401 {"error": true, "message": "Invalid authentication token", "id": "API-01"}
```

### Test 5: Token Reuse & last_used_at

```bash
# Make API call with valid token
curl -H "Authorization: Bearer $TOKEN" \
  https://registry.muxi.org/api/formations/publish

# Check database
sqlite3 registry.db "SELECT last_used_at FROM cli_tokens WHERE token_hash = ..."
# Expected: Timestamp should update each time token is used
```

### Test 6: Rate Limiting - Authenticated Users

```bash
TOKEN="mxr_your_token_here"

# Make 11 requests in 1 second (should hit 10 req/sec limit)
for i in {1..11}; do
  curl -H "Authorization: Bearer $TOKEN" \
    https://registry.muxi.org/api/formations/@user/formation &
done
wait

# Expected: 1 request returns 429 Too Many Requests
```

### Test 7: Rate Limiting - Anonymous Users

```bash
# Make 6 requests in 1 second without token (should hit 5 req/sec limit)
for i in {1..6}; do
  curl https://registry.muxi.org/api/formations/@user/formation &
done
wait

# Expected: 1 request returns 429 Too Many Requests

# Make 101 requests in 10 minutes (should hit 100 req/10min limit)
for i in {1..101}; do
  curl https://registry.muxi.org/api/search?q=test
  sleep 6  # Spread out to stay under per-second limit
done

# Expected: Last request returns 429 Too Many Requests
```

---

## 🔍 Database Queries for Verification

```sql
-- Check token was created
SELECT * FROM cli_tokens WHERE user_id = YOUR_USER_ID;

-- Check token hash (should be encrypted)
SELECT token_hash FROM cli_tokens LIMIT 1;
-- Should NOT be readable plaintext

-- Check last_used_at updates
SELECT name, last_used_at FROM cli_tokens ORDER BY last_used_at DESC;

-- Check token count per user
SELECT user_id, COUNT(*) as token_count 
FROM cli_tokens 
GROUP BY user_id;
```

---

## ✅ Checklist

- [ ] CLI login flow works (authorize → oauth → token display)
- [ ] Token copy button works with success feedback
- [ ] Token format is `mxr_{60-chars}`
- [ ] Token stored encrypted in database
- [ ] Public endpoints work WITHOUT token (lower rate limits by IP)
- [ ] Public endpoints WITH valid token get higher rate limits
- [ ] Public endpoints WITH invalid token fall back to anonymous (graceful)
- [ ] Private endpoints REQUIRE valid token
- [ ] Invalid token on private endpoint returns 401 (strict)
- [ ] Valid token authenticates user correctly
- [ ] `last_used_at` updates on each API call
- [ ] Rate limiting for authenticated users: 10 req/sec, 1000 req/10min
- [ ] Rate limiting for anonymous users: 5 req/sec, 100 req/10min

---

## 🐛 Known Issues / TODO

1. **Token never expires** - Currently set to 2099-12-31
   - Consider: 90 days, 1 year, or add revocation UI

2. **No token management UI** - Users can't see/revoke tokens
   - TODO: `/account/tokens` page
   - Show: token name, created_at, last_used_at
   - Action: Revoke button

3. **No token naming** - All tokens called "CLI Token"
   - TODO: Let users name tokens ("My Laptop", "CI/CD", etc.)

4. **API endpoints not built yet** - Auth works, but endpoints need implementation:
   - `/api/formations/publish`
   - `/api/formations/@me`
   - `/api/stats/@me`
   - etc.

---

## 🔐 Security Design Decisions

### Invalid Token Handling (Graceful Degradation)

**Problem:** What should happen when an invalid/expired token is sent to a public endpoint?

**Our approach:**
- ✅ **Public endpoints**: Treat as anonymous (graceful degradation)
  - User still gets access with lower rate limits
  - Better UX - expired tokens don't break public API usage
  - Example: `muxi search` still works even if token expired
  
- ⚠️ **Private endpoints**: Return 401 error (strict validation)
  - Forces user to fix their token
  - Clear feedback that authentication failed
  - Example: `muxi push` requires valid token

**Rationale:**
- Similar to GitHub, NPM, Docker registries
- Public data should remain accessible
- Private operations require valid credentials
- Better developer experience

---

## 📝 Next Steps

1. ✅ Token display with copy button (DONE)
2. ✅ Public API endpoint checking (DONE)
3. ✅ Smart rate limiting with graceful degradation (DONE)
4. Test the flow end-to-end
5. Build actual API endpoints (Phase 2)
6. Build CLI tool that uses these tokens
7. Add token management UI (optional)
