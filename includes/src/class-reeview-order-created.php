<?php

/**
 * Call Reeview on order created
 *
 * @link
 * @since      1.0.0
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 */

/**
 * Call Reeview on order created.
 *
 * This class adds functionality to order created hook calls Reeview app
 * with order details if product reeview video has been visualized before
 * the order was placed.
 *
 * @package    Reeview
 * @subpackage Reeview/includes/src
 * @author     Interlive Metrics <gitangeorgiana97@gmail.com>
 */
class Reeview_Order_Created
{
    public function __construct()
    {
        add_action('woocommerce_checkout_order_processed', array($this, 'callback_order_placed'), 10, 1);
    }

    /**
     * @param int $orderId
     */
    public function callback_order_placed($orderId)
    {
        $token = Reeview_Utils::get_option(REEVIEW_TOKEN_OPT);
        if (is_null($token) || empty($token)) {
            Reeview_Logger::errorLog('Token not found or empty - EXIT - NO ORDER SEND.',
                Reeview_Logger::ERR
            );
            return;
        }
        $order = new WC_Order($orderId);
        $data = ['orderId' => $order->get_order_number(), 'items' => []];
        /** @var WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item) {
            /** @var WC_Product_Simple $product */
            $product = $item->get_product();
            $productId = $product->get_id();
            if ($parentId = $product->get_parent_id()) {
                $productId = $parentId;
            }
            $key = array_search($productId, array_column($data['items'], 'productId'));
            if ($key !== false) {
                $data['items'][$key]['price'] += $item->get_total() + $item->get_total_tax();
            } else {
                $data['items'][] = [
                    'productId' => $productId,
                    'price' => $item->get_total() + $item->get_total_tax() // price after coupon applies + taxes if applied for each item
                ];
            }
        }
        $is_send = Reeview_Connector::call_reeview_webhook(Reeview_Connector::ORDER_PATH, $token, $data);
        if (!$is_send) {
            Reeview_Logger::errorLog(
                'Order info not send to Reeview. Order number: ' . $order->get_order_number(),
                Reeview_Logger::WRN
            );
        }
    }
}

$reviewOrderCreated = new Reeview_Order_Created();
