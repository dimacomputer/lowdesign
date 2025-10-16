<?php
// theme/setup.php
add_action('after_setup_theme', function () {
  // Глобально включаем миниатюры
  add_theme_support('post-thumbnails');

  // (необязательно) ограничить список типов явно:
  // add_theme_support('post-thumbnails', ['post','page','fineart','modeling','photo']);
});