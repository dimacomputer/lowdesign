<?php
if (!defined('ABSPATH')) exit;

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

