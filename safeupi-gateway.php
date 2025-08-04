<?php

/**
 * Plugin Name: SafeUPi Gateway for WooCommerce
 * Description: Accept UPI payments via SafeUPi.com
 * Version: 1.1.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: <a href="https://safeupi.com">SafeUPi</a>
 * Text Domain: safeupi-gateway
 */

if (! defined('ABSPATH')) exit;

function declare_cart_checkout_blocks_compatibility()
{
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

add_action('plugins_loaded', 'safeupi_init', 11);
function safeupi_init()
{
    if (!class_exists('WC_Payment_Gateway')) return;

    require_once __DIR__ . '/includes/class-safeupi-gateway.php';
    require_once __DIR__ . '/includes/webhook-handler.php';
    require_once __DIR__ . '/includes/return-handler.php';

    add_filter('woocommerce_payment_gateways', function ($methods) {
        $methods[] = 'WC_Gateway_SafeUPI';
        return $methods;
    });
}

add_action('woocommerce_blocks_loaded', function () {
    // Check if the required class exists
    if (! class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }
    // Include the custom Blocks Checkout class
    require_once plugin_dir_path(__FILE__) . '/includes/class-safeupi-blocks-support.php';
    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            // Register an instance of My_Custom_Gateway_Blocks
            $payment_method_registry->register(new WC_Gateway_SafeUPI_Blocks_Support);
        }
    );
});

/**
 * Add a "Settings" link on Plugins list.
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'safeupi_add_settings_link');
function safeupi_add_settings_link($links)
{
    $url = admin_url('admin.php?page=wc-settings&tab=checkout&section=safeupi');
    $links['settings'] = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'safeupi-gateway') . '</a>';
    return $links;
}

register_activation_hook(__FILE__, 'my_plugin_activate');
function my_plugin_activate()
{
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and activated.', 'safeupi-gateway'));
    }

    // Flush rewrite rules to ensure the new REST route is registered
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'my_plugin_deactivate');
function my_plugin_deactivate()
{
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
