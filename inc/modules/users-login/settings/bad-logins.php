<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


$this->set_current_section( 'login_auth3' );
$this->add_section( __( 'User Logins', 'secupress' ) );


$field_name      = $this->get_field_name( 'blacklist-logins' );
$main_field_name = $field_name;

$this->add_field(
	__( 'Forbid Logins', 'secupress' ),
	array(
		'name'        => $field_name,
	),
	array(
		array(
			'type'         => 'checkbox',
			'name'         => $field_name,
			'label'        => __( 'Yes, forbid users to use some logins', 'secupress' ),
			'label_for'    => $field_name,
			'label_screen' => __( 'Yes, forbid users to use some logins', 'secupress' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => $field_name,
			'description'  => __( 'Create a list of forbidden user logins.', 'secupress' ),
		),
	)
);


$field_name  = $this->get_field_name( 'blacklist-logins-list' );

// Starting from WP 4.4 we can prevent a user creation if his/her username is in the blacklist.
if ( version_compare( $GLOBALS['wp_version'], '4.4.0' ) >= 0 ) {
	$description = __( 'Users won\'t be able to use any of the following logins. The users already using one of those will be asked to change it.', 'secupress' );
} else {
	$description = __( 'Users won\'t be able to use any of the following logins, they will be asked to change it.', 'secupress' );
}

$this->add_field(
	__( 'Forbidden Logins', 'secupress' ),
	array(
		'name'        => $field_name,
		'description' => $description,
	),
	array(
		'depends_on'  => $main_field_name,
		array(
			'type'        => 'textarea',
			'name'        => $field_name,
			'label_for'   => $field_name,
		),
		array(
			'type'        => 'helper_description',
			'name'        => $field_name,
			'description' => __( 'One login per line.', 'secupress' ) . '<br/>' . secupress_blacklist_logins_allowed_characters( true ),
		),
	)
);