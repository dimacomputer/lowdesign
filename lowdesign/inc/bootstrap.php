<?php
if (!defined('ABSPATH')) exit;

/**
 * Lowdesign bootstrap (fail-safe)
 * Порядок:
 * 0) core: vite, i18n, acf-json
 * 1) helpers → cpt → taxonomies → extensions
 * 2) theme setup (supports, logo, menus)
 * 3) assets (frontend, editor, admin-dark)
 * 4) nav & widgets
 */

require_once __DIR__ . '/core/loader.php';

add_action('after_setup_theme', function () {
  $base = get_stylesheet_directory();
  $inc  = $base . '/inc';

  // 0) ядро (безопасно: если файла нет — пропустим)
  ld_require_once_safe("$inc/core/vite.php");    // ld_vite_manifest_path(), ld_vite_asset_uri()
  ld_require_once_safe("$inc/core/i18n.php");    // load_theme_textdomain(...)
  ld_require_once_safe("$inc/core/acf-json.php");// ACF JSON paths
  ld_require_once_safe("$inc/core/custom-logo-svg.php");// Site Logo in Theme

  // 1) кастомные пласты
  ld_require_dir("$inc/helpers");
  ld_require_dir("$inc/cpt");
  ld_require_dir("$inc/taxonomies");
  ld_require_dir("$inc/extensions");

  // 2) возможности темы (supports/logo/menus/html5)
  ld_require_dir("$inc/setup");

  // 3) ассеты
  ld_require_once_safe("$inc/assets/enqueue-frontend.php"); // фронт: bootstrap custom + Vite main.css/js
  ld_require_once_safe("$inc/assets/editor.php");           // стили редактора
  //ld_require_once_safe("$inc/assets/admin-dark.php");       // тёмная админка

  // 4) навигация и виджеты
  ld_require_once_safe("$inc/nav.php");
  ld_require_once_safe("$inc/widgets-footer.php");
}, 1);
