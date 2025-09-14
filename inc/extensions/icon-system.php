<?php
if (!defined('ABSPATH')) exit;

/**
 * Icon System — admin/front integration with Site Config toggles.
 */

// ---- Site Config feature flags ---------------------------------------------

$ld_icons_cfg = function_exists('ld_icons_features')
  ? ld_icons_features()
  : ['content'=>true,'terms'=>true,'menu'=>true];
$ld_acf_available = function_exists('acf_add_local_field_group');

if ($ld_acf_available) {
  add_filter('acf/prepare_field/key=group_post_icon', fn($f)=>$ld_icons_cfg['content']?$f:false);
  add_filter('acf/prepare_field/key=group_term_icon', fn($f)=>$ld_icons_cfg['terms']?$f:false);
  add_filter('acf/prepare_field/key=group_menu_icon', fn($f)=>$ld_icons_cfg['menu']?$f:false);

  add_filter('acf/prepare_field/name=post_icon', fn($f)=>$ld_icons_cfg['content']?$f:false);
  add_filter('acf/prepare_field/name=term_icon', fn($f)=>$ld_icons_cfg['terms']?$f:false);
  add_filter('acf/prepare_field/name=menu_icon', fn($f)=>$ld_icons_cfg['menu']?$f:false);
}

// ---- Sprite helpers ---------------------------------------------------------

/** Path to built sprite */
if (!function_exists('ld_sprite_path')) {
  function ld_sprite_path(): string {
    return get_stylesheet_directory() . '/assets/icons/sprite.svg';
  }
}

/** Parse <symbol id="..."> list (keep only glyph-* / brand-*) */
if (!function_exists('ld_sprite_choices_full')) {
  function ld_sprite_choices_full(): array {
    static $choices;
    if (isset($choices)) return $choices;

    $file = ld_sprite_path();
    if (!is_file($file)) return [];

    $svg = file_get_contents($file);
    if (!$svg) return [];

    if (preg_match_all('~<symbol[^>]+id="([^"]+)"~i', $svg, $m)) {
      $ids = array_filter($m[1] ?? [], fn($id) =>
        str_starts_with($id, 'glyph-') || str_starts_with($id, 'brand-')
      );
      $choices = $ids ? array_combine($ids, $ids) : [];
    } else {
      $choices = [];
    }
    return $choices;
  }
}

// ---- ACF field wiring (choices + wrappers for JS) ---------------------------

if ($ld_icons_cfg['menu'] || $ld_icons_cfg['content'] || $ld_icons_cfg['terms']) {
  $load = function($f){
    $f['choices'] = ld_sprite_choices_full();
    $f['ui'] = 1;
    return $f;
  };

  if ($ld_icons_cfg['menu']) {
    add_filter('acf/load_field/name=menu_icon', $load);
  }

  if ($ld_icons_cfg['content']) {
    add_filter('acf/load_field/name=post_icon_name', function($f) use ($load){
      $f = $load($f);
      // обёртка селекта «Theme icon» для JS-тоггла
      $f['wrapper']['data-ld'] = 'icon-theme-wrap';
      return $f;
    });
    add_filter('acf/load_field/name=content_icon_media', function($f){
      // обёртка блока «Media Library» для JS-тоггла
      if (!isset($f['wrapper']) || !is_array($f['wrapper'])) $f['wrapper'] = [];
      $f['wrapper']['data-ld'] = 'icon-media-wrap';
      return $f;
    });
  }

  if ($ld_icons_cfg['terms']) {
    add_filter('acf/load_field/name=term_icon_name', $load);
  }

  // Inline sprite + admin assets
  add_action('admin_footer', function(){
    static $done; if($done) return; $done = true;
    $p = ld_sprite_path();
    if (is_file($p)) {
      $svg = @file_get_contents($p);
      if ($svg) echo '<div hidden style="display:none" class="ld-admin-sprite">', $svg, '</div>';
    }
  });

  add_action('admin_enqueue_scripts', function(){
    wp_enqueue_style('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.css', [], null);
    wp_enqueue_script('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.js', ['jquery','select2'], null, true);
  });
}

// ---- Backfill radio (BC for old posts) --------------------------------------

if ($ld_icons_cfg['content']) {
  add_filter('acf/load_value/name=content_icon_source', function($value, $post_id){
    if ($value) return $value;
    if (function_exists('get_field')) {
      if (get_field('post_icon_name', $post_id))   return 'sprite';
      if (get_field('content_icon_media', $post_id)) return 'media';
    }
    return 'none';
  }, 10, 2);
}

// ---- Upload permissions ------------------------------------------------------

if ($ld_icons_cfg['content'] || $ld_icons_cfg['terms']) {
  add_filter('upload_mimes', function($m){
    if(current_user_can('manage_options')) $m['svg'] = 'image/svg+xml';
    return $m;
  });
}

// ---- Front-end: menu icons ---------------------------------------------------

add_filter('walker_nav_menu_start_el', function($out, $item) use ($ld_icons_cfg){
  if (empty($ld_icons_cfg['menu'])) return $out;
  if(!function_exists('get_field') || !function_exists('ld_icon')) return $out;
  $id = (string) get_field('menu_icon', $item->ID);
  if(!$id || $id === 'none') return $out;
  return preg_replace('~(<a[^>]*>)~', '$1' . ld_icon($id, ['class'=>'menu__icon']), $out, 1);
}, 10, 2);

