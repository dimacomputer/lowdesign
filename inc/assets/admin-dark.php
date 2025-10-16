<?php
if (!defined('ABSPATH')) exit;

/**
 * Auto Dark Mode for wp-admin & login (по системной настройке).
 * Грузим CSS ПОСЛЕДНИМ и после базовых admin-стилей.
 * Ожидается ключ в manifest: 'assets/src/scss/admin-dark.scss'
 */

add_action('admin_enqueue_scripts', function () {
  if (!function_exists('ld_vite_asset_uri')) return;

  $css = ld_vite_asset_uri('assets/src/scss/admin-dark.scss');
  if (!$css) return;

  // грузим в самом конце очереди
  wp_enqueue_style('ld-admin-dark', $css, [], null, 'screen');

  // просим поставить ссылку ПОСЛЕ базовых стилей админки
  // (эти хендлы есть в /wp-admin/load-styles.php)
  wp_style_add_data('ld-admin-dark', 'after', [
    'wp-admin', 'colors', 'buttons', 'forms', 'common', 'list-tables'
  ]);
}, 999);

/** То же для страницы логина */
add_action('login_enqueue_scripts', function () {
  if (!function_exists('ld_vite_asset_uri')) return;

  $css = ld_vite_asset_uri('assets/src/scss/admin-dark.scss');
  if (!$css) return;

  wp_enqueue_style('ld-admin-dark-login', $css, [], null, 'screen');
  wp_style_add_data('ld-admin-dark-login', 'after', ['login', 'forms', 'buttons']);
}, 999);