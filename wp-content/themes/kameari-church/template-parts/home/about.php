<?php
/**
 * Front page — pastor's welcome (editorial split).
 *
 * @package Kameari_Church
 */

$image = kameari_mod( 'kameari_about_image' );
$body  = kameari_mod( 'kameari_about_body' );
$quote = kameari_mod( 'kameari_about_quote' );
$sign  = kameari_mod( 'kameari_about_signature' );
?>
<section class="split">
	<div class="split-img">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" />
		<?php else : ?>
			<?php kameari_art( 'arches', array( 'ratio' => '4/3' ) ); ?>
		<?php endif; ?>
	</div>

	<div class="split-text">
		<?php kameari_label( kameari_mod( 'kameari_about_kicker' ) ); ?>
		<h2><?php echo kameari_lines( kameari_mod( 'kameari_about_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></h2>
		<?php echo kameari_paragraphs( $body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_paragraphs(). ?>

		<?php if ( $quote ) : ?>
			<div class="quote"><?php echo kameari_lines( $quote ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></div>
		<?php endif; ?>

		<?php if ( $sign ) : ?>
			<div class="signature">
				<?php esc_html_e( 'Pastor', 'kameari-church' ); ?>
				<span class="name"><?php echo esc_html( $sign ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</section>
