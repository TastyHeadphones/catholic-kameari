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
        'kameari-kadence-child',
        get_stylesheet_uri(),
        ['kadence-global'],
        wp_get_theme()->get('Version')
    );
});

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('editor-style.css');
});

add_action('init', function (): void {
    register_block_pattern_category('kameari', [
        'label' => __('Catholic Kameari', 'kameari'),
    ]);
});

require_once get_stylesheet_directory() . '/inc/patterns.php';

add_shortcode('kameari_posts', function (array $atts = []): string {
    $atts = shortcode_atts([
        'category' => '',
        'posts_per_page' => 12,
    ], $atts, 'kameari_posts');

    $query = new WP_Query([
        'category_name' => sanitize_title((string) $atts['category']),
        'posts_per_page' => max(1, min(24, absint($atts['posts_per_page']))),
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
    ]);

    if (! $query->have_posts()) {
        return '<p>' . esc_html__('現在表示できる記事はありません。', 'kameari') . '</p>';
    }

    ob_start();
    echo '<div class="kameari-post-list">';
    while ($query->have_posts()) {
        $query->the_post();
        echo '<article class="kameari-post-list__item">';
        echo '<time datetime="' . esc_attr(get_the_date('c')) . '">' . esc_html(get_the_date()) . '</time>';
        echo '<h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2>';
        echo '<p>' . esc_html(wp_trim_words(get_the_excerpt(), 70, '...')) . '</p>';
        echo '</article>';
    }
    echo '</div>';
    wp_reset_postdata();

    return (string) ob_get_clean();
});

add_action('wp_head', function (): void {
    if (! is_front_page()) {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Church',
        'name' => '東京大司教区カトリック亀有教会',
        'alternateName' => 'Catholic Kameari Church, St. Francis of Assisi',
        'url' => home_url('/'),
        'telephone' => '+81-3-3606-1757',
        'address' => [
            '@type' => 'PostalAddress',
            'postalCode' => '120-0003',
            'addressRegion' => '東京都',
            'addressLocality' => '足立区',
            'streetAddress' => '東和4-3-20',
            'addressCountry' => 'JP',
        ],
        'sameAs' => [
            'https://www.facebook.com/%E3%82%AB%E3%83%88%E3%83%AA%E3%83%83%E3%82%AF%E4%BA%80%E6%9C%89%E6%95%99%E4%BC%9A-136875783162599/',
            'https://twitter.com/catholicKameari',
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 20);
