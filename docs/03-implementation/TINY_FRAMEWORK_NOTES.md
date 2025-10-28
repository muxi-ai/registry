# Tiny Framework Key Learnings

**From reading `/website/tiny/tiny.php`**

---

## Core Concepts

### 1. Underscore Prefix Convention

**Files starting with `_` are NOT auto-routed**

```php
// Comment in tiny.php line 286:
// "do not load files starting with underscore"
```

**Applies to:**
- Controllers (`app/controllers/_*.php`)
- Middleware (`app/middleware/_*.php`)
- Potentially views (though not explicitly stated)

**Example:**
```php
// app/controllers/_profile.php
// NOT accessible via URL: /profile or /_profile
// Can ONLY be called: tiny::controller('_profile', true)
```

**Use cases:**
- Special routing controllers (like profile pages via @username)
- Internal/helper controllers
- Controllers that should only be accessible programmatically

---

### 2. Auto-Routing Logic

**Path Resolution Order:**

```php
// From resolveControllerPath() - lines 238-259
$basePath = 'app/controllers/';
$paths = [
    "{controller}/{section}/{slug}",        // /user/profile/edit
    "{controller}/{section}/{hyphen-slug}", // /user/profile/my-profile
    "{controller}/{section}-{slug}",        // /user/profile-edit
    "{controller}/{section}-{hyphen-slug}", // /user/profile-my-page
    "{controller}/{section}",               // /user/profile
    "{controller}/index",                   // /user
    $controller,                            // /home
];

// Returns '404' if none found
```

**Examples:**
```
URL: /                     → app/controllers/home.php
URL: /about                → app/controllers/about.php
URL: /auth                 → app/controllers/auth/index.php
URL: /auth/callback        → app/controllers/auth/callback.php
URL: /user/profile         → app/controllers/user/profile.php
URL: /user/profile/edit    → app/controllers/user/profile/edit.php
URL: /nonexistent          → app/controllers/404.php
```

---

### 3. Controller Lifecycle

```
1. Router parses URL
   ↓
2. resolveControllerPath() finds matching file
   ↓
3. controller() method loads file
   ↓
4. Class name derived from file path
   ↓
5. HTTP method called (get, post, patch, delete)
   ↓
6. Response rendered
```

**Class Name Transformation:**

```php
// From line 529
$class = str_replace([' ', '-', '_', '.'], '', 
         ucwords(str_replace('/', ' ', $file)));

// Examples:
// 'home' → 'Home'
// 'auth/callback' → 'AuthCallback'
// 'user/profile' → 'UserProfile'
// '_profile' → 'Profile'
// '404' → 'Class404' (numeric classes get 'Class' prefix)
```

---

### 4. 404 Handling

**Automatic 404 routing:**

```php
// If controller not found, loads app/controllers/404.php
if (!file_exists($controllerPath)) {
    if (file_exists('app/controllers/404.php')) {
        require_once 'app/controllers/404.php';
    }
}
```

**This enables special routing patterns:**

```php
// app/controllers/404.php
class Class404 extends TinyController {
    public function get($request, $response) {
        // Check if URL starts with @
        if (str_starts_with(tiny::router()->controller, '@')) {
            // Route to _profile controller
            tiny::controller('_profile', true);
        }
        
        // Otherwise show 404 page
        tiny::render();
    }
}
```

---

### 5. The Request Worker

**Worker Stack:**

```php
// Tracks controller execution path
self::$router->worker = [$router['path']];

// Can push additional controllers
self::$router->worker[] = 'other-controller';

// Used for nested controller calls
$file = end(self::$router->worker);
```

**Example:**
```
Initial: ['home']
After routing: ['home', '_profile']
Render uses: end($worker) → '_profile'
```

---

### 6. Controller Method

```php
public static function controller(string $file = '', bool $die = false)
```

**Parameters:**
- `$file` - Controller file path (relative to `app/controllers/`)
- `$die` - Exit after execution (useful for sub-routing)

**Usage:**
```php
// Load and execute home controller
tiny::controller('home');

// Load _profile and exit (for 404 routing)
tiny::controller('_profile', true);

// Load nested controller
tiny::controller('user/profile');
```

---

### 7. Render Method

```php
public static function render(string $file = '', bool $die = false)
```

**Parameters:**
- `$file` - View file path (relative to `app/views/`)
- `$die` - Exit after rendering

**Usage:**
```php
// Auto-render view matching controller
tiny::render();

// Explicit view
tiny::render('profile/index');

// Render and exit
tiny::render('404', true);
```

---

### 8. Setup and Initialization

**Bootstrap sequence:**

```php
tiny::init() {
    1. loadConfig()        // Load app configuration
    2. initDB()            // Connect to database
    3. routerSetup()       // Parse URL and resolve controller
    4. loadHelpers()       // Load helper functions
    5. loadMiddleware()    // Execute middleware chain
    6. setupComponents()   // Initialize layouts/components
}
```

**Called from:** `html/index.php`

---

### 9. Middleware System

**Registration:**

```php
// app/middleware.php
tiny::middleware('auth');
tiny::middleware('version');
// Files starting with _ are IGNORED
```

**Execution:**

```php
foreach (self::$middlewares as $middleware) {
    require middleware file;
    $className = ucwords($middleware) . 'Middleware';
    (new $className())->handle();
}
```

**Middleware runs BEFORE controllers**

---

### 10. Component and Layout System

**Setup:**

