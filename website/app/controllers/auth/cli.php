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
}
