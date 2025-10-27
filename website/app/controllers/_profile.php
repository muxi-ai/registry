<?php

/**
 * Controller responsible for rendering user profile pages.
 */
class Profile extends TinyController
{
    private $username;

    /**
     * Derive the username from the dynamic route and expose it to the view.
     */
    public function __construct()
    {
        // Strip the leading underscore from the route-based controller name.
        $this->username = substr(tiny::router()->controller, 1);
        // Share the username with Tiny's global data store for templates.
        tiny::data()->username = $this->username;
    }

    /**
     * Render the profile page for the resolved username.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        $response->render('profile/index');
    }
}
