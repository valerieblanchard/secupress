<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Non Login Time Slot scan class.
 *
 * @package SecuPress
 * @subpackage SecuPress_Scan
 * @since 1.0
 */
class SecuPress_Scan_Non_Login_Time_Slot extends SecuPress_Scan implements SecuPress_Scan_Interface {

	const VERSION = '1.0';

	/**
	 * The reference to *Singleton* instance of this class.
	 *
	 * @var (object)
	 */
	protected static $_instance;

	/**
	 * Priority.
	 *
	 * @var (string)
	 */
	public    static $prio    = 'medium';

	/**
	 * Tells if a scanner is fixable by SecuPress. The value "pro" means it's fixable only with the version PRO.
	 *
	 * @var (bool|string)
	 */
	public    static $fixable = 'pro';


	/**
	 * Init.
	 *
	 * @since 1.0
	 */
	protected static function init() {
		self::$type     = 'WordPress';
		self::$title    = __( 'Check if you\'re back-end is accessible 24h/24.', 'secupress' );
		self::$more     = __( 'You don\'t necessarily need to let your back-end open like 24 hours a day, you should close it during your sleeping time.', 'secupress' );
		self::$more_fix = sprintf(
			__( 'This will activate the option %1$s from the module %2$s.', 'secupress' ),
			'<em>' . __( 'Non Login Time Slot', 'secupress' ) . '</em>',
			'<a href="' . esc_url( secupress_admin_url( 'modules', 'users-login' ) ) . '#row-login-protection_type">' . __( 'Users & Login', 'secupress' ) . '</a>'
		);
	}


	/**
	 * Get messages.
	 *
	 * @since 1.0
	 *
	 * @param (int) $message_id A message ID.
	 *
	 * @return (string|array) A message if a message ID is provided. An array containing all messages otherwise.
	 */
	public static function get_messages( $message_id = null ) {
		$messages = array(
			// "good"
			0   => __( 'You are currently <strong>locking</strong> your back-end, sometimes.', 'secupress' ),
			1   => __( 'Protection activated', 'secupress' ),
			// "bad"
			200 => __( 'Your website should be <strong>locked out sometimes</strong>.', 'secupress' ),
			201 => sprintf( __( 'Our module <a href="%s">%s</a> could fix this.', 'secupress' ), esc_url( secupress_admin_url( 'modules', 'users-login' ) ) . 'row-login-protection_type', __( 'Non Login Time Slot', 'secupress' ) ),
		);

		if ( isset( $message_id ) ) {
			return isset( $messages[ $message_id ] ) ? $messages[ $message_id ] : __( 'Unknown message', 'secupress' );
		}

		return $messages;
	}


	/**
	 * Scan for flaw(s).
	 *
	 * @since 1.0
	 *
	 * @return (array) The scan results.
	 */
	public function scan() {

		if ( ! secupress_is_submodule_active( 'users-login', 'nonlogintimeslot' ) ) {
			// "bad"
			$this->add_message( 200 );
			$this->add_pre_fix_message( 201 );
		}

		// "good"
		$this->maybe_set_status( 0 );

		return parent::scan();
	}


	/**
	 * Try to fix the flaw(s).
	 *
	 * @since 1.0
	 *
	 * @return (array) The fix results.
	 */
	public function fix() {

		if ( secupress_is_pro() && function_exists( 'secupress_pro_fix_non_login_time_slot' ) ) {
			secupress_pro_fix_non_login_time_slot();
		}

		return parent::fix();
	}
}