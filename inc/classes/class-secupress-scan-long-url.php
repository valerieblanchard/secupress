<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Long URL scan class.
 *
 * @package SecuPress
 * @subpackage SecuPress_Scan
 * @since 1.0
 */

class SecuPress_Scan_Long_URL extends SecuPress_Scan {

	const VERSION = '1.0';

	protected static $name = 'long_url';
	public    static $prio = 'medium';


	public function __construct() {
		if ( self::$instance ) {
			return self::$instance;
		}

		self::$type  = 'WordPress';
		self::$title = __( 'Check if long URL can reach your website (more than 255 chars).', 'secupress' );
		self::$more  = __( '////?', 'secupress' );
	}


	public static function get_messages( $status = null, $id = null ) {
		$messages = array(
			'good'    => array(
				0 => __( 'You are currently blocking bad request methods.', 'secupress' ),
			),
			'warning' => array(
				0 => __( 'Unable to determine status of your homepage.', 'secupress' ),
			),
			'cantfix' => array(
				0 => __( 'I can not fix this, you have to do it yourself, have fun.', 'secupress' ),
			),
			'bad'     => array(
				0 => __( 'Your website should block <strong>too long string requests</strong>.', 'secupress' ),
			),
		);

		if ( isset( $status ) ) {
			if ( isset( $id ) ) {
				return isset( $messages[ $status ][ $id ] ) ? $messages[ $status ][ $id ] : __( 'Unknown message', 'secupress' );
			}

			return isset( $messages[ $status ] ) ? $messages[ $status ] : array( __( 'Unknown message', 'secupress' ), );
		}

		return $messages;
	}


	public function scan() {

		$response = wp_remote_get( home_url( '/?' . time() . '=' . wp_generate_password( 255, false ) ), array( 'redirection' => 0 ) );

		if ( ! is_wp_error( $response ) ) {

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

				$this->result = array(
					'status' => 'bad',
					'msgs'   => array(
						0 => array(),
					),
				);

			} else {

				$this->result = array(
					'status' => 'good',
					'msgs'   => array(
						0 => array(),
					),
				);

			}

		} else {

			$this->result = array(
				'status' => 'warning',
				'msgs'   => array(
					0 => array(),
				),
			);

		}

		return parent::scan();
	}


	public function fix() {

		// include the fix here.

		return parent::fix();
	}
}
