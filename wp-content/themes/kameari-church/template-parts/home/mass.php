<?php
/**
 * Front page — Mass times (ink panel).
 *
 * @package Kameari_Church
 */

$rows = kameari_get_rows( 'kameari_mass' );

if ( ! $rows ) {
	return;
}

$more_url = kameari_mod( 'kameari_hero_btn1_url' );
?>
<section class="mass-section">
	<div class="container-wide">
		<?php
		kameari_section_head(
			'02',
			__( 'Mass · 主日と平日', 'kameari-church' ),
			__( "ごミサ\nのご案内", 'kameari-church' )
		);
		?>

		<div class="mass-grid">
			<aside class="mass-aside">
				<?php kameari_label( kameari_mod( 'kameari_mass_label' ), 'on-dark' ); ?>
				<?php $quote = kameari_mod( 'kameari_mass_quote' ); ?>
				<?php if ( $quote ) : ?>
					<p class="quote"><?php echo kameari_lines( $quote ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></p>
				<?php endif; ?>
				<?php $cite = kameari_mod( 'kameari_mass_cite' ); ?>
				<?php if ( $cite ) : ?>
					<div class="cite"><?php echo esc_html( $cite ); ?></div>
				<?php endif; ?>

				<?php if ( $more_url ) : ?>
					<div class="more">
						<a class="btn btn-ghost" href="<?php echo esc_url( $more_url ); ?>">
							<?php esc_html_e( '月間予定を見る', 'kameari-church' ); ?> <span class="arr" aria-hidden="true">→</span>
						</a>
					</div>
				<?php endif; ?>
			</aside>

			<div class="mass-rows">
				<?php foreach ( $rows as $row ) : ?>
					<?php $featured = '1' === kameari_meta( $row->ID, 'featured' ); ?>
					<div class="mass-row<?php echo $featured ? ' featured' : ''; ?>">
						<div class="mass-day">
							<?php echo esc_html( kameari_meta( $row->ID, 'day_jp' ) ); ?>
							<?php $day_en = kameari_meta( $row->ID, 'day_en' ); ?>
							<?php if ( $day_en ) : ?>
								<span class="en"><?php echo esc_html( $day_en ); ?></span>
							<?php endif; ?>
						</div>
						<div class="mass-name">
							<?php echo esc_html( get_the_title( $row ) ); ?>
							<?php $note = kameari_meta( $row->ID, 'note' ); ?>
							<?php if ( $note ) : ?>
								<span class="note"><?php echo esc_html( $note ); ?></span>
							<?php endif; ?>
						</div>
						<div class="mass-time tabnum<?php echo $featured ? ' lg' : ''; ?>">
							<?php echo esc_html( kameari_meta( $row->ID, 'time' ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
