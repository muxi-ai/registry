<?php

declare(strict_types=1);


tiny::helpers(['cypher']);

/**
 * AuthMiddleware Class
 *
 * Handles authentication for both web and API requests.
 * Validates user sessions, manages authentication tokens, and loads user data.
 */
class AuthMiddleware
{
    /**
     * Paths that are accessible without authentication
     */
    private const ALLOWED_PATHS = [
        'stripe/webhook',
        'r',
        'auth',
        'signup',
        '404',
        'webhooks',
        'rpc',
        'api',
    ];
    private const DISALLOWED_PATHS = [
        'account',
    ];

    private const PUBLIC_API_ENDPOINTS = [
        'GET /api/formations/*',          // Get single formation
        'GET /api/search',                // Search formations
        'POST /api/formations/*/download', // Track downloads (anonymous)
    ];

    private const ACCESS_MODE = 'disallowed';

    /**
     * User cookie object containing authentication data
     */
    private object $userCookie;

    /**
     * Main handler method that processes authentication for all requests
     *
     * @return void
     */
    public function handle(): void
    {
        // Load the user cookie
        $this->userCookie = tiny::cookie('user');

        // Handle different authentication flows based on request type
        if ($this->isApiRequest()) {
            $this->handleApiAuthentication();
            return;
        }

        // Skip authentication for allowed paths when no user cookie exists
        if (!$this->userCookie->exists && $this->isAllowedPath()) {
            return;
        }
        $this->handleWebAuthentication();

        // Load additional user data if user is authenticated
        if (tiny::user() && $this->userCookie->exists) {
            $this->loadUserData();
        }
    }

    /**
     * Checks if the current path is in the allowed paths list
     *
     * @return bool True if the path is allowed without authentication
     */
    private function isAllowedPath(): bool
    {
        if (self::ACCESS_MODE == 'allowed') {
            return in_array(tiny::router()->controller, self::ALLOWED_PATHS) ||
                in_array(tiny::router()->path, self::ALLOWED_PATHS);
        } else {
            return !in_array(tiny::router()->controller, self::DISALLOWED_PATHS) &&
                !in_array(tiny::router()->path, self::DISALLOWED_PATHS);
        }
    }

    /**
     * Determines if the current request is an API request
     *
     * @return bool True if the request path starts with 'api'
     */
    private function isApiRequest(): bool
    {
        return str_starts_with(tiny::router()->path, 'api');
    }

    /**
     * Handles authentication for API requests using bearer tokens
     *
     * @return void
     */
    private function handleApiAuthentication(): void
    {
        // Try to get bearer token (may be null for public endpoints)
        $token = $this->getBearerToken();
        $user = null;

        // Check if endpoint is public (needed for auth validation logic)
        $isPublic = $this->isPublicApiEndpoint();

        // If token is provided, validate it
        if ($token) {
            $user = tiny::model('user')->getUserByCliToken($token);

            // If token is invalid:
            // - For public endpoints: treat as anonymous (graceful degradation)
            // - For private endpoints: return 401 (strict validation)
            if (!$user && !$isPublic) {
                $this->sendApiError('Invalid authentication token', 'API-01', 401);
            }
            // For public endpoints with invalid token, $user stays null (anonymous access)
        }

        // If endpoint is not public and no valid user, require authentication
        if (!$isPublic && !$user) {
            $this->sendApiError('Authentication required', 'API-01', 401);
        }

        // --- rate limiting ---
        tiny::helpers(['ratelimiter']);

        if ($user) {
            // Authenticated user: Higher rate limits
            $rateLimit = tiny::rateLimiter("api_auth", 10, 1); // 10 requests per second
            $rateLimit->add(1000, 600); // max 1000 requests per 10 minutes
            $rateLimitIdentifier = 'lmt_'. (string)$user['id'];
        } else {
            // Anonymous user (public endpoints only): Lower rate limits by IP
            $rateLimit = tiny::rateLimiter("api_public", 5, 1); // 5 requests per second
            $rateLimit->add(100, 600); // max 100 requests per 10 minutes
            $rateLimitIdentifier = tiny::getClientRealIP();
        }

        if (!$rateLimit->check($rateLimitIdentifier)) {
            $this->sendApiError('Too Many Requests', 'API-03', 429);
        }

        // Additional rate limiting for expensive operations (file uploads)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            strpos($_SERVER['REQUEST_URI'], '/api/formations/publish') !== false) {

            // Stricter limits for uploads: 1 per minute, max 10 per hour
            $uploadRateLimit = tiny::rateLimiter("api_upload", 1, 60); // 1 per minute
            $uploadRateLimit->add(10, 3600); // Max 10 per hour

            if (!$uploadRateLimit->check($rateLimitIdentifier)) {
                $this->sendApiError(
                    'Upload rate limit exceeded. You can publish 1 formation per minute, maximum 10 per hour.',
                    'API-17',
                    429
                );
            }
        }
        // ---------------------

