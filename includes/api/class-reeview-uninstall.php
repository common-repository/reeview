<?php

/**
 * Uninstall API endpoint
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 */

/**
 * Uninstall API endpoint.
 *
 * This class is responsible for creating and implementing /reeview/uninstall endpoint.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Uninstall
{
    /** @var string Plugin namespace. */
    public $namespace = 'reeview';

    protected $rest_base = 'uninstall';

    /**
     * Reeview_Uninstall constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'reeview_uninstall_route'));
    }

    /**
     * Reeview uninstall route definition.
     */
    public function reeview_uninstall_route()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'reeview_uninstall_route_callback'),
            'permission_callback' => array(Reeview_Api_Auth::class, 'validate_access_token'),
        ));
    }

    /**
     * Function to execute when the endpoint is called.
     *
     * @return mixed|WP_REST_Response
     */
    public function reeview_uninstall_route_callback()
    {
        // Call deactivator to empty table and clear configurations.
        Reeview_Deactivator::deactivate();
        $response = rest_ensure_response(null);
        $response->set_status(200);
        return $response;
    }
}

new Reeview_Uninstall();
