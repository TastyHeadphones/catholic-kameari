<?php
/**
 * Comments.
 *
 * @package Kameari_Church
 */

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$kameari_count = get_comments_number();
			printf(
				/* translators: %s: comment count. */
				esc_html( _n( 'コメント %s件', 'コメント %s件', $kameari_count, 'kameari-church' ) ),
				esc_html( number_format_i18n( $kameari_count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 48,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation( array(
			'prev_text' => '← ' . esc_html__( 'Older comments', 'kameari-church' ),
			'next_text' => esc_html__( 'Newer comments', 'kameari-church' ) . ' →',
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="entry-meta"><?php esc_html_e( 'コメントは受け付けていません。', 'kameari-church' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</div>
