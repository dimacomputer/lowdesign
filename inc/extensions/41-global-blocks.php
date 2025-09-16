<?php
/**
 * LowDesign — Global gentle layer for ALL core blocks
 */
if (!defined("ABSPATH")) {
    exit();
}

add_action(
    "wp_enqueue_scripts",
    function () {
        wp_enqueue_style(
            "ld-global-blocks",
            get_stylesheet_directory_uri() . "/assets/css/ld-global-blocks.css",
            [],
            null,
        );
    },
    15,
);
