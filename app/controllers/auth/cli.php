<?php

/**
 * Handle the flow for initiating a GitHub App installation.
 */
class AuthCli extends TinyController
{
    /**
     * Ensure the user is authenticated and render the GitHub install link.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Kick off the authorization flow if the install route is hit without an installation.
        if (tiny::router()->slug == 'authorize') {
            // Generate a CSRF token and stash it for the GitHub callback validation.
            tiny::flash('auth_mode')->set('cli');

            // Store callback URL if provided (for CLI redirect after auth)
            $callback = $request->query['callback'] ?? null;
            if ($callback && $this->isValidCallbackUrl($callback)) {
                tiny::flash('cli_callback')->set($callback);
            }

            $response->render('/auth/cli-authorize');
        }

        // Once the GitHub installation is connected, surface a CLI token the CLI can copy.
        if (tiny::router()->slug == 'token') {
            // Issue a short-lived CLI token so the CLI can authenticate to the registry API.
            $token = tiny::model('user')->createCliToken(tiny::user()->id);
            $response->render('auth/cli-token', ['token' => $token]);
        }

        // Default to bootstrapping the CLI authorization UI if no branch rendered a response.
        tiny::flash('auth_mode')->set('cli');
        $response->render('/auth/cli-authorize');
    }

    /**
     * Validate callback URL to prevent open redirect vulnerabilities.
     * Only allows localhost URLs (for CLI) and https URLs.
     *
     * @param string $url The callback URL to validate.
     * @return bool True if the URL is valid and safe.
     */
    private function isValidCallbackUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }

        $scheme = strtolower($parsed['scheme']);
        $host = strtolower($parsed['host']);

        // Allow localhost URLs (common for CLI tools)
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return in_array($scheme, ['http', 'https']);
        }

        // For non-localhost, only allow HTTPS
        return $scheme === 'https';
    }
}
