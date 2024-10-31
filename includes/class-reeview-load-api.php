<?php

/**
 * Loads api files
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 */

/**
 * Loads api files.
 *
 * This class is responsible for loading endpoints files and other classes used by them.
 *
 * @package    Reeview
 * @subpackage Reeview/includes
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Load_Api
{
    /**
     * Include helping files first.
     */
    public static function includes()
    {
        try {
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/helpers/class-reeview-api-auth.php'))
                throw new Exception ('class-reeview-api-auth.php');
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-reeview-status.php'))
                throw new Exception ('class-reeview-status.php');
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-reeview-install.php'))
                throw new Exception ('class-reeview-install.php');
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-reeview-uninstall.php'))
                throw new Exception ('class-reeview-uninstall.php');
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-reeview-import.php'))
                throw new Exception ('class-reeview-import.php');
            if (!@include_once(plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-reeview-on-demand-sync.php'))
                throw new Exception ('class-reeview-on-demand-sync.php');
        } catch (\Exception $exception) {
            Reeview_Logger::errorLog($exception->getMessage() . ' NOT FOUND');
        }
    }
}

// Add constructor if action needs to be registered
Reeview_Load_Api::includes();
