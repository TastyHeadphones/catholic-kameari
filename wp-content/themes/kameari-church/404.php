<?php
/**
 * 404.
 *
 * @package Kameari_Church
 */

get_header();
?>

<div class="page-enter">
	<div class="page-head">
		<div class="container-wide">
			<?php kameari_crumbs( '404' ); ?>
			<h1><?php esc_html_e( 'ページが見つかりません', 'kameari-church' ); ?></h1>
			<p class="lead"><?php esc_html_e( 'お探しのページは移動または削除された可能性があります。下の検索、またはホームからお探しください。', 'kameari-church' ); ?></p>
		</div>
	</div>

	<section class="section">
		<div class="container">
			<?php get_search_form(); ?>
			<p style="margin-top:48px">
				<a class="btn btn-outline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'ホームへ', 'kameari-church' ); ?> <span class="arr" aria-hidden="true">→</span>
				</a>
			</p>
		</div>
	</section>
</div>

<?php
get_footer();
