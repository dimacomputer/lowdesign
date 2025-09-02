<?php
/**
 * Singleton CPT: Site Settings (ACF Free friendly)
 * Путь: inc/cpt/10-cpt-config.php
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  register_post_type('ld_config', [
    'label' => 'Site Settings',
    'labels' => [
      'name'          => 'Site Settings',
      'singular_name' => 'Site Settings',
      'menu_name'     => 'Site Settings',
      'add_new'       => 'Add Settings',
      'add_new_item'  => 'Add Settings',
      'edit_item'     => 'Edit Settings',
      'new_item'      => 'New Settings',
      'view_item'     => 'View Settings',
      'search_items'  => 'Search Settings',
      'not_found'     => 'No settings found',
      'all_items'     => 'Site Settings',
    ],
    'public'        => false,
    'show_ui'       => true,
    'show_in_menu'  => true,
    'menu_position' => 58,
    'menu_icon'     => 'dashicons-admin-generic',
    'supports'      => ['title'],
    'capability_type' => 'page',
    'map_meta_cap'    => true,
  ]);

  // Гарантируем единственную запись
  $existing = get_posts([
    'post_type'   => 'ld_config',
    'post_status' => 'any',
    'numberposts' => 1,
    'fields'      => 'ids'
  ]);

  if (empty($existing)) {
    wp_insert_post([
      'post_type'   => 'ld_config',
      'post_title'  => 'Site Settings',
      'post_status' => 'publish'
    ]);
  }
});

// UX: список редиректим сразу на редактирование единственной записи
add_action('admin_init', function () {
  if (!function_exists('get_current_screen')) return;
  $screen = get_current_screen();
  if (!$screen || $screen->id !== 'edit-ld_config') return;

  $ids = get_posts([
    'post_type'   => 'ld_config',
    'post_status' => 'any',
    'numberposts' => 1,
    'fields'      => 'ids'
  ]);

  if (!empty($ids)) {
    wp_redirect( admin_url('post.php?post=' . (int) $ids[0] . '&action=edit') );
    exit;
  }
});