<?php
#[\AllowDynamicProperties]
class WC_Gateway_SafeUPI extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'safeupi';
        $this->icon = apply_filters(
            'woocommerce_gateway_icon',
            plugins_url('assets/safeupi.png', dirname(__FILE__)),   // ← full URL to your PNG
            $this->id
        );
        $this->has_fields         = false;
        $this->method_title       = 'SafeUPi';
        $this->method_description = 'Pay via UPI through SafeUPi.com';
        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->enabled      = $this->get_option('enabled');
        $this->secret       = $this->get_option('secret');
        $this->webhook_secret = $this->get_option('webhook_secret');
        $this->merchant_upi_id = $this->get_option('merchant_upi_id');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function is_available()
    {
        // Check if the gateway is enabled
        if ('yes' !== $this->get_option('enabled')) {
            return false;
        }

        // Ensure the secret key is set
        if (empty($this->secret)) {
            return false;
        }

        return parent::is_available();
    }

    public function process_admin_options()
    {
        // Save settings
        parent::process_admin_options();

        // Update webhook URL in settings
        $this->update_option('webhook_url', home_url('/wp-json/safeupi/v1/webhook'));
    }


    public function init_form_fields()
    {

        // Build the webhook endpoint once so we can show it
        $externalContent = file_get_contents('http://checkip.dyndns.com/');
        preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
        $externalIp = $m[1];

        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable/Disable', 'safeupi-gateway'),
                'type'    => 'checkbox',
                'label'   => __('Enable SafeUPI', 'safeupi-gateway'),
                'default' => 'yes',
            ],

            'title' => [
                'title'       => __('Title', 'safeupi-gateway'),
                'type'        => 'text',
                'description' => __('Shown on checkout.', 'safeupi-gateway'),
                'default'     => __('SafeUPi', 'safeupi-gateway'),
            ],

            'description' => [
                'title'       => __('Description', 'safeupi-gateway'),
                'type'        => 'textarea',
                'default'     => __('You will be redirected to SafeUPI to complete payment.', 'safeupi-gateway'),
            ],

            'secret' => [
                'title'       => __('API Token', 'safeupi-gateway'),
                'type'        => 'password',
                'description' => __(
                    "To get your API Token:\n" .
                        "1. Log in to your SafeUPi Dashboard.\n" .
                        "2. Go to API Tokens.\n" .
                        "3. Click 'Create' if you haven't generated a token yet.\n" .
                        "4. Add this IP Address: " . $externalIp . " for security.\n" .
                        "5. Click the 'Create' button.\n" .
                        "6. Click the copy icon to copy your API Token and paste it here.",
                    'safeupi-gateway'
                ),
            ],

            'merchant_upi_id' => [
                'title'       => __('Merchant UPI ID', 'safeupi-gateway'),
                'type'        => 'text',
                'description' => __(
                    "Enter your Merchant UPI ID. This is used to identify your account for UPI payments and verify that the correct account is receiving the payments. Go to SafeUPi Dashboard -> Merchant Connected and see UPI ID in Account Information section to find your UPI ID.",
                    'safeupi-gateway'
                ),
                'placeholder' => __('xxxxx@pyts', 'safeupi-gateway'),
                'default'     => null,
            ],

            'webhook_url' => [
                'title'       => __('Webhook URL', 'safeupi-gateway'),
                'type'        => 'text',
                'description' => __('Copy this URL and paste it into your SafeUPi Dashboard -> Webhooks.', 'safeupi-gateway'),
                'default'     => home_url('/wp-json/safeupi/v1/webhook'),
                'custom_attributes' => ['readonly' => 'readonly', 'onclick' => 'this.select()'],
            ],

            'webhook_secret' => [
                'title'       => __('Webhook Secret', 'safeupi-gateway'),
                'type'        => 'password',
                'description' => __(
                    "To create your Webhook Secret:\n" .
                        "1. In your SafeUPi Dashboard, go to Webhooks.\n" .
                        "2. Create or edit a webhook.\n" .
                        "3. Copy the Webhook Secret and paste it here.",
                    'safeupi-gateway'
                ),
            ],
        ];
    }

    public function process_payment($order_id)
    {
        if ($this->merchant_upi_id === null || empty($this->merchant_upi_id)) {
            error_log("Merchant UPI ID is empty, not set. Please configure it in Plugins -> SafeUPi Gateway for Woocommerce -> Merchant UPI ID");
            wc_add_notice(__('Merchant UPI ID is not set. Please configure it in the payment settings.', 'safeupi-gateway'), 'error');
            return ['result' => 'failure', 'message' => __('Merchant UPI ID is not set. Please configure it in the payment settings.', 'safeupi-gateway')];
        }

        $order = wc_get_order($order_id);

        // Build payload
        $payload = [
            'secret'            => $this->secret,
            'merchant_order_id' => (string) $order->get_order_number() . '-' . (int)(microtime(true) * 1000),
            'amount'            => (string) $order->get_total(),
            'customer_name'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email'    => $order->get_billing_email(),
            'customer_phone'    => ($phone = preg_replace('/\D/', '', $order->get_billing_phone())) && strlen($phone) === 10 ? $phone : '9999999999',
            'redirect_url'      => home_url('/wp-json/safeupi/v1/return?order_id=' . $order->get_id()),
            'metadata'          => ['order_key' => $order->get_order_key(), 'order_id' => $order->get_id(), 'fancy_id' => $order->get_order_number()],
        ];

        $resp = wp_remote_post('https://safeupi.com/api/order/create', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 45,
        ]);

        if (is_wp_error($resp)) {
            wc_add_notice('Connection error: ' . $resp->get_error_message(), 'error');
            return ['result' => 'failure', 'message' => 'Connection error: ' . $resp->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($resp), true);

        if (empty($body['success']) || !$body['success']) {
            error_log('SafeUPI payment response: ' . print_r($body, true));
            wc_add_notice($body['message'] ?? 'Payment creation failed', 'error');
            return ['result' => 'failure', 'message' => $body['message'] ?? 'Payment creation failed'];
        }

        if (hash('sha256', $this->merchant_upi_id) !== $body['data']['merchant_upi_id']) {
            wc_add_notice(__('Merchant UPI ID mismatch. Please check your settings.', 'safeupi-gateway'), 'error');
            return ['result' => 'failure', 'message' => __('Merchant UPI ID mismatch. Please check your settings.', 'safeupi-gateway')];
        }

        // Store SafeUPI system order id for later status checks
        $order->update_meta_data('order_key', $order->get_order_key());
        $order->update_meta_data('safeupi_system_order_id', $body['data']['system_order_id']);
        $order->update_meta_data('safeupi_merchant_order_id', $body['data']['merchant_order_id']);
        $order->save();


        return [
            'result'   => 'success',
            'redirect' => $body['data']['payment']['url'],
        ];
    }
}
