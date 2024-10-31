<?php

/**
 * The class that is responsible for calling Reeview API
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 */

/**
 * The class that is responsible for calling Reeview API.
 *
 * This class calls Reeview app and handles responses. One call will have n attempts.
 * If it returns error every time, error will be logged and process finished.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Connector
{
    const DOMAIN = 'https://api.reeview.app';
    const CREATE_PATH = '/products/webhooks/create';
    const UPDATE_PATH = '/products/webhooks/update';
    const DELETE_PATH = '/products/webhooks/delete';
    const ORDER_PATH = '/products/webhooks/order';
    const UNINSTALL_PATH = '/sites/webhooks/uninstall';

    /**
     * Return response from API calls.
     *
     * @param $url
     * @param $body
     * @param null $hmacHeader
     * @return mixed
     */
    public static function call_reeview($url, $body, $hmacHeader = null)
    {
        $headersArray['Content-Type'] = 'application/json';
        if ($hmacHeader) {
            $headersArray['x-reeview-hmac-sha256'] = $hmacHeader;
        }
        $args = array(
            'body' => json_encode($body),
            'timeout' => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headersArray,
            'cookies' => array(),
        );
        /** @var array|WP_Error $response */
        $response = wp_remote_post($url, $args);
        if (!is_wp_error($response)) {
            return json_decode(wp_remote_retrieve_body($response), true);
        } else {
            return false;
        }
    }

    /**
     * @param string $operation Possible values: CREATE|UPDATE|DELETE|ORDER
     * @param string $accessToken
     * @param array $data
     * @return bool
     */
    public static function call_reeview_webhook($operation, $accessToken, $data)
    {
        $body = ['accessToken' => $accessToken, 'data' => $data];
        switch ($operation) {
            case 'CREATE':
                $operation = self::CREATE_PATH;
                break;
            case 'UPDATE':
                $operation = self::UPDATE_PATH;
                break;
            case 'DELETE':
                $operation = self::DELETE_PATH;
                break;
            case 'ORDER':
                $operation = self::ORDER_PATH;
                break;
            case 'UNINSTALL':
                $operation = self::UNINSTALL_PATH;
                unset($body['data']);
                $body['siteId'] = $data;
                break;
        }
        $hmacHeader = Reeview_Utils::generate_hmac_signature($body, Reeview_Utils::get_option(REEVIEW_KEY_OPT));
        Reeview_Logger::errorLog($hmacHeader);
        $response = self::call_reeview(self::DOMAIN . $operation, $body, $hmacHeader);

        if ($response['statusCode'] != 201) {
            Reeview_Logger::errorLog('Connector FAILED. Error: ' . $response['error'] . ' Info: ' . json_encode($response['message']));
            return false;
        }
        return true;
    }
}