// ---- Content icon (posts/pages) ---------------------------------------------

if (!function_exists('ld_content_icon')) {
  /**
   * @param int|null $post_id Post ID (defaults to current post)
   * @param array    $attrs   Extra attributes for SVG/IMG
   */
  function ld_content_icon($post_id = null, array $attrs = []): string {
    if (!function_exists('get_field')) return '';

    $post_id = $post_id ?: get_the_ID();
    if (!$post_id) return '';

    $color_class = function_exists('ld_get_page_color_class') ? ld_get_page_color_class('icon', $post_id) : '';

    $src = (string) get_field('content_icon_source', $post_id);
    if (!$src) { // backward compat
      $src = get_field('post_icon_name', $post_id) ? 'sprite' :
             (get_field('content_icon_media', $post_id) ? 'media' : 'none');
    }

    $attr  = $attrs;
    $class = trim($attr['class'] ?? '');
    if (!preg_match('/(^|\s)icon(\s|$)/', $class)) $class = trim('icon ' . $class);
    if ($color_class) $class = trim($class . ' ' . $color_class);
    $attr['class'] = $class;

    switch ($src) {
      case 'sprite':
        $name = (string) get_field('post_icon_name', $post_id);
        if ($name && function_exists('ld_icon')) return ld_icon($name, $attr);
        break;
      case 'media':
        $id = (int) get_field('content_icon_media', $post_id);
        if ($id && function_exists('ld_image_or_svg_html')) return ld_image_or_svg_html($id, 'full', $attr);
        break;
      default:
        return '';
    }
    return '';
  }
}

// ---- Term icon (categories/tags) --------------------------------------------

if (!function_exists('ld_term_icon_html')) {
  /**
   * @param int|WP_Term|null $term
   * @param string           $class
   * @param array            $attrs
   */
  function ld_term_icon_html($term = null, string $class = '', array $attrs = []): string {
    if (!function_exists('get_field')) return '';

    if (!$term && (is_tax() || is_category() || is_tag())) $term = get_queried_object();
    $term_id = ($term instanceof WP_Term) ? (int) $term->term_id : (int) $term;
    if (!$term_id) return '';

    // 1) sprite
    $icon = (string) get_field('term_icon_name', 'term_'.$term_id);
    if ($icon && $icon !== 'none' && function_exists('ld_icon')) {
      $attr = $attrs; $attr['class'] = trim('icon '.($attr['class'] ?? '').' '.$class);
      return ld_icon($icon, $attr);
    }

    // 2) media fallback
    $media = (int) get_field('term_icon_media', 'term_'.$term_id);
    if ($media && function_exists('ld_image_or_svg_html')) {
      $attr = $attrs; $attr['class'] = trim('icon '.($attr['class'] ?? '').' '.$class);
      return ld_image_or_svg_html($media, 'full', $attr);
    }

    return '';
  }
}

// ---- Admin list columns (24px) ----------------------------------------------

if ($ld_icons_cfg['terms']) {
  add_filter('manage_edit-category_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
  add_filter('manage_category_custom_column', function($out, $col, $term_id){
    if ($col !== 'icon') return $out;
    $html = ld_term_icon_html($term_id, 'icon--24');
    return $html ?: '—';
  }, 10, 3);

  add_filter('manage_edit-post_tag_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
  add_filter('manage_post_tag_custom_column', function($out, $col, $term_id){
    if ($col !== 'icon') return $out;
    $html = ld_term_icon_html($term_id, 'icon--24');
    return $html ?: '—';
  }, 10, 3);
}

if ($ld_icons_cfg['content']) {
  foreach (['post','fineart','modeling'] as $pt) {
    add_filter("manage_{$pt}_posts_columns", function($cols) {
      $new = ['icon' => __('Icon','ld')];
      return array_slice($cols, 0, 1, true) + $new + array_slice($cols, 1, null, true);
    });
    add_action("manage_{$pt}_posts_custom_column", function($col, $post_id) {
      if ($col !== 'icon') return;
      if (function_exists('ld_content_icon')) echo ld_content_icon($post_id, ['class' => 'icon icon--24']);
    }, 10, 2);
  }
  add_filter('manage_page_posts_columns', function($cols) {
    $new = ['icon' => __('Icon','ld')];
    return array_slice($cols, 0, 1, true) + $new + array_slice($cols, 1, null, true);
  });
  add_action('manage_page_posts_custom_column', function($col, $post_id) {
    if ($col !== 'icon') return;
    echo ld_content_icon($post_id, ['class' => 'icon icon--24']);
  }, 10, 2);
}

// 24px + 4px margin layout for admin tables (if any icon feature is on)
if ($ld_icons_cfg['content'] || $ld_icons_cfg['terms']) {
  add_action('admin_head', function(){
    echo '<style>
      .wp-list-table .column-icon{width:28px}
      .wp-list-table td.column-icon{padding-left:4px;padding-right:0;text-align:center}
      .wp-list-table td.column-icon .icon{width:24px;height:24px;display:inline-block}
    </style>';
  });
}