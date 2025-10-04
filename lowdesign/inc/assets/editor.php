<?php
if (!defined('ABSPATH')) exit;

/**
 * Стили для редактора (iframe Gutenberg).
 * Здесь подключаем ЛЁГКИЙ бандл editor.scss (только токены Bootstrap).
 * Сборку editor.scss добавьте в Vite.
 */
add_action('enqueue_block_editor_assets', function () {
  if (function_exists('ld_vite_asset_uri')) {
    if ($css = ld_vite_asset_uri('assets/src/scss/editor.scss')) {
      wp_enqueue_style('ld-editor', $css, [], null);
    }
  }
}, 20);