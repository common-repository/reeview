<?php

/**
 * Contains different helping functions
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 */

/**
 * Contains different helping functions.
 *
 * This class defines util functions used in this plugin.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/helpers
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Utils
{
    public static function map_items($items, $getOnlyIds = false, $addProductPrice = true)
    {
        $data = [];
        if ($getOnlyIds) {
            foreach ($items as $item) {
                $data[] = ['id' => $item->id];
            }
            return ['items' => $data, 'ids' => array_column($data, 'id')];
        } else {
            $ids = [];
            foreach ($items as $item) {
                $thumbnailId = get_post_thumbnail_id($item->id);
                $thumbnailUrl = wp_get_attachment_image_src($thumbnailId, [200, 200])[0];
                $parsedItem = [
                    'id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'vendor' => $item->vendor,
                    'previewImageSrc' => ($thumbnailUrl ? $thumbnailUrl : $item->img),
                    'productUrl' => get_post_permalink($item->id)
                ];
                if ($addProductPrice) {
                    $product = wc_get_product($item->id);
                    $parsedItem['price'] = $product->get_regular_price();

                }
                $data[] = $parsedItem;
                $ids[] = $item->id;
            }
            return ['items' => $data, 'ids' => $ids];
        }
    }

    /**
     * Return new token based on wp_domain and random_string
     * @return string
     */
    public static function generate_token()
    {
        $domain = self::get_domain();
        $random = self::generate_random_string();
        return md5($domain . $random);
    }

    /**
     * Generate key based on wp_domain and HARDCODED_KEY
     * @return string
     */
    public static function generate_key()
    {
        $domain = self::get_domain();
        $key = self::get_option(REEVIEW_KEY_OPT);
        return md5($domain . '_' . $key);
    }

    public static function generate_hmac_signature($data, $token)
    {
        return base64_encode(hash_hmac('sha256', json_encode($data), $token, true));
    }

    /**
     * Return random string of a specified length.
     *
     * @param int $length
     * @return string
     */
    public static function generate_random_string($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public static function get_domain()
    {
        $siteUrl = get_site_url();
        $siteUrl = preg_replace('#^https?://#', '', $siteUrl);
        $siteUrl = rtrim($siteUrl, '/');
        return $siteUrl;
    }

    /**
     * Return option from wp_options table based on option name.
     *
     * @param string $option
     * @return mixed
     */
    public static function get_option($option = '')
    {
        return get_option($option, null);
    }

    /**
     * Save/Update option value in wp_options table.
     *
     * @param string $option
     * @param $value
     */
    public static function set_option($option, $value)
    {
        // If the options doesn't exist will be created;
        update_option($option, $value);
    }
}
