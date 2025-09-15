<?php
if (!defined("ABSPATH")) {
    exit();
}

// Helpers and admin integration for SVG sprite icons.

/** Path to built sprite */
if (!function_exists("ld_sprite_path")) {
    function ld_sprite_path(): string
    {
        return get_stylesheet_directory() . "/assets/icons/sprite.svg";
    }
}

/** Parse <symbol id="..."> list (glyph-* / brand-*) */
if (!function_exists("ld_sprite_choices_full")) {
    function ld_sprite_choices_full(): array
    {
        static $choices;
        if (isset($choices)) {
            return $choices;
        }
        $file = ld_sprite_path();
        if (!is_file($file)) {
            return [];
        }
        $svg = file_get_contents($file);
        if (!$svg) {
            return [];
        }
        if (
            preg_match_all(
                '~<symbol[^>]+id="((?:glyph|brand)-[^"]+)"~i',
                $svg,
                $m,
            )
        ) {
            $ids = $m[1] ?? [];
            $choices = $ids ? array_combine($ids, $ids) : [];
        } else {
            $choices = [];
        }
        return $choices;
    }
}

/** Hook ACF selects with full sprite ids + mark wrappers for JS */
add_filter("acf/load_field/name=menu_icon", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    return $f;
});
add_filter("acf/load_field/name=term_icon_name", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    $f["wrapper"]["data-ld"] = "icon-theme-wrap";
    return $f;
});
add_filter("acf/load_field/name=post_icon_name", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    $f["wrapper"]["data-ld"] = "icon-theme-wrap";
    return $f;
});
add_filter("acf/load_field/name=content_icon_media", function ($f) {
    if (!isset($f["wrapper"]) || !is_array($f["wrapper"])) {
        $f["wrapper"] = [];
    }
    $f["wrapper"]["data-ld"] = "icon-media-wrap";
    return $f;
});
add_filter("acf/load_field/name=term_icon_media", function ($f) {
    if (!isset($f["wrapper"]) || !is_array($f["wrapper"])) {
        $f["wrapper"] = [];
    }
    $f["wrapper"]["data-ld"] = "icon-media-wrap";
    return $f;
});

/** Backfill icon source radio (compat) */
add_filter(
    "acf/load_value/name=content_icon_source",
    function ($value, $post_id) {
        if ($value) {
            return $value;
        }
        if (!function_exists("get_field")) {
            return "none";
        }
        if (is_string($post_id) && 0 === strpos($post_id, "term_")) {
            if (get_field("term_icon_name", $post_id)) {
                return "sprite";
            }
            if (get_field("term_icon_media", $post_id)) {
                return "media";
            }
        } else {
            if (get_field("post_icon_name", $post_id)) {
                return "sprite";
            }
            if (get_field("content_icon_media", $post_id)) {
                return "media";
            }
        }
        return "none";
    },
    10,
    2,
);

/** Inline sprite once per admin page */
add_action("admin_footer", function () {
    static $done;
    if ($done) {
        return;
    }
    $done = true;
    $p = ld_sprite_path();
    if (is_file($p)) {
        $svg = @file_get_contents($p);
        if ($svg) {
            echo '<div hidden style="display:none" class="ld-admin-sprite">',
                $svg,
                "</div>";
        }
    }
});

/** Admin preview assets */
add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style(
        "ld-admin-icons",
        get_stylesheet_directory_uri() . "/assets/css/admin-icons.css",
        [],
        null,
    );
    wp_enqueue_style(
        "ld-icon-preview",
        get_stylesheet_directory_uri() . "/assets/admin/icon-preview.css",
        [],
        null,
    );
    wp_enqueue_script(
        "ld-icon-preview",
        get_stylesheet_directory_uri() . "/assets/admin/icon-preview.js",
        ["jquery", "select2"],
        null,
        true,
    );
});

/** Allow SVG uploads for admins */
add_filter("upload_mimes", function ($m) {
    if (current_user_can("manage_options")) {
        $m["svg"] = "image/svg+xml";
    }
    return $m;
});

/** Inline SVG from attachment (force currentColor & classes) */
