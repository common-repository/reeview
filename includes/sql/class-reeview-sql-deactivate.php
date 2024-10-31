<?php

/**
 * Fired during the uninstall endpoint call
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 */

/**
 * Fired during the uninstall endpoint call.
 *
 * This class defines all sql code necessary to run during the uninstall endpoint call.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Sql_Deactivate
{
    /**
     * Empty REEVIEW_PRODUCTS_STATUS_TABLE table.
     */
    public static function reeview_empty_products_status_table()
    {
        global $wpdb;
        $query = "TRUNCATE TABLE " . REEVIEW_PRODUCTS_STATUS_TABLE . ";";
        $queryResult = $wpdb->query($query);
        if (!$queryResult) {
            Reeview_Logger::errorLog('Truncate REEVIEW_PRODUCTS_STATUS_TABLE - FAILED');
        }
    }
}
