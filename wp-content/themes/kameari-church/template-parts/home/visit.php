<?php
/**
 * Front page — visitor guide.
 *
 * @package Kameari_Church
 */

$steps = kameari_parse_steps( kameari_mod( 'kameari_visit_steps' ) );
$label = kameari_mod( 'kameari_visit_label' );
?>
<section class="visit-block">
	<div class="visit-text">
		<?php kameari_label( kameari_mod( 'kameari_visit_kicker' ), 'on-dark' ); ?>
		<h2><?php echo kameari_lines( kameari_mod( 'kameari_visit_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></h2>
		<?php $body = kameari_mod( 'kameari_visit_body' ); ?>
		<?php if ( $body ) : ?>
			<p><?php echo esc_html( $body ); ?></p>
		<?php endif; ?>
		<?php if ( $label ) : ?>
			<a class="btn btn-primary" href="<?php echo esc_url( kameari_url( kameari_mod( 'kameari_visit_url' ) ) ); ?>">
				<?php echo esc_html( $label ); ?> <span class="arr" aria-hidden="true">→</span>
			</a>
		<?php endif; ?>
	</div>

	<div class="visit-checklist">
		<?php kameari_label( __( 'Five steps · ご来訪の前に', 'kameari-church' ) ); ?>
		<?php foreach ( $steps as $index => $step ) : ?>
			<div class="visit-check">
				<div class="num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></div>
				<div class="text">
					<?php echo esc_html( $step['text'] ); ?>
					<?php if ( $step['small'] ) : ?>
						<small><?php echo esc_html( $step['small'] ); ?></small>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
