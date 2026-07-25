<?php
/**
 * Shown when a loop has nothing in it.
 *
 * @package Kameari_Church
 */

?>
<div class="no-results prose">
	<h3><?php esc_html_e( '該当する記事はありませんでした', 'kameari-church' ); ?></h3>
	<p><?php esc_html_e( '別のことばでお探しいただくか、ホームからご覧ください。', 'kameari-church' ); ?></p>
	<?php get_search_form(); ?>
</div>
