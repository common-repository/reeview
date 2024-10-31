<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 */

if (current_user_can('manage_options')
    && $_REQUEST['plugin'] === 'reeview/reeview.php'
    && $_REQUEST['slug'] === 'reeview'
) {
    // The class responsible for removing configuration during plugin uninstall.
    require_once plugin_dir_path(__FILE__) . 'includes/class-reeview-uninstaller.php';
    require_once plugin_dir_path(__FILE__) . 'includes/sql/class-reeview-sql-uninstall.php';
    // The class that will define actions and callbacks functions for crons
    require_once plugin_dir_path(__FILE__) . 'includes/src/class-reeview-cron-jobs-callbacks.php';

    // Call function that will take care to delete everything the plugin created/set on install/activation.
    Reeview_Uninstaller::uninstall();
}

die("No permission to delete plugin....");

