<?php
if (!defined('ABSPATH')) exit;

/**
 * LowDesign — Render filter for section theme scope
 *
 * Converts block style class:
 *   is-style-ld-theme-<key>
 * into attributes on the wrapper element:
 *   data-ld-theme="<key>"
 *   data-bs-theme="light|dark" (sync)
 *
 * Requires WP_HTML_Tag_Processor (WP 6.2+).
 */

function ld_bs_theme_from_ld(string $k): string
{
  if ($k === 'dark' || str_ends_with($k, '-dark')) return 'dark';
  return 'light';
}

add_filter('render_block', function ($content, $block) {
  $name = $block['blockName'] ?? '';
  if ($name !== 'core/group') return $content;

  $class = $block['attrs']['className'] ?? '';
  if (!$class) return $content;

  if (!preg_match('/\bis-style-ld-theme-([a-z0-9-]+)\b/', $class, $m)) return $content;
  $theme = sanitize_key($m[1]);

  if (!class_exists('WP_HTML_Tag_Processor')) return $content;

  $p = new WP_HTML_Tag_Processor($content);
  if ($p->next_tag()) {
    $p->set_attribute('data-ld-theme', $theme);
    $p->set_attribute('data-bs-theme', ld_bs_theme_from_ld($theme));
    return $p->get_updated_html();
  }
  return $content;
}, 20, 2);
