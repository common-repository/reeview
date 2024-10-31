<?php

/**
 * Contains functions to be executed on cron jobs and on demand sync
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 */

/**
 * Contains functions to be executed on cron jobs and on demand sync.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Sync
{
    const DELETE = 'DELETE';
    const CREATE = 'CREATE';
    const UPDATE = 'UPDATE';

    /**
     * Get items based on cron name.
     *
     * @param $cron
     * @param bool $setLimit If setLimit is true will return a number of REEVIEW_BATCHES_LIMIT items.
     *                       If setLimit is false will return all items for that cron type.
     * @return array|object|null
     */
    public static function get_cron_batches($cron, $setLimit = false)
    {
        switch ($cron) {
            case self::CREATE:
                Reeview_Cron_Jobs_Sql::reeview_insert_into_products_status_table();
                return Reeview_Cron_Jobs_Sql::reeview_select_from_products_status_table($setLimit);
                break;
            case self::UPDATE:
                Reeview_Cron_Jobs_Sql::reeview_update_products_status_table();
                return Reeview_Cron_Jobs_Sql::reeview_select_from_products_status_table($setLimit);
                break;
            case self::DELETE:
                return Reeview_Cron_Jobs_Sql::reeview_select_only_in_products_status_table($setLimit);
                break;
        }
    }

    /**
     * Parse items and send to Reeview.
     *
     * @param string $cron
     * @param array $batch Array of items ids to parse and send.
     * @return void
     */
    public static function parse_batch_and_send($cron, $batch)
    {
        $token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT);
        ($cron == self::DELETE ? $getOnlyItemsIds = true : $getOnlyItemsIds = false);

        $parsedItems = Reeview_Utils::map_items($batch, $getOnlyItemsIds, false);
        $is_send = Reeview_Connector::call_reeview_webhook(
            $cron,
            $token,
            $parsedItems['items']
        );

        if ($cron == self::DELETE) {
            Reeview_Cron_Jobs_Sql::reeview_delete_from_products_status_table($parsedItems['ids']);
        } elseif ($is_send) {
            // Log ids for items that were send
            Reeview_Logger::errorLog('Send items: ' . json_encode($parsedItems['items']),
                Reeview_Logger::NOTICE
            );
            Reeview_Cron_Jobs_Sql::reeview_update_sends_sync_at($parsedItems['ids']);
        } else {
            // Nothing was send and will try again when the cron starts next time
            Reeview_Cron_Jobs_Sql::reeview_update_sends_sync_at($parsedItems['ids'], true);
        }
    }
}
