<?php
/**
 * Parish content types.
 *
 * These four post types back the structured strips on the front page. They are
 * deliberately admin-only (no single templates, no archives): the parish edits
 * rows in wp-admin, and the theme renders them inside the page design.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the parish post types.
 */
function kameari_register_post_types() {
	$shared = array(
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'menu_position'       => 21,
	);

	register_post_type( 'kameari_mass', array_merge( $shared, array(
		'labels'        => kameari_pt_labels(
			__( 'Mass Times', 'kameari-church' ),
			__( 'Mass Time', 'kameari-church' )
		),
		'menu_icon'     => 'dashicons-clock',
		'supports'      => array( 'title', 'page-attributes' ),
	) ) );

	register_post_type( 'kameari_event', array_merge( $shared, array(
		'labels'        => kameari_pt_labels(
			__( 'Liturgical Calendar', 'kameari-church' ),
			__( 'Liturgical Date', 'kameari-church' )
		),
		'menu_icon'     => 'dashicons-calendar-alt',
		'supports'      => array( 'title', 'page-attributes' ),
	) ) );

	register_post_type( 'kameari_activity', array_merge( $shared, array(
		'labels'        => kameari_pt_labels(
			__( 'Parish Activities', 'kameari-church' ),
			__( 'Parish Activity', 'kameari-church' )
		),
		'menu_icon'     => 'dashicons-groups',
		'supports'      => array( 'title', 'excerpt', 'page-attributes' ),
	) ) );

	register_post_type( 'kameari_clergy', array_merge( $shared, array(
		'labels'        => kameari_pt_labels(
			__( 'Clergy', 'kameari-church' ),
			__( 'Priest', 'kameari-church' )
		),
		'menu_icon'     => 'dashicons-businessperson',
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) ) );
}
add_action( 'init', 'kameari_register_post_types' );

/**
 * Build a labels array for a parish post type.
 *
 * @param string $plural   Plural label.
 * @param string $singular Singular label.
 * @return array
 */
function kameari_pt_labels( $plural, $singular ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		/* translators: %s: singular post type label. */
		'add_new_item'       => sprintf( __( 'Add %s', 'kameari-church' ), $singular ),
		/* translators: %s: singular post type label. */
		'edit_item'          => sprintf( __( 'Edit %s', 'kameari-church' ), $singular ),
		/* translators: %s: singular post type label. */
		'new_item'           => sprintf( __( 'New %s', 'kameari-church' ), $singular ),
		/* translators: %s: plural post type label. */
		'all_items'          => sprintf( __( 'All %s', 'kameari-church' ), $plural ),
		/* translators: %s: plural post type label. */
		'search_items'       => sprintf( __( 'Search %s', 'kameari-church' ), $plural ),
		/* translators: %s: plural post type label. */
		'not_found'          => sprintf( __( 'No %s yet', 'kameari-church' ), $plural ),
	);
}

/**
 * Fetch parish rows in the order the editor arranged them.
 *
 * @param string $post_type Post type name.
 * @param int    $limit     Maximum rows. -1 for all.
 * @return WP_Post[]
 */
function kameari_get_rows( $post_type, $limit = -1 ) {
	$posts = get_posts( array(
		'post_type'        => $post_type,
		'post_status'      => 'publish',
		'numberposts'      => $limit,
		'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'suppress_filters' => false,
	) );

	return is_array( $posts ) ? $posts : array();
}
