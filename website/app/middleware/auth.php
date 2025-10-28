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
        } else {
            // Skip authentication for allowed paths when no user cookie exists
            if (!$this->userCookie->exists && $this->isAllowedPath()) {
                return;
            }
            $this->handleWebAuthentication();
        }

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
        // Extract bearer token from request headers
        $token = $this->getBearerToken();
        if (!$token) {
            $this->sendApiError('Cannot authenticate user', 'API-01');
        }

        // Decrypt the token to get the project ID
        $userId = $this->decryptToken($token);

        if (!$userId) {
            $this->sendApiError('Cannot authenticate user', 'API-01');
        }

        // --- rate limiting ---
        tiny::helpers(['ratelimiter']);
        $rateLimit = tiny::rateLimiter("api", 10, 1); // 10 requests per second
        $rateLimit->add(1000, 600); // max 1000 requests per 10 minutes
        if (!$rateLimit->check($userId)) {
            $this->sendApiError('Too Many Requests', 'API-03', 429);
        }
        // ---------------------

        // Set user data for the authenticated API request
        tiny::user([
            'id' => $userId,
        ]);
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
        return tiny::cypher()->decrypt($token, @$_SERVER['CRYPTO_SECRET']);
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
