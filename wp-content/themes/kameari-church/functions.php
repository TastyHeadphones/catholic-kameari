<?php
/**
 * Kameari Church theme bootstrap.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KAMEARI_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function kameari_setup() {
	load_theme_textdomain( 'kameari-church', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 80,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'kameari-hero', 2200, 1200, true );
	add_image_size( 'kameari-card', 900, 675, true );
	add_image_size( 'kameari-portrait', 800, 1000, true );

	register_nav_menus( array(
		'primary'       => __( 'Primary navigation', 'kameari-church' ),
		'mobile'        => __( 'Mobile navigation', 'kameari-church' ),
		'footer-parish' => __( 'Footer — Parish', 'kameari-church' ),
		'footer-liturgy'=> __( 'Footer — Worship', 'kameari-church' ),
		'footer-contact'=> __( 'Footer — Contact', 'kameari-church' ),
	) );
}
add_action( 'after_setup_theme', 'kameari_setup' );

/**
 * Content width, used by WordPress for oEmbeds and large images.
 */
function kameari_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'kameari_content_width', 860 );
}
add_action( 'after_setup_theme', 'kameari_content_width', 0 );

/**
 * Front-end assets.
 */
function kameari_scripts() {
	if ( get_theme_mod( 'kameari_google_fonts', true ) ) {
		wp_enqueue_style(
			'kameari-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=JetBrains+Mono:wght@400;500&family=Noto+Sans+JP:wght@400;500;700&family=Shippori+Mincho+B1:wght@400;500;600&display=swap',
			array(),
			null
		);
	}

	wp_enqueue_style( 'kameari-style', get_stylesheet_uri(), array(), KAMEARI_VERSION );

	wp_enqueue_script(
		'kameari-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		KAMEARI_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'kameari_scripts' );

/**
 * Preconnect to the Google Fonts hosts only when the fonts are actually used.
 *
 * @param array  $urls          URLs to print.
 * @param string $relation_type Relation type.
 * @return array
 */
function kameari_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && get_theme_mod( 'kameari_google_fonts', true ) ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => '' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'kameari_resource_hints', 10, 2 );

/**
 * Put the chosen accent on <html> so the CSS custom properties resolve.
 *
 * @param string $output Existing html tag attributes.
 * @return string
 */
function kameari_language_attributes( $output ) {
	$accent = get_theme_mod( 'kameari_accent', 'green' );
	return $output . ' data-accent="' . esc_attr( $accent ) . '"';
}
add_filter( 'language_attributes', 'kameari_language_attributes' );

/**
 * Footer widget area.
 */
function kameari_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer', 'kameari-church' ),
		'id'            => 'footer-1',
		'description'   => __( 'Optional widgets shown above the footer columns.', 'kameari-church' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h5 class="widget-title">',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'kameari_widgets_init' );

/**
 * Excerpt tuning — Japanese text needs a character count, not a word count.
 *
 * @param string $more Existing more string.
 * @return string
 */
function kameari_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'kameari_excerpt_more' );

/**
 * Trim an excerpt to a sensible length for CJK text.
 *
 * @param int|null $length Character length. Defaults to 90.
 * @param int|null $post_id Post ID.
 * @return string
 */
function kameari_get_excerpt( $length = 90, $post_id = null ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	$text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
	$text = wp_strip_all_tags( strip_shortcodes( $text ), true );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

	if ( mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length ) . '…';
	}

	return $text;
}

/**
 * Numbered pagination in the theme's own markup.
 */
function kameari_pagination() {
	$links = paginate_links( array(
		'type'      => 'list',
		'mid_size'  => 1,
		'prev_text' => '← ' . __( 'Newer', 'kameari-church' ),
		'next_text' => __( 'Older', 'kameari-church' ) . ' →',
	) );

	if ( ! $links ) {
		return;
	}

	echo '<nav class="pagination" aria-label="' . esc_attr__( 'Posts navigation', 'kameari-church' ) . '">';
	echo wp_kses_post( str_replace( '<ul class=\'page-numbers\'>', '<ul class="nav-links page-numbers">', $links ) );
	echo '</nav>';
}

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/illustrations.php';
require get_template_directory() . '/inc/content-types.php';
require get_template_directory() . '/inc/meta-boxes.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/patterns.php';
