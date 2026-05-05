<?php
/**
 * Plugin Name: Kameari Content Seeder
 * Description: Seeds an empty deployment with the captured Catholic Kameari site content and redesigned homepage.
 *
 * @package Kameari
 */

if (! defined('ABSPATH')) {
    exit;
}

final class Kameari_Content_Seeder
{
    private const OPTION_SEEDED = 'kameari_content_seeded_version';
    private const OPTION_ERROR = 'kameari_content_seed_last_error';
    private const SOURCE_VERSION = '2026-05-05-public-snapshot';
    private const DEFAULT_SOURCE_DIR = '/opt/kameari/source-content';

    public static function boot(): void
    {
        add_action('init', [__CLASS__, 'maybe_seed'], 1);
    }

    public static function maybe_seed(): void
    {
        if (self::env_is_false('KAMEARI_AUTO_SEED')) {
            return;
        }

        if (function_exists('wp_installing') && wp_installing()) {
            return;
        }

        if (get_option(self::OPTION_SEEDED) && ! self::env_is_true('KAMEARI_FORCE_SEED')) {
            return;
        }

        if (! self::source_available() || ! self::site_can_be_seeded()) {
            return;
        }

        self::seed();
    }

    public static function seed(): void
    {
        if (get_option(self::OPTION_SEEDED) && ! self::env_is_true('KAMEARI_FORCE_SEED')) {
            return;
        }

        if (! self::source_available()) {
            return;
        }

        if (get_transient('kameari_content_seed_lock') && ! self::env_is_true('KAMEARI_FORCE_SEED')) {
            return;
        }

        set_transient('kameari_content_seed_lock', time(), 10 * MINUTE_IN_SECONDS);
        @set_time_limit(600);

        try {
            self::load_admin_includes();
            self::remove_sample_content();
            self::activate_theme();
            self::configure_site();

            $term_maps = self::import_terms();
            $media_map = self::import_media();
            $page_map = self::import_pages($media_map);

            self::create_redesigned_pages($page_map);
            self::import_posts($term_maps, $media_map);
            self::setup_menu();

            flush_rewrite_rules(false);
            update_option(self::OPTION_SEEDED, self::SOURCE_VERSION, false);
            delete_option(self::OPTION_ERROR);

            self::log('Seeded Catholic Kameari content snapshot.');
        } catch (Throwable $exception) {
            update_option(self::OPTION_ERROR, $exception->getMessage(), false);
            self::log('Catholic Kameari content seed failed: ' . $exception->getMessage(), true);
        } finally {
            delete_transient('kameari_content_seed_lock');
        }
    }

    private static function load_admin_includes(): void
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    private static function configure_site(): void
    {
        update_option('blogname', '東京大司教区カトリック亀有教会');
        update_option('blogdescription', 'Catholic Kameari Church, St. Francis of Assisi, Tokyo');
        update_option('timezone_string', 'Asia/Tokyo');
        update_option('date_format', 'Y年n月j日');
        update_option('time_format', 'G:i');
        update_option('start_of_week', 0);
        update_option('WPLANG', 'ja');
        update_option('permalink_structure', '/%year%/%postname%/');
    }

    private static function activate_theme(): void
    {
        if (wp_get_theme('kameari-kadence-child')->exists()) {
            switch_theme('kameari-kadence-child');
        }
    }

    private static function remove_sample_content(): void
    {
        $sample_post = get_page_by_path('hello-world', OBJECT, 'post');
        if ($sample_post instanceof WP_Post) {
            wp_delete_post((int) $sample_post->ID, true);
        }

        $sample_page = get_page_by_path('sample-page', OBJECT, 'page');
        if ($sample_page instanceof WP_Post) {
            wp_delete_post((int) $sample_page->ID, true);
        }
    }

