<?php
/**
 * Registers the Beecomm order status cron job and schedule interval.
 */

// Prevent redeclaring hooks if already hooked
if ( ! has_action( 'init', 'beecomm_cron_init' ) ) {
	add_action( 'init', 'beecomm_cron_init' );
}

/**
 * Initialize Beecomm cron event if not already scheduled.
 */
function beecomm_cron_init() {
	if ( ! wp_next_scheduled( BEECOMM_ORDER_STATUS_CRON ) ) {
		wp_schedule_event( time(), BEECOMM_ORDER_STATUS_CRON_INTERVAL, BEECOMM_ORDER_STATUS_CRON );
	}
}

// Register custom cron interval
add_filter( 'cron_schedules', 'beecomm_cron_add_schedule' );

/**
 * Add custom interval option (in minutes) for Beecomm cron.
 *
 * @param array $schedules
 * @return array
 */
function beecomm_cron_add_schedule( $schedules ) {
	$interval = get_option( BEECOMM_ORDER_STATUS_CRON_INTERVAL );
	$schedules[ BEECOMM_ORDER_STATUS_CRON_INTERVAL ] = array(
		'interval' => $interval ? $interval * 60 : 14400, // 14400 = 4 hours
		'display'  => $interval ? __( 'Every ' . $interval . ' minutes' ) : __( 'Every 4 hours' ),
	);
	return $schedules;
}

// Attach main cron callback to the event
add_action( BEECOMM_ORDER_STATUS_CRON, 'beecomm_cron_update_order_status' );
