<?php
if (!defined('ABSPATH')) exit;

// inc/extensions/icon-system.php
// Helpers and admin integration for SVG sprite icons.

/** Path to built sprite */
if (!function_exists('ld_sprite_path')) {
  function ld_sprite_path(): string {
    return get_stylesheet_directory() . '/assets/icons/sprite.svg';
  }
}

/** Parse <symbol id="..."> list (full ids, only glyph-* / brand-*) */
if (!function_exists('ld_sprite_choices_full')) {
  function ld_sprite_choices_full(): array {
    static $choices;
    if (isset($choices)) return $choices;

    $file = ld_sprite_path();
    if (!is_file($file)) return [];

    $svg = file_get_contents($file);
    if (!$svg) return [];

    if (preg_match_all('~<symbol[^>]+id="((?:glyph|brand)-[^"]+)"~i', $svg, $m)) {
      $ids = $m[1] ?? [];
      $choices = $ids ? array_combine($ids, $ids) : [];
    } else {
      $choices = [];
    }
    return $choices;
  }
}

/** Hook ACF selects with full sprite ids (and mark wrappers for JS) */
add_filter('acf/load_field/name=menu_icon', function($f){
  $f['choices'] = ld_sprite_choices_full();
  $f['ui'] = 1;
  return $f;
});

add_filter('acf/load_field/name=term_icon_name', function($f){
  $f['choices'] = ld_sprite_choices_full();
  $f['ui'] = 1;
  // mark wrapper for JS toggling
  $f['wrapper']['data-ld'] = 'icon-theme-wrap';
  return $f;
});

add_filter('acf/load_field/name=post_icon_name', function($f){
  $f['choices'] = ld_sprite_choices_full();
  $f['ui'] = 1;
  // помечаем обёртку селекта иконок тем для JS-тоггла
  $f['wrapper']['data-ld'] = 'icon-theme-wrap';
  return $f;
});

add_filter('acf/load_field/name=content_icon_media', function($f){
  // помечаем обёртку медиа-аплоада для JS-тоггла
  if (!isset($f['wrapper']) || !is_array($f['wrapper'])) $f['wrapper'] = [];
  $f['wrapper']['data-ld'] = 'icon-media-wrap';
  return $f;
});

add_filter('acf/load_field/name=term_icon_media', function($f){
  // mark wrapper for JS toggling
  if (!isset($f['wrapper']) || !is_array($f['wrapper'])) $f['wrapper'] = [];
  $f['wrapper']['data-ld'] = 'icon-media-wrap';
  return $f;
});

/** Backfill icon source radio based on existing fields (for older posts/terms) */
add_filter('acf/load_value/name=content_icon_source', function($value, $post_id){
  if ($value) return $value;
  if (!function_exists('get_field')) return 'none';
  if (is_string($post_id) && 0 === strpos($post_id, 'term_')) {
    if (get_field('term_icon_name', $post_id))  return 'sprite';
    if (get_field('term_icon_media', $post_id)) return 'media';
  } else {
    if (get_field('post_icon_name', $post_id))  return 'sprite';
    if (get_field('content_icon_media', $post_id)) return 'media';
  }
  return 'none';
}, 10, 2);

/** Render a content icon based on source selection */
if (!function_exists('ld_content_icon')) {
  /**
   * @param int|null $post_id Post ID (defaults to current post)
   * @param array    $attrs   Extra attributes for SVG/IMG
   */
  function ld_content_icon($post_id = null, array $attrs = []): string {
    if (!function_exists('get_field')) return '';

    $post_id = $post_id ?: get_the_ID();
    if (!$post_id) return '';

    // Base classes + optional color context
    $attr  = $attrs;
    $class = trim($attr['class'] ?? '');
    if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
      $class = trim('icon ' . $class);
    }
    if (!preg_match('/(^|\s)icon--24(\s|$)/', $class)) {
      $class = trim('icon--24 ' . $class);
    }
    if (function_exists('ld_get_page_color_class')) {
      $color_class = ld_get_page_color_class('icon', $post_id);
      if ($color_class) {
        $class = trim($class . ' ' . $color_class);
      }
    }
    $attr['class'] = $class;

    $src = (string) get_field('content_icon_source', $post_id);
    if (!$src) { // backward compat
      $src = get_field('post_icon_name', $post_id) ? 'sprite' :
             (get_field('content_icon_media', $post_id) ? 'media' : 'none');
    }

    switch ($src) {
      case 'sprite':
        $name = (string) get_field('post_icon_name', $post_id);
        return $name && function_exists('ld_icon')
          ? ld_icon($name, $attr, $post_id)
          : '';

      case 'media':
        $id = (int) get_field('content_icon_media', $post_id);
        return ($id && function_exists('ld_image_or_svg_html'))
          ? ld_image_or_svg_html($id, 'full', $attr)
          : '';

      default:
        return '';
    }
  }
}

/** Admin list columns: Posts & custom post types */
foreach (['post','fineart','modeling'] as $pt) {
  add_filter("manage_{$pt}_posts_columns", function($cols) {
    $new = ['icon' => __('Icon','ld')];
    return array_slice($cols, 0, 1, true) + $new + array_slice($cols, 1, null, true);
  });
  add_action("manage_{$pt}_posts_custom_column", function($col, $post_id) {
    if ($col !== 'icon') return;
    if (function_exists('ld_content_icon')) {
      echo ld_content_icon($post_id, ['class' => 'icon icon--24']);
    }
  }, 10, 2);
}

