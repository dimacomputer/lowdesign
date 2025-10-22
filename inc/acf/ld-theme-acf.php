<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — ACF: Theme Controls (families, not shades)
 * Берём списки из селекторов классов:
 *  - 012-ld_chroma.css     → .ld-chroma-<family>    → Chroma (bg)
 *  - 013-ld_highlight.css  → .ld-highlight-<family> → Highlight (btn)
 *  - 011-ld-color.css      → .ld-color-<family>     → Color (fg/body)
 *
 * Никаких "custom color". Только семейства.
 */

function ld_extract_families_from_css($file, $class_prefix)
{
    // Поддержим "ld" / "Id" в именах файлов на всякий случай
    $candidates = [
        $file,
        str_replace("Id", "ld", $file),
        str_replace("ld", "Id", $file),
    ];
    $path = "";
    foreach ($candidates as $cand) {
        $try = get_stylesheet_directory() . "/assets/css/" . $cand;
        if (file_exists($try)) {
            $path = $try;
            break;
        }
    }
    if (!$path) {
        return [];
    }

    $css = file_get_contents($path);

    // Ищем селекторы вида: .ld-chroma-blue { ... }  или  .ld-chroma-blue, .ld-chroma-indigo { ... }
    // Берём только буквенно-дефисные имена семейств (без чисел/суффиксов -rgb)
    $pattern =
        "/\." . preg_quote($class_prefix, "/") . "-([a-z-]+)\b(?![0-9-])/i";
    preg_match_all($pattern, $css, $m);

    // Убираем очевидные уровни и служебные хвосты
    $raw = array_unique($m[1] ?? []);
    $families = [];
    foreach ($raw as $name) {
        $clean = strtolower($name);
        // отфильтруем возможные хвосты вроде "-rgb" и случайные числа
        $clean = preg_replace('/-?rgb$/', "", $clean);
        if (preg_match("/\d/", $clean)) {
            continue;
        } // если всё же попалась цифра — пропускаем
        $families[$clean] = $clean; // показываем как есть, чтобы 1:1 совпадало с классами
    }
    ksort($families);
    return $families;
}

// Списки семейств
$choices_chroma = ld_extract_families_from_css(
    "012-ld-chroma.css",
    "ld-chroma",
); // bg
$choices_highlight = ld_extract_families_from_css(
    "013-ld-highlight.css",
    "ld-highlight",
); // btn
$choices_color = ld_extract_families_from_css("011-ld-color.css", "ld-color"); // fg/body

add_action("acf/init", function () use (
    $choices_chroma,
    $choices_highlight,
    $choices_color,
) {
    if (!function_exists("acf_add_local_field_group")) {
        return;
    }

    // --- Site Config (CPT: site_config) ---
    acf_add_local_field_group([
        "key" => "group_ld_theme_global",
        "title" => "Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_theme_mode",
                "label" => "Theme Mode",
                "name" => "theme_mode",
                "type" => "select",
                "choices" => [
                    "auto" => "Auto (system)",
                    "light" => "Light",
                    "dark" => "Dark",
                ],
                "default_value" => "auto",
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_theme_chroma",
                "label" => "Chroma (bg)",
                "name" => "theme_chroma",
                "type" => "select",
                "choices" => $choices_chroma,
                "ui" => 1,
                "allow_null" => 0,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_theme_highlight",
                "label" => "Highlight (btn)",
                "name" => "theme_highlight",
                "type" => "select",
                "choices" => $choices_highlight,
                "ui" => 1,
                "allow_null" => 0,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_theme_color",
                "label" => "Color (fg/body)",
                "name" => "theme_color",
                "type" => "select",
                "choices" => $choices_color,
                "ui" => 1,
                "allow_null" => 0,
                "wrapper" => ["width" => "25"],
            ],
        ],
        "location" => [
            [
                [
                    "param" => "post_type",
                    "operator" => "==",
                    "value" => "site_config",
                ],
            ],
        ],
        "style" => "seamless",
        "active" => true,
    ]);

    // --- Page override (post_type: page) ---
    acf_add_local_field_group([
        "key" => "group_ld_theme_page",
        "title" => "Page Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_page_theme_mode",
                "label" => "Theme Mode",
                "name" => "page_theme_mode",
                "type" => "select",
                "choices" => [
                    "inherit" => "Inherit",
                    "auto" => "Auto (system)",
                    "light" => "Light",
                    "dark" => "Dark",
                ],
                "default_value" => "inherit",
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_chroma",
                "label" => "Chroma (bg)",
                "name" => "page_chroma",
                "type" => "select",
                "choices" => $choices_chroma,
                "allow_null" => 1,
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_highlight",
                "label" => "Highlight (btn)",
                "name" => "page_highlight",
                "type" => "select",
                "choices" => $choices_highlight,
                "allow_null" => 1,
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_color",
                "label" => "Color (fg/body)",
                "name" => "page_color",
                "type" => "select",
                "choices" => $choices_color,
                "allow_null" => 1,
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
        ],
        "location" => [
            [["param" => "post_type", "operator" => "==", "value" => "page"]],
        ],
        "style" => "seamless",
        "active" => true,
    ]);
});
