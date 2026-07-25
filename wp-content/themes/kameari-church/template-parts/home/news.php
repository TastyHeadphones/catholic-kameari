<?php
/**
 * Front page — news (the five-card editorial grid).
 *
 * @package Kameari_Church
 */

$news = get_posts( array(
	'post_type'        => 'post',
	'post_status'      => 'publish',
	'numberposts'      => 5,
	'ignore_sticky_posts' => false,
	'suppress_filters' => false,
) );

if ( ! $news ) {
	return;
}

$archive = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );

$action = sprintf(
	'<a class="btn-link" href="%1$s">%2$s <span class="arr" aria-hidden="true">→</span></a>',
	esc_url( $archive ),
	esc_html__( 'すべて見る', 'kameari-church' )
);
?>
<section class="section">
	<div class="container-wide">
		<?php
		kameari_section_head(
			'03',
			__( 'News · お知らせ', 'kameari-church' ),
			__( "教会から\nのお知らせ", 'kameari-church' ),
			$action
		);
		?>

		<div class="news-grid">
			<?php foreach ( $news as $index => $post ) : ?>
				<?php
				setup_postdata( $post );
				$tag   = kameari_post_tag( $post->ID );
				$ratio = ( 1 === $index ) ? '3/4' : '4/3';
				?>
				<article <?php post_class( 'news-card', $post->ID ); ?>>
					<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<div class="thumb"><?php kameari_card_thumb( $post->ID, 'kameari-card', $ratio ); ?></div>
					</a>
					<div class="meta">
						<?php if ( $tag ) : ?>
							<span class="tag"><?php echo esc_html( $tag ); ?></span>
						<?php endif; ?>
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>"><?php echo esc_html( kameari_post_date( $post->ID ) ); ?></time>
					</div>
					<h4><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h4>
					<p><?php echo esc_html( kameari_get_excerpt( 78, $post->ID ) ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