/** Admin list column: Pages (24px default) */
add_filter('manage_page_posts_columns', function($cols) {
  $new = ['icon' => __('Icon','ld')];
  return array_slice($cols, 0, 1, true) + $new + array_slice($cols, 1, null, true);
});
add_action('manage_page_posts_custom_column', function($col, $post_id) {
  if ($col !== 'icon') return;
  echo ld_content_icon($post_id, ['class' => 'icon icon--24']);
}, 10, 2);

/** Inline sprite once per admin page */
add_action('admin_footer', function(){
  static $done; if($done) return; $done = true;
  $p = ld_sprite_path();
  if (is_file($p)) {
    $svg = @file_get_contents($p);
    if ($svg) {
      echo '<div hidden style="display:none" class="ld-admin-sprite">', $svg, '</div>';
    }
  }
});

/** Admin preview assets (CSS/JS) */
add_action('admin_enqueue_scripts', function(){
  // общий css для иконок в админке собран в пайплайне
  wp_enqueue_style('ld-admin-icons', get_stylesheet_directory_uri().'/assets/css/admin-icons.css', [], null);
  // вспомогательные стили превью/селектов
  wp_enqueue_style('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.css', [], null);
  // ensure Select2 is loaded before initializing previews
  wp_enqueue_script('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.js', ['jquery','select2'], null, true);
});

/** Allow SVG uploads for admins (fallback images) */
add_filter('upload_mimes', function($m){
  if(current_user_can('manage_options')) $m['svg'] = 'image/svg+xml';
  return $m;
});

/** Inject icon before menu label (front-end) */
add_filter('walker_nav_menu_start_el', function($out, $item){
  if(!function_exists('get_field') || !function_exists('ld_icon')) return $out;
  $id = (string) get_field('menu_icon', $item->ID);
  if(!$id || $id === 'none') return $out;
  return preg_replace('~(<a[^>]*>)~', '$1' . ld_icon($id, ['class'=>'menu__icon']), $out, 1);
}, 10, 2);

/** Render a term icon, preferring sprite over uploaded image */
if (!function_exists('ld_term_icon_html')) {
  /**
   * @param int|WP_Term|null $term Term ID or object (defaults to queried term on term archives)
   * @param string           $class Extra class names for SVG/IMG (default size is 24px via CSS)
   * @param array            $attrs Extra attributes for the rendered tag
   */
  function ld_term_icon_html($term = null, string $class = '', array $attrs = []): string {
    if (!function_exists('get_field')) return '';

    if (!$term && (is_tax() || is_category() || is_tag())) {
      $term = get_queried_object();
    }

    $term_id = ($term instanceof WP_Term) ? (int) $term->term_id : (int) $term;
    if (!$term_id) return '';

    // 1) sprite (library)
    $icon = (string) get_field('term_icon_name', 'term_'.$term_id);
    if ($icon && $icon !== 'none' && function_exists('ld_icon')) {
      $attr = $attrs;
      $cls = trim(($attr['class'] ?? '') . ' ' . $class);
      if (!preg_match('/(^|\s)icon(\s|$)/', $cls)) {
        $cls = trim('icon ' . $cls);
      }
      if (!preg_match('/(^|\s)icon--24(\s|$)/', $cls)) {
        $cls = trim('icon--24 ' . $cls);
      }
      $attr['class'] = $cls;
      return ld_icon($icon, $attr);
    }

    // 2) uploaded image fallback
    $media = (int) get_field('term_icon_media', 'term_'.$term_id);
    if ($media && function_exists('ld_image_or_svg_html')) {
      $attr = $attrs;
      $cls = trim(($attr['class'] ?? '') . ' ' . $class);
      if (!preg_match('/(^|\s)icon(\s|$)/', $cls)) {
        $cls = trim('icon ' . $cls);
      }
      if (!preg_match('/(^|\s)icon--24(\s|$)/', $cls)) {
        $cls = trim('icon--24 ' . $cls);
      }
      $attr['class'] = $cls;
      return ld_image_or_svg_html($media, 'full', $attr);
    }

    return '';
  }
}

/** Admin list columns: Category & Tag (24px default) */
add_filter('manage_edit-category_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
add_filter('manage_category_custom_column', function($out, $col, $term_id){
  if ($col !== 'icon') return $out;
  $html = ld_term_icon_html($term_id, '', ['class' => 'icon icon--24']);
  return $html ?: '—';
}, 10, 3);

add_filter('manage_edit-post_tag_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
add_filter('manage_post_tag_custom_column', function($out, $col, $term_id){
  if ($col !== 'icon') return $out;
  $html = ld_term_icon_html($term_id, '', ['class' => 'icon icon--24']);
  return $html ?: '—';
}, 10, 3);

/** Consistent sizing/padding for icon column in admin lists (24px + 4px margin) */
add_action('admin_head', function(){
  echo '<style>
      .wp-list-table .column-icon{width:28px}
      .wp-list-table td.column-icon{padding-left:4px;padding-right:0;text-align:center}
      .wp-list-table td.column-icon .icon{width:24px;height:24px;display:inline-block}
    </style>';
});