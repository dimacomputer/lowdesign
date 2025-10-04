<?php
// Unified Site Config helpers (ACF Free + WP core)
if (!defined('ABSPATH')) exit;

// Cache config post ID
if (!function_exists('ld_config_id')) {
  function ld_config_id(): int {
    static $id = null;
    if ($id !== null) return $id;
    $q = get_posts(['post_type' => 'ld_site_config', 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any']);
    $id = !empty($q) ? (int)$q[0] : 0;
    return $id;
  }
}

// Generic option getter from ACF on config post
if (!function_exists('ld_opt')) {
  function ld_opt(string $name, $default = null) {
    if (!function_exists('get_field')) return $default;
    $id = ld_config_id();
    if (!$id) return $default;
    $v = get_field($name, $id);
    return ($v !== null && $v !== '') ? $v : $default;
  }
}

// Core title & tagline (with optional overrides later if needed)
if (!function_exists('ld_site_title')) {
  function ld_site_title(): string {
    return get_bloginfo('name');
  }
}
if (!function_exists('ld_site_tagline')) {
  function ld_site_tagline(): string {
    return get_bloginfo('description');
  }
}

// Return attachment ID of best logo choice: ACF SVG > ACF raster > Theme Custom Logo
if (!function_exists('ld_logo_id')) {
  function ld_logo_id(): int {
    $id = (int) ld_opt('site_logo_svg', 0);
    if ($id) return $id;
    $id = (int) ld_opt('site_logo_raster', 0);
    if ($id) return $id;
    $id = (int) get_theme_mod('custom_logo');
    return $id ?: 0;
  }
}

// Inline SVG or <img>, depending on mime
if (!function_exists('ld_logo_html')) {
  function ld_logo_html(array $attrs = []): string {
    $id = ld_logo_id();
    if (!$id) return '';
    $mime = get_post_mime_type($id);
    $attr_str = '';
    foreach ($attrs as $k => $v) { $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"'; }

    if ($mime === 'image/svg+xml') {
      $file = get_attached_file($id);
      if (!$file || !file_exists($file)) return '';
      $svg = file_get_contents($file);
      // Minimal sanitize: strip script tags
      $svg = preg_replace('~<script[^>]*>.*?</script>~is', '', $svg);
      // Add role/presentational hints
      return '<span class="ld-logo-svg" aria-hidden="true"' . $attr_str . '>' . $svg . '</span>';
    }
    // Raster path
    return wp_get_attachment_image($id, 'full', false, $attrs);
  }
}

// Site icon URL (favicon) from WP core
if (!function_exists('ld_site_icon_url')) {
  function ld_site_icon_url(int $size = 512): string {
    $url = function_exists('get_site_icon_url') ? get_site_icon_url($size) : '';
    if ($url) return $url;
    // fallback: use raster logo if exists
    $rid = (int) ld_opt('site_logo_raster', 0);
    return $rid ? wp_get_attachment_image_url($rid, 'full') : '';
  }
}

// Footer copyright with {Y} macro
if (!function_exists('ld_copyright')) {
  function ld_copyright(): string {
    $raw = (string) ld_opt('site_footer_copyright', '');
    if ($raw === '') {
      return '© ' . date('Y') . ' ' . esc_html(get_bloginfo('name'));
    }
    $out = str_replace('{Y}', date('Y'), $raw);
    return wp_kses_post($out);
  }
}

