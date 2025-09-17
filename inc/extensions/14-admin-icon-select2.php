<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Enqueue Select2 init for our icon selects on post/term screens.
 * Делаем зависимость от acf-input, чтобы был подключен select2 и его CSS.
 */
add_action("admin_enqueue_scripts", function ($hook) {
    // пост/страница + таксономии
    $is_post_screen = in_array($hook, ["post.php", "post-new.php"], true);
    $is_terms_screen = $hook === "edit-tags.php";

    if (!$is_post_screen && !$is_terms_screen) {
        return;
    }

    // ACF обычно тянет select2 — подстрахуемся зависимостью
    wp_enqueue_script(
        "ld-icon-select2-init",
        get_stylesheet_directory_uri() . "/assets/admin/icon-select2-init.js",
        ["jquery", "acf-input"], // acf-input включает select2 + css
        null,
        true,
    );
});
