<?php
/**
 * LowDesign — Theme ACF glue (global + per-page theme controls)
 */
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Получить ID записи-конфига сайта (CPT: ld_site_config)
 */
function ld_get_site_config_id(): ?int
{
    static $id = null;
    if ($id !== null) {
        return $id;
    }

    $q = new WP_Query([
        "post_type" => "ld_site_config",
        "posts_per_page" => 1,
        "post_status" => "publish",
        "orderby" => "ID",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $id = $q->have_posts() ? intval($q->posts[0]) : null;
    wp_reset_postdata();
    return $id;
}

/**
 * Прочитать глобальные настройки темы из Site Config
 */
function ld_get_global_theme_settings(): array
{
    $id = ld_get_site_config_id();
    if (!$id) {
        return [
            "mode" => "auto", // auto|default|dark
            "chroma" => "default", // см. 012-ld-chroma.css
            "highlight" => "default", // см. 013-ld-highlight.css
            "color" => "default", // см. 011-ld-color.css
        ];
    }
    // поля в site-settings.json
    $mode = get_field("theme_mode", $id) ?: "auto";
    $chroma = get_field("theme_chroma", $id) ?: "default";
    $highlight = get_field("theme_highlight", $id) ?: "default";
    $color = get_field("theme_color", $id) ?: "default";

    return compact("mode", "chroma", "highlight", "color");
}

/**
 * Перезапись из Page Color Settings (если есть)
 * Ожидаемые имена полей в page-color-settings.json:
 *   page_theme_mode, page_theme_chroma, page_theme_highlight, page_theme_color
 * Если у тебя другие — поправь массив $page_keys.
 */
function ld_get_effective_theme_settings(?int $post_id = null): array
{
    $g = ld_get_global_theme_settings();

    if (!$post_id) {
        $post_id = get_queried_object_id();
    }
    if (!$post_id) {
        return $g;
    }

    $page_keys = [
        "mode" => "page_theme_mode",
        "chroma" => "page_theme_chroma",
        "highlight" => "page_theme_highlight",
        "color" => "page_theme_color",
    ];

    foreach ($page_keys as $k => $field) {
        $v = get_field($field, $post_id);
        if ($v && $v !== "inherit") {
            $g[$k] = $v;
        }
    }
    return $g;
}

/**
 * Выставляем data-* на <html> в фронтенде
 */
add_action(
    "wp_head",
    function () {
        $t = ld_get_effective_theme_settings(); ?>
  <script>
    (function () {
      try {
        var root = document.documentElement;
        // mode: auto|default|dark
        var mode = <?php echo json_encode($t["mode"]); ?>;
        var chroma = <?php echo json_encode($t["chroma"]); ?>;
        var highlight = <?php echo json_encode($t["highlight"]); ?>;
        var color = <?php echo json_encode($t["color"]); ?>;

        // auto → вычисляем по prefers-color-scheme
        var themeVal = mode === 'auto'
          ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default')
          : mode;

        root.setAttribute('data-theme', themeVal);
        root.setAttribute('data-chroma', chroma);
        root.setAttribute('data-highlight', highlight);
        root.setAttribute('data-color', color);
      } catch (e) {}
    })();
  </script>
  <?php
    },
    1,
);

/**
 * То же в редакторе (Gutenberg), чтобы предпросмотр совпадал
 */
add_action(
    "admin_head",
    function () {
        if (!function_exists("get_current_screen")) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Для редактора записей/страниц и кастомных CPT
        if ($screen->base !== "post") {
            return;
        }

        $post_id = isset($_GET["post"]) ? intval($_GET["post"]) : 0;
        $t = ld_get_effective_theme_settings($post_id);
        ?>
  <script>
    (function () {
      try {
        var root = document.documentElement;
        var mode = <?php echo json_encode($t["mode"]); ?>;
        var chroma = <?php echo json_encode($t["chroma"]); ?>;
        var highlight = <?php echo json_encode($t["highlight"]); ?>;
        var color = <?php echo json_encode($t["color"]); ?>;
        var themeVal = mode === 'auto'
          ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default')
          : mode;

        root.setAttribute('data-theme', themeVal);
        root.setAttribute('data-chroma', chroma);
        root.setAttribute('data-highlight', highlight);
        root.setAttribute('data-color', color);
      } catch (e) {}
    })();
  </script>
  <?php
    },
    1,
);
