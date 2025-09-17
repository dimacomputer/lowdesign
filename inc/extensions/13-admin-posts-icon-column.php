<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Admin list tables (posts/pages/CPT): add a narrow "Icon" column that shows the Content Icon.
 * Works with our meta shape from the icon system:
 *   ['source' => 'theme'|'media'|'none', 'theme' => 'symbol-id', 'media_id' => int]
 */

add_action("admin_init", function () {
    // 1) Register the column for all public post types
    foreach (get_post_types(["show_ui" => true], "names") as $pt) {
        add_filter("manage_edit-{$pt}_columns", function ($cols) {
            // Append at the end (safer than reordering defaults)
            $cols["ld_icon"] = __("Icon", "lowdesign");
            return $cols;
        });

        // 2) Render cell
        add_action(
            "manage_{$pt}_posts_custom_column",
            function ($col, $post_id) {
                if ($col !== "ld_icon") {
                    return;
                }

                $m = get_post_meta($post_id, "_ld_content_icon", true);
                $src = is_array($m) ? $m["source"] ?? "none" : "none";

                if ($src === "theme" && !empty($m["theme"])) {
                    $id = esc_attr($m["theme"]);
                    echo '<span class="ld-icon-preview" title="' .
                        esc_attr($id) .
                        '"><svg aria-hidden="true"><use href="#' .
                        $id .
                        '"></use></svg></span>';
                } elseif ($src === "media" && !empty($m["media_id"])) {
                    $url = wp_get_attachment_url((int) $m["media_id"]);
                    if ($url && preg_match('/\.svg(\?|#|$)/i', $url)) {
                        // inline, sanitized to currentColor
                        $svg = @file_get_contents(
                            get_attached_file((int) $m["media_id"]),
                        );
                        if ($svg) {
                            $svg = preg_replace(
                                "/<script[^>]*>[\s\S]*?<\/script>/i",
                                "",
                                $svg,
                            );
                            $svg = preg_replace('/\sfill="[^"]*"/i', "", $svg);
                            $svg = preg_replace(
                                "/<svg\b([^>]*)>/i",
                                '<svg$1 fill="currentColor">',
                                $svg,
                            );
                            echo '<span class="ld-icon-preview" title="media SVG">' .
                                $svg .
                                "</span>";
                        }
                    } else {
                        echo "&mdash;"; // not an SVG – не показываем
                    }
                } else {
                    echo "&mdash;";
                }
            },
            10,
            2,
        );

        // 3) Make column not sortable (explicit) and set width via CSS
        add_filter("manage_edit-{$pt}_sortable_columns", function ($cols) {
            if (isset($cols["ld_icon"])) {
                unset($cols["ld_icon"]);
            }
            return $cols;
        });
    }
});

/** Narrow column style */
add_action("admin_head", function () {
    echo '<style>
    .column-ld_icon{width:54px;text-align:center;}
    .column-ld_icon .ld-icon-preview{
      display:inline-flex;align-items:center;justify-content:center;
      width:24px;height:24px;border:1px solid #dcdcdc;border-radius:4px;background:#fff;
      line-height:0;
    }
    .column-ld_icon .ld-icon-preview svg{width:18px;height:18px;fill:currentColor;}
  </style>';
});
