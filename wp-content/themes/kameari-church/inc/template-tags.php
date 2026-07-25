<?php
/**
 * Template tags.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The church mark: a bell tower, a curved worship drum, and a low wing.
 *
 * @param int  $size Pixel size.
 * @param bool $mono Draw in currentColor instead of ink.
 */
function kameari_logo( $size = 40, $mono = false ) {
	$ink  = $mono ? 'currentColor' : 'var(--ink)';
	$gold = $mono ? 'currentColor' : 'var(--ink)';
	$sw   = '1.6';
	?>
	<svg width="<?php echo absint( $size ); ?>" height="<?php echo absint( $size ); ?>" viewBox="0 0 64 64" aria-hidden="true" focusable="false" style="display:block">
		<rect x="29.4" y="4" width="1.4" height="9" fill="<?php echo esc_attr( $gold ); ?>" />
		<rect x="26.5" y="6.6" width="7.2" height="1.4" fill="<?php echo esc_attr( $gold ); ?>" />
		<rect x="26" y="13" width="8" height="38" fill="none" stroke="<?php echo esc_attr( $ink ); ?>" stroke-width="<?php echo esc_attr( $sw ); ?>" />
		<rect x="28.5" y="18" width="3" height="3" fill="<?php echo esc_attr( $ink ); ?>" />
		<path d="M 34 51 V 28 A 14 14 0 0 1 48 22 A 14 14 0 0 1 56 28 V 51" fill="none" stroke="<?php echo esc_attr( $ink ); ?>" stroke-width="<?php echo esc_attr( $sw ); ?>" stroke-linejoin="round" />
		<rect x="40" y="34" width="1.2" height="13" fill="<?php echo esc_attr( $ink ); ?>" />
		<rect x="45" y="34" width="1.2" height="13" fill="<?php echo esc_attr( $ink ); ?>" />
		<rect x="50" y="34" width="1.2" height="13" fill="<?php echo esc_attr( $ink ); ?>" />
		<rect x="8" y="36" width="18" height="15" fill="none" stroke="<?php echo esc_attr( $ink ); ?>" stroke-width="<?php echo esc_attr( $sw ); ?>" />
		<rect x="6" y="51.6" width="52" height="1.2" fill="<?php echo esc_attr( $gold ); ?>" />
	</svg>
	<?php
}

/**
 * The church name in Japanese, falling back to the site title.
 *
 * @return string
 */
function kameari_brand_name() {
	$name = kameari_mod( 'kameari_brand_jp' );
	return $name ? $name : get_bloginfo( 'name' );
}

/**
 * Brand lockup: mark plus name.
 *
 * @param array $args mono, link, subtitle.
 */
function kameari_brand( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'mono'     => false,
		'link'     => true,
		'subtitle' => true,
	) );

	$tag      = $args['link'] ? 'a' : 'div';
	$href     = $args['link'] ? ' href="' . esc_url( home_url( '/' ) ) . '" rel="home"' : '';
	$subtitle = kameari_mod( 'kameari_brand_sub' );
	$logo_id  = get_theme_mod( 'custom_logo' );
	?>
	<<?php echo esc_html( $tag ) . $href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- href pre-escaped above. ?> class="brand">
		<span class="brand-mark">
			<?php
			if ( $logo_id && ! $args['mono'] ) {
				echo wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => '' ) );
			} else {
				kameari_logo( 40, $args['mono'] );
			}
			?>
		</span>
		<span class="brand-text">
			<span class="brand-jp"><?php echo esc_html( kameari_brand_name() ); ?></span>
			<?php if ( $args['subtitle'] && $subtitle ) : ?>
				<span class="brand-sub"><?php echo esc_html( $subtitle ); ?></span>
			<?php endif; ?>
		</span>
	</<?php echo esc_html( $tag ); ?>>
	<?php
}

/**
 * Menu fallback: list top-level pages when no menu is assigned yet.
 *
 * @param array $args wp_nav_menu args.
 */
