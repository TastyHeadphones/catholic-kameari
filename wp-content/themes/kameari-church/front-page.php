<?php
/**
 * The designed front page.
 *
 * Each strip can be switched off in Customizer → Front page — sections. If the
 * page assigned as the static front page has any content of its own, it is
 * rendered as a prose block directly under the hero.
 *
 * @package Kameari_Church
 */

get_header();
?>

<div class="page-enter">

	<?php get_template_part( 'template-parts/home/hero' ); ?>

	<?php
	$kameari_front_content = is_page() ? trim( (string) get_post_field( 'post_content', get_queried_object_id() ) ) : '';

	if ( $kameari_front_content && have_posts() ) :
		the_post();
		?>
		<section class="section">
			<div class="container">
				<div class="prose"><?php the_content(); ?></div>
			</div>
		</section>
		<?php
		rewind_posts();
	endif;
	?>

	<?php if ( kameari_mod( 'kameari_show_mass' ) ) : ?>
		<?php get_template_part( 'template-parts/home/mass' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_news' ) ) : ?>
		<?php get_template_part( 'template-parts/home/news' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_liturgy' ) ) : ?>
		<?php get_template_part( 'template-parts/home/liturgy' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_about' ) ) : ?>
		<?php get_template_part( 'template-parts/home/about' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_activities' ) ) : ?>
		<?php get_template_part( 'template-parts/home/activities' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_verse' ) ) : ?>
		<?php get_template_part( 'template-parts/home/verse' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_visit' ) ) : ?>
		<?php get_template_part( 'template-parts/home/visit' ); ?>
	<?php endif; ?>

	<?php if ( kameari_mod( 'kameari_show_access' ) ) : ?>
		<?php get_template_part( 'template-parts/home/access' ); ?>
	<?php endif; ?>

</div>

<?php
get_footer();
