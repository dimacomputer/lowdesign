<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ld_require_once_safe')) {
  function ld_require_once_safe(string $file): void {
    if (is_file($file)) require_once $file;
  }
}
if (!function_exists('ld_require_dir')) {
  /**
   * Require all PHP files within a directory in deterministic order.
   *
   * Files are loaded in natural sort order. Files ending in `_first.php`
   * are loaded before other files, while those ending in `_last.php` load
   * after the rest. Any `index.php` file is skipped.
   *
   * @param string $dir Directory path to load.
   * @return void
   */
  function ld_require_dir(string $dir): void {
    if (!is_dir($dir)) return;

    $files = glob(trailingslashit($dir) . '*.php') ?: [];
    sort($files, SORT_NATURAL);

    $first = $last = $regular = [];
    foreach ($files as $file) {
      $base = basename($file);
      if ($base === 'index.php') continue;
      if (substr($base, -10) === '_first.php') {
        $first[] = $file;
      } elseif (substr($base, -9) === '_last.php') {
        $last[] = $file;
      } else {
        $regular[] = $file;
      }
    }

    foreach (array_merge($first, $regular, $last) as $file) {
      require_once $file;
    }
  }
}

