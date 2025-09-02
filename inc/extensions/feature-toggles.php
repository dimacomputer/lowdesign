<?php
// inc/extensions/feature-toggles.php
// Categories on pages/CPT and Excerpt on pages — controlled by ACF fields (free) stored on ld_config.
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  // helper: read ACF from ld_config singleton (works without PRO)
  $opt = function($key, $default = true) {
    if (!function_exists('get_field')) return $default;
    $id = 0;
    $q = get_posts(['post_type'=>'ld_config','post_status'=>'any','numberposts'=>1,'fields'=>'ids']);
    if (!empty($q)) $id = (int) $q[0];
    if (!$id) return $default;
    $v = get_field($key, $id);
    return $v === null || $v === '' ? $default : (bool)$v;
  };

  // 1) Categories on Pages / CPT
  if ($opt('enable_categories_on_pages', true)) {
    $map = [
      'category' => ['page', 'fineart', 'photo'],
      'ui_role'  => ['page'],
    ];
    foreach ($map as $tax => $pts) {
      if (!taxonomy_exists($tax)) continue;
      foreach ($pts as $pt) {
        register_taxonomy_for_object_type($tax, $pt);
      }
    }
  }

  // 2) Excerpt on Pages
  if ($opt('enable_excerpt_on_pages', true)) {
    add_post_type_support('page', 'excerpt');
  }
}, 20);
