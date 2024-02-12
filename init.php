<?php
/**
 * Plugin Name: XPay Payment for Woocommerce
 * Plugin URI: https://xpay.com
 * Author Name: Raisul Islam
 * Author URI: https://xpay.com
 * Description: XPay Payment for Woocommerce
 * Version: 0.1.0
 * License: 0.1.0
 * License URI: 0.1.0
 * text-domain: xpay-payment-for-woocommerce
*/ 

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}


add_action('plugins_loaded', 'xpay_init', 11);
function xpay_init() {
    if (class_exists('WC_Payment_Gateway')) {
        class WC_XPay_Payment_Gateway extends WC_Payment_Gateway {
            public function __construct() {
                $this->id = 'xpay';  // payment gateway plugin ID
                $this->icon = apply_filters('woocommerce_gateway_icon', plugins_url('/assets/images/xpay.png', __FILE__));
                $this->has_fields = false;
                $this->method_title = __('Xpay Payment', 'xpay-payment-for-woocommerce');
                $this->method_description = __('XPay Payment Description', 'xpay-payment-for-woocommerce');

                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->instruction = $this->get_option('instruction');

                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                add_action('woocommerce_thankyou_' . $this->id, array($this, 'thank_you_page'));
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters('xpay_init_form_fields', array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'xpay-payment-for-woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Enable XPay Payment', 'xpay-payment-for-woocommerce'),
                        'default' => 'yes',
                    ),
                    'title' => array(
                        'title' => __('XPay Payment gateway', 'xpay-payment-for-woocommerce'),
                        'type' => 'text',
                        'description' => __('xpay payment gateway description for woocommerce custom','xpay-payment-for-woocommerce'),
                        'default' => __('XPay Payment', 'xpay-payment-for-woocommerce'),
                        'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => __('XPay Payment gateway description', 'xpay-payment-for-woocommerce'),
                        'type' => 'textarea',
                        'default' => __('Please remit the exact amount to the shop to complete the order.', 'xpay-payment-for-woocommerce'),
                        'desc_tip' => true,
                        'description' => __('xpay payment gateway description for woocommerce custom','xpay-payment-for-woocommerce'),
                    ),
                    'instructions' => array(
                        'title' => __('Instructions', 'xpay-payment-for-woocommerce'),
                        'type' => 'textarea',
                        'default' => __('default instructions', 'xpay-payment-for-woocommerce'),
                        'desc_tip' => true,
                        'description' => __('xpay payment gateway description for woocommerce custom','xpay-payment-for-woocommerce'),
                    ),
                ));
            }

            public function process_payments($order_id) {
                $order = wc_get_order($order_id);
                $order->update_status('on-hold', __('Awaiting payment', 'xpay-payment-for-woocommerce'));
                $this->clear_payment_with_api();
                $order->reduce_order_stock();
                WC()->cart->empty_cart();
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_return_url($order)
                );                
            }
            public function clear_payment_with_api() {
                
            }

            public function thank_you_page() {
                if($this->instructions) {
                    echo wpautop(wptexturize($this->instructions));
                }
            }
        }
    }
}

add_filter('woocommerce_payment_gateways', 'xpay_add_gateway_class');
function xpay_add_gateway_class($methods) {
    $methods[] = 'WC_XPay_Payment_Gateway';
    return $methods;
}



