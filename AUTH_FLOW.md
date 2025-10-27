# GitHub App Authentication Flow

**MUXI Registry - "Check First" Approach**

---

## 🎯 Core Concept

GitHub Apps have **two separate flows**:

1. **Installation** - Grants repository permissions (done ONCE)
2. **OAuth** - Authenticates user identity (done EVERY login)

We use a **"Check First"** approach to determine which flow to use.

---

## 🔑 The Two URLs

### Installation URL
```
https://github.com/apps/muxi-registry/installations/new
```

**What it does:**
- ✅ Installs the app on user's account
- ✅ User selects which repositories to grant access
- ✅ Grants permissions (read/write code, metadata, etc.)
- ✅ Also authenticates the user
- ✅ Redirects to callback with **BOTH** `code` AND `installation_id`

**Use when:**
- User has never installed the app
- User's installation was deleted/revoked
- First-time users

---

### OAuth URL
```
https://github.com/login/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=...&scope=user:email
```

**What it does:**
- ✅ Authenticates user identity only
- ✅ Quick authorization screen (already installed)
- ✅ Redirects to callback with **ONLY** `code` (no installation_id)

**Use when:**
- User has already installed the app
- We have their `installation_id` in database
- Returning users

---

## 🔄 "Check First" Flow

### User Journey

```
1. User clicks "Login with GitHub"
   ↓
2. Backend checks database:
   "Does this user exist with installation_id?"
   ↓
   ├─ NO (new user OR no installation)
   │   ├─ Redirect to: Installation URL
   │   ├─ GitHub shows: "Install MUXI Registry"
   │   │  - Grant access to repositories
   │   │  - Review permissions
   │   ├─ User clicks "Install"
   │   └─ Callback receives: code + installation_id
   │
   └─ YES (returning user with installation)
       ├─ Redirect to: OAuth URL
       ├─ GitHub shows: "Authorize MUXI Registry"
       │  - Quick confirmation screen
       ├─ User clicks "Authorize"
       └─ Callback receives: code only
```

---

## 📝 Implementation Details

### Step 1: Login Endpoint

**Route:** `GET /auth/login`

**Logic:**

```php
public function login(): void
{
    // Check if user is already authenticated
    if ($existingUser = $this->getAuthenticatedUser()) {
        // Check if they have an installation_id
        $user = $this->getUserFromDatabase($existingUser['github_id']);
        
        if ($user && $user['github_installation_id']) {
            // User has installation → use OAuth flow
            $this->redirectToOAuth();
        } else {
            // User exists but no installation → use installation flow
            $this->redirectToInstallation();
        }
    } else {
        // New user → use installation flow
        $this->redirectToInstallation();
    }
}
```

**Alternative Simpler Approach:**

Just check if a user cookie/session exists:

```php
public function login(): void
{
    // For new users, always go to installation
    // For returning users, they're already logged in
    $this->redirectToInstallation();
}
```

---

### Step 2A: Installation Flow

**Redirect to:**
```
https://github.com/apps/muxi-registry/installations/new?state=RANDOM_STATE
```

**Parameters:**
- `state` - Random CSRF token (store in session)

**What GitHub Does:**
1. Shows installation screen
2. User selects repositories
3. User clicks "Install"
4. Redirects to: `YOUR_CALLBACK_URL?code=ABC&installation_id=12345&state=RANDOM_STATE`

---

### Step 2B: OAuth Flow

**Redirect to:**
```
https://github.com/login/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=YOUR_CALLBACK_URL&state=RANDOM_STATE&scope=user:email
```

**Parameters:**
- `client_id` - Your GitHub App's client ID
- `redirect_uri` - Your callback URL
- `state` - Random CSRF token
- `scope` - Minimal: `user:email` (just identity)

**What GitHub Does:**
1. Shows "Authorize MUXI Registry" screen
2. User clicks "Authorize"
3. Redirects to: `YOUR_CALLBACK_URL?code=XYZ&state=RANDOM_STATE`

**Note:** No `installation_id` because app is already installed!

---

## 🎬 Callback Handler

**Route:** `GET /auth/callback`

**Receives:**

### Installation Callback
```
?code=AUTHORIZATION_CODE
&installation_id=12345678
&state=CSRF_TOKEN
&setup_action=install  (optional)
```

### OAuth Callback
```
?code=AUTHORIZATION_CODE
&state=CSRF_TOKEN
```

---

### Callback Logic

```php
public function callback(): void
{
    // 1. VERIFY STATE (CSRF protection)
    $state = $_GET['state'] ?? '';
    $sessionState = $this->getSessionState();
    
    if ($state !== $sessionState) {
        throw new Exception('Invalid state - CSRF protection');
    }
    
    // 2. GET AUTHORIZATION CODE
    $code = $_GET['code'] ?? null;
    if (!$code) {
        throw new Exception('Missing authorization code');
    }
    
    // 3. CHECK IF THIS IS AN INSTALLATION
    $installationId = $_GET['installation_id'] ?? null;
    $isInstallation = $installationId !== null;
    
    // 4. EXCHANGE CODE FOR ACCESS TOKEN
    $accessToken = $this->exchangeCodeForToken($code);
    
    // 5. GET USER INFO FROM GITHUB
    $githubUser = $this->getGitHubUser($accessToken);
    
    // 6. HANDLE BASED ON FLOW TYPE
    if ($isInstallation) {
        $this->handleInstallationCallback($githubUser, $installationId);
    } else {
        $this->handleOAuthCallback($githubUser);
    }
    
    // 7. REDIRECT TO SUCCESS PAGE
    $this->showSuccessPage();
}
```

---

