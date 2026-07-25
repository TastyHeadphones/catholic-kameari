<?php
/**
 * Template Name: Wide page (blocks edge to edge)
 * Template Post Type: page
 *
 * No prose container — use this when the page is built from block patterns
 * that need the full page width.
 *
 * @package Kameari_Church
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article <?php post_class( 'page-enter' ); ?>>
		<?php if ( ! get_post_meta( get_the_ID(), '_kameari_hide_page_head', true ) ) : ?>
			<div class="page-head">
				<div class="container-wide">
					<?php kameari_crumbs( get_the_title() ); ?>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php the_content(); ?>
	</article>

	<?php
endwhile;

get_footer();
