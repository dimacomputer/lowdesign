<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Lowdesign bootstrap (fail-safe, semantic-only styles)
 * Порядок:
 * 0) core: vite, i18n, acf-json, logo-svg
 * 1) helpers → cpt → taxonomies → extensions
 * 2) theme setup (supports, logo, menus)
 * 3) assets (semantic CSS only)
 * 4) nav & widgets
 */

require_once __DIR__ . "/core/loader.php";

add_action(
    "after_setup_theme",
    function () {
        $base = get_stylesheet_directory();
        $inc = $base . "/inc";

        // 0) ядро
        ld_require_once_safe("$inc/core/vite.php"); // JS-сборка (не мешает стилям)
        ld_require_once_safe("$inc/core/i18n.php"); // локализация
        ld_require_once_safe("$inc/core/acf-json.php"); // ACF JSON paths
        ld_require_once_safe("$inc/core/custom-logo-svg.php"); // SVG логотип

        // 1) кастомные пласты
        ld_require_dir("$inc/helpers");
        ld_require_dir("$inc/cpt");
        ld_require_dir("$inc/taxonomies");
        ld_require_dir("$inc/extensions");

        // 2) возможности темы
        ld_require_dir("$inc/setup");

        // 3) ассеты – только семантические CSS (новый модуль)
        ld_require_once_safe("$inc/assets/ld-css-enqueue.php");

        // 3.1) runtime темы: body-классы и :root vars
        ld_require_once_safe("$inc/acf/ld-theme-acf.php");
        ld_require_once_safe("$inc/core/ld-theme-runtime.php");

        // 4) навигация и виджеты
        ld_require_once_safe("$inc/nav.php");
        ld_require_once_safe("$inc/widgets-footer.php");
    },
    1,
);
