# GitHub App Setup Guide

**Step-by-step guide to create and configure the MUXI Registry GitHub App**

---

## 📋 Overview

The MUXI Registry uses a **GitHub App** (not OAuth App) for authentication because:
- ✅ Fine-grained permissions (only formation repos)
- ✅ Users control access per repository
- ✅ Better security and trust
- ✅ Clearer audit trail

---

## 🚀 Step 1: Create the GitHub App

### 1.1 Navigate to GitHub App Settings

1. Go to your **organization** settings (if creating for `@muxi-ai`):
   - https://github.com/organizations/muxi-ai/settings/apps
   - Click **"New GitHub App"**

   OR your **personal** settings (for testing):
   - https://github.com/settings/apps
   - Click **"New GitHub App"**

### 1.2 Basic Information

**GitHub App name:**
```
MUXI Registry
```
*(Must be unique across all of GitHub)*

**Homepage URL:**
```
https://registry.muxi.org
```
*(Use `http://localhost:8080` for local testing)*

**Description:**
```
Official GitHub App for MUXI Registry - publish and discover AI formations.
```

**Identifying and authorizing users:**
- ✅ Check: **"Request user authorization (OAuth) during installation"**

**Callback URL:**
```
https://registry.muxi.org/auth/callback
```
*(For local testing: `http://localhost:8080/auth/callback`)*

**Setup URL (optional):**
```
https://registry.muxi.org/setup
```
*(Can leave blank for now)*

