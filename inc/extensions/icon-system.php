<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Icon System (admin + front):
 * - Sprite choices (glyph-*, brand-*).
 * - ACF field wiring (radio: none/sprite/media + wrappers for JS).
 * - Unified 24px sizing & currentColor for both sprite and uploaded SVG.
 * - Inline <svg> from Media Library (strip fills → currentColor) when possible.
 * - Admin list columns with 24px icons.
 */

/** Path to built sprite */
if (!function_exists("ld_sprite_path")) {
    function ld_sprite_path(): string
    {
        return get_stylesheet_directory() . "/assets/icons/sprite.svg";
    }
}

/** Symbol ids (glyph-*, brand-*) */
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

        $svg = @file_get_contents($file);
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

/** === ACF wiring ========================================================= */

/** select2 choices + mark wrappers so JS может скрывать блоки */
add_filter("acf/load_field/name=menu_icon", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    return $f;
});

add_filter("acf/load_field/name=post_icon_name", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    $f["wrapper"]["data-ld"] = "icon-theme-wrap";
    return $f;
});

add_filter("acf/load_field/name=term_icon_name", function ($f) {
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

/** Backfill radio (совместимость со старыми постами/термами) */
add_filter(
    "acf/load_value/name=content_icon_source",
    function ($value, $post_id) {
        if ($value) {
            return $value;
        }
        if (!function_exists("get_field")) {
            return "none";
        }
        if (is_string($post_id) && str_starts_with($post_id, "term_")) {
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

/** === Helpers ============================================================ */

/** merge/normalize class => ensure 'icon icon--24' present */
if (!function_exists("ld_icon_merge_class_24")) {
    function ld_icon_merge_class_24(array $attrs): array
    {
        $class = trim($attrs["class"] ?? "");
        if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
            $class = trim("icon " . $class);
        }
        if (!preg_match('/(^|\s)icon--24(\s|$)/', $class)) {
            $class = trim("icon--24 " . $class);
        }
        $attrs["class"] = $class;
        return $attrs;
    }
}

/** inline SVG from media attachment (strip fills → currentColor) */
if (!function_exists("ld_inline_svg_from_attachment")) {
    function ld_inline_svg_from_attachment(
        int $attachment_id,
        array $attrs = [],
    ): string {
        $path = get_attached_file($attachment_id);
        if (!$path || !is_file($path) || !preg_match('/\.svg$/i', $path)) {
            return "";
        }
        $svg = @file_get_contents($path);
        if (!$svg) {
            return "";
        }

        // sanitize: drop scripts, remove fills, force currentColor
        $svg = preg_replace("~<script[^>]*>.*?</script>~is", "", $svg);
        $svg = preg_replace('~\sfill="[^"]*"~i', "", $svg);
        $svg = preg_replace("~<svg\b~i", '<svg fill="currentColor"', $svg, 1);

        // inject class/id/attrs on root <svg>
        $attrs = ld_icon_merge_class_24($attrs);
        $extra = "";
        foreach ($attrs as $k => $v) {
            if (
                $k === "class" ||
                $k === "id" ||
                $k === "style" ||
                str_starts_with($k, "data-")
            ) {
                $extra .= " " . $k . '="' . esc_attr($v) . '"';
            }
        }
        $svg = preg_replace(
            "~<svg\b([^>]*)>~i",
            '<svg$1' . $extra . ">",
            $svg,
            1,
        );
        return $svg;
    }
}

/** === Rendering ========================================================== */

/** Content icon (post/page/CPT) */
if (!function_exists("ld_content_icon")) {
    function ld_content_icon($post_id = null, array $attrs = []): string
    {
        if (!function_exists("get_field")) {
            return "";
        }
        $post_id = $post_id ?: get_the_ID();
        if (!$post_id) {
            return "";
        }

        // color class (page color system)
        if (function_exists("ld_get_page_color_class")) {
            $cc = ld_get_page_color_class("icon", $post_id);
            if ($cc) {
                $attrs["class"] = trim(($attrs["class"] ?? "") . " " . $cc);
            }
        }
        $attrs = ld_icon_merge_class_24($attrs);

        $src = (string) get_field("content_icon_source", $post_id);
        if (!$src) {
            $src = get_field("post_icon_name", $post_id)
                ? "sprite"
                : (get_field("content_icon_media", $post_id)
                    ? "media"
                    : "none");
        }

        if ($src === "sprite") {
            $name = (string) get_field("post_icon_name", $post_id);
            return $name && function_exists("ld_icon")
                ? ld_icon($name, $attrs, $post_id)
                : "";
        }

        if ($src === "media") {
            $id = (int) get_field("content_icon_media", $post_id);
            if ($id) {
                $inline = ld_inline_svg_from_attachment($id, $attrs);
                if ($inline) {
                    return $inline; // SVG → inline, currentColor + 24px
                }
                if (function_exists("ld_image_or_svg_html")) {
                    // fallback (PNG/JPG) — просто 24px img
                    $attrs = ld_icon_merge_class_24($attrs);
                    return ld_image_or_svg_html($id, "full", $attrs);
                }
            }
        }
        return "";
    }
}

/** Term icon (category/tag) */
if (!function_exists("ld_term_icon_html")) {
    function ld_term_icon_html(
        $term = null,
        string $class = "",
        array $attrs = [],
    ): string {
        if (!function_exists("get_field")) {
            return "";
        }
        if (!$term && (is_tax() || is_category() || is_tag())) {
            $term = get_queried_object();
        }
        $term_id =
            $term instanceof WP_Term ? (int) $term->term_id : (int) $term;
        if (!$term_id) {
            return "";
        }

        // sprite first
        $icon = (string) get_field("term_icon_name", "term_" . $term_id);
        if ($icon && $icon !== "none" && function_exists("ld_icon")) {
            $attrs["class"] = trim(($attrs["class"] ?? "") . " " . $class);
            $attrs = ld_icon_merge_class_24($attrs);
            return ld_icon($icon, $attrs);
        }

        // media fallback
        $media = (int) get_field("term_icon_media", "term_" . $term_id);
        if ($media) {
            $attrs["class"] = trim(($attrs["class"] ?? "") . " " . $class);
            $inline = ld_inline_svg_from_attachment($media, $attrs);
            if ($inline) {
                return $inline;
            }
            if (function_exists("ld_image_or_svg_html")) {
                $attrs = ld_icon_merge_class_24($attrs);
                return ld_image_or_svg_html($media, "full", $attrs);
            }
        }
        return "";
    }
}

/** === Admin UI (sprite inline once, assets) ============================== */

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

add_action("admin_enqueue_scripts", function () {
    // CSS/JS для превью и select2
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

/** === Admin list columns ================================================= */

add_filter(
    "manage_edit-category_columns",
    fn($c) => ["icon" => __("Icon", "ld")] + $c,
);
add_filter(
    "manage_category_custom_column",
    function ($out, $col, $term_id) {
        if ($col !== "icon") {
            return $out;
        }
        $html = ld_term_icon_html($term_id, "", ["class" => "icon icon--24"]);
        return $html ?: "—";
    },
    10,
    3,
);

add_filter(
    "manage_edit-post_tag_columns",
    fn($c) => ["icon" => __("Icon", "ld")] + $c,
);
add_filter(
    "manage_post_tag_custom_column",
    function ($out, $col, $term_id) {
        if ($col !== "icon") {
            return $out;
        }
        $html = ld_term_icon_html($term_id, "", ["class" => "icon icon--24"]);
        return $html ?: "—";
    },
    10,
    3,
);

foreach (["post", "fineart", "modeling"] as $pt) {
    add_filter("manage_{$pt}_posts_columns", function ($cols) {
        $new = ["icon" => __("Icon", "ld")];
        return array_slice($cols, 0, 1, true) +
            $new +
            array_slice($cols, 1, null, true);
    });
    add_action(
        "manage_{$pt}_posts_custom_column",
        function ($col, $post_id) {
            if ($col !== "icon") {
                return;
            }
            echo ld_content_icon($post_id, ["class" => "icon icon--24"]);
        },
        10,
        2,
    );
}

// Pages
add_filter("manage_page_posts_columns", function ($cols) {
    $new = ["icon" => __("Icon", "ld")];
    return array_slice($cols, 0, 1, true) +
        $new +
        array_slice($cols, 1, null, true);
});
add_action(
    "manage_page_posts_custom_column",
    function ($col, $post_id) {
        if ($col !== "icon") {
            return;
        }
        echo ld_content_icon($post_id, ["class" => "icon icon--24"]);
    },
    10,
    2,
);

/** width/padding for column, 24px inside */
add_action("admin_head", function () {
    echo '<style>
    .wp-list-table .column-icon{width:28px}
    .wp-list-table td.column-icon{padding-left:4px;padding-right:0;text-align:center}
    .wp-list-table td.column-icon .icon{width:24px;height:24px;display:inline-block}
  </style>';
});

/** Allow SVG uploads for admins */
add_filter("upload_mimes", function ($m) {
    if (current_user_can("manage_options")) {
        $m["svg"] = "image/svg+xml";
    }
    return $m;
});

/** Front menu: inject icon before label (kept) */
add_filter(
    "walker_nav_menu_start_el",
    function ($out, $item) {
        if (!function_exists("get_field") || !function_exists("ld_icon")) {
            return $out;
        }
        $id = (string) get_field("menu_icon", $item->ID);
        if (!$id || $id === "none") {
            return $out;
        }
        return preg_replace(
            "~(<a[^>]*>)~",
            '$1' . ld_icon($id, ["class" => "menu__icon"]),
            $out,
            1,
        );
    },
    10,
    2,
);
