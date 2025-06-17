<?php

namespace BeeComm\API;

final class AuthService
{
    public static function getApiKey(): ?string
    {
        $options = get_option('beecomm_integration_settings');
        return $options['api_key'] ?? null;
    }

    public static function getStoreId(): ?string
    {
        $options = get_option('beecomm_integration_settings');
        return $options['store_id'] ?? null;
    }
}
