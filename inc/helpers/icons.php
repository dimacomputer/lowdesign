<?php
if (!defined('ABSPATH')) exit;

/**
 * Output icon from SVG sprite.
 *
 * @param string    $name    Symbol ID, e.g. "icon-ui-chevron".
 * @param array     $attrs   Extra attributes for <svg>.
 * @param int|null  $post_id Optional post ID for color context.
 * @return string   SVG markup.
 */
if (!function_exists('ld_icon')) {
  function ld_icon(string $name, array $attrs = [], $post_id = null): string {
    // Support "no icon" (empty / 'none')
    if ($name === '' || $name === 'none') return '';

    // Ensure base class ".icon"
    $class = trim($attrs['class'] ?? '');
    if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
      $class = trim('icon ' . $class);
    }

    // Optional color context from page
    if (function_exists('ld_get_page_color_class')) {
      $color_class = ld_get_page_color_class('icon', $post_id);
      if ($color_class) {
        $class = trim($class . ' ' . $color_class);
      }
    }

    // Finalize attributes
    $attrs['class'] = $class;
    $attrs['aria-hidden'] = $attrs['aria-hidden'] ?? 'true';
    unset($attrs['fill']); // color via CSS

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
      if (!$file || !file_exists($file)) return '';

      $svg = file_get_contents($file);

      // sanitize + unify fill
      $svg = preg_replace('#<script[^>]*>.*?</script>#is', '', $svg);
      $svg = preg_replace('/\sfill=["\'][^"\']*["\']/i', '', $svg);
      $svg = preg_replace('/^<svg\b([^>]*)>/', '<svg$1 fill="currentColor">', $svg, 1);

      // merge extra attributes (except 'fill', already set)
      if ($attr) {
        $extra = '';
        foreach ($attr as $key => $value) {
          if ($key === 'fill') continue;
          $extra .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        $svg = preg_replace('/^<svg\b([^>]*)>/', '<svg$1' . $extra . '>', $svg, 1);
      }

      return $svg;
    }

    return wp_get_attachment_image($attachment_id, $size, false, $attr);
  }
}

/**
 * Filter: wrap featured SVG with color class if page_color targets it
 */
add_filter('post_thumbnail_html', function ($html, $post_id, $thumb_id, $size, $attr) {
  if (!function_exists('ld_get_page_color_class') || !$html) return $html;
  if (get_post_mime_type($thumb_id) !== 'image/svg+xml') return $html;

  $class = ld_get_page_color_class('featured_svg', $post_id);
  if (!$class) return $html;

  $html = preg_replace('/\sfill=["\'][^"\']*["\']/i', '', $html);
  $html = preg_replace('/^<svg\b([^>]*)>/', '<svg$1 fill="currentColor">', $html, 1);

  return '<div class="' . esc_attr($class) . '">' . $html . '</div>';
}, 10, 5);