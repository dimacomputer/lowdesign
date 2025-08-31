<?php
/**
 * Lowdesign — clean bootstrap with Vite bundles
 */
if (!defined('ABSPATH')) exit;

define('LD_THEME_DIR', get_stylesheet_directory());
define('LD_THEME_URI', get_stylesheet_directory_uri());

// точка входа
require_once LD_THEME_DIR . '/bootstrap.php';