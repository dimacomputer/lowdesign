<?php
// inc/extensions/template-loader.php
if (!defined('ABSPATH')) exit;

/**
 * Позволяет выбрать шаблон через ACF-поле:
 *   - для single постов: поле `ld_template_slug` (например, "case", "profile")
 *   - для страниц:       поле `ld_template_slug` (например, "landing", "docs")
 *
 * Будет искать:
 *   single-{slug}.php  /  page-{slug}.php
 * Если не найден — падает на дефолт.
 *
 * Управляется тумблером: enable_template_loader (Options → Features)
 */

// общий хелпер чтения тумблера
function ld_feature_enabled($key, $default = true) {
  return function_exists('get_field')
    ? (bool) get_field($key, 'option')
    : $default;
}

// Single template
add_filter('single_template', function($template) {
  if (!ld_feature_enabled('enable_template_loader', true)) return $template;
  $post = get_queried_object();
  if (!$post || empty($post->ID)) return $template;

  // читаем slug из ACF
  $slug = function_exists('get_field') ? (string) get_field('ld_template_slug', $post->ID) : '';
  if (!$slug) return $template;

  $candidate = locate_template("single-{$slug}.php");
  return $candidate ?: $template;
}, 20);

// Page template
add_filter('page_template', function($template) {
  if (!ld_feature_enabled('enable_template_loader', true)) return $template;
  $post = get_queried_object();
  if (!$post || empty($post->ID)) return $template;

  $slug = function_exists('get_field') ? (string) get_field('ld_template_slug', $post->ID) : '';
  if (!$slug) return $template;

  $candidate = locate_template("page-{$slug}.php");
  return $candidate ?: $template;
}, 20);