```php
self::$components = new TinyComponent('app/views/components');
self::$layouts = new TinyLayout('app/views/layouts');

// Global constants for convenience
define('Component', self::$components);
define('Layout', self::$layouts);
```

**Usage:**

```php
// Via tiny class
tiny::components()->require('Footer');
tiny::components()->Footer();

// Via global constant (in views)
Component->require('Footer');
Component->Footer();
```

---

### 11. Data Sharing

**Global data store:**

```php
// Set data (in controller)
tiny::data()->username = 'ranaroussi';
tiny::data()->user = $userObject;

// Access data (in view)
echo tiny::data()->username;
if (tiny::data()->user) { ... }
```

**User data:**

```php
// Set user (usually in auth middleware)
tiny::user(['id' => 123, 'email' => 'user@example.com']);

// Access user (anywhere)
if (tiny::user()) {
    echo tiny::user()->email;
}
```

---

### 12. Router Information

**Access router data:**

```php
tiny::router()->uri           // /auth/callback
tiny::router()->controller    // auth
tiny::router()->section       // callback
tiny::router()->slug          // (empty)
tiny::router()->permalink     // https://example.com/auth/callback
tiny::router()->path          // auth/callback
tiny::router()->query         // ['code' => 'abc', 'state' => 'xyz']
tiny::router()->worker        // ['auth/callback']
tiny::router()->htmx          // true if HTMX request
```

---

### 13. URL Helpers

```php
// Generate home URL
tiny::homeURL('/path')        // https://example.com/path

// Generate static URL
tiny::staticURL('/css/app.css') // https://example.com/static/css/app.css

// Get home URL (function wrapper)
tiny::getHomeURL('/path')

// Get static URL (function wrapper)
tiny::getStaticURL('/css/app.css')
```

---

### 14. Configuration

**Access config:**

```php
tiny::config()                // Entire config object
tiny::config('app_path')      // Specific config value

// Available config:
tiny::config()->app_dir       // 'app'
tiny::config()->app_path      // '/path/to/app'
tiny::config()->homepage      // 'home'
tiny::config()->static_dir    // 'static'
tiny::config()->public_path   // '/path/to/html'
```

---

### 15. Database Access

```php
tiny::db()->getOne('table', ['id' => 1])
tiny::db()->getAll('table', ['status' => 'active'])
tiny::db()->insert('table', ['data' => 'value'])
tiny::db()->update('table', ['data' => 'new'], ['id' => 1])
tiny::db()->delete('table', ['id' => 1])
tiny::db()->lastInsertId()
```

---

### 16. Cache Access

```php
tiny::cache()->get('key')
tiny::cache()->set('key', 'value', 3600)
tiny::cache()->delete('key')
tiny::cache()->remember('key', 3600, function() {
    return expensive_operation();
})
```

---

## Key Patterns from MUXI Registry

### Pattern 1: Special Routing via 404

```php
// app/controllers/404.php
class Class404 extends TinyController {
    public function get($request, $response) {
        // Route @username URLs
        if (str_starts_with(tiny::router()->controller, '@')) {
            tiny::controller('_profile', true);
        }
        
        // Show 404 for everything else
        tiny::render();
    }
}
```

### Pattern 2: Non-Routable Controller

```php
// app/controllers/_profile.php
// Only accessible via tiny::controller('_profile')

class Profile extends TinyController {
    private $username;
    
    public function __construct() {
        // Extract username from @username route
        $this->username = substr(tiny::router()->controller, 1);
        tiny::data()->username = $this->username;
    }
    
    public function get($request, $response) {
        $response->render('profile/index');
    }
}
```

### Pattern 3: Middleware Authentication

```php
// app/middleware/auth.php
class AuthMiddleware {
    public function handle(): void {
        // Check if path is allowed without auth
        if ($this->isAllowedPath()) {
            return;
        }
        
        // Validate session
        $cookie = tiny::cookie('user');
        if (!$cookie->exists) {
            tiny::redirect('/auth/login');
        }
        
        // Load user data
        $userId = decrypt($cookie->data['hash']);
        $user = tiny::db()->getOne('users', ['id' => $userId]);
        tiny::user($user);
    }
}
```

---

## Performance Notes

### Caching Strategy

```php
// Router caching (1 hour)
$cacheKey = 'router_' . md5($url);
self::$router = self::cache()->remember($cacheKey, 3600, function() {
    // Expensive routing logic
});

// Config caching (1 hour)
$cacheKey = 'tiny_init_config';
$cachedConfig = self::cache()->get($cacheKey);
if ($cachedConfig === null) {
    // Load config
    self::cache()->set($cacheKey, self::$config, 3600);
}
```

**Cached:**
- Router resolution
- Configuration
- User data (in auth middleware)

**Not cached:**
- Database queries (unless explicitly cached)
- Controller/view rendering

---

## Common Gotchas

1. **Underscore files are not routed**
   - `_profile.php` cannot be accessed via URL
   - Must use `tiny::controller('_profile')` explicitly

2. **Numeric class names get prefixed**
   - `404.php` → `Class404`
   - Remember this when defining classes

3. **Worker stack affects render**
   - `tiny::render()` uses `end($worker)`
   - Be careful when pushing to worker stack

4. **Middleware runs before controllers**
   - Can't access controller data in middleware
   - Can redirect before controller executes

5. **Session starts automatically**
   - `session_start()` called in tiny.php
   - Session name is 'tiny'

---

**This is a living document. Add learnings as you discover more patterns in the codebase.**
