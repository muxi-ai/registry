# GitHub App Authentication Flow

**MUXI Registry - OAuth Scope Approach**

---

## 🎯 Core Concept

We use a **GitHub App with minimal permissions** + **OAuth scopes for repo access**.

**Why this approach?**
- ✅ No repo selection during installation (users don't need existing repos)
- ✅ OAuth token grants permission to create/manage repos
- ✅ Clean user experience (no confusing "select repositories" screen)
- ✅ More trustworthy than legacy OAuth Apps

**Two components:**
1. **GitHub App Installation** - Identity only (done ONCE)
2. **OAuth with Scopes** - Grants repo creation powers (done EVERY login)

---

## ⚙️ GitHub App Settings

**CRITICAL:** Your GitHub App should have **minimal permissions**:

### Repository Permissions
```
✅ Metadata: Read-only (automatic, can't remove)
❌ Contents: No access (REMOVE THIS!)
❌ Everything else: No access
```

### Account Permissions
```
✅ Email addresses: Read-only
❌ Everything else: No access
```

**Important:** Even with minimal permissions (just Metadata), GitHub **will still ask users to select repositories** during installation. This is normal behavior because Metadata is a repository-level permission.

**Guidance for users:**
```
"You'll be asked to grant repository access.
Select 'All repositories' or any repository - we only 
read public metadata (repo names, stars). Your code 
remains private. We use OAuth to create NEW repos when 
you publish formations."
```

---

## 🔑 The Installation + OAuth Flow

### Step 1: Installation URL
```
https://github.com/apps/muxi-registry/installations/new
```

**What it does:**
- ✅ Installs the app on user's account
- ✅ User selects which repositories to grant access (even with minimal permissions)
- ✅ Redirects to callback with `code` + `installation_id`

**GitHub shows:**
```
Install MUXI Registry

Repository access:
○ All repositories (recommended)
● Only select repositories

Permissions:
  ✓ Read access to metadata
  ✓ Read your email address

[Install]  [Cancel]
```

**Note:** Repository selection is required even though we only have "Metadata: Read-only" permission. This is GitHub's standard behavior for apps with any repository-level permission.

---

### Step 2: OAuth with Scopes
```
https://github.com/login/oauth/authorize?
  client_id=YOUR_CLIENT_ID
  &redirect_uri=YOUR_CALLBACK_URL
  &scope=public_repo user:email
  &state=RANDOM_STATE
```

**OAuth Scopes (CRITICAL):**
- `public_repo` - Create and manage public repositories
- `user:email` - Read user email address

**What it does:**
- ✅ Grants permission to create/manage repos
- ✅ Returns OAuth token with repo creation powers
- ✅ Redirects to callback with `code` (may include `installation_id` on first install)

**GitHub shows:**
```
Authorize MUXI Registry

MUXI Registry by @muxi-ai would like permission to:
  ✓ Create public repositories
  ✓ Commit code and create releases
  ✓ Read your email address

[Authorize]  [Cancel]
```

**Use `repo` scope instead of `public_repo` if you need private repo support**

---

## 🔄 Simplified Flow

**Key insight:** Always use the **Installation URL** - GitHub handles everything!

### User Journey

```
1. User clicks "Login with GitHub"
   ↓
2. Redirect to: Installation URL
   ↓
3. GitHub determines:
   ├─ NOT INSTALLED?
   │   ├─ Show: "Install MUXI Registry" (minimal permissions)
   │   ├─ User clicks "Install"
   │   ├─ THEN show: "Authorize MUXI Registry" (with repo scopes)
   │   ├─ User clicks "Authorize"
   │   └─ Callback: code + installation_id
   │
   └─ ALREADY INSTALLED?
       ├─ Show: "Authorize MUXI Registry" (just OAuth with scopes)
       ├─ User clicks "Authorize"
       └─ Callback: code + installation_id
```

**No "check first" needed!** GitHub handles it automatically.

---

## 📝 Implementation Details

### Step 1: Login Endpoint

**Route:** `GET /auth/login`

**Simple approach - always use installation URL:**

```php
public function login(): void
{
    // Generate CSRF token
    $state = bin2hex(random_bytes(16));
    $_SESSION['github_state'] = $state;
    
    // Always redirect to installation URL
    // GitHub automatically handles:
    // - New users: Install + OAuth
    // - Returning users: Just OAuth re-auth
    $url = "https://github.com/apps/muxi-registry/installations/new?state={$state}";
    
    header("Location: {$url}");
    exit;
}
```

**What GitHub Does:**

**First time (not installed):**
1. Shows "Install MUXI Registry" with repository selection
2. User selects repos (recommend "All repositories")
3. User clicks "Install"
4. Then shows "Authorize MUXI Registry" with scopes (public_repo, user:email)
5. User clicks "Authorize"
6. Redirects to callback with `code` + `installation_id`

**Returning (already installed):**
1. Shows "Authorize MUXI Registry" with scopes
2. User clicks "Authorize"  
3. Redirects to callback with `code` + `installation_id`

**Note:** Installation URL handles OAuth automatically! The callback gets the OAuth `code` which you exchange for a token with the requested scopes.

---

## 🎬 Callback Handler

**Route:** `GET /auth/callback`

**Always Receives:**
```
?code=AUTHORIZATION_CODE
&installation_id=12345678
&state=CSRF_TOKEN
&setup_action=install  (optional, only on first install)
```

**Note:** With installation URL approach, you always get `installation_id`!

---

### Callback Logic

```php
public function callback(): void
{
    // 1. VERIFY STATE (CSRF protection)
    $state = $_GET['state'] ?? '';
    $sessionState = $_SESSION['github_state'] ?? '';
    
    if ($state !== $sessionState) {
        throw new Exception('Invalid state - CSRF protection');
    }
    
    // 2. GET AUTHORIZATION CODE
    $code = $_GET['code'] ?? null;
    if (!$code) {
        throw new Exception('Missing authorization code');
    }
    
    // 3. GET INSTALLATION ID (always present with installation URL)
    $installationId = $_GET['installation_id'] ?? null;
    if (!$installationId) {
        throw new Exception('Missing installation_id');
    }
    
    // 4. EXCHANGE CODE FOR OAUTH ACCESS TOKEN
    // This token has public_repo + user:email scopes!
    $oauthToken = $this->exchangeCodeForToken($code);
    
    // 5. GET USER INFO FROM GITHUB
    $githubUser = $this->getGitHubUser($oauthToken);
    
    // 6. CREATE OR UPDATE USER
    $user = $this->createOrUpdateUser($githubUser, $installationId, $oauthToken);
    
    // 7. GENERATE CLI TOKEN
    $cliToken = $this->generateCliToken($user['id']);
    
    // 8. SET SESSION
    $this->setUserSession($user);
    
    // 9. SHOW SUCCESS PAGE
    $this->showSuccessPage($user, $cliToken);
}
```

---

## 📦 Create or Update User

**Purpose:** Handle user creation/update with OAuth token storage

**What to do:**

```php
private function createOrUpdateUser($githubUser, $installationId, $oauthToken): array
{
    // 1. Check if user exists in database
    $existingUser = $this->findUserByGitHubId($githubUser['id']);
    
    // 2. Resolve registry username (check reserved mappings)
    $registryUsername = $this->resolveRegistryUsername($githubUser['login']);
    
    // 3. Prepare user data
    $userData = [
        'github_id' => $githubUser['id'],
        'github_username' => $githubUser['login'],
        'registry_username' => $registryUsername,
        'github_avatar' => $githubUser['avatar_url'] ?? null,
        'github_email' => $githubUser['email'] ?? null,
        'github_installation_id' => $installationId,
        'github_oauth_token' => $this->encryptToken($oauthToken), // ← CRITICAL!
        'last_seen_at' => date('Y-m-d H:i:s'),
    ];
    
    if ($existingUser) {
        // UPDATE existing user
        $this->updateUser($existingUser['id'], $userData);
        $userId = $existingUser['id'];
    } else {
        // CREATE new user
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userId = $this->insertUser($userData);
    }
    
    // 4. Return complete user data
    return $this->getUserById($userId);
}
```

**Key Points:**
- ✅ **Store `installation_id`** - for app identity
- ✅ **Store `oauth_token` (ENCRYPTED!)** - for repo operations
- ✅ Create new user if doesn't exist
- ✅ Update existing user if exists
- ✅ OAuth token has `public_repo` scope for creating repos

---

## 🎨 Success Page

After callback, show success page:

```
✓ Authentication Successful!

Welcome, @username!

Your CLI authentication token:
┌─────────────────────────────────────────┐
│ mxr_abc123def456...                     │
│ [Copy Token]                            │
└─────────────────────────────────────────┘

⚠️ This token is shown ONCE. Copy it now.

Next steps:
1. Run: muxi login
2. Paste your token when prompted
3. Start publishing formations!

[Go to Dashboard]
```

**Implementation:**

```php
// views/auth/success.php
<div class="success-page">
    <h1>✓ Authentication Successful!</h1>
    <p>Welcome, <strong>@<?= $user['registry_username'] ?></strong>!</p>
    
    <div class="cli-token">
        <label>Your CLI authentication token:</label>
        <div class="token-box">
            <code id="token"><?= $cli_token ?></code>
            <button onclick="copyToken()">Copy Token</button>
        </div>
        <p class="warning">⚠️ This token is shown ONCE. Copy it now.</p>
    </div>
    
    <div class="next-steps">
        <h3>Next steps:</h3>
        <ol>
            <li>Run: <code>muxi login</code></li>
            <li>Paste your token when prompted</li>
            <li>Start publishing formations!</li>
        </ol>
    </div>
    
    <a href="/dashboard" class="btn">Go to Dashboard</a>
</div>

<script>
function copyToken() {
    navigator.clipboard.writeText(document.getElementById('token').textContent);
    alert('Token copied to clipboard!');
}
</script>
```

---

## 🔧 Helper Methods

### Exchange Code for Token

```php
private function exchangeCodeForToken(string $code): string
{
    $url = 'https://github.com/login/oauth/access_token';
    
    $response = $this->httpPost($url, [
        'client_id' => $this->clientId,
        'client_secret' => $this->clientSecret,
        'code' => $code,
    ], [
        'Accept: application/json'
    ]);
    
    if (isset($response['error'])) {
        throw new Exception($response['error_description']);
    }
    
    return $response['access_token'];
}
```

**Note:** This returns an OAuth token with the scopes you requested (public_repo + user:email)!

---

### Encrypt/Decrypt Token

```php
private function encryptToken(string $token): string
{
    return tiny::cypher()->encrypt($token, $_ENV['CRYPTO_SECRET']);
}

private function decryptToken(string $encrypted): string
{
    return tiny::cypher()->decrypt($encrypted, $_ENV['CRYPTO_SECRET']);
}
```

**CRITICAL:** Never store OAuth tokens in plain text!

---

### Get User Info

```php
private function getGitHubUser(string $accessToken): array
{
    $user = $this->httpGet('https://api.github.com/user', [
        "Authorization: Bearer {$accessToken}",
        'Accept: application/vnd.github+json',
        'User-Agent: MUXI-Registry'
    ]);
    
    // Also get email
    $emails = $this->httpGet('https://api.github.com/user/emails', [
        "Authorization: Bearer {$accessToken}",
        'Accept: application/vnd.github+json',
        'User-Agent: MUXI-Registry'
    ]);
    
    // Find primary email
    foreach ($emails as $email) {
        if ($email['primary'] ?? false) {
            $user['email'] = $email['email'];
            break;
        }
    }
    
    return $user;
}
```

---

## 🗄️ Database Schema

Update your `users` table:

```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,
  registry_username TEXT UNIQUE NOT NULL,
  github_avatar TEXT,
  github_email TEXT,
  github_installation_id INTEGER,        -- ← For app identity
  github_oauth_token TEXT,               -- ← ENCRYPTED! For repo operations
  is_verified BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);
```

**CRITICAL Fields:**
- `github_installation_id` - App installation ID (future webhooks, identity)
- `github_oauth_token` - **ENCRYPTED** OAuth token with `public_repo` scope (used for ALL repo operations)

---

## 🎯 Summary

### Simplified Flow (All Users)
```
User clicks "Login"
  → Redirect to: github.com/apps/muxi-registry/installations/new
  → GitHub handles: Install (if needed) + OAuth authorization
  → Callback: code + installation_id
  → Exchange code for OAuth token (with public_repo scope)
  → Save user + installation_id + encrypted OAuth token
  → Show CLI token
```

### Token Types

| Token Type | Purpose | Storage | Permissions |
|-----------|---------|---------|-------------|
| **OAuth Token** | Create/manage repos | Encrypted in DB | `public_repo` + `user:email` scopes |
| **Installation ID** | App identity | Plain in DB | For future webhooks/features |
| **CLI Token** | Authenticate `muxi` commands | Hashed in `tokens` table | Links to user_id |

### How to Use OAuth Token

```php
// When user runs: muxi push
$user = $this->getUserById($userId);
$oauthToken = $this->decryptToken($user['github_oauth_token']);

// Create repository
$this->createRepo($oauthToken, 'muxi-formation-name');

// Push code (Git uses token as password)
$repoUrl = "https://oauth2:{$oauthToken}@github.com/{$username}/muxi-formation-name.git";
exec("git remote add origin {$repoUrl}");
exec("git push origin main");

// Create release
$this->createRelease($oauthToken, $username, 'muxi-formation-name', 'v1.0.0');
```

---

## ⚠️ Edge Cases

### User Uninstalls App
- They won't show up in `/auth/login` check (no session)
- Will be sent to installation flow again
- New `installation_id` will be generated
- Update database with new `installation_id`

### User Has Multiple Accounts
- Each GitHub account has its own `installation_id`
- Track separately in database
- Sessions are per-account

### User Deletes Account Then Re-registers
- Old database record with old `installation_id`
- New installation creates new `installation_id`
- Update existing record with new `installation_id`

---

## ✅ Implementation Checklist

### GitHub App Configuration
- [ ] Remove "Contents" permission from app (should be "No access")
- [ ] Keep only "Metadata" (read) and "Email addresses" (read)
- [ ] Set callback URL in app settings
- [ ] Accept that repo selection will be required (Metadata permission causes this)
- [ ] Add clear messaging about why repo access is needed

### Code Implementation
- [ ] `/auth/login` redirects to installation URL
- [ ] Installation URL includes state parameter (CSRF)
- [ ] Callback receives: `code` + `installation_id`
- [ ] Exchange code for OAuth token (has `public_repo` scope)
- [ ] Store OAuth token **ENCRYPTED** in database
- [ ] Store `installation_id` in database
- [ ] CSRF state token verified
- [ ] CLI token generated and shown once
- [ ] Session cookie set for web access

### Database
- [ ] `users` table has `github_oauth_token` column
- [ ] `users` table has `github_installation_id` column
- [ ] `users` table has `github_email` column
- [ ] Encryption secret configured in environment

### Testing
- [ ] Install flow works (first time users)
- [ ] Re-auth flow works (returning users)
- [ ] OAuth token can create repositories
- [ ] OAuth token can push code
- [ ] OAuth token can create releases
- [ ] CLI token authentication works

### Security
- [ ] OAuth tokens stored encrypted
- [ ] CSRF state tokens validated
- [ ] Environment secrets not committed to git
- [ ] Token decryption only happens when needed

---

## 🔧 Using the OAuth Token

When user runs `muxi push`, the CLI will:

```php
// 1. Get user from CLI token
$user = $this->getUserFromCliToken($cliToken);

// 2. Decrypt OAuth token
$oauthToken = $this->decryptToken($user['github_oauth_token']);

// 3. Create repo using OAuth token
$repo = $this->createGitHubRepo($oauthToken, [
    'name' => 'muxi-customer-support',
    'description' => 'AI customer support formation',
    'private' => false,
]);

// 4. Push code using OAuth token
$this->pushToGitHub($oauthToken, $repoUrl, $localPath);

// 5. Create release using OAuth token
$this->createGitHubRelease($oauthToken, $repoUrl, 'v1.0.0');
```

---

**Ready to implement!** 🚀
