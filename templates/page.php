<?php
/* Template Name: Default Page */
get_header(); ?>

<div class="container py-5">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article <?php post_class(); ?>>
      <h1 class="mb-4"><?php the_title(); ?></h1>
      <div class="entry"><?php the_content(); ?></div>
      <?php echo render_svg_inline_icon('brand/lowdesign_logo', 'icon icon-lg text-primary'); ?>
    </article>
  <?php endwhile; endif; ?>
</div>


// Мини-паттерны использования в шаблонах

<?php if (ld_get_option('enable_supermenu')): ?>
  <?php get_template_part('partials/header/supermenu'); ?>
<?php endif; ?>

<?php
$logo_svg = ld_get_option('site_logo_svg');
if ($logo_svg) {
  echo $logo_svg; // уже inline SVG
} else {
  $logo_raster = ld_get_option('site_logo_raster');
  if ($logo_raster) echo wp_get_attachment_image($logo_raster, 'full', false, ['class'=>'site-logo']);
}
?>

<?php
$default_title = ld_get_option('default_meta_title', get_bloginfo('name'));
$default_desc  = ld_get_option('default_meta_description', get_bloginfo('description'));
// В head:
echo '<meta name="description" content="'.esc_attr($default_desc).'">';

// Конец мини-паттернов

<?php if (ld_opt('enable_supermenu')) get_template_part('partials/header/supermenu'); ?>

<?php
$logo_svg = ld_opt('site_logo_svg');
echo $logo_svg ?: wp_get_attachment_image(ld_opt('site_logo_raster'), 'full', false, ['class'=>'site-logo']);
?>

<?php get_footer(); ?>