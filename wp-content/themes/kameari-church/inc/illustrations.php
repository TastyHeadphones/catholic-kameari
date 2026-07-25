<?php
/**
 * Drawn illustrations.
 *
 * Twelve inline SVG artworks in the parish drawing style: 1.6px hairlines, ink
 * on bone, one small gold accent. They stand in wherever a photograph is
 * missing, so a new parish site never shows a broken image.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ink colour used by the illustrations.
 */
const KAMEARI_ART_INK  = 'var(--ink)';
const KAMEARI_ART_GOLD = '#806128';
const KAMEARI_ART_SW   = '1.6';

/**
 * Every available illustration, keyed by slug.
 *
 * @return array<string, string> Slug => human label.
 */
function kameari_art_names() {
	return array(
		'rosary'   => __( 'Rosary', 'kameari-church' ),
		'candles'  => __( 'Candles', 'kameari-church' ),
		'scripture'=> __( 'Scripture', 'kameari-church' ),
		'mountain' => __( 'Retreat', 'kameari-church' ),
		'calendar' => __( 'Calendar', 'kameari-church' ),
		'rays'     => __( 'Resurrection', 'kameari-church' ),
		'chalice'  => __( 'Eucharist', 'kameari-church' ),
		'glass'    => __( 'Stained glass', 'kameari-church' ),
		'francis'  => __( 'Saint Francis', 'kameari-church' ),
		'map'      => __( 'Map', 'kameari-church' ),
		'arches'   => __( 'Arches', 'kameari-church' ),
		'portrait' => __( 'Portrait', 'kameari-church' ),
	);
}

/**
 * Print an illustration.
 *
 * @param string $name  Illustration slug, see kameari_art_names().
 * @param array  $args  ratio, bg, tone, label.
 */
function kameari_art( $name, $args = array() ) {
	echo kameari_get_art( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is generated here.
}

/**
 * Pick a deterministic illustration for a post, so a given article always keeps
 * the same drawing.
 *
 * @param int $post_id Post ID.
 * @return string Illustration slug.
 */
function kameari_art_for_post( $post_id ) {
	$rotation = array( 'rosary', 'candles', 'mountain', 'calendar', 'rays', 'scripture', 'glass', 'arches' );
	return $rotation[ absint( $post_id ) % count( $rotation ) ];
}

/**
 * Build an illustration's markup.
 *
 * @param string $name Illustration slug.
 * @param array  $args ratio, bg, tone, label.
 * @return string
 */
function kameari_get_art( $name, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'ratio' => '4/3',
		'bg'    => 'var(--paper-2)',
		'tone'  => 'ink',
		'label' => null,
	) );

	$builder = 'kameari_art_' . preg_replace( '/[^a-z]/', '', (string) $name );
	if ( ! function_exists( $builder ) ) {
		$builder = 'kameari_art_arches';
	}

	list( $inner, $default_label ) = call_user_func( $builder, $args );

	$label = null === $args['label'] ? $default_label : $args['label'];

	$html  = '<div class="art-frame" style="aspect-ratio:' . esc_attr( $args['ratio'] ) . ';background:' . esc_attr( $args['bg'] ) . '">';
	$html .= '<svg viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice" role="img" aria-hidden="true" focusable="false">' . $inner . '</svg>';
	if ( $label ) {
		$html .= '<span class="art-label">' . esc_html( $label ) . '</span>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Rosary — a circle of beads with a small cross.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_rosary( $args ) {
	$svg = '';
	for ( $i = 0; $i < 28; $i++ ) {
		$a   = ( $i / 28 ) * M_PI * 2 - M_PI / 2;
		$cx  = 200 + cos( $a ) * 80;
		$cy  = 150 + sin( $a ) * 80;
		$big = ( 0 === $i % 5 );
		$svg .= sprintf(
			'<circle cx="%s" cy="%s" r="%s" fill="%s" />',
			round( $cx, 2 ),
			round( $cy, 2 ),
			$big ? '4' : '2.4',
			$big ? KAMEARI_ART_GOLD : KAMEARI_ART_INK
		);
	}
	$svg .= '<line x1="200" y1="232" x2="200" y2="256" stroke="' . KAMEARI_ART_INK . '" stroke-width=".8" />';
	for ( $i = 0; $i < 4; $i++ ) {
		$svg .= '<circle cx="200" cy="' . ( 240 + $i * 4 ) . '" r="1.6" fill="' . KAMEARI_ART_INK . '" />';
	}
	$svg .= '<rect x="197" y="260" width="6" height="22" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<rect x="191" y="266" width="18" height="6" fill="' . KAMEARI_ART_INK . '" />';

	return array( $svg, 'ILL. 01 — ROSARIO' );
}

