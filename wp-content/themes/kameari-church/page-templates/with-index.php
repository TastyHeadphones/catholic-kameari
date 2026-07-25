<?php
/**
 * Template Name: Page with side index
 * Template Post Type: page
 *
 * The two-column prose layout from the design: a thin mono index on the left,
 * the page's own content on the right. The index lists the page's child pages,
 * or its siblings when it has none.
 *
 * @package Kameari_Church
 */

get_header();

while ( have_posts() ) :
	the_post();

	$kameari_children = get_pages( array(
		'parent'      => get_the_ID(),
		'sort_column' => 'menu_order,post_title',
	) );

	if ( empty( $kameari_children ) ) {
		$kameari_children = get_pages( array(
			'parent'      => wp_get_post_parent_id( get_the_ID() ),
			'sort_column' => 'menu_order,post_title',
			'exclude'     => array( get_the_ID() ),
		) );
	}
	?>

	<article <?php post_class( 'page-enter' ); ?>>
		<div class="page-head">
			<div class="container-wide">
				<?php kameari_crumbs( get_the_title() ); ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<section class="section">
			<div class="container-wide">
				<div class="prose-grid">
					<aside>
						<?php if ( $kameari_children ) : ?>
							<h5><?php esc_html_e( 'このページ', 'kameari-church' ); ?></h5>
							<ul>
								<?php foreach ( $kameari_children as $kameari_child ) : ?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $kameari_child->ID ) ); ?>">
											<span><?php echo esc_html( get_the_title( $kameari_child->ID ) ); ?></span>
											<span class="arr" aria-hidden="true">→</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
							<div style="margin-top:40px"><?php dynamic_sidebar( 'footer-1' ); ?></div>
						<?php endif; ?>
					</aside>

					<div class="prose">
						<?php
						the_content();

						wp_link_pages( array(
							'before' => '<div class="entry-meta">' . esc_html__( 'Pages:', 'kameari-church' ) . ' ',
							'after'  => '</div>',
						) );
						?>
					</div>
				</div>
			</div>
		</section>
	</article>

	<?php
endwhile;

get_footer();
