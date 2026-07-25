<?php
/**
 * Front page — verse of the day.
 *
 * @package Kameari_Church
 */

$verse = kameari_mod( 'kameari_verse_text' );

if ( ! $verse ) {
	return;
}
?>
<section class="verse-section">
	<div class="container-wide verse-inner">
		<div class="verse-text">
			<?php kameari_label( kameari_mod( 'kameari_verse_label' ), 'on-dark' ); ?>
			<blockquote><?php echo kameari_lines( $verse ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></blockquote>
			<?php $cite = kameari_mod( 'kameari_verse_cite' ); ?>
			<?php if ( $cite ) : ?>
				<cite><?php echo esc_html( $cite ); ?></cite>
			<?php endif; ?>
		</div>
		<div class="verse-art">
			<?php kameari_art( 'glass', array( 'ratio' => '4/5' ) ); ?>
		</div>
	</div>
</section>
