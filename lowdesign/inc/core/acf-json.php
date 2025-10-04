<?php
if (!defined('ABSPATH')) exit;

if (!defined('LD_THEME_DIR')) define('LD_THEME_DIR', get_stylesheet_directory());

add_filter('acf/settings/load_json', function(array $paths){
  $paths[] = LD_THEME_DIR . '/acf-json';
  $mu_core = WP_CONTENT_DIR . '/mu-plugins/lowdesign-core/acf-json';
  if (is_dir($mu_core)) $paths[] = $mu_core;
  return array_unique($paths);
});

add_filter('acf/settings/save_json', function($path){
  // писать JSON только вне production
  if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE !== 'production') {
    return LD_THEME_DIR . '/acf-json';
  }
  return $path; // по умолчанию — без записи на проде
});