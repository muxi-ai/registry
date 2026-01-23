<?php


class AuthLogout extends TinyController
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
        tiny::model('user')->destroySession();
        tiny::redirect('/');
    }
}
