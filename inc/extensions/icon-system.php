<?php
if (!defined('ABSPATH')) exit;

// inc/extensions/icon-system.php
// Helpers and admin integration for SVG sprite icons.

if (!function_exists('ld_sprite_path')) {
  function ld_sprite_path(): string {
    return get_stylesheet_directory() . '/assets/icons/sprite.svg';
  }
}

if (!function_exists('ld_sprite_choices')) {
  function ld_sprite_choices(): array {
    $file = ld_sprite_path();
    if (!is_file($file)) return [];
    $svg = file_get_contents($file);
    if (!$svg) return [];
    preg_match_all('/<symbol[^>]+id="([^"]+)"/i', $svg, $m);
    $choices = [];
    foreach ($m[1] ?? [] as $id) {
      $choices[$id] = $id;
    }
    return $choices;
  }
}

if (!function_exists('ld__sprite_load_field')) {
  function ld__sprite_load_field($field) {
    $field['choices'] = ld_sprite_choices();
    return $field;
  }
}

add_filter('acf/load_field/name=menu_icon', 'ld__sprite_load_field');
add_filter('acf/load_field/name=post_icon_name', 'ld__sprite_load_field');
add_filter('acf/load_field/name=term_icon_name', 'ld__sprite_load_field');

add_action('admin_enqueue_scripts', function () {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();
  wp_enqueue_style('ld-icon-preview', $uri . '/assets/admin/icon-preview.css', [], filemtime($dir . '/assets/admin/icon-preview.css'));
  wp_enqueue_script('ld-icon-preview', $uri . '/assets/admin/icon-preview.js', [], filemtime($dir . '/assets/admin/icon-preview.js'), true);
});

add_filter('walker_nav_menu_start_el', function ($item_output, $item, $depth, $args) {
  if (!function_exists('get_field')) return $item_output;
  $icon = get_field('menu_icon', $item);
  if (!$icon) return $item_output;
  $svg = ld_icon($icon, 'menu__icon');
  return preg_replace('/(<a[^>]*>)/', '$1' . $svg, $item_output, 1);
}, 10, 4);

add_filter('manage_edit-category_columns', function ($cols) {
  $cols['ld_icon'] = __('Icon', 'ld');
  return $cols;
});

add_filter('manage_category_custom_column', function ($out, $col, $term_id) {
  if ($col !== 'ld_icon') return $out;
  $icon = function_exists('get_field') ? get_field('term_icon_name', 'term_' . $term_id) : '';
  if ($icon) return ld_icon($icon);
  $media = function_exists('get_field') ? (int) get_field('term_icon_media', 'term_' . $term_id) : 0;
  if ($media) return ld_image_or_svg_html($media, 'thumbnail', ['class' => 'icon']);
  return '';
}, 10, 3);
