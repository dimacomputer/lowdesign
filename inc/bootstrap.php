<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign bootstrap (semantic + bootstrap bridge)
 */

require_once __DIR__ . "/core/loader.php";

add_action(
    "after_setup_theme",
    function () {
        $base = get_stylesheet_directory();
        $inc = $base . "/inc";

        // 0) ядро
        ld_require_once_safe("$inc/core/vite.php");
        ld_require_once_safe("$inc/core/i18n.php");
        ld_require_once_safe("$inc/core/acf-json.php");
        ld_require_once_safe("$inc/core/custom-logo-svg.php");

        // 1) helpers/cpt/taxonomies/extensions
        ld_require_dir("$inc/helpers");
        ld_require_dir("$inc/cpt");
        ld_require_dir("$inc/taxonomies");
        ld_require_dir("$inc/extensions");

        // 2) возможности темы
        ld_require_dir("$inc/setup");

        // 3) Bootstrap как каркас
        ld_require_once_safe("$inc/assets/ld-bootstrap-bridge.php");

        // 4) Наши CSS (010…015)
        ld_require_once_safe("$inc/assets/ld-css-enqueue.php");

        // 4b) Vite build assets (main.js + optional main/editor/admin CSS)
        ld_require_once_safe("$inc/assets/ld-vite-enqueue.php");

        // 5) Управление темой (ACF + runtime)
        ld_require_once_safe("$inc/acf/ld-theme-acf.php");
        ld_require_once_safe("$inc/core/ld-theme-runtime.php");

        // Старые ассеты отключены:
        // ld_require_once_safe("$inc/assets/enqueue-frontend.php");
        // ld_require_once_safe("$inc/assets/editor.php");
        // ld_require_once_safe("$inc/assets/admin-dark.php");

        // Нав и виджеты
        ld_require_once_safe("$inc/nav.php");
        ld_require_once_safe("$inc/widgets-footer.php");
    },
    1,
);
