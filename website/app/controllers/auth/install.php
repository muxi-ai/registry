<?php

/**
 * Handle the flow for initiating a GitHub App installation.
 */
class AuthInstall extends TinyController
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
        // Block installation attempts from guests and send them to the login page.
        if (!tiny::user()) {
            tiny::flash('error')->set('You must be logged in to install the MUXI Registry GitHub app');
            tiny::redirect('/auth/login');
        }

        // Generate a CSRF token and stash it for the GitHub callback validation.
        $state = bin2hex(random_bytes(16));
        tiny::flash('github_state')->set($state);

        // Render install view with the GitHub URL containing the CSRF token state.
        $response->render('auth/install', [
            'install_url' => "https://github.com/apps/muxi-registry/installations/new?state={$state}",
        ]);
    }
}
