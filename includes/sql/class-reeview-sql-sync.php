<?php

/**
 * Contains necessary sql for all cron jobs
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 */

/**
 * Contains necessary sql for all cron jobs.
 *
 * This class defines sql code necessary for cron jobs callback functions.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Cron_Jobs_Sql
{
    /**
     * Insert products into REEVIEW_PRODUCTS_STATUS_TABLE table that exists only in wp_posts table.
     */
    public static function reeview_insert_into_products_status_table()
    {
        global $wpdb;
        $vendorQuery = Reeview_Sql_Insert::get_vendor_in_query();
        $query = "INSERT INTO " . REEVIEW_PRODUCTS_STATUS_TABLE . " (`id`, `sku`, `name`, `img`, `updated_at`" . $vendorQuery['column'] . ")
                    (                        
                        SELECT p1.ID, pm.meta_value, p1.post_title, p2.guid, p1.post_date " . $vendorQuery['field'] . "
                        FROM " . $wpdb->prefix . "posts" . " p1
                        LEFT JOIN " . $wpdb->prefix . "posts" . " p2
                        ON p1.ID = p2.post_parent AND p2.post_type = 'attachment' " . $vendorQuery['join'] . "
                        LEFT JOIN " . $wpdb->prefix . "postmeta" . " pm
                        ON p1.ID = pm.post_id AND pm.meta_key = '_sku'
                        WHERE p1.post_type = 'product'
                            AND p1.ID NOT IN (SELECT r.id FROM " . REEVIEW_PRODUCTS_STATUS_TABLE . " as r)
                            AND p1.post_status='publish'
                        GROUP BY p1.ID
                        ORDER BY p1.ID ASC
                    );";
        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog(
                'No rows inserted into ' . REEVIEW_PRODUCTS_STATUS_TABLE . ' from cron/manual_sync.',
                Reeview_Logger::NOTICE,
                __METHOD__
            );
        }
    }

    /**
     * Update products in REEVIEW_PRODUCTS_STATUS_TABLE table
     * if REEVIEW_PRODUCTS_STATUS_TABLE.updated_at < wp_posts.updated_at .
     */
    public static function reeview_update_products_status_table()
    {
        global $wpdb;
        $vendorQuery = Reeview_Sql_Insert::get_vendor_in_query();
        $query = "UPDATE " . REEVIEW_PRODUCTS_STATUS_TABLE . " as r
                    LEFT JOIN " . $wpdb->prefix . "posts" . " p1
                             ON p1.ID = r.id
                    LEFT JOIN " . $wpdb->prefix . "posts" . " p2
                             ON p1.ID = p2.post_parent AND p2.post_type = 'attachment' 
                    LEFT JOIN " . $wpdb->prefix . "postmeta" . " pm
                             ON p1.ID = pm.post_id AND pm.meta_key = '_sku'
                    " . $vendorQuery['join'] . "
                    SET 
	                    r.sku = pm.meta_value,
                        r.name = p1.post_title,
                        r.img = p2.guid,
                        r.updated_at = p1.post_modified,
                        r.sends = 0 " . $vendorQuery['update'] . "
                    WHERE p1.post_modified > r.updated_at
                    AND (pm.meta_value != r.sku OR p1.post_title != r.name OR p2.guid != r.img " . $vendorQuery['compare'] . ") ;";
        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog(
                'No rows updated in ' . REEVIEW_PRODUCTS_STATUS_TABLE . ' from cron/manual_sync.',
                Reeview_Logger::NOTICE,
                __METHOD__
            );
        }
    }

    /**
     * Delete products in REEVIEW_PRODUCTS_STATUS_TABLE table that are not in wp_posts table.
     *
     * @param array $ids Array of ids to be deleted.
     */
    public static function reeview_delete_from_products_status_table($ids)
    {
        global $wpdb;
        $ids = implode("','", $ids);

        $query = "DELETE FROM  " . REEVIEW_PRODUCTS_STATUS_TABLE . " WHERE id IN ('" . $ids . "')";

        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog(
                'No rows deleted from ' . REEVIEW_PRODUCTS_STATUS_TABLE . ' by cron/manual_sync.',
                Reeview_Logger::NOTICE,
                __METHOD__
            );
        }
    }

    /**
     * Select products found only in REEVIEW_PRODUCTS_STATUS_TABLE
     *
     * @param bool $setLimit True: A limited number of items will be returned. False: All found items will be returned.
     * @return array|object|null
     */
    public static function reeview_select_only_in_products_status_table($setLimit = true)
    {
        global $wpdb;
        $query = "SELECT r.`id`, r.`name`, r.`img`, r.`vendor`, r.`sku` 
                    FROM  " . REEVIEW_PRODUCTS_STATUS_TABLE . " AS r
	                LEFT JOIN " . $wpdb->prefix . "posts" . " AS p ON p.ID = r.id
                    WHERE p.ID IS NULL
                        OR p.post_status = 'trash'
                    ORDER BY r.id ASC ";

        ($setLimit ? $query .= " LIMIT " . REEVIEW_BATCHES_LIMIT . ";" : $query .= ";");
        return $wpdb->get_results($query);
    }

    /**
     * Select products from REEVIEW_PRODUCTS_STATUS_TABLE table for sending them to Reeview.
     *
     * @param bool $setLimit True: A limited number of items should be return. False: All items will be selected.
     * @return array|object|null
     */
    public static function reeview_select_from_products_status_table($setLimit = true)
    {
        global $wpdb;
        $query = "SELECT r.`id`, r.`name`, r.`img`, r.`vendor`, r.`sku`  
                FROM " . REEVIEW_PRODUCTS_STATUS_TABLE . " AS r
                WHERE r.sends < " . REEVIEW_MAX_RETRIES . "
                AND (r.sync_at IS NULL OR r.sync_at < r.updated_at)
                ORDER BY r.updated_at ASC ";

        ($setLimit ? $query .= " LIMIT " . REEVIEW_BATCHES_LIMIT . ";" : $query .= ";");
        return $wpdb->get_results($query);
    }

    /**
     * Update sends and sync_at columns.
     * If updateBoth = false, only sends column will be updated.
     *
     * @param array $ids Array of ids to update.
     * @param bool $updateOnlySends Only sends column will be updated(incremented)
     * @return void
     */
    public static function reeview_update_sends_sync_at($ids, $updateOnlySends = false)
    {
        global $wpdb;
        $ids = implode("','", $ids);

        // only sends will be updated if data wasn't synced with Reeview
        ($updateOnlySends ? $failed = 1 : $failed = 0);

        $query = "UPDATE " . REEVIEW_PRODUCTS_STATUS_TABLE . " AS r
                     SET sends = sends + 1 ";

        if (!$updateOnlySends) {
            $query .= " , sync_at = CURRENT_TIMESTAMP() ";
        }
        $query .= " WHERE r.id IN( '" . $ids . "');";

        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog(
                'No rows updated (sends and/or sync_at) in ' . REEVIEW_PRODUCTS_STATUS_TABLE
                . ' from cron.manual_sync.',
                Reeview_Logger::NOTICE,
                __METHOD__
            );
        }
    }
}
