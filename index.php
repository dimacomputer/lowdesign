<?php get_header(); ?>
<div class="container py-5">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article <?php post_class('mb-5'); ?>>
      <h1 class="h3">
        <a href="<?php the_permalink(); ?>">
          <?php if (function_exists('ld_content_icon')) echo ld_content_icon(null, ['class' => 'icon icon--24 me-2']); ?>
          <?php the_title(); ?>
        </a>
      </h1>
      <div class="entry"><?php the_excerpt(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>