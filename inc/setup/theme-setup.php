<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', [
    'search-form','comment-form','comment-list','gallery','caption','script','style','navigation-widgets'
  ]);

  add_theme_support('custom-logo', [
    'height'      => 64,
    'width'       => 240,
    'flex-height' => true,
    'flex-width'  => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'lowdesign'),
    'footer'  => __('Footer Menu',  'lowdesign'),
    'utility' => __('Utility Menu', 'lowdesign'),
  ]);
}, 5);