# MUXI Registry - CLI & API Scope (Phase 2)

**Philosophy:** CLI for workflow, Web for discovery  
**Pattern:** Docker/GitHub/NPM model

---

## CLI Commands (Minimal Scope)

### Authentication
```bash
muxi login      # Browser OAuth flow → CLI token
muxi logout     # Clear local token
muxi whoami     # Show current user
```

### Discovery & Installation (Public)
```bash
muxi pull @user/formation    # Download and extract
muxi search <query>          # Search formations (list output)
```

### Publishing (Authenticated)
```bash
muxi push                    # Publish to YOUR account
muxi push --org muxi-ai      # Publish to organization (with permission check)
muxi list                    # List YOUR formations
muxi stats                   # YOUR stats (downloads, stars)
```

### Optional Later
```bash
muxi update @user/formation       # Update to latest version
muxi info @user/formation         # Show formation details
muxi unpublish @user/formation    # Delete formation
```

---

## Explicitly OUT of Scope for CLI

```bash
❌ muxi browse               # Use web: registry.muxi.org/browse
❌ muxi user @someone        # Use web: registry.muxi.org/@someone
❌ muxi trending             # Use web: homepage
```

**Rationale:**
- Discovery happens on the web (like Docker Hub, GitHub, NPM)
- CLI focused on workflow, not browsing
- Saves 1-2 weeks of development time
- Better UX (web UI superior for browsing)

---

## API Endpoints to Build

### Public (No Auth Required)

```bash
# Formation Discovery
GET  /api/formations/@:user/:name
     → Formation metadata (JSON)
     → Lazy fetch from GitHub if not cached
     Response: {
       name, description, version, 
       components: { agents, mcps, sops, triggers, knowledge },
       stats: { downloads, stars },
       readme_url, github_url, install_command
     }

# Search
GET  /api/search?q=query&sort=trending&limit=20
     → Search formations with FTS5 + fuzzy matching
     Response: {
       results: [...formations],
       total: 42,
       query: "customer support"
     }

# Download Tracking (Anonymous)
POST /api/formations/@:user/:name/:version/download
     → Record download stat
     → Returns GitHub release download URL
     Response: {
       download_url: "https://github.com/.../bundle.zip",
       version: "1.2.0"
     }
```

### Authenticated (Bearer Token Required)

```bash
# Publishing
POST /api/formations/publish
     Headers: Authorization: Bearer mxr_...
     Body: {
       github_repo: "user/muxi-formation",    # Personal account
       # OR
       github_repo: "muxi-ai/muxi-formation", # Organization
       version: "1.0.0",
       metadata: {...},
       org: "muxi-ai" (optional)  # If publishing to org
     }
     → Verify user has permission (if org)
     → Registry fetches from GitHub and caches
     → Creates org user if doesn't exist
     → Tracks actual publisher in published_by_user_id

# User Data
GET  /api/me
     → Current user info
     Response: {
       username, email, avatar,
       formations_count, total_downloads, total_stars
     }

GET  /api/me/formations
     → User's formations
     Response: {
       formations: [...],
       total: 5
     }

GET  /api/me/stats
     → Detailed stats
     Response: {
       total_downloads, total_stars,
       formations: [
         { name, downloads, stars, latest_version }
       ]
     }
```

### CLI Authentication Flow

```bash
# 1. CLI initiates
GET /api/cli/auth/begin
    Response: {
      device_id: "abc123",
      auth_url: "https://registry.muxi.org/cli/auth?device_id=abc123",
      polling_url: "/api/cli/auth/poll?device_id=abc123",
      expires_in: 600
    }

# 2. CLI opens browser to auth_url
GET /cli/auth?device_id=abc123
    → User sees "Authorize MUXI CLI"
    → Redirects to GitHub OAuth

# 3. OAuth callback
GET /auth/callback?code=...&state=cli:device_id:abc123
    → Exchange code for OAuth token
    → Generate CLI token
    → Store with device_id
    → Show success page: "Return to terminal"

# 4. CLI polls for token
GET /api/cli/auth/poll?device_id=abc123
    Response (pending): {
      status: "pending",
      expires_in: 540
    }
    
    Response (authorized): {
      status: "authorized",
      token: "mxr_AX7kL2p9...",
      user: { username, email, avatar }
    }
```

