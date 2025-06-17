<?php

namespace BeeComm\Admin;

final class OrderColumns
{
    public function register(): void
    {
        add_filter('manage_edit-shop_order_columns', [$this, 'addColumns']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'renderColumn'], 10, 2);
        add_action('admin_head', [$this, 'injectStyles']);
    }

    public function addColumns(array $columns): array
    {
        $new = [];

        foreach ($columns as $key => $label) {
            $new[$key] = $label;

            if ($key === 'order_status') {
                $new['beecomm_status'] = __('BeeComm Status', 'beecomm-integration');
            }
        }

        return $new;
    }

    public function renderColumn(string $column, int $postId): void
    {
        if ($column !== 'beecomm_status') {
            return;
        }

        $status = get_post_meta($postId, '_beecomm_sync_status', true) ?: 'pending';

        switch ($status) {
            case 'synced':
                $label = __('Synced', 'beecomm-integration');
                $class = 'beecomm-status synced';
                break;
            case 'failed':
                $label = __('Failed', 'beecomm-integration');
                $class = 'beecomm-status failed';
                break;
            default:
                $label = __('Pending', 'beecomm-integration');
                $class = 'beecomm-status pending';
                break;
        }

        echo "<span class='" . esc_attr($class) . "' title='" . esc_attr($label) . "'>{$label}</span>";
    }

    public function injectStyles(): void
    {
        echo '<style>
        .column-beecomm_status { width: 120px; }
        .beecomm-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            text-align: center;
        }
        .beecomm-status.synced { background-color: #46b450; }
        .beecomm-status.failed { background-color: #d63638; }
        .beecomm-status.pending { background-color: #ffba00; }
    </style>';
    }

}
