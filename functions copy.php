<?php

/** ----------------------------------------------------------------
 *  Admin уведомление: нет сборки (manifest.json от Vite)
 * ---------------------------------------------------------------- */
add_action('admin_notices', function () {
  if (!current_user_can('manage_options')) return;
  if (!ld_vite_manifest_path()) {
    echo '<div class="notice notice-warning"><p><strong>Lowdesign Margo:</strong> не найден <code>build/manifest.json</code> (или <code>build/.vite/manifest.json</code>). Собери ассеты локально и закоммить в тему.</p></div>';
  }
});

/** ----------------------------------------------------------------
 *  Helpers для manifest.json (Vite 5 может писать в .vite/)
 * ---------------------------------------------------------------- */
function ld_vite_manifest_path(): ?string {
  $base = LD_THEME_DIR . '/build';
  foreach ([$base.'/manifest.json', $base.'/.vite/manifest.json'] as $p) {
    if (file_exists($p)) return $p;
  }
  return null;
}

/** Получить публичный URI ассета по entry имени из manifest.json */
function ld_vite_asset_uri(string $entry): ?string {
  $manifest = ld_vite_manifest_path();
  if (!$manifest) return null;
  $data = json_decode(file_get_contents($manifest), true);
  if (!is_array($data) || empty($data[$entry]['file'])) return null;
  $rel = ltrim($data[$entry]['file'], '/');
  return LD_THEME_URI . '/build/' . $rel;
}

/** ----------------------------------------------------------------
 *  Подключение ассетов темы
 *  - 1) Кастомный Bootstrap (если положен в /assets/vendor/bootstrap/custom.css)
 *  - 2) CSS/JS из Vite (твои main.scss и main.js)
 * ---------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
  // 1) Готовый Bootstrap (опционально)
  $vendor_rel = '/assets/vendor/bootstrap/custom.css';
  $vendor_abs = LD_THEME_DIR . $vendor_rel;
  if (file_exists($vendor_abs)) {
    wp_enqueue_style('ld-bootstrap-custom', LD_THEME_URI . $vendor_rel, [], filemtime($vendor_abs));
  }

  // 2) CSS/JS из Vite
  if ($css = ld_vite_asset_uri('assets/src/scss/main.scss')) {
    wp_enqueue_style('ld-main', $css, file_exists($vendor_abs) ? ['ld-bootstrap-custom'] : [], null);
  }
  if ($js = ld_vite_asset_uri('assets/src/js/main.js')) {
    wp_enqueue_script('ld-main', $js, [], null, true);
  }
}, 20);

/** ----------------------------------------------------------------
 *  Стили в редактор (Гутенберг)
 * ---------------------------------------------------------------- */
add_action('enqueue_block_editor_assets', function () {
  if ($css = ld_vite_asset_uri('assets/src/scss/main.scss')) {
    wp_enqueue_style('ld-editor', $css, [], null);
  }
});

/** ----------------------------------------------------------------
 *  Локализация строк
 * ---------------------------------------------------------------- */
add_action('after_setup_theme', function () {
  load_theme_textdomain('lowdesign-margo', LD_THEME_DIR . '/languages');
});

/** ----------------------------------------------------------------
 *  ACF JSON (Local JSON в репозитории)
 * ---------------------------------------------------------------- */
add_filter('acf/settings/save_json', fn()=> LD_THEME_DIR . '/acf-json');
add_filter('acf/settings/load_json', function($paths){
  $paths[] = LD_THEME_DIR . '/acf-json';
  return $paths;
});

/** ----------------------------------------------------------------
 *  Базовые фичи темы + меню + логотип
 *  - custom-logo нужен для современного хедера
 *  - добавил меню Utility (по желанию; можно скрыть, если не нужно)
 * ---------------------------------------------------------------- */
add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','script','style','navigation-widgets']);

  // Логотип для хедера (гибкие размеры)
  add_theme_support('custom-logo', [
    'height'      => 64,
    'width'       => 240,
    'flex-height' => true,
    'flex-width'  => true,
  ]);

  // Меню
  register_nav_menus([
    'primary' => __('Primary Menu', 'lowdesign-margo'),
    'footer'  => __('Footer Menu',  'lowdesign-margo'),
    'utility' => __('Utility Menu', 'lowdesign-margo'), // маленькие ссылки/язык/вход
  ]);
});

/** ----------------------------------------------------------------
 *  Виджеты футера: 3 управляемые колонки (Appearance → Widgets)
 * ---------------------------------------------------------------- */
add_action('widgets_init', function () {
  for ($i = 1; $i <= 3; $i++) {
    register_sidebar([
      'name'          => sprintf(__('Footer %d', 'lowdesign-margo'), $i),
      'id'            => 'footer-' . $i,
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget-title">',
      'after_title'   => '</h3>',
    ]);
  }
});

/** ----------------------------------------------------------------
 *  Небольшой хелпер под супер-меню
 *  (без кастомного walker: ставим класс `is-mega` у пункта верхнего уровня)
 * ---------------------------------------------------------------- */
add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {
  $classes[] = 'sub-menu'; // оставляем единый класс — стилируем в CSS
  return array_unique($classes);
}, 10, 3);

/** ----------------------------------------------------------------
 *  Точка входа инкрементов/инклюдов темы
 * ---------------------------------------------------------------- */
require_once LD_THEME_DIR . '/inc/bootstrap.php';