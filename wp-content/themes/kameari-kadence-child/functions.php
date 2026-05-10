<?php
/**
 * Catholic Kameari Kadence child theme setup.
 *
 * @package Kameari
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'kameari-google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&family=Noto+Sans+JP:wght@400;500;700&family=Shippori+Mincho+B1:wght@400;500;600&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'kameari-kadence-child',
        get_stylesheet_uri(),
        ['kadence-global', 'kameari-google-fonts'],
        wp_get_theme()->get('Version')
    );
}, 20);

/**
 * Mark pages whose content already opens with one of our full-bleed patterns so
 * we can hide Kadence's auto-rendered page-title hero (otherwise we render two
 * heroes stacked on top of each other).
 */
add_filter('body_class', function (array $classes): array {
    if (! is_singular(['page', 'post'])) {
        return $classes;
    }
    $post = get_post();
    if (! $post instanceof WP_Post) {
        return $classes;
    }

    $content = ltrim((string) $post->post_content);
    if (
        str_starts_with($content, '<!-- wp:group {"align":"full","className":"kameari-hero"') ||
        str_starts_with($content, '<!-- wp:group {"align":"full","className":"kameari-section') ||
        str_starts_with($content, '<!-- wp:group {"align":"full","className":"kameari-split-wrap"') ||
        str_starts_with($content, '<!-- wp:group {"align":"full","className":"kameari-access-wrap"')
    ) {
        $classes[] = 'kameari-has-custom-hero';
    }

    return $classes;
});

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('post-thumbnails');
    add_editor_style('editor-style.css');
});

add_action('init', function (): void {
    register_block_pattern_category('kameari', [
        'label' => __('Catholic Kameari', 'kameari'),
    ]);
});

require_once get_stylesheet_directory() . '/inc/patterns.php';

/**
 * Topbar announcement strip — next mass + language switcher. Echoed at
 * wp_body_open so it sits above the Kadence header.
 */
add_action('wp_body_open', function (): void {
    if (is_admin()) {
        return;
    }

    $next = kameari_next_mass_label();

    echo '<div class="kameari-topbar"><div class="kameari-topbar__inner">';
    echo '<div class="kameari-topbar__mass"><span class="dot"></span><span>' . esc_html($next) . '</span></div>';
    echo '<div class="kameari-topbar__right">';
    echo '<a href="' . esc_url(home_url('/')) . '">日本語</a>';
    echo '<span style="opacity:.5;">·</span>';
    echo '<a href="' . esc_url(home_url('/en/')) . '">English</a>';
    echo '<span style="opacity:.5;">·</span>';
    echo '<a href="' . esc_url(home_url('/ko/')) . '">한국어</a>';
    echo '</div></div></div>';
}, 5);

/**
 * Compute the next Sunday principal mass label ("次の主日ミサ：M月D日（日）9:30").
 * Mass schedule is from the parish's published times.
 */
function kameari_next_mass_label(): string
{
    $tz   = new DateTimeZone('Asia/Tokyo');
    $now  = new DateTimeImmutable('now', $tz);
    $sun  = $now;
    while ((int) $sun->format('w') !== 0) {
        $sun = $sun->modify('+1 day');
    }
    // If it's Sunday before 9:30 use today; if after, jump to next Sunday.
    if ((int) $now->format('w') === 0 && (int) $now->format('Hi') >= 930) {
        $sun = $sun->modify('+7 days');
    }

    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    $w = $weekdays[(int) $sun->format('w')];

    return sprintf(
        '次の主日ミサ：%d月%d日（%s）9:30',
        (int) $sun->format('n'),
        (int) $sun->format('j'),
        $w
    );
}

/**
 * [kameari_posts] — modern card grid for category listings and the home page.
 *
 * Attributes:
 *   - category        slug, e.g. "news"
 *   - posts_per_page  int (1-24)
 *   - layout          "card" (default) or "news-grid"
 */
