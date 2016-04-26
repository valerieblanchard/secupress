<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


$this->set_current_section( 'login_auth2' );
$this->add_section( __( 'Login Control', 'secupress' ) );


$is_plugin_active = array();
$values           = array(
	'limitloginattempts' => __( 'Limit the number of bad login attempts', 'secupress' ),
	'bannonexistsuser'   => __( 'Ban login attempts on non-existing usernames', 'secupress' ),
	'nonlogintimeslot'   => __( 'Set a non-login time slot', 'secupress' ),
);

foreach ( $values as $_plugin => $label ) {
	if ( secupress_is_submodule_active( 'users-login', $_plugin ) ) {
		$is_plugin_active[] = $_plugin;
	}
}

$main_field_name = $this->get_field_name( 'type' );

$this->add_field( array(
	'title'             => __( 'Use an attempt blocker', 'secupress' ),
	'description'       => __( 'You can temporary ban people who try to mess with the login page. This is recommended to avoid to be victim of a brute-force.', 'secupress' ),
	'name'              => $main_field_name,
	'plugin_activation' => true,
	'type'              => 'checkboxes',
	'options'           => $values,
	'value'             => $is_plugin_active,
	'label_screen'      => __( 'Choose your attempts blocker', 'secupress' ),
) );


$this->add_field( array(
	'title'        => __( 'How many attempts before a ban?', 'secupress' ),
	'description'  => sprintf( __( 'Recommended: %s', 'secupress' ), '10 - 50' ),
	'depends'      => $main_field_name . '_limitloginattempts',
	'label_for'    => $this->get_field_name( 'number_attempts' ),
	'type'         => 'number',
	'default'      => '10',
	'attributes'   => array(
		'min' => 3,
		'max' => 99,
	),
) );


$this->add_field( array(
	'title'        => __( 'How long should we ban?', 'secupress' ),
	'description'  => sprintf( __( 'Recommended: %s', 'secupress' ), '5 - 15' ),
	'depends'      => $main_field_name . '_limitloginattempts ' . $main_field_name . '_bannonexistsuser',
	'label_for'    => $this->get_field_name( 'time_ban' ),
	'type'         => 'number',
	'label_after'  => _x( 'min', 'minute', 'secupress' ),
	'default'      => '5',
	'attributes'   => array(
		'min' => 1,
		'max' => 60,
	),
) );


$field_name = $this->get_field_name( 'nonlogintimeslot' );
// Server hour.
$utc          = new DateTimeZone( 'UTC' );
$new_tz       = new DateTimeZone( ini_get( 'date.timezone' ) );
$date         = new DateTime( '', $utc );
$date->setTimezone( $new_tz );
$server_hour  = $date->format( 'H \h i \m\i\n' );

$this->add_field( array(
	'title'        => __( 'Non-Login time slot settings', 'secupress' ),
	'depends'      => $main_field_name . '_nonlogintimeslot',
	'label_for'    => $field_name . '_from_hour',
	'name'         => $field_name,
	'type'         => 'time-slot',
	'label'        => __( 'Everyday:', 'secupress' ),
	'fieldset'     => 'yes',
	'label_screen' => __( 'Choose your time slot', 'secupress' ),
	'helpers'      => array(
		array(
			'type'        => 'help',
			'description' => sprintf( __( 'Current server time: %s.', 'secupress' ), '<strong>' . $server_hour . '</strong>' ),
		),
		array(
			'type'        => 'description',
			'description' => __( 'Select the range of time you need to disallow logins.', 'secupress' ),
		),
	),
) );


$this->add_field( array(
	'title'             => __( 'Avoid Double Connexions', 'secupress' ),
	'description'       => __( 'Once logged in, nobody can log in on your account at the same time as you. You have to disconnect first to allow another connexion.', 'secupress' ),
	'label_for'         => $this->get_field_name( 'only-one-connexion' ),
	'plugin_activation' => true,
	'type'              => 'checkbox',
	'value'             => (int) secupress_is_submodule_active( 'users-login', 'only-one-connexion' ),
	'label'             => __( 'Yes, do not allow double connexions', 'secupress' ),
	'helpers'           => array(
		array(
			'type'        => 'description',
			'description' => __( 'You will be able to force the disconnection of anyone or everyone when using the <b>Sessions Control</b> module below.', 'secupress' ),
		),
	),
) );


$this->add_field( array(
	'title'             => __( 'Sessions Control', 'secupress' ),
	'description'       => __( 'Disconnect any user in one click, or even every logged in user at the same time in one click (but you).', 'secupress' ),
	'label_for'         => $this->get_field_name( 'sessions_control' ),
	'plugin_activation' => true,
	'type'              => 'checkbox',
	'value'             => (int) secupress_is_submodule_active( 'users-login', 'sessions-control' ),
	'label'             => __( 'Yes, i want to use the Sessions Control Module', 'secupress' ),
	'helpers'           => array(
		array(
			'type'        => 'description',
			'description' => sprintf( __( 'You will find action links on every user\'s row in the <a href="%s">users listing administration page</a>.', 'secupress' ), esc_url( admin_url( 'users.php' ) ) ),
		),
	),
) );
