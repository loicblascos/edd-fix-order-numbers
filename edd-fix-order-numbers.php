<?php
/**
 * Easy Digital Downloads - Fix Order Numbers Plugin
 *
 * @package   Easy Digital Downloads - Fix Order Numbers
 * @author    Loïc Blascos
 * @copyright 2022 Loïc Blascos
 *
 * @wordpress-plugin
 * Plugin Name:  Easy Digital Downloads - Fix Order Numbers
 * Description:  Fix order numbers from EDD.
 * Version:      1.0.0
 * Author:       Loïc Blascos
 * License:      GPL-3.0-or-later
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  edd-fix-order-numbers
 * Domain Path:  /languages
 */

namespace EDD_Fix_Order_Numbers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDD_FON', '1.0.0' );

/**
 * Admin notice
 */
function admin_notice() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="error">
		<p>
			<strong><?php esc_html_e( 'EDD - Fix Order Numbers', 'edd-fix-order-numbers' ); ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
			<button type="button" id="edd-fix-order-numbers-process" class="button button-primary"><?php esc_html_e( 'Process', 'edd-fix-order-numbers' ); ?></button>&nbsp;&nbsp;
			<progress id="edd-fix-order-numbers-progress" max="100" value="0" hidden></progress>&nbsp;
			<span id="edd-fix-order-numbers-progress-indicator"></span>
		</p>
	</div>
	<?php

	wp_enqueue_script(
		'edd-fix-order-numbers',
		plugin_dir_url( __FILE__ ) . '/assets/js/build.js',
		[],
		EDD_FON,
		true
	);

	wp_localize_script(
		'edd-fix-order-numbers',
		'edd_fix_order_numbers',
		[
			'nonce'    => wp_create_nonce( 'edd_fix_order_numbers_nonce' ),
			'complete' => esc_html__( 'Complete!', 'edd-fix-order-numbers' ),
			'process'  => esc_html__( 'Process', 'edd-fix-order-numbers' ),
			'cancel'   => esc_html__( 'Cancel', 'edd-fix-order-numbers' ),
			'error'    => esc_html__( 'Sorry, an error occured.', 'edd-fix-order-numbers' ),
		]
	);
}
add_action( 'admin_notices', 'EDD_Fix_Order_Numbers\admin_notice' );

/**
 * Fix order numbers
 */
function fix_order_numbers() {

	$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : false;

	if ( ! wp_verify_nonce( $nonce, 'edd_fix_order_numbers_nonce' ) || ! current_user_can( 'manage_options' ) ) {

		wp_send_json(
			[
				'success' => false,
				'message' => esc_html__( 'An error occurred. Please try to refresh the page or logout and login again.', 'edd-fix-order-numbers' ),
			]
		);
	}

	$data = wp_unslash( $_POST );

	if ( ! isset( $data['offset'] ) ) {

		wp_send_json(
			[
				'success' => false,
				'message' => esc_html__( 'Sorry, an unknown error occurred.', 'edd-fix-order-numbers' ),
			]
		);
	}

	$limit    = 50;
	$offset   = (int) $data['offset'];
	$total    = get_total();
	$orders   = query_orders( $limit, $offset );
	$progress = ( $offset + $limit ) / $total * 100;

	update_orders( $orders, $offset );

	if ( $offset + $limit >= $total ) {
		edd_update_option( 'edd_son_number_completed', $total + 1 );
	}

	wp_send_json(
		[
			'success'  => true,
			'message'  => number_format( $progress, 2 ) . '% (' . $offset . '/' . $total . ')',
			'progress' => $progress,
			'offset'   => $offset + $limit,
		]
	);
}
add_action( 'wp_ajax_edd_fix_order_numbers', 'EDD_Fix_Order_Numbers\fix_order_numbers' );


/**
 * Get total number of orders to correct
 *
 * @return integer
 */
function get_total() {

	global $wpdb;

	return $wpdb->get_var(
		"SELECT COUNT(ID) AS count
		FROM $wpdb->edd_orders
		WHERE type = 'sale'
		AND status IN ( 'complete', 'edd_subscription', 'refunded', 'revoked', 'partially_refunded' )"
	);
}

/**
 * Query orders
 *
 * @param integer $limit  Query limit.
 * @param integer $offset Query offset.
 * @return array
 */
function query_orders( $limit, $offset ) {

	global $wpdb;

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			FROM $wpdb->edd_orders
			WHERE type = 'sale'
			AND status IN ( 'complete', 'edd_subscription', 'refunded', 'revoked', 'partially_refunded' )
			ORDER BY ID ASC
			LIMIT %d OFFSET %d",
			$limit,
			$offset
		)
	);
}

/**
 * Update order numbers
 *
 * @param array   $orders Holds orders to correct.
 * @param integer $offset Query offset.
 */
function update_orders( $orders, $offset ) {

	foreach ( $orders as $order ) {

		edd_update_order(
			$order->id,
			[ 'order_number' => ++$offset ]
		);
	}
}
