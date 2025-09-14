<?php
if (!defined('ABSPATH')) exit;

function ld_icons_features(): array {
  static $cache; if (isset($cache)) return $cache;
  $cfg = ['content'=>true,'terms'=>true,'menu'=>true]; // defaults ON

  $post = get_posts([
    'post_type'      => 'ld_site_config',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
  ]);
  $pid = $post ? (int)$post[0]->ID : 0;

  if ($pid && function_exists('get_field')) {
    $getb = function(string $name, bool $def=true) use ($pid) {
      $v = get_field($name, $pid);
      return ($v !== null) ? (bool)$v : $def;
    };
    $cfg['content'] = $getb('enable_content_icons', true);
    $cfg['terms']   = $getb('enable_term_icons', true);
    $cfg['menu']    = $getb('enable_menu_icons', true);
  }
  return $cache = $cfg;
}
