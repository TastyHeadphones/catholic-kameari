<?php
/**
 * Block styles and block patterns.
 *
 * The patterns give editors the design's set pieces — the callout, the Mass
 * table, the three-up sacrament tiles, the numbered guide — without needing to
 * know any of the class names.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the theme's block styles.
 */
function kameari_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style( 'core/group', array(
		'name'  => 'kameari-callout',
		'label' => __( 'Kameari callout', 'kameari-church' ),
	) );

	register_block_style( 'core/paragraph', array(
		'name'  => 'kameari-label',
		'label' => __( 'Kameari micro label', 'kameari-church' ),
	) );

	register_block_style( 'core/list', array(
		'name'  => 'kameari-bullets',
		'label' => __( 'Kameari numbered rules', 'kameari-church' ),
	) );

	register_block_style( 'core/table', array(
		'name'  => 'kameari-schedule',
		'label' => __( 'Kameari schedule', 'kameari-church' ),
	) );
}
add_action( 'init', 'kameari_register_block_styles' );

/**
 * Register the pattern category and the patterns themselves.
 */
function kameari_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category( 'kameari', array(
		'label' => __( 'Kameari Church', 'kameari-church' ),
	) );

	foreach ( kameari_pattern_definitions() as $slug => $pattern ) {
		register_block_pattern( 'kameari-church/' . $slug, array(
			'title'      => $pattern['title'],
			'categories' => array( 'kameari' ),
			'content'    => $pattern['content'],
		) );
	}
}
add_action( 'init', 'kameari_register_patterns', 20 );

/**
 * Pattern definitions.
 *
 * @return array
 */
function kameari_pattern_definitions() {
	return array(
		'callout' => array(
			'title'   => __( 'Callout note', 'kameari-church' ),
			'content' => '<!-- wp:group {"className":"is-style-kameari-callout"} --><div class="wp-block-group is-style-kameari-callout">'
				. '<!-- wp:heading {"level":4} --><h4>' . esc_html__( '祝日・行事のときは時間が変更となります', 'kameari-church' ) . '</h4><!-- /wp:heading -->'
				. '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html__( '聖週間・降誕祭をはじめ、特別な典礼の際は時間と次第が変わります。月間予定をご確認のうえお越しください。', 'kameari-church' ) . '</p><!-- /wp:paragraph -->'
				. '</div><!-- /wp:group -->',
		),

		'mass-table' => array(
			'title'   => __( 'Mass schedule table', 'kameari-church' ),
			'content' => '<!-- wp:heading {"level":3} --><h3>' . esc_html__( '主日と平日のミサ', 'kameari-church' ) . '</h3><!-- /wp:heading -->'
				. '<!-- wp:table {"className":"is-style-kameari-schedule"} --><figure class="wp-block-table is-style-kameari-schedule"><table><thead><tr>'
				. '<th>' . esc_html__( '曜日', 'kameari-church' ) . '</th><th>' . esc_html__( '時間', 'kameari-church' ) . '</th><th>' . esc_html__( '備考', 'kameari-church' ) . '</th>'
				. '</tr></thead><tbody>'
				. '<tr><td>' . esc_html__( '日曜日 午前', 'kameari-church' ) . '</td><td>10:00</td><td>' . esc_html__( '主日のミサ（日曜学校・聖歌隊あり）', 'kameari-church' ) . '</td></tr>'
				. '<tr><td>' . esc_html__( '日曜日 朝', 'kameari-church' ) . '</td><td>07:00</td><td>' . esc_html__( '主日のミサ（早朝）', 'kameari-church' ) . '</td></tr>'
				. '<tr><td>' . esc_html__( '平日', 'kameari-church' ) . '</td><td>07:00</td><td>' . esc_html__( '月・水・金', 'kameari-church' ) . '</td></tr>'
				. '<tr><td>' . esc_html__( '平日', 'kameari-church' ) . '</td><td>10:00</td><td>' . esc_html__( '火・木・土', 'kameari-church' ) . '</td></tr>'
				. '</tbody></table></figure><!-- /wp:table -->',
		),

		'numbered-guide' => array(
			'title'   => __( 'Numbered guide list', 'kameari-church' ),
			'content' => '<!-- wp:list {"className":"is-style-kameari-bullets"} --><ul class="is-style-kameari-bullets">'
				. '<!-- wp:list-item --><li>' . esc_html__( '朝の祈り（ラウデス）— 平日ミサの15分前', 'kameari-church' ) . '</li><!-- /wp:list-item -->'
				. '<!-- wp:list-item --><li>' . esc_html__( '晩の祈り（ヴェスペレ）— 月〜金 18:30', 'kameari-church' ) . '</li><!-- /wp:list-item -->'
				. '<!-- wp:list-item --><li>' . esc_html__( '聖体礼拝 — 毎週木曜 19:00 – 20:00', 'kameari-church' ) . '</li><!-- /wp:list-item -->'
				. '<!-- wp:list-item --><li>' . esc_html__( '告解 — 主日ミサの30分前', 'kameari-church' ) . '</li><!-- /wp:list-item -->'
				. '</ul><!-- /wp:list -->',
		),

		'sacrament-tiles' => array(
			'title'   => __( 'Three sacrament tiles', 'kameari-church' ),
			'content' => '<!-- wp:columns {"align":"wide"} --><div class="wp-block-columns alignwide">'
				. kameari_pattern_tile( '01 / Matrimony', __( '教会で挙げる結婚式', 'kameari-church' ), __( '信徒の方、ご家族の方を対象に、教会での結婚式を執り行います。婚前準備講座を経て、聖堂にて秘跡を授けます。', 'kameari-church' ) )
				. kameari_pattern_tile( '02 / Funeral', __( 'カトリック葬儀', 'kameari-church' ), __( '信徒の葬儀は、教会聖堂にて司祭がミサを捧げます。通夜・葬儀の進め方、納骨までを丁寧にお手伝いいたします。', 'kameari-church' ) )
				. kameari_pattern_tile( '03 / Cemetery', __( '教会墓地', 'kameari-church' ), __( '教会管理の納骨堂と霊園のご案内。信徒の方、長くお祈りを共にしてこられた方のための場所です。', 'kameari-church' ) )
				. '</div><!-- /wp:columns -->',
		),

		'verse' => array(
			'title'   => __( 'Scripture quotation', 'kameari-church' ),
			'content' => '<!-- wp:quote --><blockquote class="wp-block-quote">'
				. '<!-- wp:paragraph --><p>' . esc_html__( '「労している者、重荷を負う者は、誰でもわたしのもとに来なさい。あなたがたを休ませてあげよう。」', 'kameari-church' ) . '</p><!-- /wp:paragraph -->'
				. '<cite>' . esc_html__( '— マタイによる福音書 11:28', 'kameari-church' ) . '</cite>'
				. '</blockquote><!-- /wp:quote -->',
		),
	);
}

/**
 * One column of the sacrament-tiles pattern.
 *
 * @param string $kicker Mono kicker.
 * @param string $title  Tile heading.
 * @param string $body   Tile copy.
 * @return string
 */
function kameari_pattern_tile( $kicker, $title, $body ) {
	return '<!-- wp:column --><div class="wp-block-column">'
		. '<!-- wp:paragraph {"className":"is-style-kameari-label"} --><p class="is-style-kameari-label">' . esc_html( $kicker ) . '</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading {"level":3} --><h3>' . esc_html( $title ) . '</h3><!-- /wp:heading -->'
		. '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html( $body ) . '</p><!-- /wp:paragraph -->'
		. '</div><!-- /wp:column -->';
}
