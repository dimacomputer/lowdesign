<?php
if (!defined('ABSPATH')) exit;

/**
 * Dark mode for wp-admin & login — авто по системе (prefers-color-scheme)
 * Ожидает сборку Vite: assets/src/scss/admin-dark.scss → build/...
 */
add_action('admin_enqueue_scripts', function () {
  if (function_exists('ld_vite_asset_uri')) {
    if ($css = ld_vite_asset_uri('assets/src/scss/admin-dark.scss')) {
      wp_enqueue_style('ld-admin-dark', $css, [], null, 'screen');
    }
  }
}, 20);

/** Подключим те же стили и на /wp-login.php */
add_action('login_enqueue_scripts', function () {
  if (function_exists('ld_vite_asset_uri')) {
    if ($css = ld_vite_asset_uri('assets/src/scss/admin-dark.scss')) {
      wp_enqueue_style('ld-admin-dark-login', $css, [], null, 'screen');
    }
  }
}, 20);