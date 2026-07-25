<?php
/**
 * Front page — access strip.
 *
 * @package Kameari_Church
 */

$postal  = kameari_mod( 'kameari_postal' );
$address = kameari_mod( 'kameari_address' );
$phone   = kameari_mod( 'kameari_phone' );
$hours   = kameari_mod( 'kameari_hours' );
$station = kameari_mod( 'kameari_station' );
$parking = kameari_mod( 'kameari_parking' );
$map     = kameari_mod( 'kameari_map_embed' );
?>
<section class="access-strip">
	<div class="access-info">
		<?php kameari_label( __( 'Access · アクセス', 'kameari-church' ) ); ?>
		<h3><?php echo kameari_lines( kameari_mod( 'kameari_access_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></h3>

		<dl class="address-block">
			<?php if ( $address || $postal ) : ?>
				<dt><?php esc_html_e( '住所', 'kameari-church' ); ?></dt>
				<dd>
					<?php if ( $postal ) : ?>
						<span class="strong"><?php echo esc_html( $postal ); ?></span><br />
					<?php endif; ?>
					<?php echo esc_html( $address ); ?>
				</dd>
			<?php endif; ?>

			<?php if ( $phone ) : ?>
				<dt><?php esc_html_e( '電話', 'kameari-church' ); ?></dt>
				<dd>
					<a class="strong" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					<?php if ( $hours ) : ?>
						<br /><?php echo esc_html( $hours ); ?>
					<?php endif; ?>
				</dd>
			<?php endif; ?>

			<?php if ( $station ) : ?>
				<dt><?php esc_html_e( '最寄駅', 'kameari-church' ); ?></dt>
				<dd><?php echo kameari_lines( $station ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></dd>
			<?php endif; ?>

			<?php if ( $parking ) : ?>
				<dt><?php esc_html_e( '駐車場', 'kameari-church' ); ?></dt>
				<dd><?php echo esc_html( $parking ); ?></dd>
			<?php endif; ?>
		</dl>
	</div>

	<div class="access-map">
		<?php if ( $map ) : ?>
			<iframe src="<?php echo esc_url( $map ); ?>"
				title="<?php esc_attr_e( 'Map', 'kameari-church' ); ?>"
				loading="lazy" referrerpolicy="no-referrer-when-downgrade"
				allowfullscreen></iframe>
		<?php else : ?>
			<?php kameari_art( 'map', array( 'ratio' => '4/3' ) ); ?>
		<?php endif; ?>
	</div>
</section>
