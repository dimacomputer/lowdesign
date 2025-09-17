<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * ACF Field Icons — per-field icon selector from multiple sources.
 * Хранит значение: "group:slug".
 * Показывает нативный select (без Select2) + маленькое превью в настройке.
 * Иконки у лейблов полей в админке остаются (assets/admin/acf-field-icons.js).
 */

/* ---------- Catalog defaults & filters ---------- */

function ld_field_icon_catalog_default(): array
{
    $base_dir = get_stylesheet_directory() . "/assets/icons/src";
    $base_url = get_stylesheet_directory_uri() . "/assets/icons/src";

    // соберём спрайтовые опции из темы (если есть)
    $sprite_opts = apply_filters("ld_icon_library_options", []);
    $sprite_map = [];
    foreach ($sprite_opts as $slug => $label) {
        if ($slug === "") {
            continue;
        }
        $sprite_map[sanitize_title($slug)] = $slug; // symbol id == slug
    }

    $catalog = [
        "sprite" => [
            "label" => __("Theme Sprite", "lowdesign"),
            "mode" => "sprite",
            "sprite_map" => $sprite_map,
        ],
        "cpt" => [
            "label" => "CPT",
            "mode" => "inline",
            "dir" => $base_dir . "/cpt",
            "url" => $base_url . "/cpt",
        ],
        "ui" => [
            "label" => "UI",
            "mode" => "inline",
            "dir" => $base_dir . "/ui",
            "url" => $base_url . "/ui",
        ],
        "brand" => [
            "label" => "Brand",
            "mode" => "inline",
            "dir" => $base_dir . "/brand",
            "url" => $base_url . "/brand",
        ],
    ];

    // можно расширить через фильтр
    $catalog = apply_filters("ld_field_icon_catalog", $catalog);

    // sanitize
    foreach ($catalog as $group => &$cfg) {
        $cfg["label"] = $cfg["label"] ?? $group;
        $cfg["mode"] = in_array(
            $cfg["mode"] ?? "inline",
            ["inline", "sprite"],
            true,
        )
            ? $cfg["mode"]
            : "inline";
        if ($cfg["mode"] === "inline") {
            $cfg["dir"] = $cfg["dir"] ?? "";
            $cfg["url"] = $cfg["url"] ?? "";
        } else {
            $cfg["sprite_map"] = is_array($cfg["sprite_map"] ?? null)
                ? $cfg["sprite_map"]
                : [];
        }
    }
    return $catalog;
}

function ld_scan_inline_svgs(string $dir): array
{
    $list = [];
    if (is_dir($dir)) {
        foreach (glob(rtrim($dir, "/") . "/*.svg") as $file) {
            $slug = sanitize_title(pathinfo($file, PATHINFO_FILENAME));
            $list[$slug] = $slug;
        }
    }
    return $list;
}

/** Построить choices c optgroup и префиксами group:slug */
function ld_field_icon_choices(): array
{
    $choices = [];
    $catalog = ld_field_icon_catalog_default();

    foreach ($catalog as $group => $cfg) {
        $label = $cfg["label"];
        $options = [];

        if ($cfg["mode"] === "inline") {
            $options = ld_scan_inline_svgs($cfg["dir"]); // slug => slug
        } else {
            $options = array_keys($cfg["sprite_map"]); // [slug, slug, ...]
            $options = array_combine($options, $options); // slug => slug
        }

        if (!empty($options)) {
            $prefixed = [];
            foreach ($options as $slug => $disp) {
                $prefixed[$group . ":" . $slug] = $disp;
            }
            $choices[$label] = $prefixed;
        }
    }

    return $choices ?: ["—" => ["" => "— No icon —"]];
}

/* ---------- Admin assets ---------- */

