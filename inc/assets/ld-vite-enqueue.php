<?php
if (!defined('ABSPATH')) exit;

/**
 * LowDesign — Vite enqueue (build mode)
 * - Loads build assets from Vite manifest via ld_vite_asset_uri().
 * - Keeps LD exports (assets/css/010.. etc) as the main source of tokens.
 */

add_action('wp_enqueue_scripts', function () {
  if (!function_exists('ld_vite_asset_uri')) return;

  // Optional: theme-wide compiled CSS (components, fixes, etc.)
  $main_css = ld_vite_asset_uri('assets/src/scss/main.scss');
  if ($main_css) {
    wp_enqueue_style('ld-vite-main', $main_css, ['bootstrap'], null);
  }

  // Theme runtime JS (dark/auto theme detection, UI hooks, etc.)
  $main_js = ld_vite_asset_uri('assets/src/js/main.js');
  if ($main_js) {
    wp_enqueue_script('ld-main', $main_js, ['bootstrap'], null, true);
  }
}, 30);

add_action('enqueue_block_editor_assets', function () {
  if (!function_exists('ld_vite_asset_uri')) return;

  $editor_css = ld_vite_asset_uri('assets/src/scss/editor.scss');
  if ($editor_css) {
    wp_enqueue_style('ld-vite-editor', $editor_css, ['bootstrap'], null);
  }
}, 30);

add_action('admin_enqueue_scripts', function () {
  if (!function_exists('ld_vite_asset_uri')) return;

  $admin_css = ld_vite_asset_uri('assets/src/scss/admin-dark.scss');
  if ($admin_css) {
    wp_enqueue_style('ld-vite-admin-dark', $admin_css, [], null);
  }
}, 30);
