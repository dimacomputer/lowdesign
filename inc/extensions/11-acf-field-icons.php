<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * ACF Field Icons — per-field icon selector from multiple sources.
 *
 * Value stored: "group:slug"
 *
 * Catalog (via filter 'ld_field_icon_catalog'):
 * [
 *   'cpt' => [
 *     'label' => 'CPT',
 *     'mode'  => 'inline' | 'sprite',      // how to render the choice
 *     // inline mode:
 *     'dir'   => abs_path_to_svgs,         // /path/to/assets/icons/src/cpt
 *     'url'   => base_url_to_svgs,         // https://.../assets/icons/src/cpt
 *     // sprite mode:
 *     'sprite_map' => [ slug => symbol_id ] // optional; default symbol_id = slug
 *   ],
 *   'sprite' => [
 *     'label' => 'Theme Sprite',
 *     'mode'  => 'sprite',
 *     'sprite_map' => [ slug => symbol_id ] // например из ld_icon_library_options()
 *   ],
 *   ...
 * ]
 *
 * По умолчанию добавляем группы: cpt, ui, brand (inline из файлов) и sprite (из ld_icon_library_options()).
 */

// ---------- Catalog defaults & filters ----------

function ld_field_icon_catalog_default(): array
{
    $base_dir = get_stylesheet_directory() . "/assets/icons/src";
    $base_url = get_stylesheet_directory_uri() . "/assets/icons/src";

    // собрать sprite-опции из твоего набора темы (если есть)
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

    // Дай возможность расширять/менять каталог
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

// Сканирует каталог inline-иконок
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

// Строит список choices c optgroups
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
            // sprite
            $options = $cfg["sprite_map"]; // slug => symbol_id (мы покажем slug)
            // превратим в slug => slug для UI
            $options = array_combine(
                array_keys($options),
                array_keys($options),
            );
        }

        // optgroup: ключ — метка группы, значения — с префиксом group:
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

// ---------- Admin assets ----------

add_action("admin_enqueue_scripts", function () {
    // CSS уже у тебя: assets/admin/icon-preview.css
    // Подключаем только JS, передаём конфиг каталога
    wp_enqueue_script(
        "ld-acf-field-icons",
        get_stylesheet_directory_uri() . "/assets/admin/acf-field-icons.js",
        ["jquery"],
        null,
        true,
    );

    // Сконструируем runtime-конфиг для JS
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
});

// ---------- ACF setting & wrapper attr ----------

add_action(
    "acf/render_field_settings",
    function ($field) {
        acf_render_field_setting(
            $field,
            [
                "label" => __("Field Icon", "lowdesign"),
                "instructions" => __(
                    "Choose an icon to display next to this field label.",
                    "lowdesign",
                ),
                "type" => "select",
                "name" => "ld_field_icon",
                "choices" => ld_field_icon_choices(), // optgroups
                "ui" => 1,
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
            // значение формата group:slug
            $wrapper["data-ld-field-icon"] = sanitize_text_field(
                $field["ld_field_icon"],
            );
        }
        return $wrapper;
    },
    10,
    2,
);

// ---------- Optional helpers (frontend) ----------

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

    // inline
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
