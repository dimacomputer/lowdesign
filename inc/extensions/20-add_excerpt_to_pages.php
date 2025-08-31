<?php
function add_excerpt_to_pages() {
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'add_excerpt_to_pages');