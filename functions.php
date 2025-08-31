<?php
/**
 * Lowdesign — core bootstrap (safe)
 */
if (!defined('ABSPATH')) exit;

if (!defined('LD_THEME_DIR')) define('LD_THEME_DIR', get_stylesheet_directory());
if (!defined('LD_THEME_URI')) define('LD_THEME_URI',  get_stylesheet_directory_uri());

/** Безопасные инклюды — доступны уже в functions.php */
if (!function_exists('ld_require_once_safe')) {
  function ld_require_once_safe(string $file): void {
    if (is_file($file)) require_once $file;
  }
}
if (!function_exists('ld_require_dir')) {
  function ld_require_dir(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (glob(trailingslashit($dir) . '*.php') as $file) {
      if (basename($file) === 'index.php') continue;
      require_once $file;
    }
  }
}

/** Точка входа темы */
ld_require_once_safe(LD_THEME_DIR . '/inc/bootstrap.php');