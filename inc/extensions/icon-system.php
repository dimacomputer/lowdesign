<?php
if (!defined('ABSPATH')) exit;

// inc/extensions/icon-system.php
// Helpers and admin integration for SVG sprite icons.

// Path to built sprite
if (!function_exists('ld_sprite_path')) {
  function ld_sprite_path(): string {
    return get_stylesheet_directory() . '/assets/icons/sprite.svg';
  }
}

// Parse <symbol id="..."> list (full ids, e.g. "icon-ui-menu")
if (!function_exists('ld_sprite_choices_full')) {
  function ld_sprite_choices_full(): array {
    static $choices;
    if (isset($choices)) return $choices;

    $file = ld_sprite_path();
    if (!is_file($file)) return [];

    $svg = file_get_contents($file);
    if (!$svg) return [];

    if (preg_match_all('~<symbol[^>]+id="([^"]+)"~i', $svg, $m)) {
      $choices = array_combine($m[1], $m[1]);
    } else {
      $choices = [];
    }
    return $choices;
  }
}

// ACF Select loader: use full ids + nice UI
if (!function_exists('ld__sprite_load_field_full')) {
  function ld__sprite_load_field_full($field) {
    $field['choices'] = ld_sprite_choices_full();
    $field['ui'] = 1;
    return $field;
  }
}
add_filter('acf/load_field/name=menu_icon',       'ld__sprite_load_field_full');
add_filter('acf/load_field/name=post_icon_name',  'ld__sprite_load_field_full');
add_filter('acf/load_field/name=term_icon_name',  'ld__sprite_load_field_full');

// Inline sprite into admin so <use href="#id"> works in previews
add_action('admin_footer', function () {
  static $done;
  if ($done) return; // print once per page
  $done = true;

  $file = ld_sprite_path();
  if (!is_file($file)) return;

  $svg = file_get_contents($file);
  if (!$svg) return;

  echo '<div hidden aria-hidden="true" style="display:none" class="ld-admin-sprite">'.$svg.'</div>';
});

// Admin preview assets (CSS/JS)
add_action('admin_enqueue_scripts', function () {
  wp_enqueue_style('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.css', [], null);
  wp_enqueue_script('ld-icon-preview', get_stylesheet_directory_uri().'/assets/admin/icon-preview.js', [], null, true);
});

// Allow SVG uploads for admins (fallback images)
add_filter('upload_mimes', function(array $mimes): array {
  if (current_user_can('manage_options')) {
    $mimes['svg'] = 'image/svg+xml';
  }
  return $mimes;
});

// Inject icon before menu label (front-end)
add_filter('walker_nav_menu_start_el', function ($item_output, $item, $depth, $args) {
  if (!function_exists('get_field') || !function_exists('ld_icon')) return $item_output;

  $id = (string) get_field('menu_icon', $item->ID);
  if (!$id) return $item_output;

  $svg = ld_icon($id, ['class' => 'menu__icon']);

  return preg_replace('/(<a[^>]*>)/', '$1'.$svg, $item_output, 1);
}, 10, 4);

// Render a content icon, preferring sprite over uploaded image
if (!function_exists('ld_content_icon')) {
  /**
   * @param int|null $post_id Post ID (defaults to current post)
   * @param array    $attrs   Extra attributes for SVG/IMG
   */
  function ld_content_icon($post_id = null, array $attrs = []): string {
    if (!function_exists('get_field')) return '';

    $post_id = $post_id ?: get_the_ID();
    if (!$post_id) return '';

    // 1) sprite selection
    $name = (string) get_field('post_icon_name', $post_id);
    if ($name && function_exists('ld_icon')) {
      $attr   = $attrs;
      $class  = trim($attr['class'] ?? '');
      if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
        $class = trim('icon ' . $class);
      }
      $attr['class'] = $class;
      return ld_icon($name, $attr);
    }

    // 2) uploaded media fallback
    $id = (int) get_field('content_icon_media', $post_id);
    if ($id && function_exists('ld_image_or_svg_html')) {
      $attr   = $attrs;
      $class  = trim($attr['class'] ?? '');
      if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
        $class = trim('icon ' . $class);
      }
      $attr['class'] = $class;
      return ld_image_or_svg_html($id, 'full', $attr);
    }

    return '';
  }
}

// Render a term icon, preferring sprite over uploaded image
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
    if ($icon && function_exists('ld_icon')) {
      $attr = $attrs;
      $attr['class'] = trim('icon '.($attr['class'] ?? '').' '.$class);
      return ld_icon($icon, $attr);
    }

    // 2) uploaded image fallback
    $media = (int) get_field('term_icon_media', 'term_'.$term_id);
    if ($media && function_exists('ld_image_or_svg_html')) {
      $attr = $attrs;
      $attr['class'] = trim('icon '.($attr['class'] ?? '').' '.$class);
      return ld_image_or_svg_html($media, 'full', $attr);
    }

    return '';
  }
}

/** Admin list columns: Category & Tag (24px default) */
add_filter('manage_edit-category_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
add_filter('manage_category_custom_column', function($out, $col, $term_id){
  if ($col !== 'icon') return $out;
  $html = ld_term_icon_html($term_id, 'icon--24'); // 24px utility
  return $html ?: '—';
}, 10, 3);

add_filter('manage_edit-post_tag_columns', fn($c) => ['icon' => __('Icon','ld')] + $c);
add_filter('manage_post_tag_custom_column', function($out, $col, $term_id){
  if ($col !== 'icon') return $out;
  $html = ld_term_icon_html($term_id, 'icon--24');
  return $html ?: '—';
}, 10, 3);

// Admin list column: Posts and custom post types
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

// Admin list column: Pages (24px default)
add_filter('manage_page_posts_columns', function($cols) {
  $new = ['icon' => __('Icon','ld')];
  return array_slice($cols, 0, 1, true) + $new + array_slice($cols, 1, null, true);
});
add_action('manage_page_posts_custom_column', function($col, $post_id) {
  if ($col !== 'icon') return;
  echo ld_content_icon($post_id, ['class' => 'icon icon--24']);
}, 10, 2);

// Consistent sizing/padding for icon column in admin lists
add_action('admin_head', function () {
  echo '<style>
  .wp-list-table .column-icon{width:28px}
  .wp-list-table td.column-icon{padding-left:4px;padding-right:0;text-align:center}
  .wp-list-table td.column-icon .icon{width:24px;height:24px;display:inline-block}
  </style>';
});
