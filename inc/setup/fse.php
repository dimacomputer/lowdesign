<?php
if (!defined('ABSPATH')) exit;

/**
 * LowDesign — Full Site Editing (block theme) baseline
 * - Adds Gutenberg supports
 * - Registers pattern categories
 * - Registers core block styles (Buttons)
 */

add_action('after_setup_theme', function () {
  add_theme_support('wp-block-styles');
  add_theme_support('align-wide');
  add_theme_support('responsive-embeds');
  add_theme_support('editor-styles');

  // Site editor uses front-end styles; block editor still needs explicit enqueue (handled elsewhere).
  // Optional: provide a small editor-only stylesheet if needed.
});

add_action('init', function () {
  if (function_exists('register_block_pattern_category')) {
    register_block_pattern_category('lowdesign', [
      'label' => __('LowDesign', 'lowdesign-platform'),
    ]);
  }

  if (function_exists('register_block_style')) {

  // Group (section) — theme scope styles -> becomes data-ld-theme via render filter
  $ld_themes = ["amber", "amber-dark", "blue", "blue-dark", "cyan", "cyan-dark", "dark", "default", "green", "green-dark", "indigo", "indigo-dark", "orange", "orange-dark", "pink", "pink-dark", "red", "red-dark", "teal", "teal-dark", "violet", "violet-dark"];
  foreach ($ld_themes as $k) {
    register_block_style('core/group', [
      'name'  => 'ld-theme-' . $k,
      'label' => 'LD Theme: ' . strtoupper($k),
    ]);
  }

    register_block_style('core/button', [
      'name'  => 'ld-primary',
      'label' => __('LD Primary', 'lowdesign-platform'),
    ]);
    register_block_style('core/button', [
      'name'  => 'ld-outline',
      'label' => __('LD Outline', 'lowdesign-platform'),
    ]);
  }
});
