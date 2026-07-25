<?php
/**
 * Search results.
 *
 * @package Kameari_Church
 */

get_header();
?>

<div class="page-enter">
	<div class="page-head">
		<div class="container-wide">
			<?php kameari_crumbs( 'SEARCH' ); ?>
			<h1>
				<?php
				printf(
					/* translators: %s: search term. */
					esc_html__( '「%s」の検索結果', 'kameari-church' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
			<?php get_search_form(); ?>
		</div>
	</div>

	<section class="section tight">
		<div class="container-wide">
			<?php if ( have_posts() ) : ?>
				<div class="news-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'list' );
					endwhile;
					?>
				</div>
				<?php kameari_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php
get_footer();
