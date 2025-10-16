<?php

/* @ld-tags: core, header, nav, supermenu */

if (!defined('ABSPATH')) exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
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