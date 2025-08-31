<?php
function lowdesign_custom_template_loader($template) {
    $post_type = get_post_type();
    $category = get_the_category();

    if (is_singular() && $post_type && $category) {
        $cat_slug = $category[0]->slug ?? null;
        if ($cat_slug) {
            $custom_template = locate_template("page-{$cat_slug}.php");
            if ($custom_template) {
                return $custom_template;
            }
        }
    }

    return $template;
}
add_filter('template_include', 'lowdesign_custom_template_loader');