<?php
/**
 * Site footer.
 *
 * @package Kameari_Church
 */

$kameari_footer_menus = array(
	'footer-parish'  => __( 'Parish / 教会', 'kameari-church' ),
	'footer-liturgy' => __( 'Worship / 典礼', 'kameari-church' ),
	'footer-contact' => __( 'Contact / ご連絡', 'kameari-church' ),
);
?>
</main><!-- .site-main -->

<footer class="footer">
	<div class="container-wide">

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="footer-widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
		<?php endif; ?>

		<div class="footer-top">
			<div>
				<?php kameari_brand( array( 'mono' => true ) ); ?>
				<?php $kameari_tagline = kameari_mod( 'kameari_footer_tagline' ); ?>
				<?php if ( $kameari_tagline ) : ?>
					<p class="footer-tag"><?php echo kameari_lines( $kameari_tagline ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></p>
				<?php endif; ?>
			</div>

			<?php foreach ( $kameari_footer_menus as $kameari_location => $kameari_heading ) : ?>
				<?php if ( has_nav_menu( $kameari_location ) ) : ?>
					<div class="footer-col">
						<h5><?php echo esc_html( $kameari_heading ); ?></h5>
						<?php
						wp_nav_menu( array(
							'theme_location' => $kameari_location,
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						) );
						?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<div class="footer-bottom">
			<span>
				<?php
				printf(
					/* translators: 1: year, 2: church name. */
					esc_html__( '© %1$s %2$s', 'kameari-church' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( kameari_brand_name() )
				);
				?>
			</span>
			<?php $kameari_motto = kameari_mod( 'kameari_footer_motto' ); ?>
			<?php if ( $kameari_motto ) : ?>
				<span><?php echo esc_html( $kameari_motto ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
