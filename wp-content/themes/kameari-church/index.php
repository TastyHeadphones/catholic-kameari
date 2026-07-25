<?php
/**
 * The fallback template — blog home, archives, categories.
 *
 * @package Kameari_Church
 */

get_header();

if ( is_home() && ! is_front_page() && get_option( 'page_for_posts' ) ) {
	$kameari_title = get_the_title( get_option( 'page_for_posts' ) );
	$kameari_lead  = get_post_field( 'post_excerpt', get_option( 'page_for_posts' ) );
} elseif ( is_archive() ) {
	$kameari_title = get_the_archive_title();
	$kameari_lead  = get_the_archive_description();
} else {
	$kameari_title = __( 'お知らせ', 'kameari-church' );
	$kameari_lead  = '';
}
?>

<div class="page-enter">
	<div class="page-head">
		<div class="container-wide">
			<?php kameari_crumbs( 'NEWS' ); ?>
			<h1><?php echo wp_kses_post( $kameari_title ); ?></h1>
			<?php if ( $kameari_lead ) : ?>
				<div class="lead"><?php echo wp_kses_post( wpautop( $kameari_lead ) ); ?></div>
			<?php endif; ?>
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
