<?php
if (!defined('ABSPATH')) exit;

/**
 * Рендер компонента из /components/<slug>.php
 * Пример: ld_component('hero/hero', ['title' => 'Hi'])
 */
if (!function_exists('ld_component')) {
  function ld_component(string $slug, array $args = []): void {
    $path = get_theme_file_path('components/' . ltrim($slug, '/') . '.php');
    if (!file_exists($path)) {
      echo '<!-- component not found: ' . esc_html($slug) . ' -->';
      return;
    }
    load_template($path, false, $args);
  }
}