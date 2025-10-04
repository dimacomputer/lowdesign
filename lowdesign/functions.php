<?php
/**
 * Lowdesign — core bootstrap (safe)
 */
if (!defined('ABSPATH')) exit;

if (!defined('LD_THEME_DIR')) define('LD_THEME_DIR', get_stylesheet_directory());
if (!defined('LD_THEME_URI')) define('LD_THEME_URI',  get_stylesheet_directory_uri());

/** Точка входа темы */
$bootstrap = LD_THEME_DIR . '/inc/bootstrap.php';
if (is_file($bootstrap)) require_once $bootstrap;