**Webhook:**
- ❌ Uncheck: **"Active"** (we don't need webhooks for alpha)

---

## 🔐 Step 2: Set Permissions

**CRITICAL:** Only request the minimum permissions needed!

### Repository Permissions

**Contents:**
- Access: **Read and write**
- Why: Push formation code, create tags, releases

**Metadata:**
- Access: **Read-only** (automatically selected)
- Why: Read repo info, stars, etc.

### Organization Permissions

**Leave all as "No access"** - we don't need org-level permissions for alpha

### Account Permissions

Look for **one of these** (GitHub's UI varies):

**Option A: If you see "Administration":**

- Access: **Read and write**
- Why: Create new repositories on user's behalf

**Option B: If you see "Git SSH keys" or other options but NO "Administration":**

- Skip this section for now
- The repo creation permission might be elsewhere

**Email addresses:**
- Access: **Read-only** (if available here)
- Why: User identity and profile

**NOTE:** If you don't see an option to create repositories, that's okay! The permission might be granted through the OAuth scopes during installation. We'll test this after setup.

**DO NOT SELECT:**
- ❌ Issues, Pull Requests, Secrets, Webhooks, etc.
- Keep it minimal!

### What You Should See

After setting permissions, scroll down. You should have selected:

✅ **Repository permissions:**
- Contents: Read and write
- Metadata: Read-only

✅ **Organization permissions:**
- (All set to "No access")

✅ **Account permissions:**
- Email addresses: Read-only (if available)
- Administration: Read and write (if available)

**If "Administration" or repo creation permission is missing:**
Don't worry! We'll handle this through OAuth scopes. Continue with the setup.

---

## 🌍 Step 3: Installation Settings

**Where can this GitHub App be installed?**

Select: **"Any account"**
- Allows anyone to install the app (public registry)

---

## ✅ Step 4: Create the App

Click **"Create GitHub App"** button at the bottom.

---

## 🔑 Step 5: Generate Credentials

After creation, you'll be on the app settings page.

### 5.1 App ID

Note your **App ID** (you'll need this):
```
App ID: 123456
```

### 5.2 Generate Private Key

1. Scroll to **"Private keys"** section
2. Click **"Generate a private key"**
3. A `.pem` file will download
4. **Save this securely!** You can't download it again

**Save as:**
```
config/github-app-private-key.pem
```

### 5.3 Generate Client Secret

1. Scroll to **"Client secrets"** section
2. Click **"Generate a new client secret"**
3. Copy the secret (shown once!)

**Save as environment variable:**
```bash
GITHUB_APP_CLIENT_SECRET=abc123def456...
```

---

## 📝 Step 6: Configure Your App

Create `config/github-app.php`:

```php
<?php

return [
    'app_id' => 123456,  // Your App ID
    'client_id' => 'Iv1.abc123def456',  // From app settings page
    'client_secret' => getenv('GITHUB_APP_CLIENT_SECRET'),
    'private_key_path' => __DIR__ . '/github-app-private-key.pem',
    'webhook_secret' => null,  // Not needed for alpha
];
```

**Environment variables (.env):**
```bash
GITHUB_APP_ID=123456
GITHUB_APP_CLIENT_ID=Iv1.abc123def456
GITHUB_APP_CLIENT_SECRET=your_secret_here
GITHUB_APP_PRIVATE_KEY_PATH=/path/to/github-app-private-key.pem
```

---

## 🧪 Step 7: Test the Installation Flow

### 7.1 Get Installation URL

Your app installation URL is:
```
https://github.com/apps/muxi-registry/installations/new
```

Replace `muxi-registry` with your app's slug (lowercase, hyphenated name).

### 7.2 Test User Installation

1. Open the installation URL in browser
2. You should see GitHub's install screen:

```
┌─────────────────────────────────────────────────┐
│  Install MUXI Registry                          │
│                                                 │
│  Permissions:                                   │
│  ✓ Read and write access to code                │
│  ✓ Read metadata                                │
│  ✓ Read email addresses                         │
│                                                 │
│  Repository access:                             │
│  ○ All repositories                             │
│  ● Only select repositories ← Recommended       │
│                                                 │
│  [Install]  [Cancel]                            │
└─────────────────────────────────────────────────┘
```

**Note:** The exact wording may vary. Key things to check:
- ✅ Can read/write code (for pushing formations)
- ✅ Can read metadata (for repo info)
- ✅ Can read email (for user identity)

3. Click **"Install"**
4. GitHub redirects to: `https://registry.muxi.org/auth/callback?code=ABC&installation_id=12345`

### 7.3 Handle the Callback

You need to implement the callback handler (see IMPLEMENTATION-GUIDE.md):

```php
// routes/web.php
Route::get('/auth/callback', function() {
    $code = $_GET['code'] ?? null;
    $installationId = $_GET['installation_id'] ?? null;
    
    if (!$code) {
        return "Error: Missing code";
    }
    
    // Exchange code for token
    $github = new GitHubApp();
    $token = $github->getInstallationToken($installationId);
    
    // Create user, generate CLI token, etc.
    // See IMPLEMENTATION-GUIDE.md for full code
    
    return "Success! You're authenticated.";
});
```

---

## 🔧 Step 8: OAuth Implementation

### 8.1 Exchange Code for Token

```php
<?php

class GitHubApp {
    private $appId;
    private $clientId;
    private $clientSecret;
    private $privateKey;
    
    public function __construct() {
        $config = require __DIR__ . '/../config/github-app.php';
        $this->appId = $config['app_id'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->privateKey = file_get_contents($config['private_key_path']);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCode($code) {
        $url = 'https://github.com/login/oauth/access_token';
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception("GitHub OAuth error: " . $result['error_description']);
        }
        
        return $result['access_token'];
    }
    
    /**
     * Get user info with access token
     */
    public function getUser($accessToken) {
        $ch = curl_init('https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            'Accept: application/vnd.github+json',
            'User-Agent: MUXI-Registry'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Generate JWT for GitHub App authentication
     */
    private function generateJWT() {
        $now = time();
        
        $payload = [
            'iat' => $now,
            'exp' => $now + 600, // 10 minutes
            'iss' => $this->appId
        ];
        
        // Use JWT library or implement RS256 signing
        // For simplicity, use firebase/php-jwt package
        require_once 'vendor/autoload.php';
        return \Firebase\JWT\JWT::encode($payload, $this->privateKey, 'RS256');
    }
    
    /**
     * Get installation access token
     */
    public function getInstallationToken($installationId) {
        $jwt = $this->generateJWT();
        
        $url = "https://api.github.com/app/installations/$installationId/access_tokens";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $jwt",
            'Accept: application/vnd.github+json',
            'User-Agent: MUXI-Registry'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return $result['token'];
    }
}
```

### 8.2 Install JWT Library

```bash
composer require firebase/php-jwt
```

---

## 📱 Step 9: CLI Integration

The CLI needs to trigger this flow:

### Option 1: Browser-based Flow

```bash
$ muxi login

Opening browser to authenticate...
→ https://github.com/apps/muxi-registry/installations/new

Waiting for authentication...
```

**CLI Implementation:**
1. Generate random state token
2. Open browser to installation URL
3. Poll registry API for token (or use callback)
4. Save token to `~/.muxi/credentials.json`

### Option 2: Device Flow (Future)

GitHub also supports device flow (like `gh auth login`):
- Shows code on terminal
- User enters code in browser
- Better for headless systems

---

## 🔍 Step 10: Verify Installation

### 10.1 Check Installed Apps

Users can view installed apps at:
```
https://github.com/settings/installations
```

They should see:
```
┌─────────────────────────────────────────┐
│  MUXI Registry                          │
│  Installed on: Jan 15, 2025             │
│  Repository access: 3 repositories      │
│  [Configure] [Uninstall]                │
└─────────────────────────────────────────┘
```

### 10.2 Check Repository Access

When CLI runs `muxi push`, it should:
1. Use installation token
2. Create repo: `github.com/username/muxi-formation-name`
3. Push code, create tag, create release

### 10.3 Test API Calls

```php
// Test creating a repository (using user's OAuth token, not installation token)
$github = new GitHubApp();
$userToken = $github->exchangeCode($code);  // From OAuth callback

$ch = curl_init('https://api.github.com/user/repos');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $userToken",  // User's OAuth token
    'Accept: application/vnd.github+json',
    'User-Agent: MUXI-Registry'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'muxi-test-formation',
    'description' => 'Test formation',
    'private' => false
]));

$response = curl_exec($ch);
$result = json_decode($response, true);

if (isset($result['id'])) {
    echo "✓ Repository created successfully!\n";
    echo "URL: " . $result['html_url'] . "\n";
} else {
    echo "✗ Error: " . ($result['message'] ?? 'Unknown error') . "\n";
}
```

**Important:** Repository creation uses the **user's OAuth token**, not the installation token. The OAuth flow grants the necessary permissions.

---

## 🚨 Common Issues

### Issue 1: "Bad credentials"

**Cause:** Wrong token or expired JWT

**Fix:** 
- Verify App ID is correct
- Regenerate private key
- Check JWT expiration (should be < 10 minutes)

### Issue 2: "Resource not accessible by integration"

**Cause:** Missing permissions

**Fix:**
- Go to app settings
- Update permissions
- Users need to accept new permissions

### Issue 3: Callback not working

**Cause:** Wrong callback URL

**Fix:**
- Verify URL in app settings matches your server
- Check for HTTPS (GitHub requires HTTPS in production)
- For local testing, use ngrok or similar

---

## 🔒 Security Best Practices

1. **Never commit private key** to git
   - Add `*.pem` to `.gitignore`
   - Store in secure location

2. **Use environment variables** for secrets
   - Never hardcode client secret
   - Use `.env` file (and add to `.gitignore`)

3. **Rotate secrets regularly**
   - Generate new client secret every 90 days
   - Revoke old secrets

4. **Validate callback state** (CSRF protection)
   - Generate random state token
   - Verify on callback

5. **Use HTTPS in production**
   - GitHub requires HTTPS for OAuth callbacks
   - Use Let's Encrypt for free SSL

---

## 📚 Resources

- **GitHub Apps Documentation:** https://docs.github.com/en/developers/apps
- **Creating a GitHub App:** https://docs.github.com/en/developers/apps/building-github-apps/creating-a-github-app
- **Authenticating with GitHub Apps:** https://docs.github.com/en/developers/apps/building-github-apps/authenticating-with-github-apps
- **OAuth Flow:** https://docs.github.com/en/developers/apps/building-oauth-apps/authorizing-oauth-apps

---

## ✅ Checklist

Setup:
- [ ] Created GitHub App
- [ ] Set correct permissions (Contents R/W, Admin R/W, Email R)
- [ ] Generated private key (saved securely)
- [ ] Generated client secret (saved as env var)
- [ ] Configured callback URL
- [ ] Installed JWT library (`firebase/php-jwt`)

Testing:
- [ ] Can access installation URL
- [ ] Installation flow works (redirects to callback)
- [ ] Can exchange code for token
- [ ] Can fetch user info
- [ ] Can create repository with installation token

Integration:
- [ ] CLI can trigger OAuth flow
- [ ] CLI can save token
- [ ] CLI can use token for `muxi push`

---

**You're ready to authenticate users!** 🎉
