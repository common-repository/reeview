<?php

/**
 * Fired during the install endpoint call
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 */

/**
 * Fired during the install endpoint call.
 *
 * This class defines sql code necessary to run during the install endpoint call.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Sql_Insert
{
    public static function get_vendor_in_query()
    {
        global $wpdb;
        $vendorFieldName = Reeview_Utils::get_option(REEVIEW_OPTIONS)[REEVIEW_VENDOR_OPT];
        if (!$vendorFieldName) {
            return ['column' => "", 'field' => "", 'join' => "", 'update' => "", 'compare' => ""];
        }
        return [
            'column' => ", `vendor` ",
            'field' => " , t.name ",
            'join' => "
                LEFT JOIN " . $wpdb->prefix . "term_relationships" . " AS tr
                    ON p1.ID = tr.object_id 
                    AND tr.term_taxonomy_id IN (
                    SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_taxonomy" . " WHERE taxonomy = '" . $vendorFieldName . "'
                    )
                LEFT JOIN " . $wpdb->prefix . "terms" . " AS t
                    ON tr.term_taxonomy_id = t.term_id ",
            'update' => " ,
                        r.vendor =  t.name ",
            'compare' => " OR r.vendor !=  t.name "
        ];
    }

    /**
     * Insert all woo products into REEVIEW_PRODUCTS_STATUS_TABLE table.
     * @param int $cursor
     * @param int $limit
     */
    public static function reeview_insert_into_products_status_table($cursor, $limit)
    {
        global $wpdb;
        $vendorQuery = self::get_vendor_in_query();
        $insertQuery = "REPLACE INTO " . REEVIEW_PRODUCTS_STATUS_TABLE . " (`id`, `sku`, `name`, `img`, `updated_at`" . $vendorQuery['column'] . ", `sync_at`)
                        ( SELECT p1 . ID, p1 . post_name, p1 . post_title, p2 . guid, p1 . post_modified " . $vendorQuery['field'] . ", CURRENT_TIMESTAMP()
                            FROM " . $wpdb->prefix . "posts" . " p1
                            LEFT JOIN " . $wpdb->prefix . "posts" . " p2
                            ON p1.ID = p2.post_parent AND p2.post_type = 'attachment' " . $vendorQuery['join'] . " 
                        WHERE p1.post_type = 'product' 
                            AND p1.ID >= " . $cursor . "
                        GROUP BY p1.ID
                        ORDER BY p1.ID ASC
                        LIMIT " . $limit . "
                        );";
        $queryResult = $wpdb->query($insertQuery);
        if (!$queryResult) {
            Reeview_Logger::errorLog('No rows inserted into ' . REEVIEW_PRODUCTS_STATUS_TABLE . ' from /import.',
                Reeview_Logger::WRN,
                __METHOD__
            );
        }
    }
}