## 📦 Installation Callback Handler

**Purpose:** Handle first-time app installation

**Receives:**
- `code` - Authorization code
- `installation_id` - GitHub App installation ID
- User info from GitHub API

**What to do:**

```php
private function handleInstallationCallback($githubUser, $installationId): void
{
    // 1. Check if user exists in database
    $existingUser = $this->findUserByGitHubId($githubUser['id']);
    
    if ($existingUser) {
        // User exists, just update installation_id
        $this->updateUser($existingUser['id'], [
            'github_installation_id' => $installationId,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);
        
        $userId = $existingUser['id'];
    } else {
        // New user - create account
        $registryUsername = $this->resolveRegistryUsername($githubUser['login']);
        
        $userId = $this->createUser([
            'github_id' => $githubUser['id'],
            'github_username' => $githubUser['login'],
            'registry_username' => $registryUsername,
            'github_avatar' => $githubUser['avatar_url'],
            'github_installation_id' => $installationId,  // ← Store this!
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    // 2. Generate CLI token
    $cliToken = $this->generateCliToken($userId);
    
    // 3. Set session cookie
    $this->setUserSession($userId);
    
    // 4. Store success data for display
    $this->setSuccessData([
        'user' => $this->getUserById($userId),
        'cli_token' => $cliToken,
        'is_new_installation' => true,
    ]);
}
```

**Key Points:**
- ✅ **Store `installation_id`** in database
- ✅ Create new user if doesn't exist
- ✅ Update existing user if exists
- ✅ Generate CLI token for `muxi` command
- ✅ Set session cookie for web

---

## 🔐 OAuth Callback Handler

**Purpose:** Handle returning user authentication (app already installed)

**Receives:**
- `code` - Authorization code
- User info from GitHub API
- **NO installation_id** (already in database)

**What to do:**

```php
private function handleOAuthCallback($githubUser): void
{
    // 1. Find user in database
    $user = $this->findUserByGitHubId($githubUser['id']);
    
    if (!$user) {
        // User doesn't exist - they need to install first!
        throw new Exception('App not installed. Please install the app first.');
    }
    
    if (!$user['github_installation_id']) {
        // User exists but no installation - redirect to installation
        $this->redirectToInstallation();
        return;
    }
    
    // 2. Update last seen
    $this->updateUser($user['id'], [
        'last_seen_at' => date('Y-m-d H:i:s'),
    ]);
    
    // 3. Generate NEW CLI token (optional - or reuse existing)
    $cliToken = $this->generateCliToken($user['id']);
    
    // 4. Set session cookie
    $this->setUserSession($user['id']);
    
    // 5. Store success data for display
    $this->setSuccessData([
        'user' => $user,
        'cli_token' => $cliToken,
        'is_new_installation' => false,
    ]);
}
```

**Key Points:**
- ✅ User **must exist** in database
- ✅ User **must have** `installation_id`
- ✅ Use existing `installation_id` from database
- ✅ Update last_seen timestamp
- ✅ Optionally generate new CLI token

---

## 🎨 Success Page

After either callback, show a success page with:

### For Installation (First Time)
```
✓ MUXI Registry Installed Successfully!

Welcome, @username!

Your CLI authentication token:
┌─────────────────────────────────────────┐
│ mxr_abc123def456...                     │
│ [Copy Token]                            │
└─────────────────────────────────────────┘

This token is shown ONCE. Copy it now.

Next steps:
1. Run: muxi login
2. Paste your token when prompted
3. Start publishing formations!

[Go to Dashboard]
```

### For OAuth (Returning User)
```
✓ Welcome back, @username!

You're logged in and ready to go.

Need a new CLI token?
┌─────────────────────────────────────────┐
│ mxr_xyz789ghi012...                     │
│ [Copy Token]                            │
└─────────────────────────────────────────┘

[Go to Dashboard]
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

**Note:** Both installation and OAuth flows use the **same token exchange endpoint**!

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

Make sure your `users` table has:

```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  github_id INTEGER UNIQUE NOT NULL,
  github_username TEXT NOT NULL,
  registry_username TEXT UNIQUE NOT NULL,
  github_avatar TEXT,
  github_installation_id INTEGER,  -- ← CRITICAL!
  is_verified BOOLEAN DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen_at DATETIME
);
```

**Note:** `github_installation_id` can be NULL initially if user does OAuth before installation (shouldn't happen with "Check First" approach).

---

## 🎯 Summary

### Installation Flow (First Time)
```
User clicks "Login"
  → No installation in DB
  → Redirect to: github.com/apps/muxi-registry/installations/new
  → User installs app
  → Callback: code + installation_id
  → Save user + installation_id
  → Show token
```

### OAuth Flow (Returning)
```
User clicks "Login"
  → Has installation in DB
  → Redirect to: github.com/login/oauth/authorize
  → User authorizes
  → Callback: code (no installation_id)
  → Use existing installation_id from DB
  → Show token
```

### Key Difference in Callbacks

| Callback Type | Receives | Action |
|--------------|----------|--------|
| **Installation** | `code` + `installation_id` | Create/update user, **store installation_id** |
| **OAuth** | `code` only | Update user, **use existing installation_id** |

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

## ✅ Checklist

- [ ] `/auth/login` checks for existing installation
- [ ] Installation URL used for new users
- [ ] OAuth URL used for returning users
- [ ] Callback handles both `code + installation_id` and `code` only
- [ ] `github_installation_id` stored in database
- [ ] CSRF state token verified
- [ ] CLI token generated and shown once
- [ ] Session cookie set for web access
- [ ] Success page shows appropriate message

---

**Ready to implement!** 🚀
