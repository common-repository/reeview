<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://apps.shopify.com/reeview
 * @since             1.0.0
 * @package           Reeview
 *
 * @wordpress-plugin
 * Plugin Name:       Reeview
 * Plugin URI:
 * Description:       Reeview is the world’s first video delivery system that automatically searches and serves your Youtube product reviews, straight to your shoppers. Control the conversation and keep shoppers on your site with a ground-breaking widget that will revolutionize how you use video.
 * Version:           1.0.0
 * Author:            Reeview Inc.
 * Author URI:        https://reeview.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reeview
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Update it for new versions.
 */
define('REEVIEW_VERSION', '1.0.0');

/**
 * Table name. This table will be created on install and deleted on uninstall.
 * Also, will be emptied once the /reeview/uninstall endpoint is called.
 */
define('REEVIEW_PRODUCTS_STATUS_TABLE', 'reeview_products_status');

/**
 * Options name.
 * This will be saved in `wp_options` table and removed once the plugin
 * is uninstall or deactivated through the endpoint.
 */
define('REEVIEW_KEY_OPT', 'reeview_key');
define('REEVIEW_TOKEN_OPT', 'reeview_token');
define('REEVIEW_SITE_ID_OPT', 'reeview_site_id');
define('REEVIEW_OPTIONS', 'reeview_option_name');
define('REEVIEW_VENDOR_OPT', 'vendor');
define('REEVIEW_ENABLE_LOGS_OPT', 'enable_logs');

/**
 * Define how many attempts should be for sending data to Reeview when a cron job is executed.
 */
define('REEVIEW_MAX_RETRIES', 3);

/**
 * Define how many products should be send when the cron starts.
 */
define('REEVIEW_BATCHES_LIMIT', 100);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reeview-activator.php
 */
function activate_reeview()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-reeview-activator.php';
    Reeview_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reeview-deactivator.php
 */
function deactivate_reeview()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-reeview-deactivator.php';
    Reeview_Deactivator::deactivate(true);
}

register_activation_hook(__FILE__, 'activate_reeview');
register_deactivation_hook(__FILE__, 'deactivate_reeview');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-reeview.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function Reeview()
{
    return Reeview::instance();
}

$reeview = Reeview();
// loader must run to register hooks
$reeview->run();
