<?php
if (!defined('ABSPATH')) exit;

add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {
  $classes[] = 'sub-menu';
  return array_unique($classes);
}, 10, 3);