add_action("admin_enqueue_scripts", function () {
    // 1) Иконки у лейблов полей (оставляем включённым)
    wp_enqueue_script(
        "ld-acf-field-icons",
        get_stylesheet_directory_uri() . "/assets/admin/acf-field-icons.js",
        ["jquery"],
        null,
        true,
    );

    // передаём конфиг источников
    $catalog = ld_field_icon_catalog_default();
    $runtime = [];
    foreach ($catalog as $group => $cfg) {
        $runtime[$group] = [
            "mode" => $cfg["mode"],
            "url" => $cfg["url"] ?? "",
            "map" => $cfg["sprite_map"] ?? [],
            "label" => $cfg["label"],
        ];
    }
    wp_localize_script("ld-acf-field-icons", "LD_FIELD_ICON_CATALOG", $runtime);

    // 2) Превью справа от селекта в настройке ACF (только на экране Field Group)
    if (function_exists("acf_is_screen") && acf_is_screen("field-group")) {
        wp_enqueue_script(
            "ld-acf-field-icon-setting-preview",
            get_stylesheet_directory_uri() .
                "/assets/admin/acf-field-icon-setting-preview.js",
            ["jquery"],
            null,
            true,
        );
        wp_localize_script(
            "ld-acf-field-icon-setting-preview",
            "LD_FIELD_ICON_CATALOG",
            $runtime,
        );
    }
});

/* ---------- ACF setting & wrapper attr ---------- */

add_action(
    "acf/render_field_settings",
    function ($field) {
        acf_render_field_setting(
            $field,
            [
                "label" => __("Field Icon", "lowdesign"),
                "instructions" => __(
                    "Choose an icon for this field. Grouped by source.",
                    "lowdesign",
                ),
                "type" => "select",
                "name" => "ld_field_icon",
                "choices" => ld_field_icon_choices(), // optgroups
                "ui" => 1, // нативный select (фикс «странного дропа»)
                "ajax" => 0,
                "allow_null" => 1,
            ],
            true,
        );
    },
    20,
);

add_filter(
    "acf/field_wrapper_attributes",
    function ($wrapper, $field) {
        if (!empty($field["ld_field_icon"])) {
            $wrapper["data-ld-field-icon"] = sanitize_text_field(
                $field["ld_field_icon"],
            ); // group:slug
        }
        return $wrapper;
    },
    10,
    2,
);

/* ---------- Helpers (frontend) ---------- */

/** @return array{group:string,slug:string}|null */
function ld_parse_field_icon_value($val)
{
    if (!is_string($val) || strpos($val, ":") === false) {
        return null;
    }
    [$g, $s] = explode(":", $val, 2);
    $g = sanitize_title($g);
    $s = sanitize_title($s);
    return $g && $s ? ["group" => $g, "slug" => $s] : null;
}

function ld_get_acf_field_icon($field_or_key): string
{
    $field = is_array($field_or_key)
        ? $field_or_key
        : acf_get_field($field_or_key);
    return !empty($field["ld_field_icon"])
        ? sanitize_text_field($field["ld_field_icon"])
        : "";
}

function ld_render_field_icon_svg($value, array $attrs = []): string
{
    $info = ld_parse_field_icon_value($value);
    if (!$info) {
        return "";
    }
    $catalog = ld_field_icon_catalog_default();

    $attr = "";
    foreach ($attrs as $k => $v) {
        $attr .= " " . esc_attr($k) . '="' . esc_attr($v) . '"';
    }

    $g = $info["group"];
    $slug = $info["slug"];
    if (!isset($catalog[$g])) {
        return "";
    }
    $cfg = $catalog[$g];

    if ($cfg["mode"] === "sprite") {
        $symbol = $cfg["sprite_map"][$slug] ?? $slug;
        return '<svg class="ld-field-icon" aria-hidden="true"' .
            $attr .
            '><use href="#' .
            $symbol .
            '"></use></svg>';
    }

    $file = trailingslashit($cfg["dir"]) . $slug . ".svg";
    if (!file_exists($file)) {
        return "";
    }
    $svg = file_get_contents($file);
    $svg = preg_replace("/<script[^>]*>[\s\S]*?<\/script>/i", "", $svg);
    $svg = preg_replace('/\sfill="[^"]*"/i', "", $svg);
    $svg = preg_replace(
        "/<svg\b([^>]*)>/i",
        '<svg$1 fill="currentColor" class="ld-field-icon">',
        $svg,
    );
    return $svg;
}
