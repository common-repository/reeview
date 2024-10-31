<?php

/**
 * Fired during plugin uninstall
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run when the plugin is uninstall.
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Uninstaller
{
    const REEVIEW_PRODUCTS_STATUS_TABLE = 'reeview_products_status';
    const PLUGIN_OPTIONS = [
        REEVIEW_KEY_OPT => 'reeview_key',
        REEVIEW_TOKEN_OPT => 'reeview_token',
        REEVIEW_SITE_ID_OPT => 'reeview_site_id',
        REEVIEW_OPTIONS => 'reeview_option_name',
        REEVIEW_VENDOR_OPT => 'vendor',
        REEVIEW_ENABLE_LOGS_OPT => 'enable_logs'
    ];

    /**
     * Remove cron jobs, drop table and delete plugin configurations.
     */
    public static function uninstall()
    {
        Reeview_Sql_Uninstall::reeview_drop_products_status_table(self::REEVIEW_PRODUCTS_STATUS_TABLE);

        foreach (self::PLUGIN_OPTIONS as $option=>$optionName) {
            delete_option($optionName, null);
        }
        self::remove_cron_jobs();
    }

    /**
     * Remove actions, cron jobs and custom intervals.
     */
    public static function remove_cron_jobs()
    {
        // Instantiate to get access to cron callbacks
        $cronJobsCallbacks = new Reeview_Cron_Jobs_Callbacks();
        // remove actions
        remove_action('cron_reeview_create', array($cronJobsCallbacks, 'cron_reeview_create'), 10);
        remove_action('cron_reeview_update', array($cronJobsCallbacks, 'cron_reeview_update'), 10);
        remove_action('cron_reeview_delete', array($cronJobsCallbacks, 'cron_reeview_delete'), 10);
        // remove cron jobs
        wp_clear_scheduled_hook('cron_reeview_create');
        wp_clear_scheduled_hook('cron_reeview_update');
        wp_clear_scheduled_hook('cron_reeview_delete');

        remove_filter('cron_schedules', array('Reeview', 'add_cron_interval_5_min'));
    }
}
