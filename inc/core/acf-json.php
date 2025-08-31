<?php
if (!defined('ABSPATH')) exit;

add_filter('acf/settings/save_json', fn()=> LD_THEME_DIR . '/acf-json');

add_filter('acf/settings/load_json', function($paths){
  $paths[] = LD_THEME_DIR . '/acf-json';
  return $paths;
});