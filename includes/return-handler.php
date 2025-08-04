<?php
add_action('rest_api_init', function () {

    register_rest_route(
        'safeupi/v1',
        '/return',
        [
            'methods' => 'GET',
            'callback' => 'safeupi_handle_return',
            'permission_callback' => '__return_true',
        ]
    );
});

if (!function_exists('safeupi_handle_return')) {
    /**
     * Customer lands here after SafeUPI redirects them back.
     * Route: /wc-api/safeupi_return
     */
    function safeupi_handle_return(WP_REST_Request $request)
    {
        $order_id = $request->get_param('order_id');
        $order    = wc_get_order($order_id);

        if (! $order || $order->get_payment_method() !== "safeupi") {
            wp_die('Invalid request', '', 400);
        }

        if ($order->is_paid()) {
            wp_redirect($order->get_checkout_order_received_url());
            exit;
        }

        if ($order->has_status('failed')) {
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        $gateway = WC()->payment_gateways->payment_gateways()['safeupi'];
        $response = safeupi_real_order_status($order, $gateway, $request->get_param('merchant_order_id'));

        //3) Check if order amount is less than or equal to the amount in the webhook
        if (floatval($order->get_total()) > floatval($response['amount'])) {
            if ($response['status'] === "failed") {
                $order->update_status('failed', 'Order Amount does not match the payment amount.');
            }
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        if ($response['status'] === 'success') {
            if (!$order->is_paid()) {
                $order->payment_complete($response['utr']);
                $order->add_order_note(sprintf('SafeUPi paid (UTR: %s)', $response['utr']));
            }
            wp_redirect($order->get_checkout_order_received_url());
        } else {
            if ($response['status'] === "failed") {
                $order->update_status('failed', 'SafeUPi payment request expired');
            }
            if ($response['status'] === "cancelled") {
                $order->update_status('cancelled', 'SafeUPi payment request cancelled');
            }
            wp_redirect(wc_get_checkout_url());
        }
        exit;
    }
}

/**
 * Private helper: call SafeUPi /api/order/status
 */
function safeupi_order_status($order, $gateway, $merchant_order_id)
{
    $payload = [
        'secret'            => $gateway->get_option('secret'),
        'merchant_order_id' => (string) $merchant_order_id,
    ];

    $resp = wp_remote_post('https://safeupi.com/api/order/status', [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => wp_json_encode($payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($resp)) {
        return ['status' => 'error', 'message' => $resp->get_error_message()];
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($body['success']) || !($body['success'])) {
        return ['status' => 'error', 'message' => $body['message'] ?? 'Unknown'];
    }

    return [
        'status' => $body['data']['status'] ?? 'pending',
        'utr'    => $body['data']['payment']['utr'] ?? '',
        'amount' => $body['data']['amount'] ?? 0, // Added amount to the response
    ];
}
