<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Reeview
 * @subpackage Reeview/admin
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Admin
{
    /** @var string The ID of this plugin. */
    private $plugin_name;

    /** @var string The current version of this plugin. */
    private $version;

    /** Holds the values to be used in the fields callbacks */
    private $reeview_options;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function reeview_add_plugin_page()
    {
        add_options_page(
            'Reeview', // page_title
            'Reeview', // menu_title
            'manage_options', // capability
            'reeview', // menu_slug
            array($this, 'reeview_create_admin_page') // function
        );
    }

    public function reeview_create_admin_page()
    {
        $this->reeview_options = get_option(REEVIEW_OPTIONS); ?>

        <div class="wrap">
            <h2>Reeview</h2>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('reeview_option_group');
                do_settings_sections('reeview-admin');
                submit_button('Save');
                ?>
            </form>
        </div>
    <?php }

    public function reeview_page_init()
    {
        register_setting(
            'reeview_option_group', // option_group
            REEVIEW_OPTIONS, // option_name
            array($this, 'reeview_sanitize') // sanitize_callback
        );

        add_settings_section(
            'reeview_setting_section', // id
            'Settings', // title
            array($this, 'reeview_section_info'), // callback
            'reeview-admin' // page
        );

        add_settings_field(
            REEVIEW_VENDOR_OPT, // id
            'Select field for vendor(manufacturer)', // title
            array($this, 'vendor_callback'), // callback
            'reeview-admin', // page
            'reeview_setting_section' // section
        );

        add_settings_field(
            REEVIEW_ENABLE_LOGS_OPT, // id
            'Check if you want to log any message from Reeview', // title
            array($this, 'enable_logs_callback'), // callback
            'reeview-admin', // page
            'reeview_setting_section' // section
        );
    }

    public function reeview_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input[REEVIEW_VENDOR_OPT])) {
            $sanitary_values[REEVIEW_VENDOR_OPT] = $input[REEVIEW_VENDOR_OPT];
        }
        if (isset($input[REEVIEW_ENABLE_LOGS_OPT])) {
            $sanitary_values[REEVIEW_ENABLE_LOGS_OPT] = $input[REEVIEW_ENABLE_LOGS_OPT];
        }
        return $sanitary_values;
    }

    public function reeview_section_info()
    {
        printf('<strong>REST_API Url Prefix:</strong><code>' . rest_get_url_prefix()
            . '</code><p><em>Note: If the prefix is other then <strong>wp-json</strong> 
            please inform Reeview.</em></p>');
    }

    public function vendor_callback()
    {
        ?>
        <select name="<?= REEVIEW_OPTIONS . '[' . REEVIEW_VENDOR_OPT . ']' ?>" id="REEVIEW_VENDOR_OPT">
            <option value=""><?php esc_html_e('Select', 'woocommerce'); ?></option>
            <?php
            // Array of defined attribute taxonomies.
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $tax) {
                    $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
                    $label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                    $selected = (isset($this->reeview_options[REEVIEW_VENDOR_OPT]) && $this->reeview_options[REEVIEW_VENDOR_OPT] === esc_attr($attribute_taxonomy_name)) ? 'selected' : '';
                    echo '<option value="' . esc_attr($attribute_taxonomy_name) . '"' . $selected . '>' . esc_html($label) . '</option>';
                }
            }
            ?>
        </select>
        <?php
    }

    public function enable_logs_callback()
    {
        $checked = ((isset($this->reeview_options[REEVIEW_ENABLE_LOGS_OPT]) && $this->reeview_options[REEVIEW_ENABLE_LOGS_OPT] === REEVIEW_ENABLE_LOGS_OPT) ? 'checked' : '');
        ?>
        <input type="checkbox" name="<?= REEVIEW_OPTIONS . '[' . REEVIEW_ENABLE_LOGS_OPT . ']' ?>"
               id="<?= REEVIEW_ENABLE_LOGS_OPT ?>"
               value="<?= REEVIEW_ENABLE_LOGS_OPT ?>" <?= $checked ?>>
        <?php
    }
}
