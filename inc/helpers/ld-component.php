<?php
if (!defined('ABSPATH')) exit;

/**
 * Рендер компонента. Ищет файл сначала в /templates/components/<slug>.php,
 * затем падает обратно на /templates/<slug>.php
 * Пример: ld_component('hero/hero', ['title' => 'Hi'])
 */
if (!function_exists('ld_component')) {
  function ld_component(string $slug, array $args = []): void {
    $slug     = ltrim($slug, '/');
    $base_dir = 'templates/';

    $component_path = get_theme_file_path($base_dir . 'components/' . $slug . '.php');
    $fallback_path  = get_theme_file_path($base_dir . $slug . '.php');

    if (file_exists($component_path)) {
      load_template($component_path, false, $args);
      return;
    }

    if (file_exists($fallback_path)) {
      load_template($fallback_path, false, $args);
      return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('ld_component: component not found: ' . $slug);
    }

    echo '<!-- component not found: ' . esc_html($slug) . ' -->';
  }
}
