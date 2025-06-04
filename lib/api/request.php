<?php
/**
 * Handles generic HTTP requests to the Beecomm API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Makes a request to the Beecomm API
 *
 * @param string $method    HTTP method (GET, POST, etc.)
 * @param string $endpoint  API endpoint (e.g. '/v2/oauth/token')
 * @param array|null $data  Payload to send
 *
 * @return string|false     API response or false on failure
 */
function beecomm_api_request(string $method, string $endpoint, ?array $data = null)
{
    $url = beecomm_get_base_url() . $endpoint;

    $headers = [
        'Content-Type: application/json',
    ];

    if (strpos($endpoint, 'oauth/token') === false) {
        $headers[] = 'access_token: ' . beecomm_get_auth();
    }

    $args = [
        'method' => strtoupper($method),
        'headers' => $headers,
        'timeout' => 15,
    ];

    if ($data !== null) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        error_log('[Beecomm API] Request error: ' . $response->get_error_message());
        return false;
    }

    return wp_remote_retrieve_body($response);
}

/**
 * Returns the base URL for the Beecomm API.
 *
 * @return string
 */
function beecomm_get_base_url(): string
{
    return 'https://biapp.beecomm.co.il:8094';
}
