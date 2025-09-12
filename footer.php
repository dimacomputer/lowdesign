<?php /* Footer */ ?>
</main>
<footer class="site-footer">
  <div class="container footer-widgets">
    <div class="row">
      <div class="col footer-col"><?php if (is_active_sidebar('footer-1')) dynamic_sidebar('footer-1'); ?></div>
      <div class="col footer-col"><?php if (is_active_sidebar('footer-2')) dynamic_sidebar('footer-2'); ?></div>
      <div class="col footer-col"><?php if (is_active_sidebar('footer-3')) dynamic_sidebar('footer-3'); ?></div>
    </div>
  </div>

  <div class="container footer-bottom">
    <nav class="footer-nav" aria-label="Footer">
      <?php wp_nav_menu([
        'theme_location' => 'footer',
        'container'      => false,
        'menu_class'     => 'menu footer-menu',
        'depth'          => 1,
      ]); ?>
    </nav>
    <div class="copyright">© <?php echo date('Y'); ?> <?php bloginfo('name'); ?></div>
  </div>
</footer>
<?php
$sprite_path = get_theme_file_path('assets/icons/sprite.svg');
if (file_exists($sprite_path)) {
  $ver   = filemtime($sprite_path);
  $svg   = file_get_contents($sprite_path);
  echo '<div class="ld-icon-sprite" hidden data-sprite-ver="' . esc_attr($ver) . '">' . $svg . '</div>';
}
?>
<?php wp_footer(); ?>
</body>
</html>