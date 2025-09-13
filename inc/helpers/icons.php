<?php
if (!defined('ABSPATH')) exit;

/**
 * Output icon from SVG sprite.
 *
 * @param string|null $name Symbol ID, e.g. "icon-ui-chevron".
 * @param array  $attrs Extra attributes for <svg>.
 *
 * @return string SVG markup.
 */
if (!function_exists('ld_icon')) {
  function ld_icon(?string $name, array $attrs = []): string {
    if ($name === null || $name === '' || $name === 'none') {
      return '';
    }

    $class = trim($attrs['class'] ?? '');
    if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
      $class = trim('icon ' . $class);
    }
    $attrs = array_merge(['class' => $class, 'aria-hidden' => 'true'], $attrs);

    $attributes = '';
    foreach ($attrs as $key => $value) {
      $attributes .= ' ' . $key . '="' . esc_attr($value) . '"';
    }

    return '<svg' . $attributes . '><use href="#' . esc_attr($name) . '"></use></svg>';
  }
}

/**
 * Return HTML for an image attachment or inline SVG.
 * Strips any <script> tags from SVGs for safety.
 *
 * @param int          $attachment_id Attachment ID.
 * @param string|array $size          Image size for raster images.
 * @param array        $attr          Additional attributes.
 *
 * @return string Image or SVG HTML.
 */
if (!function_exists('ld_image_or_svg_html')) {
  function ld_image_or_svg_html(int $attachment_id, $size = 'full', array $attr = []): string {
    $mime = get_post_mime_type($attachment_id);

    if ($mime === 'image/svg+xml') {
      $file = get_attached_file($attachment_id);
      if (!$file || !file_exists($file)) {
        return '';
      }

      $svg = file_get_contents($file);
      $svg = preg_replace('#<script[^>]*>.*?</script>#is', '', $svg);

      if ($attr) {
        $extra = '';
        foreach ($attr as $key => $value) {
          $extra .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        $svg = preg_replace('/^<svg\b([^>]*)>/', '<svg$1' . $extra . '>', $svg, 1);
      }

      return $svg;
    }

    return wp_get_attachment_image($attachment_id, $size, false, $attr);
  }
}
