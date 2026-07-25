<?php
/**
 * A static page.
 *
 * The page excerpt, if set, becomes the standfirst under the title — that is
 * the calm lead paragraph in the design.
 *
 * @package Kameari_Church
 */

get_header();

while ( have_posts() ) :
	the_post();
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
			<div class="container">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-hero-image"><?php the_post_thumbnail( 'kameari-hero' ); ?></div>
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
