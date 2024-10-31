<?php

/**
 * Install API endpoint
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 */

/**
 * Install API endpoint.
 *
 * This class is responsible for creating and implementing /reeview/install endpoint.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Install
{
    /** @var string Plugin namespace. */
    protected $namespace = 'reeview';

    protected $rest_base = 'install';

    /**
     * Reeview_Install constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'reeview_install_route'));
    }

    /**
     * Reeview install route definition.
     */
    public function reeview_install_route()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'reeview_install_route_callback'),
            'permission_callback' => array(Reeview_Api_Auth::class, 'validate_key'),
        ));
    }

    /**
     * Function to execute when the endpoint is called.
     *
     * @param WP_REST_Request $request
     * @return mixed|WP_REST_Response
     */
    public function reeview_install_route_callback($request)
    {
        $requestData = json_decode($request->get_body(), true);
        // If token is not db : generate and save it
        $token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT);
        if (is_null($token) || empty($token)) {
            $token = Reeview_Utils::generate_token();
            Reeview_Utils::set_option(REEVIEW_TOKEN_OPT, $token);
            // Save siteId in db
            Reeview_Utils::set_option(REEVIEW_SITE_ID_OPT, $requestData['siteId']);
        }
        $data = [
            'accessToken' => $token
        ];
        $response = rest_ensure_response($data);
        $response->set_status(200);
        return $response;
    }
}

new Reeview_Install();
