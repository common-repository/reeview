<?php
/**
 * Import API endpoint
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 */

/**
 * Import API endpoint.
 *
 * This class is responsible for creating and implementing /reeview/import endpoint.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/api
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_On_Demand_Sync
{
    /** @var array Order for crons to be executed. Do not change this order. */
    const CRONS_ORDER = [Reeview_Sync::DELETE, Reeview_Sync::CREATE, Reeview_Sync::UPDATE];

    /** @var string Plugin namespace. */
    protected $namespace = 'reeview';

    protected $rest_base = 'sync';

    protected $wpdb;

    /**
     * Reeview_Import constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        add_action('rest_api_init', array($this, 'reeview_sync_route'));
    }

    /**
     * Reeview sync route definition.
     */
    public function reeview_sync_route()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'reeview_sync_route_callback'),
            'permission_callback' => array(Reeview_Api_Auth::class, 'validate_access_token'),
        ));
    }

    /**
     * Function to execute when the endpoint is called.
     *
     * @param WP_REST_Request $request
     * @return mixed|WP_REST_Response
     */
    public function reeview_sync_route_callback($request)
    {
        foreach (self::CRONS_ORDER as $cron) {
            // Get all items
            $cronBatches = Reeview_Sync::get_cron_batches($cron);
            if (empty($cronBatches)) {
                continue;
            }
            // Split items in arrays of size = REEVIEW_BATCHES_LIMIT
            $cronBatches = array_chunk($cronBatches, REEVIEW_BATCHES_LIMIT);
            foreach ($cronBatches as $batch) {
                if (!empty($batch)) {
                    Reeview_Sync::parse_batch_and_send($cron, $batch);
                }
            }
        }
        $response = rest_ensure_response(null);
        $response->set_status(200);
        return $response;
    }
}

new Reeview_On_Demand_Sync();
