<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Icon system (admin + front)
 * --------------------------------------
 * - Parses /assets/icons/sprite.svg and exposes full <symbol id="..."> choices.
 * - Wires ACF fields (post/page/terms/menu) to use sprite ids.
 * - Unifies admin/frontend rendering to 24px icons using currentColor.
 * - Adds admin list "Icon" column (after checkbox) for posts/pages/terms.
 * - Inlines sprite once per admin page; enqueues admin css/js.
 */

/** Path to built sprite */
if (!function_exists("ld_sprite_path")) {
    function ld_sprite_path(): string
    {
        return get_stylesheet_directory() . "/assets/icons/sprite.svg";
    }
}

/** Parse <symbol id="..."> list (only glyph-* / brand-*) */
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

/** ---- ACF: load choices + mark wrappers for JS toggles ---- */

/** Menu item icon (no radios there, просто селект) */
add_filter("acf/load_field/name=menu_icon", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    return $f;
});

/** Term icon select (theme) */
add_filter("acf/load_field/name=term_icon_name", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    $f["wrapper"]["data-ld"] = "icon-theme-wrap";
    return $f;
});

/** Post/Page icon select (theme) */
add_filter("acf/load_field/name=post_icon_name", function ($f) {
    $f["choices"] = ld_sprite_choices_full();
    $f["ui"] = 1;
    $f["wrapper"]["data-ld"] = "icon-theme-wrap";
    return $f;
});

/** Media upload wrappers (post & term) — чтобы radio прятал/показывал блок */
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

/** ---- Backfill radio "Icon source" for старых записей ---- */
add_filter(
    "acf/load_value/name=content_icon_source",
    function ($value, $post_id) {
        if ($value) {
            return $value;
        }
        if (!function_exists("get_field")) {
            return "none";
        }

        // для term экранов $post_id приходит как 'term_123'
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

/** ---- Renderers ---------------------------------------------------------- */

/**
 * Render content icon for posts/pages (24px + currentColor)
 */
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

        // normalize classes: enforce 24px & base .icon
        $attr = $attrs;
        $class = trim($attr["class"] ?? "");
        if (!preg_match('/(^|\s)icon(\s|$)/', $class)) {
            $class = trim("icon " . $class);
        }
        if (!preg_match('/(^|\s)icon--24(\s|$)/', $class)) {
            $class = trim("icon--24 " . $class);
        }

        // optional color scope from page color system
        if (function_exists("ld_get_page_color_class")) {
            $cc = ld_get_page_color_class("icon", $post_id);
            if ($cc) {
                $class = trim($class . " " . $cc);
            }
        }
        $attr["class"] = $class;

        // decide source
        $src = (string) get_field("content_icon_source", $post_id);
        if (!$src) {
            $src = get_field("post_icon_name", $post_id)
                ? "sprite"
                : (get_field("content_icon_media", $post_id)
                    ? "media"
                    : "none");
        }

        switch ($src) {
            case "sprite":
                $name = (string) get_field("post_icon_name", $post_id);
                return $name && function_exists("ld_icon")
                    ? ld_icon($name, $attr, $post_id)
                    : "";

            case "media":
                $id = (int) get_field("content_icon_media", $post_id);
                // ld_image_or_svg_html должен инлайнить SVG (чтобы работал currentColor)
                return $id && function_exists("ld_image_or_svg_html")
                    ? ld_image_or_svg_html($id, "full", $attr)
                    : "";

            default:
                return "";
        }
    }
}

