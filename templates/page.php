<?php
/* Template Name: Default Page */
get_header(); ?>

<div class="container py-5">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article <?php post_class(); ?>>
      <h1 class="mb-4"><?php the_title(); ?></h1>
      <div class="entry"><?php the_content(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>