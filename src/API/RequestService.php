<?php

namespace BeeComm\API;

use BeeComm\Config\PluginConfig;

final class RequestService
{
    public static function post(string $endpoint, array $body): array
    {
        $config = PluginConfig::get();

        $url = rtrim($config['api_base_url'], '/') . '/' . ltrim($endpoint, '/');
        $apiKey = AuthService::getApiKey();
        $storeId = AuthService::getStoreId();

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'X-Store-ID' => $storeId,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true) ?? [];
    }

    public static function get(string $endpoint): array
    {
        $config = PluginConfig::get();

        $url = rtrim($config['api_base_url'], '/') . '/v2/oauth/token' . ltrim($endpoint, '/');
        $apiKey = AuthService::getApiKey();
        $storeId = AuthService::getStoreId();

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'X-Store-ID' => $storeId,
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true) ?? [];
    }
}
