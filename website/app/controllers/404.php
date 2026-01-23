<?php
/**
 * 404 Error Handler Controller
 *
 * This controller handles 404 (Not Found) errors by:
 * 1. Collecting information about the user and request
 * 2. Preparing data for logging (currently commented out)
 * 3. Rendering the 404 error page
 */

 class Class404 extends TinyController
{
    /**
     * Handle 404 responses by routing special cases and rendering the error view.
     *
     * @param mixed $request Incoming Tiny request wrapper.
     * @param mixed $response Outgoing Tiny response wrapper.
     * @return void
     */
    public function get($request, $response)
    {
        // Route social-style profile URLs to the profile controller when present.
        if (str_starts_with(tiny::router()->controller, '@')) {
            // Check if this is a formation page (@user/formation) or profile (@user)
            $section = tiny::router()->section;

            if ($section) {
                // @user/formation - route to formation controller
                tiny::controller('_formation', true);
            } else {
                // @user - route to profile controller
                tiny::controller('_profile', true);
            }
            return;
        }

        if (isset(tiny::user()->email)) {
            // Create payload with user information and requested URL
            // This data can be used for error tracking and analytics
            $payload = [
                'email' => tiny::user()->email,    // User's email address
                'user_id' => tiny::user()->id,     // User's unique identifier
                'url' => tiny::router()->permalink, // The URL that resulted in 404
            ];

            // Add referrer information if available
            // This helps track where users are coming from when they hit 404 pages
            if (isset($_SERVER['HTTP_REFERER'])) {
                $payload['referer'] = $_SERVER['HTTP_REFERER'];
            }

            // Note: The payload is prepared but not currently used
            // Future implementation could send this data to a logging service
            // or analytics platform to track and analyze 404 occurrences
        }

        /**
         * Render the 404 error page template
         *
         * This function displays the appropriate 404 error page to the user.
         * The template is defined elsewhere and this call renders it.
         */
        tiny::render();
    }

}
