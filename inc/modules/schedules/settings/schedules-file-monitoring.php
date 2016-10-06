<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


$this->set_current_section( 'files-monitoring' );
$this->add_section( __( 'File Monitoring', 'secupress' ) );


/** Translators: use %d, nothing else. */
$label_before = __( 'Every %d days', 'secupress' );
$label_before = explode( '%d', $label_before );
$label_after  = $label_before[1];
$label_before = $label_before[0];

$this->add_field( array(
	'title'        => __( 'Frequency', 'secupress' ),
	'label_for'    => $this->get_field_name( 'periodicity' ),
	'type'         => 'number',
	'label_before' => $label_before,
	'label_after'  => $label_after,
	'attributes'   => array(
		'min' => 0,
		'max' => 7,
	),
	'helpers'      => array(
		array(
			'type'        => 'help',
			'description' => sprintf( __( 'Maximum %d days.', 'secupress' ), 7 ),
		),
	),
) );


$this->add_field( array(
	'title'        => __( 'Notification of result', 'secupress' ),
	'description'  => __( 'When finished, a notification will be sent to the following email address (optional).', 'secupress' ),
	'label'        => __( 'Email' ),
	'label_for'    => $this->get_field_name( 'email' ),
	'type'         => 'email',
	'default'      => wp_get_current_user()->user_email,
) );


$this->add_field( array(
	'title'        => __( 'Scheduled monitoring', 'secupress' ),
	'name'         => $this->get_field_name( 'scheduled' ),
	'type'         => 'scheduled_monitoring',
) );