        // Set user data for authenticated API requests
        if ($user) {
            tiny::user($user);
        }
    }

    /**
     * Checks if the current API endpoint is public (doesn't require authentication)
     *
     * @return bool True if the endpoint is public
     */
    private function isPublicApiEndpoint(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'];
        // Use the full URI path, not just the controller
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $endpoint = $method . ' ' . $path;

        foreach (self::PUBLIC_API_ENDPOINTS as $pattern) {
            // Convert wildcard pattern to regex
            // Example: "GET /api/formations/*" becomes "GET /api/formations/.*"
            $regex = '/^' . str_replace(['/', '*'], ['\/', '.*'], $pattern) . '$/';

            if (preg_match($regex, $endpoint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles authentication for web requests using cookies
     *
     * @return void
     */
    private function handleWebAuthentication(): void
    {
        // Check if user cookie exists and contains hash
        if (!$this->userCookie->exists || !isset($this->userCookie->data['hash'])) {
            $this->redirectToSignin();
            return;
        }

        // Decrypt user data from the cookie hash
        $userData = tiny::model('user')->getSession($this->userCookie->data['hash']);
        if (!$userData) {
            $this->redirectToSignin();
            return;
        }

        // Set basic user data from the decrypted hash
        tiny::user(['id' => $userData]);
    }

    /**
     * Loads complete user data from database and caches it
     *
     * @return void
     */
    private function loadUserData(): void
    {
        // Extract basic user information
        $userId = (int)tiny::user()->id;

        // Create cache key for user data
        $cacheKey = 'user_' . $userId;

        // Skip cache in local environment
        $u = $_SERVER['ENV'] == 'local' ? null : tiny::cache()->get($cacheKey);

        if (!$u) {
            // Fetch user data from database if not in cache
            $dbUser = tiny::model('user')->getUserById((int)$userId);
            if (!$dbUser) {
                $this->handleInvalidUser();
            }

            // Convert to object and replace boolean strings
            $u = json_decode(str_replace(['"f"', '"t"'], ['false', 'true'], json_encode($dbUser)));

            // Cache the user data for 1 hour (3600 seconds)
            tiny::cache()->set($cacheKey, $u, 3600);
        }

        // Set the complete user data
        tiny::user($u);
    }

    /**
     * Extracts bearer token from request headers
     *
     * @return string|null The bearer token or null if not found
     */
    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }
        // Remove 'bearer' prefix and trim the token
        return tiny::trim(preg_replace('/^(?:bearer|Bearer)\s+/i', '', $headers['Authorization']));
    }

    /**
     * Decrypts an API token
     *
     * @param string $token The encrypted token
     * @return false|string The decrypted token or false on failure
     */
    private function decryptToken(string $token): false|string
    {
        return tiny::cypher()->decrypt($token, @$_SERVER['TINY_CRYPTO_SECRET']);
    }

    /**
     * Sends an API error response
     *
     * @param string $message Error message
     * @param string $id Error identifier
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function sendApiError(string $message, string $id, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        tiny::jsonResponse(['error' => true, 'message' => $message, 'id' => $id]);
    }

    /**
     * Redirects user to the sign-in page
     *
     * @return void
     */
    private function redirectToSignin(): void
    {
        // Store current URI for redirect after login
        tiny::flash('login_redir')->set(tiny::router()->uri);
        tiny::redirect('/auth/signin');
    }

    /**
     * Handles invalid user by destroying session and redirecting
     *
     * @return void
     */
    private function handleInvalidUser(): void
    {
        // Clear session and cookie data
        session_destroy();
        tiny::cookie('user')->destroy();
        // Redirect to sign-in page
        $this->redirectToSignin();
    }

}
