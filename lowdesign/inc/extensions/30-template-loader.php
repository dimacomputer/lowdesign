<?php
// inc/extensions/template-loader.php
// Choose page/single template by ACF field "ld_template_slug" stored on the post.
if (!defined('ABSPATH')) exit;

function ld__read_feature($key, $default = true) {
  if (!function_exists('get_field')) return $default;
  $id = 0;
  $q = get_posts(['post_type'=>'ld_config','post_status'=>'any','numberposts'=>1,'fields'=>'ids']);
  if (!empty($q)) $id = (int) $q[0];
  if (!$id) return $default;
  $v = get_field($key, $id);
  return $v === null || $v === '' ? $default : (bool)$v;
}

add_filter('single_template', function($template) {
  if (!ld__read_feature('enable_template_loader', true)) return $template;
  $post = get_queried_object();
  if (!$post || empty($post->ID)) return $template;
  $slug = function_exists('get_field') ? (string)get_field('ld_template_slug', $post->ID) : '';
  if (!$slug) return $template;
  $candidate = locate_template("single-{$slug}.php");
  return $candidate ?: $template;
}, 20);

add_filter('page_template', function($template) {
  if (!ld__read_feature('enable_template_loader', true)) return $template;
  $post = get_queried_object();
  if (!$post || empty($post->ID)) return $template;
  $slug = function_exists('get_field') ? (string)get_field('ld_template_slug', $post->ID) : '';
  if (!$slug) return $template;
  $candidate = locate_template("page-{$slug}.php");
  return $candidate ?: $template;
}, 20);
