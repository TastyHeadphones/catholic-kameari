<?php
/**
 * Customizer settings.
 *
 * Everything the parish is likely to want to change without touching a template
 * lives here: the hero, the two scripture quotes, the address block, and the
 * footer. Defaults are the Kameari values from the original design.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declarative description of every Customizer section and control.
 *
 * @return array
 */
function kameari_customizer_schema() {
	return array(
		'kameari_identity' => array(
			'title'    => __( 'Church identity', 'kameari-church' ),
			'priority' => 20,
			'settings' => array(
				'kameari_brand_jp'      => array( 'label' => __( 'Church name', 'kameari-church' ), 'type' => 'text', 'default' => '', 'hint' => __( 'Leave blank to use the site title.', 'kameari-church' ) ),
				'kameari_brand_sub'     => array( 'label' => __( 'Latin-letter subtitle', 'kameari-church' ), 'type' => 'text', 'default' => 'Kameari Catholic Church · est. 1955' ),
				'kameari_accent'        => array(
					'label'   => __( 'Accent colour', 'kameari-church' ),
					'type'    => 'select',
					'default' => 'green',
					'choices' => array(
						'green'  => __( 'Sumi green', 'kameari-church' ),
						'navy'   => __( 'Ink navy', 'kameari-church' ),
						'walnut' => __( 'Walnut', 'kameari-church' ),
					),
				),
				'kameari_google_fonts'  => array(
					'label'   => __( 'Load fonts from Google Fonts', 'kameari-church' ),
					'type'    => 'checkbox',
					'default' => true,
					'hint'    => __( 'Turn off if you host the fonts yourself or need to avoid third-party requests.', 'kameari-church' ),
				),
			),
		),

		'kameari_hero' => array(
			'title'    => __( 'Front page — hero', 'kameari-church' ),
			'priority' => 21,
			'settings' => array(
				'kameari_hero_image'      => array( 'label' => __( 'Background photograph', 'kameari-church' ), 'type' => 'image', 'default' => '' ),
				'kameari_hero_kicker'     => array( 'label' => __( 'Kicker', 'kameari-church' ), 'type' => 'text', 'default' => 'Kameari Catholic Church · 1954 —' ),
				'kameari_hero_title'      => array( 'label' => __( 'Headline', 'kameari-church' ), 'type' => 'textarea', 'default' => "主の平和が、\nあなたと\nともに。", 'hint' => __( 'One line break per line of the headline.', 'kameari-church' ) ),
				'kameari_hero_lede'       => array( 'label' => __( 'Standfirst', 'kameari-church' ), 'type' => 'textarea', 'default' => '東京・亀有にあるアシジの聖フランシスコを保護者とする小さなカトリック共同体です。日々のミサ、祈りと聖書の集い、地域への奉仕を通して、あなたのお越しをお待ちしています。' ),
				'kameari_hero_btn1_label' => array( 'label' => __( 'Primary button label', 'kameari-church' ), 'type' => 'text', 'default' => 'ミサの時間' ),
				'kameari_hero_btn1_url'   => array( 'label' => __( 'Primary button link', 'kameari-church' ), 'type' => 'url', 'default' => '' ),
				'kameari_hero_btn2_label' => array( 'label' => __( 'Secondary button label', 'kameari-church' ), 'type' => 'text', 'default' => '初めての方へ' ),
				'kameari_hero_btn2_url'   => array( 'label' => __( 'Secondary button link', 'kameari-church' ), 'type' => 'url', 'default' => '' ),
				'kameari_hero_next_label' => array( 'label' => __( 'Next-service caption', 'kameari-church' ), 'type' => 'text', 'default' => 'Next service · 次の主日ミサ' ),
				'kameari_hero_next_date'  => array( 'label' => __( 'Next-service date', 'kameari-church' ), 'type' => 'text', 'default' => '日曜日' ),
				'kameari_hero_next_time'  => array( 'label' => __( 'Next-service time', 'kameari-church' ), 'type' => 'text', 'default' => '10:00' ),
				'kameari_hero_note'       => array( 'label' => __( 'Small note', 'kameari-church' ), 'type' => 'text', 'default' => '聖堂は毎日 9時〜17時 開放' ),
			),
		),

		'kameari_scripture' => array(
			'title'    => __( 'Front page — scripture', 'kameari-church' ),
			'priority' => 22,
			'settings' => array(
				'kameari_mass_label' => array( 'label' => __( 'Mass strip — kicker', 'kameari-church' ), 'type' => 'text', 'default' => 'Matthew 18:20' ),
				'kameari_mass_quote' => array( 'label' => __( 'Mass strip — quotation', 'kameari-church' ), 'type' => 'textarea', 'default' => "「二人または三人が\nわたしの名によって\n集まるところには、\nわたしもその中に\nいるのである。」" ),
				'kameari_mass_cite'  => array( 'label' => __( 'Mass strip — citation', 'kameari-church' ), 'type' => 'text', 'default' => '— マタイによる福音書 18:20' ),
				'kameari_verse_label'=> array( 'label' => __( 'Verse of the day — kicker', 'kameari-church' ), 'type' => 'text', 'default' => 'Verbum diei · 今日のみことば' ),
				'kameari_verse_text' => array( 'label' => __( 'Verse of the day', 'kameari-church' ), 'type' => 'textarea', 'default' => "「労している者、\n重荷を負う者は、\n誰でもわたしのもとに来なさい。\nあなたがたを休ませてあげよう。」" ),
				'kameari_verse_cite' => array( 'label' => __( 'Verse citation', 'kameari-church' ), 'type' => 'text', 'default' => '— マタイによる福音書 11:28' ),
			),
		),

		'kameari_contact' => array(
			'title'    => __( 'Contact & access', 'kameari-church' ),
			'priority' => 23,
			'settings' => array(
				'kameari_access_heading' => array( 'label' => __( 'Access heading', 'kameari-church' ), 'type' => 'textarea', 'default' => "JR亀有駅 北口\nより徒歩15分" ),
				'kameari_postal'         => array( 'label' => __( 'Postal code', 'kameari-church' ), 'type' => 'text', 'default' => '〒120-0003' ),
				'kameari_address'        => array( 'label' => __( 'Address', 'kameari-church' ), 'type' => 'text', 'default' => '東京都足立区東和 4丁目 3-20' ),
				'kameari_phone'          => array( 'label' => __( 'Telephone', 'kameari-church' ), 'type' => 'text', 'default' => '03-3606-1757' ),
				'kameari_hours'          => array( 'label' => __( 'Office hours', 'kameari-church' ), 'type' => 'text', 'default' => '受付 9:00 – 17:00（火曜定休）' ),
				'kameari_email'          => array( 'label' => __( 'Email address', 'kameari-church' ), 'type' => 'text', 'default' => '' ),
				'kameari_station'        => array( 'label' => __( 'Nearest station', 'kameari-church' ), 'type' => 'textarea', 'default' => "JR常磐線「亀有駅」北口\n徒歩15分 または東武バス「東和二丁目」下車徒歩3分" ),
				'kameari_parking'        => array( 'label' => __( 'Parking', 'kameari-church' ), 'type' => 'text', 'default' => '境内に15台分（日曜ミサ時は満車）' ),
				'kameari_map_station_label' => array( 'label' => __( 'Station label on the drawn map', 'kameari-church' ), 'type' => 'text', 'default' => 'KAMEARI', 'hint' => __( 'Latin letters, uppercase.', 'kameari-church' ) ),
				'kameari_map_embed'      => array(
					'label'   => __( 'Map embed URL', 'kameari-church' ),
					'type'    => 'url',
					'default' => '',
					'hint'    => __( 'Paste only the src URL of a map iframe. Leave blank to show the drawn map illustration instead.', 'kameari-church' ),
				),
			),
		),

		'kameari_footer' => array(
			'title'    => __( 'Footer', 'kameari-church' ),
			'priority' => 24,
			'settings' => array(
				'kameari_footer_tagline' => array( 'label' => __( 'Footer tagline', 'kameari-church' ), 'type' => 'textarea', 'default' => "東京カトリック大司教区　葛飾ブロック\nアシジの聖フランシスコ保護\n東京管区フランシスコ会司牧" ),
				'kameari_footer_motto'   => array( 'label' => __( 'Footer motto', 'kameari-church' ), 'type' => 'text', 'default' => 'Pax et Bonum · 平和と善' ),
			),
		),

		'kameari_sections' => array(
			'title'    => __( 'Front page — sections', 'kameari-church' ),
			'priority' => 25,
			'settings' => array(
				'kameari_show_mass'       => array( 'label' => __( 'Show Mass times', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_news'       => array( 'label' => __( 'Show news', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_liturgy'    => array( 'label' => __( 'Show liturgical calendar', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_about'      => array( 'label' => __( 'Show pastor’s welcome', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_activities' => array( 'label' => __( 'Show parish activities', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_verse'      => array( 'label' => __( 'Show verse of the day', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_visit'      => array( 'label' => __( 'Show visitor guide', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
				'kameari_show_access'     => array( 'label' => __( 'Show access strip', 'kameari-church' ), 'type' => 'checkbox', 'default' => true ),
			),
		),

		'kameari_about' => array(
			'title'    => __( 'Front page — pastor’s welcome', 'kameari-church' ),
			'priority' => 26,
			'settings' => array(
				'kameari_about_image'     => array( 'label' => __( 'Photograph', 'kameari-church' ), 'type' => 'image', 'default' => '' ),
				'kameari_about_kicker'    => array( 'label' => __( 'Kicker', 'kameari-church' ), 'type' => 'text', 'default' => 'About · 主任司祭より' ),
				'kameari_about_title'     => array( 'label' => __( 'Heading', 'kameari-church' ), 'type' => 'textarea', 'default' => "祈りと、\n出会いの場所として。" ),
				'kameari_about_body'      => array( 'label' => __( 'Body copy', 'kameari-church' ), 'type' => 'textarea', 'default' => '亀有教会は、アシジの聖フランシスコを保護者とする教区の小さな共同体です。信者の方も、まだ教会のことを知らない方も、悲しみのなか祈りを求めて来られた方も、ふと足を運んでくださった方も、すべて主の客として迎え入れたいと願っています。', 'hint' => __( 'Blank lines start a new paragraph.', 'kameari-church' ) ),
				'kameari_about_quote'     => array( 'label' => __( 'Pull quote', 'kameari-church' ), 'type' => 'textarea', 'default' => "主よ、わたしを\nあなたの平和の道具と\nしてください——" ),
				'kameari_about_signature' => array( 'label' => __( 'Signature', 'kameari-church' ), 'type' => 'text', 'default' => '主任司祭　田中 ヨハネ・ボスコ' ),
			),
		),

		'kameari_visit' => array(
			'title'    => __( 'Front page — visitor guide', 'kameari-church' ),
			'priority' => 27,
			'settings' => array(
				'kameari_visit_kicker' => array( 'label' => __( 'Kicker', 'kameari-church' ), 'type' => 'text', 'default' => 'Visit · 初めての方へ' ),
				'kameari_visit_title'  => array( 'label' => __( 'Heading', 'kameari-church' ), 'type' => 'textarea', 'default' => "はじめて\n教会を訪れる\n方へ。" ),
				'kameari_visit_body'   => array( 'label' => __( 'Body copy', 'kameari-church' ), 'type' => 'textarea', 'default' => '「教会へ行ってみたいけれど、作法がわからない」という声を、よくいただきます。信仰の有無にかかわらず、どなたでもお越しいただけます。ささやかな訪問の手引きをご用意しました。' ),
				'kameari_visit_url'    => array( 'label' => __( 'Button link', 'kameari-church' ), 'type' => 'url', 'default' => '' ),
				'kameari_visit_label'  => array( 'label' => __( 'Button label', 'kameari-church' ), 'type' => 'text', 'default' => '訪問の手引き' ),
				'kameari_visit_steps'  => array(
					'label'   => __( 'Five steps', 'kameari-church' ),
					'type'    => 'textarea',
					'default' => "どなたでもご参加いただけます | 信者でない方、初めての方、ふらりと立ち寄られる方を心より歓迎いたします。\n服装は自由です | 特別な服装は必要ありません。落ち着いた装いでお越しください。\nミサは1時間ほどです | 歌・祈り・聖書朗読・説教・聖体拝領の構成です。座って静かにお過ごしください。\n聖体拝領は信者のみです | 未受洗の方は、列に並んで腕を胸の前で組んでいただくと、司祭が祝福をお授けします。\nご質問はお気軽に | 聖堂入口にて受付の信徒がお待ちしております。お子様連れも安心してお越しください。",
					'hint'    => __( 'One step per line, written as: headline | explanation', 'kameari-church' ),
				),
			),
		),
	);
}

/**
 * Register everything in the schema.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function kameari_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	foreach ( kameari_customizer_schema() as $section_id => $section ) {
		$wp_customize->add_section( $section_id, array(
			'title'    => $section['title'],
			'priority' => $section['priority'],
		) );

		foreach ( $section['settings'] as $key => $field ) {
			$wp_customize->add_setting( $key, array(
				'default'           => $field['default'],
				'sanitize_callback' => kameari_sanitize_callback( $field['type'] ),
				'transport'         => 'refresh',
			) );

			$args = array(
				'label'       => $field['label'],
				'section'     => $section_id,
				'description' => isset( $field['hint'] ) ? $field['hint'] : '',
			);

			if ( 'image' === $field['type'] ) {
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, $args ) );
				continue;
			}

			$args['type'] = $field['type'];
			if ( 'select' === $field['type'] ) {
				$args['choices'] = $field['choices'];
			}

			$wp_customize->add_control( $key, $args );
		}
	}
}
add_action( 'customize_register', 'kameari_customize_register' );

/**
 * Map a field type to its sanitize callback.
 *
 * @param string $type Field type.
 * @return callable
 */
function kameari_sanitize_callback( $type ) {
	switch ( $type ) {
		case 'checkbox':
			return 'kameari_sanitize_checkbox';
		case 'url':
			return 'esc_url_raw';
		case 'image':
			return 'esc_url_raw';
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'select':
			return 'sanitize_key';
		default:
			return 'sanitize_text_field';
	}
}

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function kameari_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Read a theme mod, falling back to the schema default.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function kameari_mod( $key ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = array();
		foreach ( kameari_customizer_schema() as $section ) {
			foreach ( $section['settings'] as $setting_key => $field ) {
				$defaults[ $setting_key ] = $field['default'];
			}
		}
	}

	$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}
