<?php

/**
 * Status API endpoint
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 */

/**
 * Status API endpoint.
 *
 * This class is responsible for creating and implementing /reeview/status endpoint.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Status
{
    /** @var string Plugin namespace. */
    protected $namespace = 'reeview';

    protected $rest_base = 'status';

    /**
     * Reeview_Status constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'reeview_status_route'));
    }

    /**
     * Reeview status route definition.
     */
    public function reeview_status_route()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'reeview_status_route_callback'),
            'permission_callback' => array(Reeview_Api_Auth::class, 'no_permission_needed'),
        ));
    }

    /**
     * Function to execute when the endpoint is called.
     *
     * @return mixed|WP_REST_Response
     */
    public function reeview_status_route_callback()
    {
        $token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT);
        $data = [
            'hasToken' => (is_null($token) || empty($token) ? false : true)
        ];
        $response = rest_ensure_response($data);
        $response->set_status(200);
        return $response;
    }
}

new Reeview_Status();
