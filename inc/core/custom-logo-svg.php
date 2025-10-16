<?php
if (!defined('ABSPATH')) exit;

/** Включаем поддержку кастомного логотипа */
add_action('after_setup_theme', function () {
  add_theme_support('custom-logo', [
    'flex-width'  => true,
    'flex-height' => true,
    'unlink-homepage-logo' => true,
  ]);
});

/**
 * Если логотип — SVG, подменяем стандартный HTML и выводим IN-LINE,
 * чтобы работали currentColor/масштабирование.
 */
add_filter('get_custom_logo', function ($html) {
  $id = get_theme_mod('custom_logo');
  if (!$id) return $html;

  $mime = get_post_mime_type($id);
  if ($mime !== 'image/svg+xml') return $html;

  $file = get_attached_file($id);
  if (! $file || ! file_exists($file)) return $html;

  $svg = file_get_contents($file);
  if (! $svg) return $html;

  // добавим класс, если нет
  $svg = preg_replace('/<svg\b(?![^>]*class=)/', '<svg class="custom-logo" ', $svg, 1);

  $home = esc_url(home_url('/'));
  $label = esc_attr(get_bloginfo('name'));

  return sprintf(
    '<a href="%s" class="custom-logo-link" rel="home" aria-label="%s">%s</a>',
    $home, $label, $svg
  );
});