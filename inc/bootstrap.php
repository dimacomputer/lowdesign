<?php
if (!defined('ABSPATH')) exit;

/** Универсальная автозагрузка .php из директории (не рекурсивно) */
function ld_require_dir(string $dir): void {
  if (!is_dir($dir)) return;
  foreach (glob(trailingslashit($dir) . '*.php') as $file) {
    if (basename($file) === 'index.php') continue;
    require_once $file; // файлы только регистрируют хуки/функции, ничего не echo!
  }
}

/** Хелпер для компонентов */
require_once get_stylesheet_directory() . '/inc/helpers/ld_component.php';

/** Порядок: helpers → cpt → taxonomies → extensions */
add_action('after_setup_theme', function () {
  $base = get_stylesheet_directory();
  ld_require_dir($base . '/inc/helpers');
  ld_require_dir($base . '/inc/cpt');
  ld_require_dir($base . '/inc/taxonomies');
  ld_require_dir($base . '/inc/extensions');
});