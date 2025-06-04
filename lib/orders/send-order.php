<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Send a Beecomm order payload to the Beecomm API.
 *
 * @param array $payload The order payload.
 * @return string The API response (JSON string).
 */
function beecomm_send_order(array $payload): string
{
    $url = beecomm_get_base_url() . '/api/v2/services/orderCenter/pushOrder';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'access_token: ' . beecomm_get_auth(),
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response ?: '{}';
}
