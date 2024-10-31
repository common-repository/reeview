<?php

/**
 * Contains all cron jobs callback functions
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 */

/**
 * Contains all cron jobs callback functions.
 *
 * This class calls Reeview app and handles responses. One call will have n attempts.
 * If it returns error every time, error will be logged and process finished.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Cron_Jobs_Callbacks
{
    protected $cronType;

    /**
     * Get batch, parse and send based on cron type.
     */
    public function execute()
    {
        $batch = Reeview_Sync::get_cron_batches($this->cronType, true);
        if (!empty($batch)) {
            Reeview_Sync::parse_batch_and_send($this->cronType, $batch);
        }
    }

    /**
     * Set cron type and call execute.
     */
    public function cron_reeview_create()
    {
        if (is_null($token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT)) || empty($token)) {
            Reeview_Logger::errorLog('Token not found.', Reeview_Logger::ERR);
            return;
        }
        $this->cronType = Reeview_Sync::CREATE;
        $this->execute();
    }

    /**
     * Set cron type and call execute.
     */
    public function cron_reeview_update()
    {
        if (is_null($token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT)) || empty($token)) {
            Reeview_Logger::errorLog('Token not found.', Reeview_Logger::ERR);
            return;
        }
        $this->cronType = Reeview_Sync::UPDATE;
        $this->execute();
    }

    /**
     * Set cron type and call execute.
     */
    public function cron_reeview_delete()
    {
        if (is_null($token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT)) || empty($token)) {
            Reeview_Logger::errorLog('Token not found.', Reeview_Logger::ERR);
            return;
        }
        $this->cronType = Reeview_Sync::DELETE;
        $this->execute();
    }
}
