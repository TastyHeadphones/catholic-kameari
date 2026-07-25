<?php
/**
 * A single post.
 *
 * @package Kameari_Church
 */

get_header();

while ( have_posts() ) :
	the_post();
	$kameari_tag = kameari_post_tag( get_the_ID() );
	?>

	<article <?php post_class( 'page-enter' ); ?>>
		<div class="page-head">
			<div class="container-wide">
				<?php kameari_crumbs( 'NEWS' ); ?>
				<div class="entry-meta">
					<?php if ( $kameari_tag ) : ?>
						<span class="tag"><?php echo esc_html( $kameari_tag ); ?></span>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( kameari_post_date( get_the_ID() ) ); ?></time>
				</div>
				<h1><?php the_title(); ?></h1>
			</div>
		</div>

		<section class="section">
			<div class="container">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-hero-image"><?php the_post_thumbnail( 'kameari-hero', array( 'loading' => 'eager' ) ); ?></div>
				<?php endif; ?>

				<div class="prose">
					<?php
					the_content();

					wp_link_pages( array(
						'before' => '<div class="entry-meta">' . esc_html__( 'Pages:', 'kameari-church' ) . ' ',
						'after'  => '</div>',
					) );
					?>
				</div>

				<?php
				$kameari_terms = get_the_category_list( '  ·  ' );
				if ( $kameari_terms ) :
					?>
					<div class="entry-footer"><?php echo wp_kses_post( $kameari_terms ); ?></div>
				<?php endif; ?>

				<nav class="post-nav" aria-label="<?php esc_attr_e( 'Post navigation', 'kameari-church' ); ?>">
					<div class="nav-previous">
						<?php
						previous_post_link(
							'<span class="dir">' . esc_html__( 'Previous', 'kameari-church' ) . '</span>%link',
							'%title'
						);
						?>
					</div>
					<div class="nav-next">
						<?php
						next_post_link(
							'<span class="dir">' . esc_html__( 'Next', 'kameari-church' ) . '</span>%link',
							'%title'
						);
						?>
					</div>
				</nav>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</section>
	</article>

	<?php
endwhile;

get_footer();