---

## Organization Publishing

### CLI Flow

```bash
$ muxi push --org muxi-ai

Checking permissions for muxi-ai...
✓ You are a member of muxi-ai
✓ You have repo creation permission

Publishing @muxi/my-formation...  # Note: @muxi, not @muxi-ai
→ Creating GitHub repo: github.com/muxi-ai/muxi-my-formation
→ Uploading files...
→ Creating release v1.0.0...
→ Notifying registry...

✓ Published successfully!
View at: https://registry.muxi.org/@muxi/my-formation
```

### Permission Verification

**Pre-flight check (CLI):**
```bash
# Before creating repo, CLI checks:
GET https://api.github.com/orgs/muxi-ai/memberships/:username
→ Verify user is member

GET https://api.github.com/orgs/muxi-ai
→ Check user has "admin" or "write" permission
```

**Server-side verification (API):**
```php
// In POST /api/formations/publish

if ($org) {
    // Use publisher's OAuth token to verify
    $github = new GitHubHelper($publisherOAuthToken);
    
    // Check membership
    $membership = $github->getOrgMembership($org, $publisher->github_username);
    if (!$membership || $membership['state'] !== 'active') {
        return $response->json(['error' => 'Not a member of org'], 403);
    }
    
    // Check permission
    if (!in_array($membership['role'], ['admin', 'member'])) {
        return $response->json(['error' => 'Insufficient permissions'], 403);
    }
}
```

### Database Handling

**When publishing to org:**

1. **Check if org exists in users table:**
   ```sql
   SELECT * FROM users WHERE github_username = 'muxi-ai';
   ```

2. **If not exists, create "ghost" org user:**
   ```php
   // Fetch org data from GitHub
   $orgData = $github->getOrg('muxi-ai');
   
   // Create user record
   $orgUserId = DB::insert('users', [
       'github_id' => $orgData['id'],
       'github_username' => 'muxi-ai',
       'registry_username' => 'muxi',  // From reserved_usernames
       'github_avatar' => $orgData['avatar_url'],
       'github_email' => null,         // Orgs don't have emails
       'github_type' => 'Organization',
       'bio' => $orgData['description'],
       'github_installation_id' => null,  // Not installed yet
       'github_oauth_token' => null,      // Org doesn't have token
       'is_verified' => true              // Official org
   ]);
   ```

3. **Create formation record:**
   ```php
   DB::insert('formations', [
       'user_id' => $orgUserId,              // Org's user_id
       'published_by_user_id' => $publisherUserId,  // Actual publisher
       'name' => 'my-formation',
       'github_repo' => 'muxi-ai/muxi-my-formation',
       // ... other fields
   ]);
   ```

### Reserved Username Mapping

```sql
-- In reserved_usernames table:
INSERT INTO reserved_usernames (registry_username, github_username)
VALUES ('muxi', 'muxi-ai');
```

**Result:**
- GitHub repo: `github.com/muxi-ai/muxi-customer-support`
- Registry URL: `registry.muxi.org/@muxi/customer-support` ✨
- Formation owner: @muxi (org)
- Published by: @ranaroussi (actual user)

### Who Can See What?

**On formation page:**
```
@muxi/customer-support
├── Owner: @muxi (verified org badge)
└── Published by: @ranaroussi
```

**In database queries:**
```sql
-- Get formations published by user (including to orgs)
SELECT * FROM formations 
WHERE published_by_user_id = :user_id;

-- Get formations owned by org
SELECT * FROM formations 
WHERE user_id = :org_user_id;
```

---

## Endpoints NOT Needed (Web Only)

```bash
❌ GET /api/browse              # Web uses direct DB queries
❌ GET /api/users/@:username    # Web uses direct DB queries
❌ GET /api/users               # Not needed
```

---

## CLI Token Format

