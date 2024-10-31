<?php

/**
 * Fired during plugin activation
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all sql code necessary to run during the plugin's activation.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/sql
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Sql_Install
{
    /**
     * Create REEVIEW_PRODUCTS_STATUS_TABLE table.
     */
    public static function reeview_create_products_status_table()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $createQuery = "CREATE TABLE " . REEVIEW_PRODUCTS_STATUS_TABLE . " (
		        id INTEGER (11) NOT NULL,
		        sku VARCHAR (100) NOT NULL,
		        name VARCHAR (150) NOT NULL,
		        img VARCHAR (100) NULL DEFAULT NULL,
		        vendor VARCHAR (100) NULL DEFAULT NULL,
		        sends INTEGER (11) NOT NULL DEFAULT 0,
		        updated_at TIMESTAMP NULL DEFAULT NULL,
		        sync_at TIMESTAMP NULL DEFAULT NULL,
		        PRIMARY KEY (id)
	    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $queryResult = dbDelta($createQuery);
        Reeview_Logger::errorLog(
            'Create table: ' . REEVIEW_PRODUCTS_STATUS_TABLE . '. Definition: ',
            Reeview_Logger::NOTICE
        );
        Reeview_Logger::errorLog(json_encode($queryResult), Reeview_Logger::NOTICE);
    }
}
