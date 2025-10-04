<?php
if (!defined('ABSPATH')) exit;

/**
 *  Feature toggles, driven by ACF fields on ld_site_config:
 *  - enable_categories_on_pages (true/false)
 *  - enable_excerpt_on_pages   (true/false)
 *  - enable_template_loader    (true/false)
 *
 *  Требования: функция ld_opt() уже подключена.
 */

// 1) Категории к страницам и CPT
add_action('init', function () {
  if (!function_exists('ld_opt')) return;

  $on = (bool) ld_opt('enable_categories_on_pages', false);
  if (!$on) return;

  // Берём все публичные CPT + page
  $pts = get_post_types([
    'public' => true,
  ], 'names');

  // Ядро исключаем
  $exclude = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles'];

  foreach ($pts as $pt) {
    if (in_array($pt, $exclude, true)) continue;
    register_taxonomy_for_object_type('category', $pt);
  }
}, 20);

// 2) Excerpt у страниц
add_action('init', function () {
  if (!function_exists('ld_opt')) return;

  $on = (bool) ld_opt('enable_excerpt_on_pages', false);
  if ($on) {
    add_post_type_support('page', 'excerpt');
  } else {
    // мягко выключать не будем (ядро не умеет убирать метабокс обратно аккуратно),
    // просто не трогаем если уже включено.
  }
}, 20);

// 3) Template Loader по полю `ld_template_slug`
add_filter('template_include', function ($template) {
  if (!function_exists('ld_opt')) return $template;

  $on = (bool) ld_opt('enable_template_loader', false);
  if (!$on) return $template;

  if (!is_singular()) return $template;

  $slug = function_exists('get_field') ? get_field('ld_template_slug') : '';
  if (!$slug) return $template;

  if (is_page()) {
    $file = locate_template("page-{$slug}.php");
    return $file ?: $template;
  }

  $pt = get_post_type();
  $file = locate_template("single-{$slug}.php");
  if ($file) return $file;

  // как fallback можно попытаться single-{pt}-{slug}.php (если захочешь)
  return $template;
}, 20);