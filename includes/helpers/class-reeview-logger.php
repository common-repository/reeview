<?php

/**
 * Used for logging errors
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 */

/**
 * Used for logging errors.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Logger
{
    const ERR = 'Error: ';
    const WRN = 'Warning: ';
    const NOTICE = 'Notice: ';

    /**
     * Logs errors in module file.
     *
     * @param string $message Error message to log.
     * @param string $type ERR|WRN|NOTICE
     * @param string $errorSource Class, function in witch occurred.
     */
    public static function errorLog($message = 'test', $type = self::ERR, $errorSource = '')
    {
        if (isset(Reeview_Utils::get_option(REEVIEW_OPTIONS)[REEVIEW_ENABLE_LOGS_OPT]) &&
            Reeview_Utils::get_option(REEVIEW_OPTIONS)[REEVIEW_ENABLE_LOGS_OPT] !== REEVIEW_ENABLE_LOGS_OPT
        ) {
            return;
        }
        $path =  WP_CONTENT_DIR . '/reeview.log';;
        $timestamp = date('Y-m-d H:i:s');
        $completeMessage = "[" . $timestamp . "] - " . $type . ": " . $message . "(" . $errorSource . ")" . PHP_EOL;
        error_log($completeMessage, 3, $path);
    }
}
