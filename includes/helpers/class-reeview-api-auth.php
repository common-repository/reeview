<?php

/**
 * Check API permission
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 */

/**
 * Check API permission.
 *
 * This class defines different validations for API.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Api_Auth
{
    /**
     * Validate access token from request.
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public static function validate_access_token($request)
    {
        $requestData = json_decode($request->get_body(), true);
        // Check for accessToken to be set
        if (!key_exists('accessToken', $requestData)) {
            return new WP_Error('bad_request', __('\'accessToken\' field not found'), array('status' => 400));
        }
        $token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT);
        if (strcmp($requestData['accessToken'], $token) !== 0) {
            return new WP_Error('bad_request', __('Access token do not match.'), array('status' => 400));
        }
        return true;
    }

    /**
     * Validate key from request.
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public static function validate_key($request)
    {
        $requestData = json_decode($request->get_body(), true);
        // Check for key and siteId to be set
        if (!key_exists('key', $requestData)) {
            return new WP_Error('bad_request', __('\'key\' field not found.'), array('status' => 400));
        }
        if (!key_exists('siteId', $requestData)) {
            return new WP_Error('bad_request', __('\'siteId\' field not found'), array('status' => 400));
        }
        // Check for the keys to match
        if ($requestData['key'] !== Reeview_Utils::generate_key()) {
            return new WP_Error('bad_request', __('Keys do not match.'), array('status' => 400));
        }
        return true;
    }

    /**
     * @return bool
     */
    public static function no_permission_needed()
    {
        return true;
    }
}
