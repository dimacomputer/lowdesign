<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  register_post_type('ld_site_config', [
    'label'         => 'Site Config',
    'labels'        => ['name' => 'Site Config', 'singular_name' => 'Site Config', 'menu_name' => 'Site Config'],
    'public'        => false,
    'show_ui'       => true,
    'show_in_menu'  => true,
    'show_in_rest'  => true,
    'menu_position' => 58,
    'menu_icon'     => 'dashicons-admin-generic',
    'supports'      => ['title'],   // ← без 'editor' — чтобы чистый экран под ACF
    'capability_type' => 'page',
    'map_meta_cap'    => true,
  ]);

  // создать единственную запись, если её нет
  $exists = get_posts(['post_type'=>'ld_site_config','numberposts'=>1,'fields'=>'ids','post_status'=>'any']);
  if (empty($exists)) {
    wp_insert_post(['post_type'=>'ld_site_config','post_title'=>'Site Settings','post_status'=>'publish']);
  }
}, 0);

// редирект из списка сразу на редактирование единственной записи
add_action('admin_init', function () {
  if (!function_exists('get_current_screen')) return;
  $s = get_current_screen();
  if (!$s || $s->id !== 'edit-ld_site_config') return;
  if (!current_user_can('manage_options')) return;

  $ids = get_posts(['post_type'=>'ld_site_config','numberposts'=>1,'fields'=>'ids','post_status'=>'any']);
  if (!empty($ids)) {
    wp_safe_redirect( admin_url('post.php?post='.(int)$ids[0].'&action=edit') );
    exit;
  }
});