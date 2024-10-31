<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Reeview
 * @subpackage Reeview/public
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Public
{
    /** @var string The ID of this plugin. */
    private $plugin_name;

    /** @var string The current version of this plugin. */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    function add_reeview_script_on_checkout()
    {
        // Enqueue external script from reeview
        wp_enqueue_script($this->plugin_name, 'https://reeview.s3.amazonaws.com/widget.js.gz', array('jquery'), $this->version, false);

        // Only on order received
        if (!is_wc_endpoint_url('order-received'))
            return;
        $order_id = absint(get_query_var('order-received'));
        if (get_post_type($order_id) !== 'shop_order') {
            return;
        }
        $order = wc_get_order($order_id);
        if (!is_a($order, 'WC_Order')) {
            return;
        }
        $siteId = Reeview_Utils::get_option(REEVIEW_SITE_ID_OPT);
        ?>
        <script type="text/javascript">
            console.log('track order from footer')
            window.document.addEventListener('DOMContentLoaded', () => {
                trackOrder('<?= $siteId ?>', '<?= $order->get_id() ?>');
            });
        </script>
        <?php
    }

    public function add_reeview_script_on_product_page()
    {
        // Enqueue external script from reeview
        wp_enqueue_script($this->plugin_name, 'https://reeview.s3.amazonaws.com/widget.js.gz', array('jquery'), $this->version, false);

        global $post;
        $siteId = Reeview_Utils::get_option(REEVIEW_SITE_ID_OPT);
        ?>
        <script type="text/javascript">
            window.document.addEventListener('DOMContentLoaded', () => {
                initReeview('<?= $siteId ?>', '<?= $post->ID ?>');
            });
        </script>
        <?php
    }
}