add_shortcode('kameari_posts', function (array $atts = []): string {
    $atts = shortcode_atts([
        'category'       => '',
        'posts_per_page' => 12,
        'layout'         => 'card',
    ], $atts, 'kameari_posts');

    $args = [
        'posts_per_page'      => max(1, min(24, absint($atts['posts_per_page']))),
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
    ];

    if ('' !== $atts['category']) {
        $args['category_name'] = sanitize_title((string) $atts['category']);
    }

    $query = new WP_Query($args);

    if (! $query->have_posts()) {
        return '<p style="text-align:center;color:var(--ink-3);font-family:var(--mono);font-size:12px;letter-spacing:.2em;">'
            . esc_html__('現在表示できる記事はありません。', 'kameari')
            . '</p>';
    }

    $is_news_grid = 'news-grid' === $atts['layout'];

    ob_start();
    echo $is_news_grid ? '<div class="kameari-news-grid">' : '<div class="kameari-post-list">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
        if (! $thumb) {
            $thumb = kameari_first_inline_image(get_the_ID());
        }
        $cats     = get_the_category();
        $cat_name = $cats ? (string) $cats[0]->name : '';
        $excerpt  = wp_trim_words(get_the_excerpt(), 50, '…');

        if ($is_news_grid) {
            echo '<article class="kameari-news-card">';
            if ($thumb) {
                echo '<a class="kameari-news-card__thumb" href="' . esc_url(get_permalink()) . '" style="background-image:url(' . esc_url($thumb) . ');" aria-hidden="true"></a>';
            } else {
                echo '<span class="kameari-news-card__thumb kameari-news-card__thumb--empty" aria-hidden="true"></span>';
            }
            echo '<div class="kameari-news-card__body">';
            echo '<div class="kameari-news-card__meta">';
            if ('' !== $cat_name) {
                echo '<span class="kameari-news-card__tag">' . esc_html($cat_name) . '</span>';
            }
            echo '<span>' . esc_html(get_the_date('Y.m.d')) . '</span>';
            echo '</div>';
            echo '<h4><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h4>';
            echo '<p>' . esc_html($excerpt) . '</p>';
            echo '<a class="kameari-news-card__more" href="' . esc_url(get_permalink()) . '">続きを読む <span class="arr">→</span></a>';
            echo '</div>';
            echo '</article>';
        } else {
            echo '<article class="kameari-post-list__item">';
            if ($thumb) {
                echo '<a class="kameari-post-list__thumb" href="' . esc_url(get_permalink()) . '" style="background-image:url(' . esc_url($thumb) . ');" aria-hidden="true"></a>';
            }
            echo '<div class="kameari-post-list__body">';
            echo '<time datetime="' . esc_attr(get_the_date('c')) . '">' . esc_html(get_the_date()) . '</time>';
            echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
            echo '<p>' . esc_html($excerpt) . '</p>';
            echo '</div>';
            echo '</article>';
        }
    }
    echo '</div>';
    wp_reset_postdata();

    return (string) ob_get_clean();
});

/**
 * Prepend a clean paper-coloured hero (eyebrow category + serif title + date)
 * to single posts.
 */
add_filter('the_content', function (string $content): string {
    if (! is_singular('post') || ! in_the_loop() || ! is_main_query()) {
        return $content;
    }

    $category = '';
    $cats = get_the_category();
    if ($cats) {
        $category = (string) $cats[0]->name;
    }

    $hero  = '<div class="kameari-post-hero alignfull"><div class="kameari-post-hero__inner">';
    if ('' !== $category) {
        $hero .= '<p class="kameari-eyebrow"><span class="kanji">' . esc_html($category) . '</span><span class="rule"></span><span class="en">News</span></p>';
    }
    $hero .= '<h1>' . esc_html(get_the_title()) . '</h1>';
    $hero .= '<p class="kameari-post-hero__meta">' . esc_html(get_the_date('Y.m.d')) . '</p>';
    $hero .= '</div></div>';

    return $hero . $content;
}, 5);

