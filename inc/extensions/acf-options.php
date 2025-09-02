<?php
function add_taxonomies_to_custom_post_types() {
    // Attach default 'category' taxonomy
    register_taxonomy_for_object_type('category', 'page');
    register_taxonomy_for_object_type('category', 'fineart');
    register_taxonomy_for_object_type('category', 'photo');

    // Attach custom 'ui_role' taxonomy to pages
    register_taxonomy_for_object_type('ui_role', 'page');
}
add_action('init', 'add_taxonomies_to_custom_post_types');