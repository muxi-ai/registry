# User Messaging Guide

**How to explain GitHub permissions to users**

---

## 🎯 The Challenge

Users see a "Select repositories" screen during installation and might wonder:
- "Why does this need repo access?"
- "Will you access my private code?"
- "What repos should I select?"

Here's how to address these concerns transparently.

---

## 📝 Login Page Copy

### Before "Login with GitHub" Button

```html
<div class="auth-info">
  <h2>Login with GitHub</h2>
  
  <p>
    MUXI Registry uses a GitHub App for secure authentication.
    You'll be asked to:
  </p>
  
  <ol>
    <li><strong>Select repositories</strong> - We only read public metadata 
        (repo names, stars). Your code stays private.</li>
    <li><strong>Grant permissions</strong> - To create NEW repositories 
        when you publish formations.</li>
  </ol>
  
  <p>
    <small>
      💡 Tip: Select "All repositories" for easiest setup. We never 
      access your existing code.
    </small>
  </p>
  
  <button>Login with GitHub</button>
</div>
```

---

## ❓ FAQ Section

### Q: Why do you need repository access?

**A:** We request minimal permissions:

- ✅ **Metadata (Read-only)** - To read public repo info like names and star counts
- ✅ **Create repos** - To publish new formation repositories when you run `muxi push`
- ❌ **We do NOT access your existing code or private data**

All code is stored in YOUR GitHub repos under YOUR account.

---

### Q: What repositories should I select?

**A:** We recommend selecting **"All repositories"** for convenience. 

Don't worry - we only have "Read metadata" permission on existing repos, which means:
- ✅ We can see repo names and descriptions
- ❌ We CANNOT read your code
- ❌ We CANNOT make changes to existing repos

When you publish a formation, we create a NEW repository using OAuth permissions.

---

### Q: Can you access my private repos?

**A:** No. We only read **public metadata** (repo names, descriptions, star counts). 

We cannot:
- ❌ Read private code
- ❌ Read private repo contents
- ❌ Access issues or pull requests
- ❌ Modify existing repositories

---

### Q: What's the difference between the app installation and OAuth?

**A:** Great question!

1. **App Installation** (once) - Minimal permissions, just for identity
2. **OAuth Authorization** (every login) - Grants permission to create NEW repos

This separation ensures we only have access to what's necessary.

---

## 🎨 Installation Screen Guidance

### Add a Help Link

During the installation flow, you can add a "Setup URL" in GitHub App settings:

```
https://registry.muxi.org/install-help
```

This page should explain:

```html
<div class="install-help">
  <h1>Installing MUXI Registry</h1>
  
  <div class="permission-box">
    <h2>📋 Permissions Explained</h2>
    
    <div class="permission">
      <h3>✅ Read access to metadata</h3>
      <p>
        Allows us to read public information about your repositories:
      </p>
      <ul>
        <li>Repository names</li>
        <li>Descriptions</li>
        <li>Star counts</li>
        <li>Public URLs</li>
      </ul>
      <p><strong>Does NOT give access to:</strong></p>
      <ul>
        <li>❌ Your code</li>
        <li>❌ Private files</li>
        <li>❌ Issues or PRs</li>
        <li>❌ Commit history</li>
      </ul>
    </div>
    
    <div class="permission">
      <h3>✅ Read email address</h3>
      <p>For user identification and account creation.</p>
    </div>
    
    <div class="permission">
      <h3>✅ Create repositories (OAuth)</h3>
      <p>
        When you run <code>muxi push</code>, we create a new 
        repository under YOUR account to publish your formation.
      </p>
    </div>
  </div>
  
  <div class="recommendation">
    <h2>💡 Recommended Setup</h2>
    <ol>
      <li>Select <strong>"All repositories"</strong> when prompted</li>
      <li>Click <strong>"Install"</strong></li>
      <li>Click <strong>"Authorize"</strong> on the next screen</li>
      <li>Copy your CLI token</li>
      <li>Run <code>muxi login</code> in your terminal</li>
    </ol>
  </div>
  
  <div class="security">
    <h2>🔒 Security & Privacy</h2>
    <ul>
      <li>✅ All code stays on YOUR GitHub account</li>
      <li>✅ We never store your code on our servers</li>
      <li>✅ OAuth tokens are encrypted in our database</li>
      <li>✅ You can revoke access anytime</li>
    </ul>
    
    <p>
      Revoke access at: 
      <a href="https://github.com/settings/installations">
        GitHub Settings → Applications
      </a>
    </p>
  </div>
</div>
```

---

## 📧 Email Confirmation

After successful installation, send an email:

```
Subject: Welcome to MUXI Registry! 🎉

Hi @username,

Thanks for installing MUXI Registry!

Your CLI token is: mxr_abc123... 
(Also visible at: https://registry.muxi.org/tokens)

Quick start:
1. Run: muxi login
2. Paste your token
3. Publish your first formation: muxi push

---

About the permissions you granted:

✓ Read metadata - We see your public repo names/stars
✗ We do NOT access your code or private data
✓ Create repos - When you publish formations

Questions? Visit https://registry.muxi.org/docs/security

Happy building!
The MUXI Team
```

---

## 🎯 Trust Signals

### On Your Landing Page

```html
<section class="trust">
  <h2>Trusted by developers</h2>
  
  <div class="security-badges">
    <div class="badge">
      <span class="icon">🔒</span>
      <h3>Your Code Stays Yours</h3>
      <p>All formations stored on YOUR GitHub repos</p>
    </div>
    
    <div class="badge">
      <span class="icon">👁️</span>
      <h3>Minimal Permissions</h3>
      <p>Read-only metadata access only</p>
    </div>
    
    <div class="badge">
      <span class="icon">🔓</span>
      <h3>Open Source</h3>
      <p>Registry code is public on GitHub</p>
    </div>
    
    <div class="badge">
      <span class="icon">⚡</span>
      <h3>Revoke Anytime</h3>
      <p>Full control via GitHub settings</p>
    </div>
  </div>
</section>
```

---

## 🚨 What NOT to Say

❌ **"We need full access to your repositories"**
- Too vague and scary

❌ **"Just select all repos"**
- Doesn't explain why

❌ **"We read your code"**
- We don't! Don't say this

❌ **"Trust us"**
- Explain instead of asking for blind trust

---

## ✅ What TO Say

✅ **"We read public metadata like repo names and stars"**
- Specific and transparent

✅ **"Your code never leaves GitHub"**
- Clear security statement

✅ **"We create NEW repos when you publish"**
- Explains the create permission

✅ **"You can revoke access anytime"**
- Gives users control

---

## 📊 Messaging Priority

1. **Transparency** - Be clear about what permissions do
2. **Specificity** - Don't say "access" - say what KIND of access
3. **Security** - Emphasize data never leaves GitHub
4. **Control** - Users can revoke anytime
5. **Purpose** - Explain WHY each permission is needed

---

**Remember:** Users are rightfully protective of their code. Clear, honest communication builds trust.