/**
 * Render icon for terms (prefer sprite, fallback to uploaded image)
 */
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

        // sprite
        $icon = (string) get_field("term_icon_name", "term_" . $term_id);
        if ($icon && $icon !== "none" && function_exists("ld_icon")) {
            $attr = $attrs;
            $cls = trim(($attr["class"] ?? "") . " " . $class);
            if (!preg_match('/(^|\s)icon(\s|$)/', $cls)) {
                $cls = trim("icon " . $cls);
            }
            if (!preg_match('/(^|\s)icon--24(\s|$)/', $cls)) {
                $cls = trim("icon--24 " . $cls);
            }
            $attr["class"] = $cls;
            return ld_icon($icon, $attr);
        }

        // media fallback
        $media = (int) get_field("term_icon_media", "term_" . $term_id);
        if ($media && function_exists("ld_image_or_svg_html")) {
            $attr = $attrs;
            $cls = trim(($attr["class"] ?? "") . " " . $class);
            if (!preg_match('/(^|\s)icon(\s|$)/', $cls)) {
                $cls = trim("icon " . $cls);
            }
            if (!preg_match('/(^|\s)icon--24(\s|$)/', $cls)) {
                $cls = trim("icon--24 " . $cls);
            }
            $attr["class"] = $cls;
            return ld_image_or_svg_html($media, "full", $attr);
        }

        return "";
    }
}

/** ---- Admin list columns ------------------------------------------------- */

/** Taxonomies: Category & Tag */
add_filter(
    "manage_edit-category_columns",
    fn($c) => ["cb" => $c["cb"]] + ["icon" => __("Icon", "ld")] +
        array_diff_key($c, ["cb" => 1]),
);
add_action(
    "manage_category_custom_column",
    function ($col, $term_id) {
        if ($col !== "icon") {
            return;
        }
        $html = ld_term_icon_html($term_id, "", ["class" => "icon icon--24"]);
        echo $html ?: "—";
    },
    10,
    2,
);

add_filter(
    "manage_edit-post_tag_columns",
    fn($c) => ["cb" => $c["cb"]] + ["icon" => __("Icon", "ld")] +
        array_diff_key($c, ["cb" => 1]),
);
add_action(
    "manage_post_tag_custom_column",
    function ($col, $term_id) {
        if ($col !== "icon") {
            return;
        }
        $html = ld_term_icon_html($term_id, "", ["class" => "icon icon--24"]);
        echo $html ?: "—";
    },
    10,
    2,
);

/** Posts & CPTs */
foreach (["post", "fineart", "modeling"] as $pt) {
    add_filter("manage_{$pt}_posts_columns", function ($cols) {
        // вставляем ICON сразу после чекбокса
        $first = ["cb" => $cols["cb"] ?? ""];
        $rest = $cols;
        unset($rest["cb"]);
        return $first + ["icon" => __("Icon", "ld")] + $rest;
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

/** Pages */
add_filter("manage_page_posts_columns", function ($cols) {
    $first = ["cb" => $cols["cb"] ?? ""];
    $rest = $cols;
    unset($rest["cb"]);
    return $first + ["icon" => __("Icon", "ld")] + $rest;
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

/** Consistent sizing/padding for icon column (24px + 4px margin) */
add_action("admin_head", function () {
    echo '<style>
    .wp-list-table .column-icon{width:28px}
    .wp-list-table td.column-icon{padding-left:4px;padding-right:0;text-align:center}
    .wp-list-table td.column-icon .icon{width:24px;height:24px;display:inline-block}
  </style>';
});

/** ---- Admin assets & helpers -------------------------------------------- */

/** Inline sprite once per admin page (hidden container) */
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

/** Enqueue admin preview css/js (select2 templates + svg inline preview) */
add_action("admin_enqueue_scripts", function () {
    // твой скомпилированный CSS с токенами/размерами для админки
    wp_enqueue_style(
        "ld-admin-icons",
        get_stylesheet_directory_uri() . "/assets/css/admin-icons.css",
        [],
        null,
    );
    // минимальные стили предпросмотра
    wp_enqueue_style(
        "ld-icon-preview",
        get_stylesheet_directory_uri() . "/assets/admin/icon-preview.css",
        [],
        null,
    );
    // JS (зависит от jquery и select2, чтобы шаблоны селектов отрисовывались с иконками)
    wp_enqueue_script(
        "ld-icon-preview",
        get_stylesheet_directory_uri() . "/assets/admin/icon-preview.js",
        ["jquery", "select2"],
        null,
        true,
    );
});

/** Allow SVG uploads for admins (media fallback) */
add_filter("upload_mimes", function ($m) {
    if (current_user_can("manage_options")) {
        $m["svg"] = "image/svg+xml";
    }
    return $m;
});

/** Front: inject icon before menu label (optional menu feature) */
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
