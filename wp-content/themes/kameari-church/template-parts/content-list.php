<?php
/**
 * One row in the news list.
 *
 * @package Kameari_Church
 */

$tag = kameari_post_tag( get_the_ID() );
?>
<a <?php post_class( 'news-list-row' ); ?> href="<?php the_permalink(); ?>">
	<div class="date">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( kameari_post_date( get_the_ID() ) ); ?></time>
	</div>
	<?php if ( $tag ) : ?>
		<span class="tag"><?php echo esc_html( $tag ); ?></span>
	<?php else : ?>
		<span></span>
	<?php endif; ?>
	<div class="body">
		<h4><?php the_title(); ?></h4>
		<p><?php echo esc_html( kameari_get_excerpt( 90 ) ); ?></p>
	</div>
	<div class="arr" aria-hidden="true">→</div>
</a>
