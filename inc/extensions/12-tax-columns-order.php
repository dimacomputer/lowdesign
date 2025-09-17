<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Move our "Icon" column right after the checkbox on taxonomy list tables.
 * Works on any taxonomy where a column with key 'ld_icon' is present.
 */
add_action("current_screen", function ($screen) {
    if (empty($screen->base) || $screen->base !== "edit-tags") {
        return;
    } // taxonomy list screen only
    if (empty($screen->taxonomy)) {
        return;
    }

    $tax = $screen->taxonomy;
    add_filter(
        "manage_edit-{$tax}_columns",
        function ($cols) {
            if (!isset($cols["ld_icon"])) {
                return $cols;
            }

            // pull out columns we need to reorder
            $cb = isset($cols["cb"]) ? ["cb" => $cols["cb"]] : [];
            $icon = ["ld_icon" => $cols["ld_icon"]];

            // drop originals
            unset($cols["cb"], $cols["ld_icon"]);

            // new order: checkbox → icon → the rest
            return $cb + $icon + $cols;
        },
        20,
    );
});
