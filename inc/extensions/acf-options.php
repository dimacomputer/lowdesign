<?php
// inc/extensions/acf-options.php
if (!defined('ABSPATH')) exit;

add_action('acf/init', function () {
  if (!function_exists('acf_add_options_page')) return;

  acf_add_options_page([
    'page_title' => 'Site Settings',
    'menu_title' => 'Site Settings',
    'menu_slug'  => 'ld-site-settings',
    'redirect'   => false,
    'position'   => 58,
    'icon_url'   => 'dashicons-admin-generic',
  ]);

  acf_add_options_sub_page([
    'page_title'  => 'Header & Supermenu',
    'menu_title'  => 'Header',
    'parent_slug' => 'ld-site-settings',
  ]);
  acf_add_options_sub_page([
    'page_title'  => 'Brand & Identity',
    'menu_title'  => 'Brand',
    'parent_slug' => 'ld-site-settings',
  ]);
  acf_add_options_sub_page([
    'page_title'  => 'Contacts & Social',
    'menu_title'  => 'Contacts',
    'parent_slug' => 'ld-site-settings',
  ]);
  acf_add_options_sub_page([
    'page_title'  => 'SEO Defaults',
    'menu_title'  => 'SEO',
    'parent_slug' => 'ld-site-settings',
  ]);
});