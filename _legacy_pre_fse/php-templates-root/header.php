<?php

/* @ld-tags: core, header, nav, supermenu */

if (!defined('ABSPATH')) exit;
?><!doctype html>
<?php
  $ld_html_classes = [];
  if (function_exists("ld_theme_context")) {
    $t = ld_theme_context();
    $ld_html_classes[] = "ld-theme-" . sanitize_html_class($t["mode"]); // auto|light|dark
    if (!empty($t["chroma"])) $ld_html_classes[] = "ld-chroma-" . sanitize_html_class($t["chroma"]);
    if (!empty($t["highlight"])) $ld_html_classes[] = "ld-highlight-" . sanitize_html_class($t["highlight"]);
    if (!empty($t["color"])) $ld_html_classes[] = "ld-color-" . sanitize_html_class($t["color"]);
    if (!empty($t["locked"])) $ld_html_classes[] = "ld-theme-locked";
  }
?>
<html <?php language_attributes(); ?> class="<?php echo esc_attr(implode(' ', $ld_html_classes)); ?>">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <div class="container py-3 d-flex align-items-center gap-3">
    <a class="site-logo text-decoration-none fw-bold" href="<?php echo esc_url(home_url('/')); ?>">
      <?php bloginfo('name'); ?>
    </a>

    <?php if (has_nav_menu('primary')): ?>
      <nav class="ms-auto">
        <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'nav','container'=>false]); ?>
      </nav>
    <?php endif; ?>
  </div>
</header>
<main class="site-main">