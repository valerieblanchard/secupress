<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

$plans = get_transient( 'secupress_pro_plans' );

if ( false === $plans ) {
	$response = wp_remote_get( SECUPRESS_WEB_MAIN . 'plans.php' );
	if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
		$plans = wp_remote_retrieve_body( $response );
		$plans = json_decode( $plans, true );
		set_transient( 'secupress_pro_plans', $plans, DAY_IN_SECONDS );
	}
}
?>

	<div class="secupress-section-dark secupress-settings-header secupress-flex">
		<div class="secupress-col-1-3 secupress-col-logo secupress-text-center">
			<div class="secupress-logo-block">
				<div class="secupress-lb-logo">
					<?php echo secupress_get_logo( array( 'width' => 96 ), true ); ?>
				</div>
			</div>
		</div>
		<div class="secupress-col-2-3 secupress-col-text">
			<p class="secupress-text-medium"><?php esc_html_e( 'Preorder Now Your SecuPress Pro and Get an Exclusive Discount', 'secupress' ); ?></p>
			<p><?php esc_html_e( 'Choose the licence that suits you the best and be part of the first users to get SecuPress Pro upon its release', 'secupress' ); ?></p>
		</div>
	</div>

	<div class="secupress-section">

		<p class="secupress-catchphrase"><?php printf( esc_html__( 'Improve Your Security Unlocking%sAll the Features from SecuPress Pro', 'secupress' ), '<br/>' ); ?></p>

		<p class="secupress-inline-options secupress-text-center hide-if-no-js secupress-type-yearly">
			<button type="button" class="secupress-button secupress-inline-option secupress-current" data-type="yearly">
				<?php esc_html_e( 'Yearly', 'secupress' ); ?>
			</button>
			<button type="button" class="secupress-button secupress-inline-option" data-type="monthly">
				<?php esc_html_e( 'Monthly', 'secupress' ); ?>
				<span class="secupress-tip"><?php esc_html_e( 'Coming soon', 'secupress' ) ?></span>
			</button>
		</p>

		<div id="secupress-pricing" class="secupress-pricing secupress-flex secupress-text-center">
		<?php
		foreach ( $plans as $plan ) {
		?>
			<div class="secupress-col-1-3 secupress-flex">
				<div class="secupress-price secupress-box-shadow secupress-flex-col">
					<div class="secupress-price-header">
						<?php
						if ( isset( $plan['names'][ get_locale() ] ) ) {
							$plan_name = $plan['names'][ get_locale() ];
						} else {	
							$plan_name = $plan['names']['en_US'];
						}
						?>
						<p class="secupress-price-name"><?php echo esc_html( $plan_name ); ?></p>
						<p class="secupress-amounts secupress-hide-monthly">
							<span class="secupress-dollars">$</span>
							<ins><?php echo esc_html( $plan['price_new'] ); ?></ins>
							<del>$<?php echo esc_html( $plan['price_old'] ); ?></del>
						</p>
						<p class="secupress-amounts secupress-hide-yearly">
							<span class="secupress-dollars">$</span>
							<?php
							$price = explode( '.', $plan['price'] );
							if ( isset( $price[1] ) ) { 
								$price = $price[0] . '<small>' . $price[1] . '</small>';
							} else {
								$price = $price[0];
							}
							?>
							<span class="price"><?php echo $price; ?></span>
						</p>
						<p class="secupress-price-desc secupress-hide-monthly"><?php echo esc_html( sprintf( _n( '%d month for free', '%d months for free', (int) $plan['free'], 'secupress' ), (int) $plan['free'] ) ); ?></p>
					</div>
					<div class="secupress-price-details">
						<p class="secupress-pd-info secupress-hide-monthly"><?php esc_html_e( 'Billed per year', 'secupress' ); ?></p>
						<p class="secupress-pd-info secupress-hide-yearly"><?php esc_html_e( 'Billed per month', 'secupress' ); ?></p>
						<p class="secupress-pd-benefits">
							<?php esc_html_e( 'Secure & Protect', 'secupress' ); ?>
							<strong><?php echo esc_html( sprintf( _n( '%d Website', '%d Websites', (int) $plan['websites'], 'secupress' ), (int) $plan['websites'] ) ); ?></strong>
							<?php esc_html_e( 'Forever', 'secupress' ); ?>
						</p>
					</div>
					<div class="secupress-price-cta">
						<?php
						$order_button = 'preorder' == $plan['button'] ? esc_html__( 'Pre-Order', 'secupress' ) : esc_html__( 'Order', 'secupress' );
						?>
						<a href="<?php echo esc_url( $plan['url'] ); ?>" class="secupress-button secupress-button-primary shadow" target="_blank"><?php echo $order_button; ?></a>
					</div>
				</div>
			</div>
		<?php 
		}
		?>
		</div><!-- #secupress-pricing -->
		<p class="secupress-catchphrase"><?php _e( 'Included With All Plans', 'secupress' ); ?></p>

		<div class="secupress-pro-crossed-offers secupress-flex secupress-text-center secupress-p2">
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-sos.png" width="66" height="66" alt="<?php esc_attr_e( 'Support', 'secupress'); ?>">
				<p><?php esc_html_e( 'Unlimited Support and Updates', 'secupress' ); ?></p>
			</div>
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-imagify.png" width="66" height="66" alt="Imagify">
				<p><?php printf( _x( 'Bonus %s on %s', 'one line text please', 'secupress' ), '<strong class="secupress-tertiary">' . __( '100 Mb for free', 'secupress' ) . '</strong>', '<strong>Imagify</strong>' ); ?></p>
			</div>
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-wp-rocket.png" width="66" height="66" alt="WP Rocket">
				<p><?php printf( _x( 'Bonus %s on %s', 'one line text please', 'secupress' ), '<strong class="secupress-tertiary">' . __( '20% OFF', 'secupress' ) . '</strong>', '<strong>WP&nbsp;Rocket</strong>' ); ?></p>
			</div>
		</div>

		<?php secupress_print_pro_advantages(); ?>

	</div>