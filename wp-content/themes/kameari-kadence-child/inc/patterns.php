<?php
/**
 * Reusable block patterns for the Catholic Kameari rebuild.
 *
 * @package Kameari
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', function (): void {
    $homepage_pattern = get_stylesheet_directory() . '/patterns/home.html';

    register_block_pattern('kameari/homepage', [
        'title' => __('Kameari homepage', 'kameari'),
        'description' => __('A complete peaceful church homepage layout.', 'kameari'),
        'categories' => ['kameari'],
        'content' => file_exists($homepage_pattern) ? file_get_contents($homepage_pattern) : '',
    ]);
});
