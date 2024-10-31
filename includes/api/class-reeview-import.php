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
class Reeview_Import
{
    /** @var string Plugin namespace. */
    protected $namespace = 'reeview';

    protected $rest_base = 'import';

    protected $wpdb;

    /**
     * Reeview_Import constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        add_action('rest_api_init', array($this, 'reeview_import_route'));
    }

    /**
     * Reeview import route definition.
     */
    public function reeview_import_route()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'reeview_import_route_callback'),
            'permission_callback' => array(Reeview_Api_Auth::class, 'validate_access_token'),
        ));
    }

    /**
     * Function to execute when the endpoint is called.
     *
     * @param WP_REST_Request $request
     * @return mixed|WP_REST_Response
     */
    public function reeview_import_route_callback($request)
    {
        $requestData = json_decode($request->get_body(), true);
        // Check for cursor and limit to be set
        $cursor = (int)$requestData['cursor'];
        if (is_null($requestData['cursor'])) {
            return new WP_Error('bad_request', __('\'cursor\' field not found or is not a number'), array('status' => 400));
        }
        if (!$limit = (int)$requestData['limit']) {
            return new WP_Error('bad_request', __('\'limit\' field not found or is not a number'), array('status' => 400));
        }
        // Insert into REEVIEW_PRODUCTS_STATUS_TABLE products that will be inserted
        Reeview_Sql_Insert::reeview_insert_into_products_status_table($cursor, $limit);
        $results = $this->get_items($cursor, $limit);

        $parsedItems = [];
        $lastId = $cursor;
        if ($results) {
            $parsedItems = Reeview_Utils::map_items($results);
            $lastId = end($parsedItems['ids']);
        }
        $cursor = null; // set null if next item doesn't exit
        $hasNext = false;
        if ($nextId = $this->exists_next_item($lastId)) {
            $cursor = $nextId;
            $hasNext = true;
        }
        $responseData = [
            'data' => $parsedItems['items'],
            'cursor' => $cursor,
            'hasNext' => $hasNext,
            'totalCount' => $this->get_total_item_count()
        ];
        $response = rest_ensure_response($responseData);
        $response->set_status(200);
        return $response;
    }

    /**
     * Return {$limit} items from id {$id} and update selected item sync_at column.
     *
     * @param $cursor
     * @param $limit
     * @return array|object|null
     */
    public function get_items($cursor, $limit)
    {
        $selectItemsQuery = $this->wpdb->prepare("SELECT *  FROM " . REEVIEW_PRODUCTS_STATUS_TABLE . "
                WHERE id >= %d
                ORDER BY `id` ASC
                LIMIT %d;", [$cursor, $limit]);
        return $this->wpdb->get_results($selectItemsQuery);
    }

    public function get_total_item_count()
    {
        global $wpdb;
        $totalCountQuery = "SELECT COUNT(*) as total_count FROM " . $wpdb->prefix . "posts" . ";";
        $totalCount = $this->wpdb->get_results($totalCountQuery);
        return $totalCount[0]->total_count;
    }

    /**
     * @param $lastImportedItem
     * @return int|bool Return next item id if exists. Return boolean false if nest item not found.
     */
    public function exists_next_item($lastImportedItem)
    {
        global $wpdb;
        $existsNextQuery = $this->wpdb->prepare("SELECT *  FROM " . $wpdb->prefix . "posts" . "
                WHERE ID > %d ORDER BY ID ASC LIMIT 1;", $lastImportedItem);
        $existsNextResult = $this->wpdb->get_results($existsNextQuery);
        if ($existsNextResult) {
            return $existsNextResult[0]->ID;
        }
        return false;
    }
}

new Reeview_Import();
