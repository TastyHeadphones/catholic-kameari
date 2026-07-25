<?php
/**
 * Search form.
 *
 * @package Kameari_Church
 */

$kameari_search_id = 'kameari-search-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $kameari_search_id ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'kameari-church' ); ?></span>
		<input type="search" id="<?php echo esc_attr( $kameari_search_id ); ?>" class="search-field"
			placeholder="<?php esc_attr_e( 'サイト内を検索', 'kameari-church' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	</label>
	<button type="submit"><?php esc_html_e( '検索', 'kameari-church' ); ?></button>
</form>
