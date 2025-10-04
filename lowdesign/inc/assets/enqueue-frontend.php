<?php
if (!defined('ABSPATH')) exit;

/**
 * Фронтовые ассеты:
 * 1) Кастомный Bootstrap (если есть /assets/vendor/bootstrap/custom.css)
 * 2) Vite-бандлы main.scss / main.js из manifest.json
 *
 * Зависимости:
 * - функции ld_vite_asset_uri() и ld_vite_manifest_path() из inc/vite.php
 */
add_action('wp_enqueue_scripts', function () {
  // 1) Кастомный Bootstrap (опционально)
  $vendor_rel = '/assets/vendor/bootstrap/custom.css';
  $vendor_abs = LD_THEME_DIR . $vendor_rel;

  if (file_exists($vendor_abs)) {
    wp_enqueue_style(
      'ld-bootstrap-custom',
      LD_THEME_URI . $vendor_rel,
      [],
      filemtime($vendor_abs)
    );
  }

  // 2) CSS/JS из Vite (manifest.json или .vite/manifest.json)
  if (function_exists('ld_vite_asset_uri')) {
    if ($css = ld_vite_asset_uri('assets/src/scss/main.scss')) {
      wp_enqueue_style(
        'ld-main',
        $css,
        file_exists($vendor_abs) ? ['ld-bootstrap-custom'] : [],
        null
      );
    }

    if ($js = ld_vite_asset_uri('assets/src/js/main.js')) {
      wp_enqueue_script('ld-main', $js, [], null, true);
    }
  }
}, 20);