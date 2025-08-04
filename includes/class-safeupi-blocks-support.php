
<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Gateway_SafeUPI_Blocks_Support extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'safeupi'; // your payment gateway name
    public function initialize()
    {
        $this->settings = get_option('woocommerce_safeupi_settings', []);
        $this->gateway = new WC_Gateway_SafeUPI();
    }
    public function is_active()
    {
        return $this->gateway->is_available();
    }
    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'safeupi-blocks-integration',
            plugin_dir_url(__FILE__) . 'blocks/build/index.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            SAFEUPI_WOOCOMMERCE_VERSION,
            true
        );
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('safeupi-blocks-integration');
        }
        return ['safeupi-blocks-integration'];
    }
    public function get_payment_method_data()
    {
        return [
            'title'       => $this->settings['title'] ?? __('SafeUPi', 'safeupi-woo'),
            'description' => $this->settings['description'] ?? __('Scan QR Code and pay instantly', 'safeupi-woo'),
        ];
    }
}
?>