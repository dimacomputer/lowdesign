<?php
/* Template Name: Front Page */
get_header(); ?>

<div class="container py-5">
  <?php ld_component('hero/hero', ['title' => get_bloginfo('name'), 'subtitle' => get_bloginfo('description')]); ?>
</div>

<?php get_footer(); ?>