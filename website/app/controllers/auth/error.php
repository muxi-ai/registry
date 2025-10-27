<?php

/**
 * Present user-facing errors encountered during authentication flows.
 */
class AuthError extends TinyController
{
    /**
     * Render the error view with flash messages describing what went wrong.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Pull the error details from flash storage and pass them to the template.
        $response->render('auth/error', [
            'message' => tiny::flash('error')->get(true),
            'error_code' => tiny::flash('error_code')->get(true),
        ]);
    }
}
