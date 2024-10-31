<?php

/**
 * Fired during plugin uninstall
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all sql code necessary to run during the plugin uninstall.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Sql_Uninstall
{
    /**
     * Drop REEVIEW_PRODUCTS_STATUS_TABLE table.
     *
     * @param string $tableName
     */
    public static function reeview_drop_products_status_table($tableName)
    {
        global $wpdb;
        $query = "DROP TABLE IF EXISTS " . $tableName . ";";
        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog('Drop table: ' . REEVIEW_PRODUCTS_STATUS_TABLE . ' - FAILED');
        }
    }
}
