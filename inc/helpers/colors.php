<?php
if (!defined('ABSPATH')) exit;

/**
 * Return color class based on Page Color Settings ACF fields.
 *
 * @param string    $target  Target: icon, featured_svg, or primary.
 * @param int|null  $post_id Optional post ID; defaults to current post.
 * @return string   Color class or empty string if not applicable.
 */
if (!function_exists('ld_get_page_color_class')) {
  function ld_get_page_color_class(string $target = 'icon', $post_id = null): string {
    if (!function_exists('get_field')) return '';

    $post_id = $post_id ?: get_the_ID();
    $color   = get_field('page_color', $post_id);
    $targets = get_field('page_color_targets', $post_id) ?: [];

    if (!$color || $color === 'default') return '';
    if (!in_array($target, $targets, true)) return '';

    return 'text-' . $color;
  }
}

