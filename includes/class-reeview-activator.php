<?php

/**
 * Fired during plugin activation
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Activator
{
    /**
     * This a hardcoded key used to generate another key - DO NOT MODIFY/DELETE
     */
    const KEY = 'F6VmCXvyr9Y5ptpr3Moy';

    /**
     * Call function to create custom table and set values for some of the config options.
     */
    public static function activate()
    {
        Reeview_Sql_Install::reeview_create_products_status_table();
        // Save in db the hardcoded key as option
        Reeview_Utils::set_option(REEVIEW_KEY_OPT, self::KEY);
        // Log by default
        Reeview_Utils::set_option(REEVIEW_OPTIONS, [REEVIEW_ENABLE_LOGS_OPT => REEVIEW_ENABLE_LOGS_OPT]);

        self::activate_cron_jobs();
    }

    /**
     * Register cron jobs and set time to start.
     */
    public static function activate_cron_jobs()
    {
        if (!wp_next_scheduled('cron_reeview_create')) {
            Reeview_Logger::errorLog('cron_reeview_create - REGISTERED - ' . time(), Reeview_Logger::NOTICE);
            wp_schedule_event(time() + 60 * 2, 'every_5_minutes', 'cron_reeview_create');
        }
        // create cron to start 15 min later
        if (!wp_next_scheduled('cron_reeview_update')) {
            Reeview_Logger::errorLog('cron_reeview_update - REGISTERED - ' . time(), Reeview_Logger::NOTICE);
            wp_schedule_event(time() + 60 * 3, 'every_5_minutes', 'cron_reeview_update');
        }
        // create cron to start 30 min later
        if (!wp_next_scheduled('cron_reeview_delete')) {
            Reeview_Logger::errorLog('cron_reeview_delete - REGISTERED - ' . time(), Reeview_Logger::NOTICE);
            wp_schedule_event(time() + 60 * 1, 'every_5_minutes', 'cron_reeview_delete');
        }
    }
}
