<?php
if (!defined('ABSPATH')) exit;

add_action('widgets_init', function () {
  for ($i = 1; $i <= 3; $i++) {
    register_sidebar([
      'name'          => sprintf(__('Footer %d', 'lowdesign'), $i),
      'id'            => 'footer-' . $i,
      'before_widget' => '<section id="%1$s" class="widget %2$s">',
      'after_widget'  => '</section>',
      'before_title'  => '<h3 class="widget-title">',
      'after_title'   => '</h3>',
    ]);
  }
});