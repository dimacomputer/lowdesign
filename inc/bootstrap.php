<?php
if (!defined('ABSPATH')) exit;

/** Инклюд одного файла, безопасно */
function ld_require_once_safe(string $file): void {
  if (is_file($file)) require_once $file;
}

/** Инклюд всех php в папке (кроме index.php), с поддержкой цифровых префиксов */
function ld_require_dir(string $dir): void {
  if (!is_dir($dir)) return;

  $files = glob(trailingslashit($dir) . '*.php');
  if (!$files) return;

  // Сортируем так, чтобы префиксы "00-", "10-" шли по порядку
  usort($files, function ($a, $b) {
    return strcmp(basename($a), basename($b));
  });

  foreach ($files as $file) {
    if (basename($file) === 'index.php') continue;
    require_once $file;
  }
}

/**
 * Порядок загрузки (директории):
 * 0) core        → vite, i18n, acf-json
 * 1) helpers     → полезные функции
 * 2) cpt         → типы записей
 * 3) taxonomies  → таксономии
 * 4) extensions  → любые расширения
 * 5) setup       → supports/logo/menus/html5
 * 6) assets      → фронт-ассеты, редактор, admin-dark
 * 7) ui          → навигация, виджеты и пр.
 */
add_action('after_setup_theme', function () {
  $base = get_stylesheet_directory() . '/inc';

  ld_require_dir($base . '/core');
  ld_require_dir($base . '/helpers');
  ld_require_dir($base . '/cpt');
  ld_require_dir($base . '/taxonomies');
  ld_require_dir($base . '/extensions');
  ld_require_dir($base . '/setup');
  ld_require_dir($base . '/assets');
  ld_require_dir($base . '/ui');
}, 1);