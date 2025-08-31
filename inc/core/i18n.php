<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  load_theme_textdomain('lowdesign', LD_THEME_DIR . '/languages');
}, 1);