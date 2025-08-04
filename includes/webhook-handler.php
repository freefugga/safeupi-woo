<?php
add_action('rest_api_init', function () {
    register_rest_route(
        'safeupi/v1',
        '/webhook',
        [
            'methods' => 'POST',
            'callback' => 'safeupi_handle_webhook',
            'permission_callback' => '__return_true',
        ]
    );
});


if (!function_exists('safeupi_handle_webhook')) {
    function safeupi_handle_webhook(WP_REST_Request $request)
    {

        $body = $request->get_json_params();
        $gateway = WC()->payment_gateways->payment_gateways()['safeupi'];

        // 1) Basic signature check
        if (($body['secret'] ?? '') !== $gateway->get_option('webhook_secret')) {
            return new WP_REST_Response(['status' => 401], 401);
        }

        $data = $body['data'] ?? [];
        $order = wc_get_order($data['metadata']['order_id'] ?? '');
        if (!$order) {
            return new WP_REST_Response(['status' => 'order_not_found'], 404);
        }

        $merchant_order_id = $data['merchant_order_id'];

        // 2) Ask SafeUPI for the REAL status
        $status_response = safeupi_real_order_status($order, $gateway, $merchant_order_id);
        if ($status_response['status'] !== 'success') {
            if ($status_response['status'] === "failed") {
                $order->update_status('failed', 'Webhook: SafeUPi payment request expired');
            }
            if ($status_response['status'] === "cancelled") {
                $order->update_status('cancelled', 'Webhook: SafeUPi payment request cancelled');
            }
            return new WP_REST_Response(
                ['status' => 'not_paid', 'message' => $status_response['message'] ?? 'Still pending'],
                200
            );
        }

        //3) Check if order amount is less than or equal to the amount in the webhook
        if (floatval($order->get_total()) > floatval($data['amount'])) {
            return new WP_REST_Response(
                ['status' => 'amount_mismatch', 'message' => 'Order amount does not match the payment amount'],
                400
            );
        }

        // 4) Mark paid
        if (!$order->is_paid()) {
            $order->payment_complete($status_response['utr']);
            $order->add_order_note(sprintf('Webhook: SafeUPi paid (UTR: %s)', $status_response['utr']));
        }

        return new WP_REST_Response(['status' => 'ok']);
    }
}

/**
 * Private helper: call SafeUPI /api/order/status
 */
function safeupi_real_order_status($order, $gateway, $merchant_order_id)
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
    if (empty($body['success'])) {
        return ['status' => 'error', 'message' => $body['message'] ?? 'Unknown'];
    }

    return [
        'status' => $body['data']['status'] ?? 'pending',
        'utr'    => $body['data']['payment']['utr'] ?? '',
        'amount' => $body['data']['amount'] ?? 0,
    ];
}