/**
 * Best-effort featured image fallback by extracting the first <img> from the
 * post content. Returns '' if none found.
 */
function kameari_first_inline_image(int $post_id): string
{
    $post = get_post($post_id);
    if (! $post instanceof WP_Post) {
        return '';
    }

    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $post->post_content, $matches)) {
        return (string) $matches[1];
    }

    return '';
}

/**
 * Replace the empty Kadence footer rows with our themed footer.
 */
add_action('wp_footer', function (): void {
    if (is_admin()) {
        return;
    }
    ?>
<script>
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    if (document.querySelector('.kameari-footer')) return;
    var footer = document.querySelector('.site-footer');
    if (!footer) return;
    var html = '\
<div class="kameari-footer">\
  <div class="kameari-footer__inner">\
    <div class="kameari-footer__grid">\
      <div>\
        <div class="kameari-footer__brand">\
          <div class="kameari-footer__mark"></div>\
          <div class="kameari-footer__brand-text">\
            <div class="kameari-footer__brand-jp">カトリック亀有教会</div>\
            <div class="kameari-footer__brand-en">St. Francis of Assisi · Tokyo</div>\
          </div>\
        </div>\
        <p class="kameari-footer__tag">東京大司教区　葛飾ブロック<br>アシジの聖フランシスコ保護<br>コンベンツアル聖フランシスコ修道会司牧</p>\
      </div>\
      <div class="kameari-footer__col">\
        <h5>Parish</h5>\
        <ul>\
          <li><a href="/about/">ご紹介</a></li>\
          <li><a href="/about/history/">沿革</a></li>\
          <li><a href="/about/ever/">歴代司祭・修道士</a></li>\
          <li><a href="/commit/">教会活動</a></li>\
        </ul>\
      </div>\
      <div class="kameari-footer__col">\
        <h5>Worship</h5>\
        <ul>\
          <li><a href="/schedule/mass/">ミサのご案内</a></li>\
          <li><a href="/schedule/">月間予定</a></li>\
          <li><a href="/memorial/guidance_wedding/">結婚式</a></li>\
          <li><a href="/memorial/memorial/">葬儀・共同墓地</a></li>\
        </ul>\
      </div>\
      <div class="kameari-footer__col">\
        <h5>Contact</h5>\
        <ul>\
          <li><a href="/access/">アクセス・地図</a></li>\
          <li><a href="tel:0336061757">03-3606-1757</a></li>\
          <li><a href="/about/visitors/">初めての方へ</a></li>\
          <li>〒120-0003<br>東京都足立区東和4-3-20</li>\
        </ul>\
      </div>\
    </div>\
    <div class="kameari-footer__bottom">\
      <span>© ' + new Date().getFullYear() + ' Catholic Kameari Church · St. Francis of Assisi</span>\
      <span>Pax et Bonum · 平和と善</span>\
    </div>\
  </div>\
</div>';
    footer.insertAdjacentHTML('beforeend', html);
  });
})();
</script>
    <?php
}, 100);

/**
 * Schema.org Church markup (only on the home page).
 */
add_action('wp_head', function (): void {
    if (! is_front_page()) {
        return;
    }

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Church',
        'name'          => '東京大司教区カトリック亀有教会',
        'alternateName' => 'Catholic Kameari Church, St. Francis of Assisi',
        'url'           => home_url('/'),
        'telephone'     => '+81-3-3606-1757',
        'address'       => [
            '@type'           => 'PostalAddress',
            'postalCode'      => '120-0003',
            'addressRegion'   => '東京都',
            'addressLocality' => '足立区',
            'streetAddress'   => '東和4-3-20',
            'addressCountry'  => 'JP',
        ],
        'sameAs'        => [
            'https://www.facebook.com/%E3%82%AB%E3%83%88%E3%83%AA%E3%83%83%E3%82%AF%E4%BA%80%E6%9C%89%E6%95%99%E4%BC%9A-136875783162599/',
            'https://twitter.com/catholicKameari',
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 20);
