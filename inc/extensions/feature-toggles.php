<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  $opt = function($key, $default = true) {
    return function_exists('get_field') ? (bool) get_field($key, 'option') : $default;
  };

  // Categories on Pages/CPT
  if ($opt('enable_categories_on_pages', true)) {
    $map = [
      'category' => ['page', 'fineart', 'photo'],
      'ui_role'  => ['page'],
    ];
    foreach ($map as $tax => $pts) {
      if (!taxonomy_exists($tax)) continue;
      foreach ($pts as $pt) register_taxonomy_for_object_type($tax, $pt);
    }
  }

  // Excerpt on Pages
  if ($opt('enable_excerpt_on_pages', true)) {
    add_post_type_support('page', 'excerpt');
  }
}, 20);