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
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Serif+JP:wght@400;500;600;700&display=swap',
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
        str_starts_with($content, '<!-- wp:group {"align":"full","className":"kameari-section')
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
 * [kameari_posts] — modern card grid for category listings and the home page.
 *
 * Attributes:
 *   - category        slug, e.g. "news"
 *   - posts_per_page  int (1-24)
 *   - layout          "card" (default) or "featured"
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
        return '<p style="text-align:center;color:var(--kameari-text-soft);">'
            . esc_html__('現在表示できる記事はありません。', 'kameari')
            . '</p>';
    }

    ob_start();
    echo '<div class="kameari-post-list">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
        if (! $thumb) {
            $thumb = kameari_first_inline_image(get_the_ID());
        }

        echo '<article class="kameari-post-list__item">';
        if ($thumb) {
            echo '<a class="kameari-post-list__thumb" href="' . esc_url(get_permalink()) . '" style="background-image:url(' . esc_url($thumb) . ');" aria-hidden="true"></a>';
        }
        echo '<div class="kameari-post-list__body">';
        echo '<time datetime="' . esc_attr(get_the_date('c')) . '">' . esc_html(get_the_date()) . '</time>';
        echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        echo '<p>' . esc_html(wp_trim_words(get_the_excerpt(), 50, '…')) . '</p>';
        echo '</div>';
        echo '</article>';
    }
    echo '</div>';
    wp_reset_postdata();

    return (string) ob_get_clean();
});

/**
 * Prepend a clean cream-coloured hero (eyebrow category + serif title + date)
 * to single posts. Replaces Kadence's "thumbnail behind title" block which
 * stacks the title on top of the featured image and is hard to read.
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
        $hero .= '<p class="kameari-eyebrow">' . esc_html($category) . '</p>';
    }
    $hero .= '<h1>' . esc_html(get_the_title()) . '</h1>';
    $hero .= '<p class="kameari-post-hero__meta">' . esc_html(get_the_date()) . '</p>';
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
 * Floating "Mass Times" CTA visible across the site, mirroring the
 * stlouiswaco.com bottom-right pill.
 */
add_action('wp_footer', function (): void {
    if (is_admin() || is_404()) {
        return;
    }

    echo '<a class="kameari-floating-cta" href="' . esc_url(home_url('/schedule/mass/')) . '">'
        . '<span class="kameari-floating-cta__icon" aria-hidden="true"></span>'
        . esc_html__('ミサの時間 / Directions', 'kameari')
        . '</a>';
}, 5);

/**
 * Inject a richer themed footer block when the default Kadence footer is empty.
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
  <div class="kameari-inner">\
    <div>\
      <p class="kameari-footer__brand">東京大司教区<br>カトリック亀有教会</p>\
      <p>St. Francis of Assisi · Tokyo</p>\
      <p>〒120-0003<br>東京都足立区東和4-3-20<br>TEL 03-3606-1757</p>\
    </div>\
    <div>\
      <h4>礼拝</h4>\
      <ul>\
        <li><a href="/schedule/mass/">ミサのご案内</a></li>\
        <li><a href="/schedule/monthly/">月間予定</a></li>\
        <li><a href="/schedule/annual/">年間予定</a></li>\
        <li><a href="/about/visitors/">初めての方へ</a></li>\
      </ul>\
    </div>\
    <div>\
      <h4>共同体</h4>\
      <ul>\
        <li><a href="/about/">教会紹介</a></li>\
        <li><a href="/commit/">教会活動</a></li>\
        <li><a href="/news/">お知らせ</a></li>\
        <li><a href="/access/">アクセス</a></li>\
      </ul>\
    </div>\
  </div>\
  <p class="kameari-footer__bottom">© ' + new Date().getFullYear() + ' Catholic Kameari Church · St. Francis of Assisi, Tokyo</p>\
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
