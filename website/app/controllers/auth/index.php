<?php

/**
 * Kick off the GitHub OAuth sign-in flow for the registry.
 */
class Auth extends TinyController
{
    /**
     * Generate the authorize URL and redirect the browser to GitHub.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Create a one-time CSRF token and store it for the OAuth callback check.
        $state = bin2hex(random_bytes(16));
        tiny::flash('github_state')->set($state);

        // Build the GitHub authorization URL with scopes and callback information.
        $redirect_uri = urlencode(tiny::getHomeURL('/auth/callback', true));
        $url = "https://github.com/login/oauth/authorize?client_id={$_SERVER['APP_GITHUB_APP_CLIENT_ID']}&redirect_uri={$redirect_uri}&scope=public_repo,delete_repo,user:email&state={$state}";

        tiny::redirect($url);
    }
}