/**
 * Three candles with soft flame halos.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_candles( $args ) {
	$svg     = '';
	$centres = array( 140, 200, 260 );
	$heights = array( 88, 116, 88 );

	foreach ( $centres as $i => $cx ) {
		$h   = $heights[ $i ];
		$top = 230 - $h;
		$svg .= '<g>';
		$svg .= '<circle cx="' . $cx . '" cy="' . ( $top - 8 ) . '" r="20" fill="' . KAMEARI_ART_GOLD . '" opacity=".14" />';
		$svg .= '<circle cx="' . $cx . '" cy="' . ( $top - 8 ) . '" r="12" fill="' . KAMEARI_ART_GOLD . '" opacity=".22" />';
		$svg .= '<ellipse cx="' . $cx . '" cy="' . ( $top - 8 ) . '" rx="3.5" ry="7" fill="' . KAMEARI_ART_GOLD . '" />';
		$svg .= '<line x1="' . $cx . '" y1="' . ( $top - 1 ) . '" x2="' . $cx . '" y2="' . ( $top + 4 ) . '" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" />';
		$svg .= '<rect x="' . ( $cx - 7 ) . '" y="' . ( $top + 4 ) . '" width="14" height="' . $h . '" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
		$svg .= '<path d="M' . ( $cx - 4 ) . ' ' . ( $top + 10 ) . ' q2 4 4 0 q2 4 4 0" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width=".8" />';
		$svg .= '</g>';
	}
	$svg .= '<line x1="60" y1="234" x2="340" y2="234" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" />';

	return array( $svg, 'ILL. 02 — CANDELABRA' );
}

/**
 * Open Bible with a ribbon.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_scripture( $args ) {
	$svg  = '<path d="M 60 90 L 200 100 L 340 90 L 340 220 L 200 230 L 60 220 Z" fill="#fff" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
	$svg .= '<line x1="200" y1="100" x2="200" y2="230" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';

	$rows = array( 120, 132, 144, 156, 168, 180, 192, 204 );
	foreach ( $rows as $i => $y ) {
		$x2   = ( 2 === $i % 3 ) ? 160 : 180;
		$svg .= '<line x1="80" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '" stroke="' . KAMEARI_ART_INK . '" stroke-width=".7" opacity=".4" />';
	}
	foreach ( $rows as $i => $y ) {
		$x2   = ( 3 === $i % 4 ) ? 300 : 320;
		$svg .= '<line x1="220" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '" stroke="' . KAMEARI_ART_INK . '" stroke-width=".7" opacity=".4" />';
	}
	$svg .= '<path d="M 230 100 L 230 250 L 236 244 L 242 250 L 242 100" fill="' . KAMEARI_ART_GOLD . '" />';

	return array( $svg, 'ILL. 03 — VERBUM' );
}

/**
 * Mountain retreat.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_mountain( $args ) {
	$svg  = '<rect x="0" y="0" width="400" height="200" fill="#fff" opacity=".55" />';
	$svg .= '<circle cx="280" cy="100" r="22" fill="' . KAMEARI_ART_GOLD . '" opacity=".55" />';
	$svg .= '<circle cx="280" cy="100" r="14" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<path d="M 0 200 L 80 150 L 160 175 L 240 130 L 320 165 L 400 140 L 400 230 L 0 230 Z" fill="' . KAMEARI_ART_INK . '" opacity=".2" />';
	$svg .= '<path d="M 0 230 L 60 180 L 130 215 L 200 170 L 280 210 L 360 175 L 400 200 L 400 260 L 0 260 Z" fill="' . KAMEARI_ART_INK . '" opacity=".45" />';
	$svg .= '<path d="M 0 260 L 90 215 L 180 250 L 270 210 L 360 245 L 400 230 L 400 300 L 0 300 Z" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<rect x="199" y="158" width="2" height="14" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<rect x="195" y="162" width="10" height="2" fill="' . KAMEARI_ART_GOLD . '" />';

	return array( $svg, 'ILL. 04 — RECESSUS' );
}

/**
 * Calendar page with one circled date.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_calendar( $args ) {
	$svg  = '<rect x="80" y="60" width="240" height="200" fill="#fff" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
	$svg .= '<line x1="80" y1="100" x2="320" y2="100" stroke="' . KAMEARI_ART_INK . '" stroke-width=".8" />';
	$svg .= '<text x="100" y="88" font-family="JetBrains Mono, monospace" font-size="13" letter-spacing="3" fill="' . KAMEARI_ART_INK . '">' . esc_html( strtoupper( wp_date( 'M · Y' ) ) ) . '</text>';

	for ( $row = 0; $row < 5; $row++ ) {
		for ( $col = 0; $col < 7; $col++ ) {
			$i   = $row * 7 + $col;
			$x   = 96 + $col * 32;
			$y   = 124 + $row * 28;
			$day = $i - 4;

			if ( 10 === $day ) {
				$svg .= '<circle cx="' . ( $x + 8 ) . '" cy="' . ( $y - 4 ) . '" r="11" fill="' . KAMEARI_ART_GOLD . '" />';
			}
			if ( $day >= 1 && $day <= 31 ) {
				$fill = ( 10 === $day ) ? '#fff' : KAMEARI_ART_INK;
				$svg .= '<text x="' . ( $x + 8 ) . '" y="' . $y . '" text-anchor="middle" font-family="Inter, sans-serif" font-size="11" fill="' . $fill . '">' . $day . '</text>';
			}
		}
	}

	return array( $svg, 'ILL. 05 — KALENDARIUM' );
}

/**
 * Rising rays — Easter and Pentecost.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_rays( $args ) {
	$svg = '<line x1="0" y1="220" x2="400" y2="220" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';

	for ( $i = 0; $i < 13; $i++ ) {
		$a    = -M_PI / 2 + ( $i - 6 ) * ( M_PI / 12 );
		$x2   = 200 + cos( $a ) * 240;
		$y2   = 220 + sin( $a ) * 240;
		$svg .= '<line x1="200" y1="220" x2="' . round( $x2, 2 ) . '" y2="' . round( $y2, 2 ) . '" stroke="' . KAMEARI_ART_GOLD . '" stroke-width="1" opacity=".5" />';
	}

	$svg .= '<path d="M 130 220 A 70 70 0 0 1 270 220 Z" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<rect x="199" y="170" width="2" height="22" fill="#fff" />';
	$svg .= '<rect x="194" y="175" width="12" height="2" fill="#fff" />';

	return array( $svg, 'ILL. 06 — RESURRECTIO' );
}

/**
 * Chalice and host.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_chalice( $args ) {
	$svg  = '<circle cx="200" cy="80" r="32" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
	$svg .= '<text x="200" y="86" text-anchor="middle" font-family="Shippori Mincho B1, serif" font-size="14" letter-spacing="2" fill="' . KAMEARI_ART_INK . '">IHS</text>';

	for ( $i = 0; $i < 16; $i++ ) {
		$a    = ( $i / 16 ) * M_PI * 2;
		$x1   = 200 + cos( $a ) * 38;
		$y1   = 80 + sin( $a ) * 38;
		$x2   = 200 + cos( $a ) * 48;
		$y2   = 80 + sin( $a ) * 48;
		$svg .= '<line x1="' . round( $x1, 2 ) . '" y1="' . round( $y1, 2 ) . '" x2="' . round( $x2, 2 ) . '" y2="' . round( $y2, 2 ) . '" stroke="' . KAMEARI_ART_GOLD . '" stroke-width="1.2" />';
	}

	$svg .= '<path d="M 165 140 Q 165 180 200 190 Q 235 180 235 140 Z" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
	$svg .= '<rect x="190" y="190" width="20" height="10" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<rect x="196" y="200" width="8" height="28" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
	$svg .= '<path d="M 170 246 L 230 246 L 220 232 L 180 232 Z" fill="' . KAMEARI_ART_GOLD . '" />';

	return array( $svg, 'ILL. 07 — EUCHARISTIA' );
}

/**
 * Abstract stained glass.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_glass( $args ) {
	$svg    = '';
	$panels = array(
		array( '#806128', '.35' ),
		array( '#1f352b', '.28' ),
		array( '#8b1a1a', '.25' ),
		array( '#806128', '.2' ),
	);

	foreach ( $panels as $i => $panel ) {
		$svg .= '<rect x="' . ( 70 + $i * 70 ) . '" y="50" width="60" height="200" fill="' . $panel[0] . '" opacity="' . $panel[1] . '" />';
	}
	for ( $i = 0; $i < 4; $i++ ) {
		$svg .= '<path d="M ' . ( 70 + $i * 70 ) . ' 80 Q ' . ( 100 + $i * 70 ) . ' 30 ' . ( 130 + $i * 70 ) . ' 80" fill="#fff" />';
	}
	foreach ( array( 70, 130, 200, 270, 330 ) as $x ) {
		$svg .= '<line x1="' . $x . '" y1="30" x2="' . $x . '" y2="250" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.2" />';
	}
	foreach ( array( 110, 170, 220 ) as $y ) {
		$svg .= '<line x1="70" y1="' . $y . '" x2="330" y2="' . $y . '" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" opacity=".6" />';
	}
	$svg .= '<line x1="50" y1="250" x2="350" y2="250" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';

	return array( $svg, 'ILL. 08 — LUX' );
}

/**
 * Saint Francis with birds.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_francis( $args ) {
	$svg  = '<path d="M 200 60 a 22 22 0 1 1 0 44 a 22 22 0 1 1 0 -44 Z" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<path d="M 145 150 Q 200 160 255 150 L 250 280 L 150 280 Z" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<path d="M 165 95 Q 200 75 235 95 L 235 110 Q 200 100 165 110 Z" fill="' . KAMEARI_ART_INK . '" />';
	foreach ( array( 190, 200, 210 ) as $cy ) {
		$svg .= '<circle cx="200" cy="' . $cy . '" r="3" fill="' . KAMEARI_ART_GOLD . '" />';
	}
	$svg .= '<circle cx="200" cy="78" r="32" fill="none" stroke="' . KAMEARI_ART_GOLD . '" stroke-width="1.4" />';
	$svg .= '<path d="M 90 80 q 8 -6 16 0 q 8 -6 16 0" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.2" />';
	$svg .= '<path d="M 290 60 q 8 -6 16 0 q 8 -6 16 0" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.2" />';
	$svg .= '<path d="M 320 130 q 6 -5 12 0 q 6 -5 12 0" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.2" />';
	$svg .= '<path d="M 70 160 q 6 -5 12 0 q 6 -5 12 0" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.2" />';

	return array( $svg, 'ILL. 09 — FRANCISCUS' );
}

/**
 * Stylised neighbourhood map.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_map( $args ) {
	$station = kameari_mod( 'kameari_map_station_label' );
	$station = $station ? $station : 'KAMEARI';

	$svg  = '<line x1="0" y1="180" x2="400" y2="180" stroke="' . KAMEARI_ART_INK . '" stroke-width="3" />';
	$svg .= '<line x1="0" y1="60"  x2="400" y2="60"  stroke="' . KAMEARI_ART_INK . '" stroke-width="1" opacity=".5" />';
	$svg .= '<line x1="0" y1="240" x2="400" y2="240" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" opacity=".5" />';
	$svg .= '<line x1="120" y1="0" x2="120" y2="300" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" opacity=".5" />';
	$svg .= '<line x1="220" y1="0" x2="220" y2="300" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.6" />';
	$svg .= '<line x1="320" y1="0" x2="320" y2="300" stroke="' . KAMEARI_ART_INK . '" stroke-width="1" opacity=".5" />';
	$svg .= '<line x1="0" y1="180" x2="400" y2="180" stroke="' . KAMEARI_ART_GOLD . '" stroke-width="1" stroke-dasharray="6 4" />';
	$svg .= '<circle cx="220" cy="180" r="6" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<circle cx="220" cy="180" r="2.4" fill="#fff" />';
	$svg .= '<text x="232" y="200" font-family="JetBrains Mono, monospace" font-size="9" letter-spacing="2" fill="' . KAMEARI_ART_INK . '">' . esc_html( $station ) . '</text>';
	$svg .= '<g transform="translate(160 100)">';
	$svg .= '<rect x="-12" y="-12" width="24" height="24" fill="' . KAMEARI_ART_INK . '" />';
	$svg .= '<rect x="-1" y="-22" width="2" height="10" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<rect x="-5" y="-19" width="10" height="2" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<text x="0" y="36" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="9" letter-spacing="2" fill="' . KAMEARI_ART_INK . '">CHURCH</text>';
	$svg .= '</g>';
	$svg .= '<path d="M 220 180 L 220 100 L 160 100" fill="none" stroke="' . KAMEARI_ART_GOLD . '" stroke-width="2" stroke-dasharray="4 3" />';

	return array( $svg, 'ILL. 10 — VIA' );
}

/**
 * Interior arches.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_arches( $args ) {
	$svg = '';
	foreach ( array( 60, 160, 260 ) as $x ) {
		$svg .= '<g>';
		$svg .= '<path d="M ' . ( $x + 40 ) . ' 90 L ' . ( $x + 40 ) . ' 280 L ' . ( $x + 120 ) . ' 280 L ' . ( $x + 80 ) . ' 90 Z" fill="' . KAMEARI_ART_GOLD . '" opacity=".18" />';
		$svg .= '<path d="M ' . $x . ' 280 L ' . $x . ' 130 Q ' . ( $x + 40 ) . ' 80 ' . ( $x + 80 ) . ' 130 L ' . ( $x + 80 ) . ' 280" fill="none" stroke="' . KAMEARI_ART_INK . '" stroke-width="' . KAMEARI_ART_SW . '" />';
		$svg .= '<rect x="' . ( $x + 39 ) . '" y="170" width="2" height="14" fill="' . KAMEARI_ART_INK . '" />';
		$svg .= '<rect x="' . ( $x + 35 ) . '" y="174" width="10" height="2" fill="' . KAMEARI_ART_INK . '" />';
		$svg .= '</g>';
	}
	$svg .= '<line x1="0" y1="280" x2="400" y2="280" stroke="' . KAMEARI_ART_INK . '" stroke-width="1.6" />';

	return array( $svg, 'ILL. 11 — TEMPLUM' );
}

/**
 * Minimal clergy portrait.
 *
 * @param array $args Illustration args.
 * @return array
 */
function kameari_art_portrait( $args ) {
	$dark = ( 'ink' === $args['tone'] );

	$figure      = $dark ? 'rgba(255,255,255,.92)' : KAMEARI_ART_INK;
	$halo_stroke = $dark ? 'rgba(255,255,255,.25)' : 'rgba(0,0,0,.15)';
	$collar      = $dark ? KAMEARI_ART_INK : 'var(--paper-2)';

	$svg  = '<circle cx="200" cy="170" r="76" fill="none" stroke="' . $halo_stroke . '" stroke-width="1.2" />';
	$svg .= '<circle cx="200" cy="180" r="58" fill="' . $figure . '" />';
	$svg .= '<path d="M 80 500 L 80 360 Q 80 280 200 280 Q 320 280 320 360 L 320 500 Z" fill="' . $figure . '" />';
	$svg .= '<rect x="186" y="280" width="28" height="6" fill="' . $collar . '" />';
	$svg .= '<rect x="198" y="320" width="4" height="22" fill="' . KAMEARI_ART_GOLD . '" />';
	$svg .= '<rect x="192" y="326" width="16" height="4" fill="' . KAMEARI_ART_GOLD . '" />';

	return array( $svg, 'PORTRAIT' );
}
