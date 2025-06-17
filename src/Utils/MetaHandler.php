<?php

namespace BeeComm\Utils;

final class MetaHandler
{
    public static function getMeta(int $postId, string $key, $default = null)
    {
        $value = get_post_meta($postId, $key, true);
        return $value !== '' ? $value : $default;
    }

    public static function updateMeta(int $postId, string $key, $value): void
    {
        update_post_meta($postId, $key, $value);
    }

    public static function deleteMeta(int $postId, string $key): void
    {
        delete_post_meta($postId, $key);
    }
}