function kameari_nav_fallback( $args ) {
	$pages = get_pages( array(
		'sort_column' => 'menu_order,post_title',
		'parent'      => 0,
		'number'      => 8,
	) );

	if ( empty( $pages ) ) {
		return;
	}

	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'nav-list';

	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $pages as $page ) {
		$current = ( is_page( $page->ID ) ) ? ' class="current_page_item"' : '';
		printf(
			'<li%1$s><a href="%2$s">%3$s</a></li>',
			$current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup.
			esc_url( get_permalink( $page->ID ) ),
			esc_html( get_the_title( $page->ID ) )
		);
	}
	echo '</ul>';
}

/**
 * A mono micro-label with its leading rule.
 *
 * @param string $text  Label text.
 * @param string $extra Extra class names, e.g. "on-dark".
 */
function kameari_label( $text, $extra = '' ) {
	if ( ! $text ) {
		return;
	}
	printf(
		'<span class="label %1$s">%2$s</span>',
		esc_attr( $extra ),
		esc_html( $text )
	);
}

/**
 * The numbered section header used across the front page.
 *
 * @param string $num    Two-digit section number.
 * @param string $kicker Small caption.
 * @param string $title  Heading. Line breaks become <br>.
 * @param string $action Optional trailing markup (already escaped).
 */
function kameari_section_head( $num, $kicker, $title, $action = '' ) {
	?>
	<div class="section-head">
		<div class="num">
			<span class="big"><?php echo esc_html( $num ); ?></span>
			<?php echo esc_html( $kicker ); ?>
		</div>
		<h2><?php echo kameari_lines( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></h2>
		<div><?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Caller-built markup. ?></div>
	</div>
	<?php
}

/**
 * Escape text and turn its line breaks into <br> tags.
 *
 * @param string $text Raw text.
 * @return string
 */
function kameari_lines( $text ) {
	return nl2br( esc_html( trim( (string) $text ) ), false );
}

/**
 * Escape text and split it into paragraphs on blank lines.
 *
 * @param string $text Raw text.
 * @return string
 */
function kameari_paragraphs( $text ) {
	$chunks = preg_split( '/\n\s*\n/u', trim( (string) $text ) );
	$out    = '';

	foreach ( (array) $chunks as $chunk ) {
		if ( '' === trim( $chunk ) ) {
			continue;
		}
		$out .= '<p>' . kameari_lines( $chunk ) . '</p>';
	}

	return $out;
}

/**
 * Read a parish meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without the leading underscore prefix.
 * @return string
 */
function kameari_meta( $post_id, $key ) {
	return (string) get_post_meta( $post_id, '_kameari_' . $key, true );
}

/**
 * Breadcrumb strip for interior page headers.
 *
 * @param string $current Current page label.
 */
function kameari_crumbs( $current ) {
	?>
	<div class="crumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a>
		<span aria-hidden="true">/</span>
		<span><?php echo esc_html( $current ); ?></span>
	</div>
	<?php
}

/**
 * The first category of a post, used as its tag chip.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function kameari_post_tag( $post_id ) {
	$terms = get_the_category( $post_id );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * A post's date in the parish's dotted format.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function kameari_post_date( $post_id ) {
	return get_the_date( 'Y.m.d', $post_id );
}

/**
 * Thumbnail for a news card: the featured image, or a drawn illustration.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @param string $ratio   CSS aspect ratio for the fallback illustration.
 */
function kameari_card_thumb( $post_id, $size = 'kameari-card', $ratio = '4/3' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy', 'alt' => '' ) );
		return;
	}

	kameari_art( kameari_art_for_post( $post_id ), array( 'ratio' => $ratio ) );
}

/**
 * Resolve a link the editor may have left blank.
 *
 * @param string $url      Configured URL.
 * @param string $fallback Fallback URL.
 * @return string
 */
function kameari_url( $url, $fallback = '' ) {
	if ( $url ) {
		return $url;
	}
	return $fallback ? $fallback : home_url( '/' );
}

/**
 * Parse the "headline | explanation" lines used by the visitor guide.
 *
 * @param string $raw Raw setting value.
 * @return array<int, array{text: string, small: string}>
 */
function kameari_parse_steps( $raw ) {
	$steps = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}

		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$steps[] = array(
			'text'  => $parts[0],
			'small' => isset( $parts[1] ) ? $parts[1] : '',
		);
	}

	return $steps;
}
