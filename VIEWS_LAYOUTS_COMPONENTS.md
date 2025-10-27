# Views, Layouts & Components Guide

**MUXI Registry - Tiny Framework View System**

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Layouts](#layouts)
3. [Components](#components)
4. [View Patterns](#view-patterns)
5. [Best Practices](#best-practices)
6. [Examples](#examples)

---

## Overview

The Tiny Framework uses a **three-tier view system**:

1. **Layouts** - Page structure (header, footer, meta tags)
2. **Views** - Page content (specific to each page)
3. **Components** - Reusable UI elements (buttons, cards, toasts)

### The Rendering Flow

```
Controller
    ↓
View File (app/views/home.php)
    ↓
Layout (app/views/layouts/default/open.php + close.php)
    ↓
Components (app/views/components/*.php)
    ↓
Final HTML Output
```

---

## Layouts

### What Are Layouts?

Layouts define the **page structure** that wraps around your view content. They handle:
- `<html>`, `<head>`, `<body>` tags
- Meta tags (SEO, Open Graph, Twitter)
- CSS/JS includes
- Navigation header
- Footer
- Global structure

### Layout Structure

In MUXI Registry, layouts are **split into two files**:

```
app/views/layouts/default/
├── open.php     # Everything before content (doctype → <body> → header)
└── close.php    # Everything after content (footer → </body> → </html>)
```

### Using Layouts in Views

**Pattern:**
```php
<?php tiny::layout()->default(title: 'Page Title', emptyLayout: false); ?>

<!-- Your page content here -->

<?php tiny::layout()->default('/'); ?>
```

**Key Points:**
1. First line: **Opens** the layout with parameters
2. Middle: Your page content
3. Last line: **Closes** the layout (parameter is `/`)

### Layout Parameters

**Available parameters** (passed to `open.php`):

```php
tiny::layout()->default(
    title: 'Page Title',           // Page title (shown in <title> tag)
    emptyLayout: false,             // Show header/footer? (true = no header/footer)
    robots: 'index, follow',        // SEO robots meta tag
    ogImage: 'url',                 // Open Graph image URL
    isHome: true,                   // Adds 'home' class to <body>
    scripts: ['script1', 'script2'], // Additional JS files to load
    styles: ['style1', 'style2']     // Additional CSS files to load
);
```

**Access parameters in layout:**
```php
tiny::layout()->props('title')      // Get title
tiny::layout()->props('emptyLayout') // Get emptyLayout
```

### Example: Auth Install View

```php
<?php tiny::layout()->default(title: 'Install the GitHub App', emptyLayout: false); ?>

<div class="card w-full max-w-md mx-auto">
  <header class="space-y-2">
    <h2 class="text-xl mt-2 font-bold">Install the GitHub App</h2>
    <p>The MUXI Registry GitHub App is required to publish formations.</p>
  </header>
  <section class="space-y-4 text-sm">
    <p>MUXI Registry uses a GitHub App for secure authentication.</p>
  </section>
  <footer class="flex flex-col items-center gap-2">
    <a href="<?php echo tiny::data()->install_url; ?>" class="btn w-full">Install App</a>
    <a href="/dashboard" class="btn-secondary w-full">Skip for now</a>
  </footer>
</div>

<?php tiny::layout()->default('/'); ?>
```

### Layout Structure Breakdown

**open.php does:**
1. Outputs HTML doctype and `<head>` section
2. Sets page title using `tiny::layout()->props('title')`
3. Loads CSS files
4. Loads JavaScript files (Alpine.js, HTMX, etc.)
5. Renders navigation header (if `emptyLayout` is false)
6. Opens content container `<div>`

**close.php does:**
1. Closes content container `</div>`
2. Renders footer component (if `emptyLayout` is false)
3. Adds inline scripts
4. Closes `</body>` and `</html>` tags

### Empty Layout Mode

**When to use `emptyLayout: true`:**
- Error pages (404, 500)
- Modal/dialog pages
- Pages that don't need header/footer
- Standalone pages

**Example:**
```php
<?php tiny::layout()->default(title: 'Page not found', emptyLayout: true); ?>

<div class="flex flex-col items-center justify-center h-screen">
  <h1>404</h1>
  <p>Page not found</p>
  <a href="/" class="btn">Go Home</a>
</div>

<?php tiny::layout()->default('/'); ?>
```

---

## Components

### What Are Components?

Components are **reusable UI elements** that can be included anywhere. They're like functions for HTML.

**Location:** `app/views/components/`

### Component Registration Pattern

Components in MUXI Registry use this pattern:

```php
<?php
tiny::components()->register('ComponentName', function (...$props) {
    // Component logic here
    return 'HTML output';
});
```

### Example: Footer Component

```php
<?php
// app/views/components/Footer.php
tiny::components()->register('Footer', function (...$props) {
    $props['year'] = $props['year'] ?? date('Y');
    $props['rootPath'] = tiny::getHomeURL('/');
    
    return <<<EOF
    <section class="w-full bg-white">
        <div class="px-8 py-12 mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between pt-10 mt-10 border-t">
                <p class="text-sm text-gray-600">&copy; {$props['year']} VarOps LLC. All Rights Reserved.</p>
                <div class="flex items-start space-x-6">
                    <a href="#_" class="text-sm text-gray-600">Terms</a>
                    <a href="#_" class="text-sm text-gray-600">Privacy</a>
                </div>
            </div>
        </div>
    </section>
    EOF;
});
```

### Using Components

**Two-step process:**

1. **Require** the component (loads the file)
2. **Call** the component (renders it)

```php
<?php
tiny::components()->require('Footer');  // Load component file
tiny::components()->Footer();            // Render component
```

**With parameters:**
```php
tiny::components()->Footer(['year' => 2024]);
```

### Component in Layout

**In `close.php`:**
```php
<?php
if (tiny::layout()->props('emptyLayout') === false) {
  tiny::components()->require('Footer');
  tiny::components()->Footer();
}
?>
```

This renders the Footer component only when `emptyLayout` is false.

### Example: Toast Component

```php
<?php
// app/views/components/Toast.php
tiny::components()->register('Toast', function (...$props) {
    return '
  <div x-data="toast" @toast.window="addToastMessage(event.detail)" id="toasts">
    <template x-if="toastMessages.length" x-for="toast in toastMessages">
      <template x-if="toast.uid">
        <div :class="`toast toast-${toast.level}`">
          <button @click="removeToastMessage(toast.uid)" class="close">
            <span>Close</span>
          </button>
          <div class="flex">
            <div x-show="toast.title" class="toast-title" x-html="toast.title"></div>
          </div>
          <div class="toast-details" x-html="toast.message"></div>
        </div>
      </template>
    </template>
  </div>
';
});
```

**Usage:**
```php
<?php
tiny::components()->require('Toast');
tiny::components()->Toast();
?>

<script>
  // Trigger toast from JavaScript
  window.dispatchEvent(new CustomEvent('toast', {
    detail: {
      level: 'success',
      title: 'Success!',
      message: 'Operation completed successfully'
    }
  }));
</script>
```

### Example: AppScript Component

**Dynamic script loading:**

```php
<?php
// app/views/components/AppScript.php
tiny::components()->register('AppScript', function (...$props) {
    $inline = $props['inline'] ?? false;
    
    if ($inline) {
        // Load script content inline
        $source = tiny::config()->app_path . 'html/' . 
                  tiny::config()->static_dir . '/js/app-' . 
                  ($props['script'] ?? $props[0]) . '.min.js';
        $source = file_get_contents($source);
        return '<script>' . $source . '</script>';
    }
    
    // Load script as external file
    return '<script src="' . 
           tiny::getStaticURL('js/app-' . ($props['script'] ?? $props[0]) . '.min.js') . 
           '"></script>';
});
```

**Usage:**
```php
<?php
// Load external script
tiny::components()->require('AppScript');
tiny::components()->AppScript(['script' => 'dashboard']);

// Or inline
tiny::components()->AppScript(['script' => 'dashboard', 'inline' => true]);
```

---

## View Patterns

### Pattern 1: Simple View (Most Common)

```php
<?php tiny::layout()->default(title: 'Page Title', emptyLayout: false); ?>

<h1>Page Heading</h1>
<p>Content goes here</p>

<?php tiny::layout()->default('/'); ?>
```

### Pattern 2: View with Data from Controller

**Controller:**
```php
class Profile extends TinyController
{
    public function get($request, $response)
    {
        $username = substr(tiny::router()->controller, 1);
        
        // Share data with view
        tiny::data()->username = $username;
        tiny::data()->user = $this->getUserData($username);
        
        $response->render('profile/index');
    }
}
```

**View:**
```php
<?php tiny::layout()->default(title: tiny::data()->username, emptyLayout: false); ?>

<h1>Profile: @<?php echo tiny::data()->username; ?></h1>

<?php if (tiny::data()->user): ?>
  <p>Bio: <?php echo tiny::data()->user->bio; ?></p>
<?php else: ?>
  <p>User not found</p>
<?php endif; ?>

<?php tiny::layout()->default('/'); ?>
```

### Pattern 3: View with User Check

```php
<?php tiny::layout()->default(title: 'Dashboard', emptyLayout: false); ?>

<?php if (tiny::user()): ?>
  <h1>Welcome, <?php echo tiny::user()->first_name; ?>!</h1>
  <p>Email: <?php echo tiny::user()->github_email; ?></p>
  <p>Username: @<?php echo tiny::user()->registry_username; ?></p>
<?php else: ?>
  <h1>Please log in</h1>
  <a href="/auth/login" class="btn">Login with GitHub</a>
<?php endif; ?>

<?php tiny::layout()->default('/'); ?>
```

### Pattern 4: View with Components

```php
<?php tiny::layout()->default(title: 'Dashboard', emptyLayout: false); ?>

<h1>Dashboard</h1>

<?php
// Render custom component
tiny::components()->require('UserCard');
tiny::components()->UserCard([
    'user' => tiny::user(),
    'showBio' => true
]);
?>

<div class="grid">
  <?php foreach ($formations as $formation): ?>
    <?php
    tiny::components()->require('FormationCard');
    tiny::components()->FormationCard(['formation' => $formation]);
    ?>
  <?php endforeach; ?>
</div>

<?php tiny::layout()->default('/'); ?>
```

### Pattern 5: Error Pages

```php
<?php tiny::layout()->default(
    title: 'Page not found', 
    emptyLayout: true, 
    robots: 'noindex, follow'
); ?>

<div class="flex flex-col items-center justify-center h-screen">
  <h1 class="text-6xl font-bold">404</h1>
  <p class="text-xl mt-4">Page not found</p>
  <a href="/" class="btn mt-8">Go Home</a>
</div>

<?php tiny::layout()->default('/'); ?>
```

---

## Underscore Prefix Convention

### Controllers and Views That Should Not Be Auto-Routed

**Rule:** Prefix with underscore (`_`) to prevent direct URL access

**Why?**
- Files starting with `_` are **not auto-routed** by Tiny
- They can only be called programmatically
- Useful for controllers that should only be accessible through special routing

**Example: Profile Controller**

```php
// app/controllers/_profile.php
// NOT accessible via /profile or /_profile URLs
// Can only be called: tiny::controller('_profile', true)

class Profile extends TinyController {
    public function get($request, $response) {
        // Extract username from route
        $username = substr(tiny::router()->controller, 1);
        tiny::data()->username = $username;
        $response->render('profile/index');
    }
}
```

**Usage in 404 Handler:**
```php
// app/controllers/404.php
if (str_starts_with(tiny::router()->controller, '@')) {
    // Route /@username to _profile controller
    tiny::controller('_profile', true);
}
```

**When to use underscore prefix:**
- Controllers that should only be accessible via special routing (like `_profile`, `_formation`)
- Internal controllers that shouldn't have public URLs
- Helper controllers used by other controllers
- Views that are only included/rendered programmatically

**When NOT to use underscore:**
- Regular pages that need direct URL access (like `home`, `about`, `dashboard`)
- Public API endpoints
- Standard web pages

---

## Best Practices

### ✅ DO

1. **Always wrap views with layout calls**
   ```php
   <?php tiny::layout()->default(...); ?>
   // content
   <?php tiny::layout()->default('/'); ?>
   ```

2. **Use semantic HTML**
   ```php
   <article>
     <header><h1>Title</h1></header>
     <section>Content</section>
     <footer>Meta info</footer>
   </article>
   ```

3. **Pass data from controller to view**
   ```php
   // Controller
   tiny::data()->title = 'My Title';
   
   // View
   echo tiny::data()->title;
   ```

4. **Use components for reusable UI**
   ```php
   tiny::components()->require('Button');
   tiny::components()->Button(['text' => 'Click me', 'type' => 'primary']);
   ```

5. **Escape output to prevent XSS**
   ```php
   <?php echo htmlspecialchars($userInput); ?>
   ```

### ❌ DON'T

1. **Don't put business logic in views**
   ```php
   ❌ BAD:
   <?php
   $user = DB::query("SELECT * FROM users WHERE id = ?", [$id]);
   ?>
   
   ✅ GOOD:
   // Do this in controller
   $user = tiny::model('user')->getUserById($id);
   tiny::data()->user = $user;
   ```

2. **Don't skip layout calls**
   ```php
   ❌ BAD:
   <h1>Title</h1>
   
   ✅ GOOD:
   <?php tiny::layout()->default(title: 'Title'); ?>
   <h1>Title</h1>
   <?php tiny::layout()->default('/'); ?>
   ```

3. **Don't mix layout types in same view**
   ```php
   ❌ BAD:
   <?php tiny::layout()->default(...); ?>
   <?php tiny::layout()->admin(...); ?> // Wrong!
   ```

4. **Don't forget to pass emptyLayout parameter**
   ```php
   ❌ BAD:
   tiny::layout()->default(title: 'Title') // Missing emptyLayout
   
   ✅ GOOD:
   tiny::layout()->default(title: 'Title', emptyLayout: false)
   ```

---

## Examples

### Example 1: Formation Page (Future)

```php
<?php 
tiny::layout()->default(
    title: tiny::data()->formation->name, 
    emptyLayout: false,
    ogImage: tiny::data()->formation->cover_image
); 
?>

<div class="formation-page">
  <header class="formation-header">
    <h1>@<?php echo tiny::data()->formation->user; ?>/<?php echo tiny::data()->formation->name; ?></h1>
    <span class="version">v<?php echo tiny::data()->formation->latest_version; ?></span>
    
    <div class="stats">
      <span>⬇ <?php echo number_format(tiny::data()->formation->total_downloads); ?> pulls</span>
      <span>⭐ <?php echo tiny::data()->formation->github_stars; ?> stars</span>
    </div>
  </header>

  <div class="install-box">
    <h3>🚀 Installation</h3>
    <pre><code>muxi pull @<?php echo tiny::data()->formation->user; ?>/<?php echo tiny::data()->formation->name; ?></code></pre>
    <button onclick="copyToClipboard()">Copy</button>
  </div>

  <div class="readme">
    <?php echo parseMarkdown(tiny::data()->formation->readme_md); ?>
  </div>
</div>

<script>
  function copyToClipboard() {
    const code = 'muxi pull @<?php echo tiny::data()->formation->user; ?>/<?php echo tiny::data()->formation->name; ?>';
    navigator.clipboard.writeText(code);
    
    // Show toast notification
    window.dispatchEvent(new CustomEvent('toast', {
      detail: {
        level: 'success',
        title: 'Copied!',
        message: 'Installation command copied to clipboard'
      }
    }));
  }
</script>

<?php tiny::layout()->default('/'); ?>
```

### Example 2: Browse Page (Future)

```php
<?php tiny::layout()->default(title: 'Browse Formations', emptyLayout: false); ?>

<div class="browse-page">
  <header>
    <h1>Browse Formations</h1>
    <div class="filters">
      <a href="?sort=trending" class="btn">Trending</a>
      <a href="?sort=newest" class="btn">Newest</a>
      <a href="?sort=popular" class="btn">Most Popular</a>
    </div>
  </header>

  <div class="formations-grid">
    <?php foreach (tiny::data()->formations as $formation): ?>
      <?php
      tiny::components()->require('FormationCard');
      tiny::components()->FormationCard([
          'formation' => $formation,
          'showStats' => true
      ]);
      ?>
    <?php endforeach; ?>
  </div>

  <?php if (tiny::data()->hasMore): ?>
    <div class="pagination">
      <a href="?page=<?php echo tiny::data()->page + 1; ?>" class="btn">Load More</a>
    </div>
  <?php endif; ?>
</div>

<?php tiny::layout()->default('/'); ?>
```

### Example 3: User Dashboard (Future)

```php
<?php 
tiny::layout()->default(
    title: 'Dashboard', 
    emptyLayout: false,
    scripts: ['dashboard'] // Load dashboard-specific JS
); 
?>

<div class="dashboard" x-data="dashboard">
  <header>
    <h1>Dashboard</h1>
    <a href="/auth/logout" class="btn-secondary">Logout</a>
  </header>

  <section class="user-info">
    <img src="<?php echo tiny::user()->github_avatar; ?>" class="avatar" />
    <div>
      <h2><?php echo tiny::user()->first_name . ' ' . tiny::user()->last_name; ?></h2>
      <p>@<?php echo tiny::user()->registry_username; ?></p>
    </div>
  </section>

  <section class="quick-stats">
    <div class="stat-card">
      <h3><?php echo count(tiny::data()->formations); ?></h3>
      <p>Formations Published</p>
    </div>
    <div class="stat-card">
      <h3><?php echo tiny::data()->totalDownloads; ?></h3>
      <p>Total Downloads</p>
    </div>
  </section>

  <section class="formations-list">
    <h2>Your Formations</h2>
    <?php if (empty(tiny::data()->formations)): ?>
      <p>You haven't published any formations yet.</p>
      <a href="/docs/publishing" class="btn">Learn How to Publish</a>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Version</th>
            <th>Downloads</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (tiny::data()->formations as $f): ?>
            <tr>
              <td><a href="/@<?php echo $f->user; ?>/<?php echo $f->name; ?>"><?php echo $f->name; ?></a></td>
              <td><?php echo $f->latest_version; ?></td>
              <td><?php echo number_format($f->total_downloads); ?></td>
              <td>
                <a href="<?php echo $f->github_repo; ?>" target="_blank" class="btn-sm">GitHub</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>

<?php tiny::layout()->default('/'); ?>
```

---

## Quick Reference

### Layout Methods
```php
tiny::layout()->default(...)           // Open/close default layout
tiny::layout()->props('key')           // Get layout property
```

### Component Methods
```php
tiny::components()->require('Name')    // Load component file
tiny::components()->ComponentName()    // Render component
tiny::components()->register('Name', fn) // Register component
```

### Data Methods
```php
tiny::data()->key = 'value'           // Set data for view
echo tiny::data()->key                 // Get data in view
```

### User Methods
```php
tiny::user()                          // Get current user object
tiny::user()->property                // Access user properties
```

### URL Methods
```php
tiny::homeURL('/path')                // Generate absolute URL
tiny::staticURL('/css/file.css')      // Generate static asset URL
tiny::router()->permalink             // Current page URL
```

---

**For more details, see:**
- Tiny Framework docs: `/website/tiny/docs/`
- Layout extension: `/website/tiny/docs/extensions/layout.md`
- Components: `/website/tiny/docs/extensions/components.md`
