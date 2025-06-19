<?php
/**
 * Elementor Pro – Newsletter form integration with Pulseem.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Hook Elementor form submission to register users to Pulseem newsletter.
 */
add_action('elementor_pro/forms/new_record', 'beecomm_handle_newsletter_form', 10, 2);

/**
 * Handle Elementor newsletter form and send data to Pulseem.
 *
 * @param \ElementorPro\Modules\Forms\Classes\Record $record
 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $handler
 */
function beecomm_handle_newsletter_form($record, $handler)
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

	beecomm_send_to_pulseem($email);
}

/**
 * Send an email address to Pulseem API.
 *
 * @param string $email
 * @return void
 */
function beecomm_send_to_pulseem(string $email): void
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
