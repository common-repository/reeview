<?php

/**
 * Fired during plugin deactivation
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Deactivator
{
    const PLUGIN_OPTIONS = [REEVIEW_TOKEN_OPT, REEVIEW_SITE_ID_OPT, REEVIEW_OPTIONS,
        REEVIEW_VENDOR_OPT, REEVIEW_ENABLE_LOGS_OPT];

    /**
     * Deactivate cron jobs, empty table and set to null plugin configurations.
     * @param bool $callReeviewUninstall
     */
    public static function deactivate($callReeviewUninstall = false)
    {
        // Tell Reeview the plugin will be deactivated
        Reeview_Connector::call_reeview_webhook(
            'UNINSTALL',
            Reeview_Utils::get_option(REEVIEW_TOKEN_OPT),
            Reeview_Utils::get_option(REEVIEW_SITE_ID_OPT)
        );
        // Empty table
        Reeview_Sql_Deactivate::reeview_empty_products_status_table();
        // Clean all plugin configurations
        foreach (self::PLUGIN_OPTIONS as $option) {
            update_option($option, null);
        }
        // Deactivate crons
        self::deactivate_cron_jobs();
    }

    public static function deactivate_cron_jobs()
    {
        $timestamp_create = wp_next_scheduled('cron_reeview_create');
        $timestamp_update = wp_next_scheduled('cron_reeview_update');
        $timestamp_delete = wp_next_scheduled('cron_reeview_delete');
        // un-schedule previous event if any
        wp_unschedule_event($timestamp_create, 'cron_reeview_create');
        wp_unschedule_event($timestamp_update, 'cron_reeview_update');
        wp_unschedule_event($timestamp_delete, 'cron_reeview_delete');
    }
}