    private static function site_can_be_seeded(): bool
    {
        if (self::env_is_true('KAMEARI_FORCE_SEED')) {
            return true;
        }

        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
            'numberposts' => 25,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        foreach ($posts as $post) {
            if (! $post instanceof WP_Post) {
                continue;
            }

            $slug = (string) $post->post_name;
            if ('post' === $post->post_type && 'hello-world' === $slug) {
                continue;
            }

            if ('page' === $post->post_type && in_array($slug, ['sample-page', 'privacy-policy'], true)) {
                continue;
            }

            return false;
        }

        return true;
    }

    private static function import_terms(): array
    {
        return [
            'category' => self::import_taxonomy('raw/categories.json', 'category'),
            'post_tag' => self::import_taxonomy('raw/tags.json', 'post_tag'),
        ];
    }

    private static function import_taxonomy(string $json_file, string $taxonomy): array
    {
        $rows = self::read_json($json_file);
        $pending = [];
        $map = [];

        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $pending[(int) $row['id']] = $row;
            }
        }

        while ($pending) {
            $progress = false;

            foreach ($pending as $old_id => $row) {
                $parent_old_id = (int) ($row['parent'] ?? 0);
                if ($parent_old_id && ! isset($map[$parent_old_id])) {
                    continue;
                }

                $map[$old_id] = self::upsert_term($row, $taxonomy, $parent_old_id ? $map[$parent_old_id] : 0);
                unset($pending[$old_id]);
                $progress = true;
            }

            if (! $progress) {
                foreach ($pending as $old_id => $row) {
                    $map[$old_id] = self::upsert_term($row, $taxonomy, 0);
                    unset($pending[$old_id]);
                }
            }
        }

        return $map;
    }

    private static function upsert_term(array $row, string $taxonomy, int $parent_id): int
    {
        $name = self::rendered_text((string) ($row['name'] ?? ''));
        $slug = (string) ($row['slug'] ?? sanitize_title($name));
        $description = self::rendered_text((string) ($row['description'] ?? ''));
        $term_id = self::find_term_by_slug($slug, $taxonomy);
        $args = [
            'slug' => $slug,
            'description' => $description,
        ];

        if ($parent_id > 0) {
            $args['parent'] = $parent_id;
        }

        if ($term_id > 0) {
            wp_update_term($term_id, $taxonomy, array_merge(['name' => $name], $args));
            return $term_id;
        }

        $created = wp_insert_term($name, $taxonomy, $args);
        if (is_wp_error($created)) {
            $fallback = term_exists($name, $taxonomy);
            return is_array($fallback) ? (int) $fallback['term_id'] : 0;
        }

        return (int) $created['term_id'];
    }

    private static function find_term_by_slug(string $slug, string $taxonomy): int
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'slug' => $slug,
            'hide_empty' => false,
            'number' => 1,
            'fields' => 'ids',
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return 0;
        }

        return (int) $terms[0];
    }

    private static function import_media(): array
    {
        $rows = self::read_json('raw/media.json');
        $source_files = self::media_source_files();
        $upload_dir = wp_upload_dir();
        $map = [];

        foreach ($rows as $row) {
            $old_id = (int) ($row['id'] ?? 0);
            if ($old_id <= 0) {
                continue;
            }

            $source = $source_files[$old_id] ?? self::derive_media_source_path($row);
            if (! $source || ! file_exists($source)) {
                continue;
            }

            $relative = self::media_relative_path($row);
            if ('' === $relative) {
                continue;
            }

            $destination = trailingslashit($upload_dir['basedir']) . $relative;
            if (! self::is_path_inside($destination, $upload_dir['basedir'])) {
                continue;
            }

            wp_mkdir_p(dirname($destination));
            if (! file_exists($destination)) {
                copy($source, $destination);
            }

            $attachment_id = self::find_post_by_source_id($old_id, 'attachment', '_kameari_source_media_id');
            if (! $attachment_id) {
                $attachment_id = self::find_attachment_by_file($relative);
            }

            $attachment = [
                'post_title' => self::rendered_text((string) ($row['title']['rendered'] ?? $row['slug'] ?? wp_basename($destination))),
                'post_content' => self::rewrite_content((string) ($row['description']['rendered'] ?? '')),
                'post_excerpt' => self::rewrite_content((string) ($row['caption']['rendered'] ?? '')),
                'post_status' => 'inherit',
                'post_mime_type' => (string) ($row['mime_type'] ?? wp_check_filetype(wp_basename($destination))['type']),
                'guid' => trailingslashit($upload_dir['baseurl']) . self::urlencode_path($relative),
                'post_date' => self::mysql_date((string) ($row['date'] ?? '')),
                'post_modified' => self::mysql_date((string) ($row['modified'] ?? '')),
            ];

            if ($attachment_id) {
                wp_update_post(array_merge(['ID' => $attachment_id], $attachment));
            } else {
                $created = wp_insert_attachment($attachment, $destination, 0, true);
                if (is_wp_error($created)) {
                    continue;
                }
                $attachment_id = (int) $created;
            }

            update_attached_file($attachment_id, $destination);
            update_post_meta($attachment_id, '_kameari_source_media_id', $old_id);

            if (isset($row['alt_text'])) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', self::rendered_text((string) $row['alt_text']));
            }

            if (0 === strpos((string) $attachment['post_mime_type'], 'image/')) {
                $metadata = wp_generate_attachment_metadata($attachment_id, $destination);
                if (! is_wp_error($metadata) && ! empty($metadata)) {
                    wp_update_attachment_metadata($attachment_id, $metadata);
                }
            }

            $map[$old_id] = $attachment_id;
        }

        return $map;
    }

    private static function import_pages(array $media_map): array
    {
        $rows = self::read_json('raw/pages.json');
        $pending = [];
        $map = [];

        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $pending[(int) $row['id']] = $row;
            }
        }

        while ($pending) {
            $progress = false;

            foreach ($pending as $old_id => $row) {
                $path = self::path_from_link((string) ($row['link'] ?? ''));
                if ('/' === $path) {
                    unset($pending[$old_id]);
                    $progress = true;
                    continue;
                }

                $parent_old_id = (int) ($row['parent'] ?? 0);
                if ($parent_old_id && ! isset($map[$parent_old_id])) {
                    continue;
                }

                $map[$old_id] = self::upsert_source_page($row, $parent_old_id ? $map[$parent_old_id] : 0, $media_map);
                unset($pending[$old_id]);
                $progress = true;
            }

            if (! $progress) {
                foreach ($pending as $old_id => $row) {
                    $map[$old_id] = self::upsert_source_page($row, 0, $media_map);
                    unset($pending[$old_id]);
                }
            }
        }

        return $map;
    }

    private static function upsert_source_page(array $row, int $parent_id, array $media_map): int
    {
        $old_id = (int) ($row['id'] ?? 0);
        $path = trim(self::path_from_link((string) ($row['link'] ?? '')), '/');
        $existing = self::find_post_by_source_id($old_id, 'page', '_kameari_source_page_id');

        if (! $existing && '' !== $path) {
            $page = get_page_by_path($path, OBJECT, 'page');
            $existing = $page instanceof WP_Post ? (int) $page->ID : 0;
        }

        $data = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => self::rendered_text((string) ($row['title']['rendered'] ?? '')),
            'post_name' => (string) ($row['slug'] ?? wp_basename($path)),
            'post_parent' => $parent_id,
            'menu_order' => (int) ($row['menu_order'] ?? 0),
            'post_content' => self::rewrite_content((string) ($row['content']['rendered'] ?? ''), $media_map),
            'post_excerpt' => self::rewrite_content((string) ($row['excerpt']['rendered'] ?? ''), $media_map),
            'post_date' => self::mysql_date((string) ($row['date'] ?? '')),
            'post_modified' => self::mysql_date((string) ($row['modified'] ?? '')),
        ];

        $post_id = self::save_post($data, $existing);
        if ($post_id > 0) {
            update_post_meta($post_id, '_kameari_source_page_id', $old_id);
            self::set_featured_media($post_id, (int) ($row['featured_media'] ?? 0), $media_map);
        }

        return $post_id;
    }

    private static function create_redesigned_pages(array $page_map): void
    {
        $home_content = '<!-- wp:paragraph --><p>東京・足立区東和にあるカトリック亀有教会の公式サイトです。</p><!-- /wp:paragraph -->';
        $pattern_file = get_theme_file_path('patterns/home.html');
        if (file_exists($pattern_file)) {
            $home_content = (string) file_get_contents($pattern_file);
        }

        $home_id = self::upsert_manual_page(
            'top',
            '東京大司教区カトリック亀有教会',
            $home_content,
            0,
            0,
            6
        );

        if ($home_id > 0) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home_id);
        }

        self::upsert_manual_page(
            'news',
            'お知らせ',
            '<!-- wp:heading --><h2 class="wp-block-heading">お知らせ</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="news" posts_per_page="12"]<!-- /wp:shortcode -->',
            0,
            30
        );

        self::upsert_manual_page(
            'topics',
            'トピックス',
            '<!-- wp:heading --><h2 class="wp-block-heading">トピックス</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="topics" posts_per_page="12"]<!-- /wp:shortcode -->',
            0,
            31
        );

        $schedule_id = $page_map[653] ?? 0;
        if (! $schedule_id) {
            $schedule_page = get_page_by_path('schedule', OBJECT, 'page');
            $schedule_id = $schedule_page instanceof WP_Post ? (int) $schedule_page->ID : 0;
        }

        self::upsert_manual_page(
            'schedule/monthly',
            '月間予定',
            '<!-- wp:heading --><h2 class="wp-block-heading">月間予定</h2><!-- /wp:heading --><!-- wp:shortcode -->[kameari_posts category="monthly_schedule" posts_per_page="12"]<!-- /wp:shortcode -->',
            $schedule_id,
            22
        );
    }

    private static function upsert_manual_page(
        string $path,
        string $title,
        string $content,
        int $parent_id = 0,
        int $menu_order = 0,
        int $source_page_id = 0
    ): int {
        $clean_path = trim($path, '/');
        $existing_page = get_page_by_path($clean_path, OBJECT, 'page');
        $existing = $existing_page instanceof WP_Post ? (int) $existing_page->ID : 0;
        $segments = explode('/', $clean_path);
        $slug = (string) end($segments);

        $data = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
            'post_parent' => $parent_id,
            'menu_order' => $menu_order,
            'post_content' => $content,
        ];

        $post_id = self::save_post($data, $existing);
        if ($post_id > 0 && $source_page_id > 0) {
            update_post_meta($post_id, '_kameari_source_page_id', $source_page_id);
        }

        return $post_id;
    }

    private static function import_posts(array $term_maps, array $media_map): void
    {
        $rows = self::read_json('raw/posts.json');

        foreach ($rows as $row) {
            $old_id = (int) ($row['id'] ?? 0);
            if ($old_id <= 0) {
                continue;
            }

            $existing = self::find_post_by_source_id($old_id, 'post', '_kameari_source_post_id');
            $data = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => self::rendered_text((string) ($row['title']['rendered'] ?? '')),
                'post_name' => (string) ($row['slug'] ?? ''),
                'post_content' => self::rewrite_content((string) ($row['content']['rendered'] ?? ''), $media_map),
                'post_excerpt' => self::rewrite_content((string) ($row['excerpt']['rendered'] ?? ''), $media_map),
                'post_date' => self::mysql_date((string) ($row['date'] ?? '')),
                'post_modified' => self::mysql_date((string) ($row['modified'] ?? '')),
            ];

            $post_id = self::save_post($data, $existing);
            if ($post_id <= 0) {
                continue;
            }

            update_post_meta($post_id, '_kameari_source_post_id', $old_id);

            $category_ids = self::mapped_term_ids((array) ($row['categories'] ?? []), $term_maps['category'] ?? []);
            if ($category_ids) {
                wp_set_object_terms($post_id, $category_ids, 'category', false);
            }

            $tag_ids = self::mapped_term_ids((array) ($row['tags'] ?? []), $term_maps['post_tag'] ?? []);
            if ($tag_ids) {
                wp_set_object_terms($post_id, $tag_ids, 'post_tag', false);
            }

            self::set_featured_media($post_id, (int) ($row['featured_media'] ?? 0), $media_map);
        }
    }

    private static function setup_menu(): void
    {
        $menu = wp_get_nav_menu_object('Primary');
        $menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu('Primary');
        if ($menu_id <= 0) {
            return;
        }

        $existing_object_ids = [];
        $existing_items = wp_get_nav_menu_items($menu_id);
        foreach ((array) $existing_items as $item) {
            if (isset($item->object_id)) {
                $existing_object_ids[] = (int) $item->object_id;
            }
        }

        $items = [
            ['schedule/mass', 'ミサ'],
            ['about/visitors', '初めての方へ'],
            ['about', '教会紹介'],
            ['news', 'お知らせ'],
            ['topics', 'トピックス'],
            ['commit', '教会活動'],
            ['memorial', '結婚式・葬儀'],
            ['access', 'アクセス'],
        ];

        foreach ($items as [$path, $title]) {
            $page = get_page_by_path($path, OBJECT, 'page');
            if (! $page instanceof WP_Post || in_array((int) $page->ID, $existing_object_ids, true)) {
                continue;
            }

            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => (int) $page->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            ]);
        }

        $locations = (array) get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    private static function save_post(array $data, int $existing = 0): int
    {
        if ($existing > 0) {
            $updated = wp_update_post(array_merge(['ID' => $existing], $data), true);
            return is_wp_error($updated) ? 0 : (int) $updated;
        }

        $created = wp_insert_post($data, true);
        return is_wp_error($created) ? 0 : (int) $created;
    }

    private static function set_featured_media(int $post_id, int $old_media_id, array $media_map): void
    {
        if ($post_id > 0 && $old_media_id > 0 && isset($media_map[$old_media_id])) {
            set_post_thumbnail($post_id, (int) $media_map[$old_media_id]);
        }
    }

    private static function mapped_term_ids(array $old_ids, array $map): array
    {
        $term_ids = [];

        foreach ($old_ids as $old_id) {
            $old_id = (int) $old_id;
            if (isset($map[$old_id]) && $map[$old_id] > 0) {
                $term_ids[] = (int) $map[$old_id];
            }
        }

        return array_values(array_unique($term_ids));
    }

    private static function find_post_by_source_id(int $source_id, string $post_type, string $meta_key): int
    {
        if ($source_id <= 0) {
            return 0;
        }

        $ids = get_posts([
            'post_type' => $post_type,
            'post_status' => 'any',
            'meta_key' => $meta_key,
            'meta_value' => $source_id,
            'numberposts' => 1,
            'fields' => 'ids',
        ]);

        return $ids ? (int) $ids[0] : 0;
    }

    private static function find_attachment_by_file(string $relative): int
    {
        $ids = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_key' => '_wp_attached_file',
            'meta_value' => $relative,
            'numberposts' => 1,
            'fields' => 'ids',
        ]);

        return $ids ? (int) $ids[0] : 0;
    }

    private static function media_source_files(): array
    {
        $rows = self::read_json('media-files.json');
        $files = [];

        foreach ($rows as $row) {
            $old_id = (int) ($row['id'] ?? 0);
            $local_path = (string) ($row['local_path'] ?? '');
            if ($old_id > 0 && '' !== $local_path) {
                $files[$old_id] = self::source_file($local_path);
            }
        }

        return $files;
    }

    private static function derive_media_source_path(array $row): string
    {
        $source_url = (string) ($row['source_url'] ?? '');
        $path = (string) wp_parse_url($source_url, PHP_URL_PATH);
        $needle = '/wp-content/uploads/';
        $position = strpos($path, $needle);

        if (false === $position) {
            return '';
        }

        return self::source_file('media/' . ltrim(substr($path, $position + strlen($needle)), '/'));
    }

    private static function media_relative_path(array $row): string
    {
        $file = (string) ($row['media_details']['file'] ?? '');
        if ('' !== $file) {
            return self::normalize_relative_path(rawurldecode($file));
        }

        $source_url = (string) ($row['source_url'] ?? '');
        $path = (string) wp_parse_url($source_url, PHP_URL_PATH);
        $needle = '/wp-content/uploads/';
        $position = strpos($path, $needle);

        if (false === $position) {
            return '';
        }

        return self::normalize_relative_path(rawurldecode(substr($path, $position + strlen($needle))));
    }

    private static function normalize_relative_path(string $path): string
    {
        $parts = [];
        $path = str_replace('\\', '/', trim($path, '/'));

        foreach (explode('/', $path) as $part) {
            if ('' === $part || '.' === $part || '..' === $part) {
                continue;
            }
            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    private static function rewrite_content(string $content, array $media_map = []): string
    {
        unset($media_map);

        $manifest = self::read_json('manifest.json');
        $source_url = rtrim((string) ($manifest['source_url'] ?? 'https://catholic-kameari.jp'), '/');
        $home_url = rtrim(home_url(), '/');

        $replacements = [
            $source_url => $home_url,
            'https://catholic-kameari.jp' => $home_url,
            'http://catholic-kameari.jp' => $home_url,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private static function path_from_link(string $link): string
    {
        $path = (string) wp_parse_url($link, PHP_URL_PATH);
        if ('' === $path) {
            return '/';
        }

        return '/' . trim($path, '/') . '/';
    }

    private static function mysql_date(string $date): string
    {
        if ('' === $date) {
            return current_time('mysql');
        }

        return str_replace('T', ' ', substr($date, 0, 19));
    }

    private static function rendered_text(string $value): string
    {
        return html_entity_decode(wp_strip_all_tags($value), ENT_QUOTES, 'UTF-8');
    }

    private static function read_json(string $relative): array
    {
        $path = self::source_file($relative);
        if (! file_exists($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function source_available(): bool
    {
        return file_exists(self::source_file('raw/pages.json'))
            && file_exists(self::source_file('raw/posts.json'))
            && file_exists(self::source_file('media-files.json'));
    }

    private static function source_file(string $relative): string
    {
        return self::source_dir() . '/' . ltrim($relative, '/');
    }

    private static function source_dir(): string
    {
        $configured = getenv('KAMEARI_SOURCE_CONTENT_DIR');
        return rtrim(is_string($configured) && '' !== $configured ? $configured : self::DEFAULT_SOURCE_DIR, '/');
    }

    private static function is_path_inside(string $path, string $base): bool
    {
        $normalized_path = wp_normalize_path($path);
        $normalized_base = trailingslashit(wp_normalize_path($base));
        return 0 === strpos($normalized_path, $normalized_base);
    }

    private static function urlencode_path(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private static function env_is_false(string $name): bool
    {
        $value = getenv($name);
        return is_string($value) && in_array(strtolower($value), ['0', 'false', 'no', 'off'], true);
    }

    private static function env_is_true(string $name): bool
    {
        $value = getenv($name);
        return is_string($value) && in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private static function log(string $message, bool $warning = false): void
    {
        if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI')) {
            $warning ? WP_CLI::warning($message) : WP_CLI::log($message);
            return;
        }

        error_log($message);
    }
}

Kameari_Content_Seeder::boot();
