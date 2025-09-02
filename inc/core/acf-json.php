<?php
if (!defined('ABSPATH')) exit;

if (!defined('LD_THEME_DIR')) define('LD_THEME_DIR', get_stylesheet_directory());

/**
 * Where to LOAD JSON from:
 * - default ACF path (kept)
 * - theme acf-json
 * - (optionally) MU-plugin core acf-json (if exists)
 */
add_filter('acf/settings/load_json', function($paths){
  $paths[] = LD_THEME_DIR . '/acf-json';
  $mu_core = WP_CONTENT_DIR . '/mu-plugins/lowdesign-core/acf-json';
  if (is_dir($mu_core)) $paths[] = $mu_core;
  return array_unique($paths);
});

/**
 * Where to SAVE JSON:
 * - DEV/STAGING only → theme acf-json
 * - PROD → disable saving to filesystem (return null keeps default; or return theme path if you do want it)
 */
add_filter('acf/settings/save_json', function($path){
  $env = wp_get_environment_type(); // 'production' | 'staging' | 'development'
  if ($env === 'production') {
    // Disable writes on prod to avoid drifting repo files from the dashboard.
    return $path; // or return LD_THEME_DIR . '/acf-json' if you DO want writes on prod
  }
  return LD_THEME_DIR . '/acf-json';
});