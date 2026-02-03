<?php
tiny::helpers(['geos']);

/**
 * Manage the GitHub OAuth callback flow and persist authenticated users.
 */
class AuthCallback extends TinyController
{
    /**
     * Validate the OAuth response, persist the user, and start a session.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Retrieve the state GitHub returned alongside the OAuth callback.
        $state = $request->query['state'] ?? '';
        // Pull the expected state from the session, clearing it as part of the read.
        $sessionState = tiny::flash('github_state')->get(true) ?? '';

        // Abort the flow if the state tokens do not match to block CSRF attempts.
        if ($state !== $sessionState) {
            tiny::flash('error')->set('Invalid state: CSRF protection');
            tiny::flash('error_code')->set('ATH-001');
            tiny::redirect('/auth/error');
        }

        // GitHub will supply an OAuth code that we trade for an access token.
        $code = $request->query['code'] ?? '';

        if (!$code) {
            tiny::flash('error')->set('Missing code');
            tiny::flash('error_code')->set('ATH-002');
            tiny::redirect('/auth/error');
        }

        // Exchange the OAuth code for access token
        // Note: OAuth App tokens are long-lived and don't expire
        $accessToken = $this->exchangeCodeForToken($code);

        // Fetch the authenticated user's GitHub profile and email information.
        $ghUser = $this->getGitHubUser($accessToken);
        if (!$ghUser) {
            tiny::flash('error')->set('Failed to get GitHub user');
            tiny::flash('error_code')->set('ATH-003');
            tiny::redirect('/auth/error');
        }

        // Store oauth token
        $ghUser->github_oauth_token = $accessToken;

        // Check if user already exists (for telemetry action)
        $existingUser = tiny::model('user')->findUserByGitHubId($ghUser->id);
        $isNewUser = empty($existingUser);

        try {
            // Upsert the GitHub user in our database and capture the Tiny user record.
            $user = tiny::model('user')->createOrUpdateUser($ghUser);
            if (!$user) {
                tiny::flash('error')->set('Failed to create or update user');
                tiny::flash('error_code')->set('ATH-004');
                tiny::redirect('/auth/error');
            }
        } catch (\Exception $e) {
            tiny::redirect($e);
        }

        // Send telemetry for registry signup/signin (fire and forget)
        $this->sendTelemetry($ghUser, $isNewUser ? 'signup' : 'signin');

        // create session
        tiny::model('user')->setSession($user['id']);

        // redirect to cli token page if auth mode is cli
        if (tiny::flash('auth_mode')->get() == 'cli') {
            // Check if callback URL was provided
            $callback = tiny::flash('cli_callback')->get(true);
            if ($callback) {
                // Generate CLI token and redirect to callback URL
                $token = tiny::model('user')->createCliToken($user['id']);
                $separator = str_contains($callback, '?') ? '&' : '?';
                tiny::redirect($callback . $separator . 'token=' . urlencode($token) . '&username=' . urlencode($user['registry_username']));
                return;
            }

            // No callback - redirect to token page (which generates its own token)
            tiny::redirect('/auth/cli/token');
            return;
        }

        // redirect to account page if auth mode is not cli
        tiny::redirect('/account');
    }

    /**
     * Swap an OAuth code for a GitHub access token using the GitHub token endpoint.
     *
     * @param string $code Short lived code returned by GitHub during OAuth callback.
     * @return string The access token
     */
    private function exchangeCodeForToken(string $code)
    {
        $url = "https://github.com/login/oauth/access_token";

        // Perform a POST request to GitHub's token endpoint with our app credentials.
        $response = tiny::http()->post($url, [
            'data' => [
                'client_id' => $_SERVER['APP_GITHUB_APP_CLIENT_ID'],
                'client_secret' => $_SERVER['APP_GITHUB_APP_CLIENT_SECRET'],
                'code' => $code,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ]
        ]);

        if (isset($response->json['error'])) {
            tiny::flash('error')->set(@$response->json['error_description']);
            tiny::redirect('/auth/error');
        }

        // OAuth App tokens are long-lived and don't expire (no refresh_token/expires_in)
        if (isset($response->json['access_token'])) {
            error_log("🔑 GitHub OAuth: Long-lived access_token received");
            return $response->json['access_token'];
        }

        return null;
    }

    /**
     * Query GitHub's APIs for user identity details and primary email address.
     *
     * @param string $accessToken OAuth access token with the `user` scope.
     * @return \stdClass|null GitHub user payload enriched with local fields.
     */
    private function getGitHubUser(string $accessToken)
    {
        // Retrieve the core GitHub user profile for the authenticated account.
        $user = tiny::http()->get('https://api.github.com/user', [
            'headers' => [
                "Authorization: Bearer {$accessToken}",
                'Accept: application/vnd.github+json',
                'User-Agent: MUXI-Registry'
            ]
        ])->json;

        // Split the provided name into first and last names for local storage.
        $name = tiny::parseName($user->name ?? '');
        $user->first_name = $name['firstname'];
        $user->last_name = $name['lastname'];

        // Also get email
        $emails = tiny::http()->get('https://api.github.com/user/emails', [
            'headers' => [
                "Authorization: Bearer {$accessToken}",
                'Accept: application/vnd.github+json',
                'User-Agent: MUXI-Registry'
            ]
        ])->json;

        // Find primary email
        foreach ($emails as $email) {
            if ($email->primary ?? false) {
                $user->email = $email->email;
                break;
            }
        }

        return $user;
    }

    /**
     * Send registry signup/signin data to telemetry endpoint.
     * Non-blocking - failures are logged but don't affect the auth flow.
     *
     * @param \stdClass $ghUser GitHub user data
     * @param string $action 'signup' for new users, 'signin' for returning users
     * @return void
     */
    private function sendTelemetry(\stdClass $ghUser, string $action): void
    {
        try {
            $payload = [
                'email' => $ghUser->email ?? null,
                'username' => $ghUser->login ?? null,
                'name' => $ghUser->name ?? null,
                'source' => 'registry',
                'action' => $action,
                'country' => tiny::geos()->getUserCountry(),
            ];

            // Include install_hash from uic cookie if available (shared across .muxi.org)
            if (!empty($_COOKIE['uic'])) {
                $payload['machine_id'] = $_COOKIE['uic'];
            }

            // Fire and forget - 2 second timeout
            tiny::http()->post('https://capture.muxi.org/v1/optin/', [
                'json' => $payload,
                'timeout' => 2,
            ]);
        } catch (\Throwable $e) {
            error_log("Registry telemetry error: " . $e->getMessage());
        }
    }
}
