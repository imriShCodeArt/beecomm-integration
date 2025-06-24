<?php

namespace Beecomm_Integration;

if (!defined('ABSPATH')) {
    exit;
}

use ElementorPro\Modules\Forms\Classes\Record;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

/**
 * Class Beecomm_Newsletter
 *
 * Integrates Elementor Pro form submissions with the Pulseem newsletter service.
 * Automatically subscribes users to a Pulseem group when the form named 'newsletter' is submitted.
 *
 * @package Beecomm_Integration
 */
class Beecomm_Newsletter
{
    /**
     * Registers the hook to listen for Elementor Pro form submissions.
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('elementor_pro/forms/new_record', [self::class, 'handle_form'], 10, 2);
    }

    /**
     * Handles form submission and triggers newsletter registration if form is named 'newsletter'.
     *
     * @param Record $record The submitted form record object.
     * @param Ajax_Handler $handler The form handler.
     * @return void
     */
    public static function handle_form($record, $handler): void
    {
        $form_name = $record->get_form_settings('form_name');

        if (strtolower($form_name) !== 'newsletter') {
            return;
        }

        $raw_fields = $record->get('fields');
        $email = isset($raw_fields['email']['value']) ? sanitize_email($raw_fields['email']['value']) : '';

        if (!is_email($email)) {
            error_log("Beecomm Newsletter: Invalid email submitted: $email");
            return;
        }

        self::send_to_pulseem($email);
    }

    /**
     * Sends a POST request to Pulseem API to add a new client to the newsletter group.
     *
     * @param string $email The sanitized email address to add.
     * @return void
     */
    private static function send_to_pulseem(string $email): void
    {
        $payload = [
            'clientsData' => [
                [
                    'email' => $email,
                    'needOptin' => true,
                    'overwrite' => true,
                ],
            ],
            'groupIds' => [640213],
        ];

        $response = wp_remote_post('https://ui-api.pulseem.com/api/v1/ClientsApi/AddClients', [
            'method' => 'POST',
            'headers' => [
                'APIKEY' => '6xaV90l9UOoKXlZmdXmDPQ==',
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($payload),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('Beecomm Newsletter: Error sending to Pulseem - ' . $response->get_error_message());
        } else {
            error_log('Beecomm Newsletter: Successfully submitted email to Pulseem.');
        }
    }
}
