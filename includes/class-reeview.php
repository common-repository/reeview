<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Reeview_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /** @var string The string used to uniquely identify this plugin. */
    protected $plugin_name;

    /** @var string The current version of the plugin. */
    protected $version;

    /**
     * The single instance of the class.
     */
    protected static $_instance = null;

    /**
     * Main Reeview Instance. Ensures only one instance of the Reeview is loaded or can be loaded.
     *
     * @return Reeview|null - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     */
    public function __construct()
    {
        if (defined('REEVIEW_VERSION')) {
            $this->version = REEVIEW_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'reeview';

        // Proceed loading the rest of the dependencies
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_cron_hooks();

        // Register function for plugin_activation hook to check that Woo is loaded.
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Load the required dependencies for this plugin.
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     */
    private function load_dependencies()
    {
        $this->load_sql_files();
        $this->load_other_files();
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reeview-loader.php';
        // The class responsible for defining all actions that occur in the admin area.
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-reeview-admin.php';
        // The class responsible for defining all actions that occur in the public-facing side of the site.
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-reeview-public.php';

        $this->loader = new Reeview_Loader();
    }

    /**
     * Load all plugin's sql files.
     */
    private function load_sql_files()
    {
        // The classes responsible for db changes and queries.
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sql/class-reeview-sql-install.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sql/class-reeview-sql-uninstall.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sql/class-reeview-sql-deactivate.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sql/class-reeview-sql-insert.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sql/class-reeview-sql-sync.php';
    }

    /**
     * Load the required files for plugin logic.
     */
    private function load_other_files()
    {
        // The class containing all the helping functions.
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/helpers/class-reeview-utils.php';
        // The class responsible for logging all error in the plugin.
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/helpers/class-reeview-logger.php';
        // The class responsible for sending data to Reeview app
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/src/class-reeview-connector.php';
        // The class that will define actions and callbacks functions for crons
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/src/class-reeview-cron-jobs-callbacks.php';
        // The class that will contain each cron logic and on-demand-sync logic
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/src/class-reeview-sync.php';
        // The class responsible for sending data to Reeview on a new order
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/src/class-reeview-order-created.php';
        // The class responsible for cleaning on uninstall call
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reeview-deactivator.php';
    }

    /**
     * Register all of the hooks related to the admin-facing functionality of the plugin.
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Reeview_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_admin, 'reeview_add_plugin_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'reeview_page_init');
    }

    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     */
    private function define_public_hooks()
    {
        $plugin_public = new Reeview_Public($this->get_plugin_name(), $this->get_version());

        // Add script to call initReeview at the end of the product page
        $this->loader->add_action('woocommerce_before_single_product', $plugin_public, 'add_reeview_script_on_product_page');
        $this->loader->add_action('wp_footer', $plugin_public, 'add_reeview_script_on_checkout');
    }

    /**
     * Register hooks related to the cron jobs functionality of the plugin.
     */
    private function define_cron_hooks()
    {
        // Add custom interval for crons
        $this->loader->add_filter('cron_schedules', $this, 'add_cron_interval_5_min', 10, 1);
        // Add actions for crons
        $cronJobsCallbacks = new Reeview_Cron_Jobs_Callbacks();
        $this->loader->add_action('cron_reeview_create', $cronJobsCallbacks, 'cron_reeview_create', 10, 0);
        $this->loader->add_action('cron_reeview_update', $cronJobsCallbacks, 'cron_reeview_update', 10, 0);
        $this->loader->add_action('cron_reeview_delete', $cronJobsCallbacks, 'cron_reeview_delete', 10, 0);
    }

    /**
     * Add a 5 minutes interval to be used for plugin cron jobs.
     *
     * @param $schedules
     * @return mixed
     */
    public function add_cron_interval_5_min($schedules)
    {
        $schedules['every_5_minutes'] = array(
            'interval' => 60 * 5,
            'display' => __('Once Every 5 Minutes')
        );
        return $schedules;
    }

    /**
     * If WooCommerce is not installed/activate display notice in admin panel
     * else load APIs and cron files.
     */
    public function init()
    {
        if (class_exists('WooCommerce')) {
            // Class that will load all API files
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reeview-load-api.php';
        } else {
            Reeview_Logger::errorLog('Not all plugin\'s files are not loaded.'
                . ' WooCommerce must be installed and activated before Reeview!');
            add_action('admin_notices', array($this, 'no_woo_fond_notice'));
        }
    }

    /**
     * Plugin WooCommerce not found notice in admin area.
     */
    public function no_woo_fond_notice()
    {
        echo '<div class="notice notice-error"><p>' .
            sprintf(
                __('Reeview requires %s to be installed and active.', 'reeview'),
                '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
            )
            . '</p></div>';
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return Reeview_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
