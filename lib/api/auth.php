<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ensure request.php is loaded so we can use beecomm_get_base_url()
require_once __DIR__ . '/request.php';

/**
 * Get the stored client credentials from plugin settings.
 *
 * @return array
 */
function beecomm_get_client_credentials(): array
{
    $options = get_option('beecomm_integration_options', []);
    return [
        'client_id' => $options['client_id'] ?? '',
        'client_secret' => $options['client_secret'] ?? '',
    ];
}

/**
 * Fetch a new access token from Beecomm API.
 *
 * @return string|null
 */
function beecomm_get_auth(): ?string
{
    $url = beecomm_get_base_url() . '/v2/oauth/token';
    $credentials = json_encode(beecomm_get_client_credentials());

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $credentials,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response);
    return $result->access_token ?? null;
}