```
mxr_[base64_encrypted_data]

Example: mxr_AX7kL2p9Mn4Qw8Zr5Vt1Jh6Gc3Fb0Nd
```

Encrypted payload:
```json
{
  "user_id": 123,
  "created_at": "2025-10-28T12:00:00Z",
  "expires_at": null,
  "scopes": ["read", "write"]
}
```

---

## User Journey

### Discovery (Web)
```
User visits registry.muxi.org
→ Homepage shows trending formations
→ Searches "customer support"
→ Finds @muxi/customer-support
→ Clicks formation → sees full details
→ Copies install command: muxi pull @muxi/customer-support
```

### Installation (CLI)
```bash
$ muxi pull @muxi/customer-support
Fetching @muxi/customer-support from registry...
Downloading v1.2.0 from GitHub...
✓ Downloaded 12 files (2.3 MB)
✓ Extracted to ./customer-support

To get started:
  cd customer-support
  muxi run
```

### Publishing (CLI)

**Personal account:**
```bash
$ cd my-formation
$ muxi login

Opening browser to authenticate...
→ [Browser opens]
→ [User authorizes with GitHub]
→ [Browser shows: "Success! Return to terminal"]

✓ Authenticated as @ranaroussi
Token saved to ~/.muxi/config.json

$ muxi push

Validating formation...
✓ Found muxi.yaml
✓ Configuration valid

Publishing @ranaroussi/my-formation...
→ Creating GitHub repository: github.com/ranaroussi/muxi-my-formation
→ Uploading files...
→ Creating release v1.0.0...
→ Notifying registry...

✓ Published successfully!
View at: https://registry.muxi.org/@ranaroussi/my-formation
```

**Organization:**
```bash
$ muxi push --org muxi-ai

Checking permissions for muxi-ai...
✓ You are a member of muxi-ai
✓ You have repo creation permission

Publishing @muxi/my-formation...  # Uses reserved username: muxi
→ Creating GitHub repository: github.com/muxi-ai/muxi-my-formation
→ Uploading files...
→ Creating release v1.0.0...
→ Notifying registry...

✓ Published successfully!
View at: https://registry.muxi.org/@muxi/my-formation
Published by: @ranaroussi to @muxi
```

---

## Phase 2 Implementation Order

1. **GitHub API Helper** (`app/helpers/github.php`)
   - Core foundation for everything
   - Repo metadata, README, releases
   - Organization methods (getOrg, getOrgMembership, verifyPermission)
   - Error handling, rate limiting

2. **Formation Model** (`app/models/formation.php`)
   - Lazy discovery logic
   - GitHub caching
   - Stats tracking

3. **Public API Endpoints**
   - GET /api/formations/@:user/:name
   - GET /api/search
   - POST /api/formations/.../download

4. **CLI Auth Flow**
   - Device code flow
   - Token generation
   - Polling endpoint

5. **Authenticated API Endpoints**
   - POST /api/formations/publish
   - GET /api/me (user data)

6. **Token Management**
   - Token display after auth
   - Token list/revoke (web UI)

---

## Rate Limiting

### Anonymous Requests (by IP)
- 60 requests/hour
- Applies to: search, formation metadata

### Authenticated Requests (by token)
- 5000 requests/hour
- Higher limits for CLI usage

Already implemented in `app/middleware/auth.php`!

---

## Success Criteria

Phase 2 is complete when:

✅ CLI can authenticate via browser  
✅ CLI can pull any formation  
✅ CLI can search formations  
✅ CLI can publish formations  
✅ CLI can list user's own formations  
✅ Lazy discovery works (any muxi-* repo is findable)  
✅ Download stats are tracked  
✅ All API endpoints documented  

---

## Development Time Estimate

**With lean scope (no browsing in CLI):**
- GitHub Helper: 2-3 days
- Formation Model: 2-3 days
- API Endpoints: 3-4 days
- CLI Auth Flow: 2-3 days
- Testing & Polish: 2-3 days

**Total: ~2 weeks**

**If we had included browsing/user profiles in CLI:** +1-2 weeks

---

**Last Updated:** 2025-10-28
