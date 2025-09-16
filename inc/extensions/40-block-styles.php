<?php
/**
 * LowDesign — Block style variations (safe)
 */
if (!defined("ABSPATH")) {
    exit();
}

add_action("after_setup_theme", function () {
    add_theme_support("editor-styles");
    add_editor_style("assets/css/ld-global-blocks.css");
    add_editor_style("assets/css/ld-blocks.css");
});

add_action(
    "wp_enqueue_scripts",
    function () {
        $base = get_stylesheet_directory_uri() . "/assets/css/";
        wp_enqueue_style(
            "ld-global-blocks",
            $base . "ld-global-blocks.css",
            [],
            null,
        );
        wp_enqueue_style(
            "ld-blocks",
            $base . "ld-blocks.css",
            ["ld-global-blocks"],
            null,
        );
    },
    20,
);

add_action("init", function () {
    // Image
    register_block_style("core/image", [
        "name" => "rounded",
        "label" => __("Rounded", "lowdesign"),
    ]);
    register_block_style("core/image", [
        "name" => "shadowed",
        "label" => __("Shadowed", "lowdesign"),
    ]);

    // Buttons
    register_block_style("core/buttons", [
        "name" => "pill",
        "label" => __("Pill", "lowdesign"),
    ]);
    register_block_style("core/button", [
        "name" => "outline-soft",
        "label" => __("Outline (soft)", "lowdesign"),
    ]);

    // Gallery
    register_block_style("core/gallery", [
        "name" => "square-crop",
        "label" => __("Square Crop", "lowdesign"),
    ]);
});
