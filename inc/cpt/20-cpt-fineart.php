<?php
// Register Custom Post Type: Fine Art (hierarchical like Pages)
function create_fineart_post_type() {

    $labels = array(
        'name'                  => _x( 'Fine Arts', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Fine Art', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Fine Arts', 'text_domain' ),
        'name_admin_bar'        => __( 'Fine Art', 'text_domain' ),
        'archives'              => __( 'Fine Art Archives', 'text_domain' ),
        'attributes'            => __( 'Fine Art Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Fine Art:', 'text_domain' ),
        'all_items'             => __( 'All Fine Arts', 'text_domain' ),
        'add_new_item'          => __( 'Add New Fine Art', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Fine Art', 'text_domain' ),
        'edit_item'             => __( 'Edit Fine Art', 'text_domain' ),
        'update_item'           => __( 'Update Fine Art', 'text_domain' ),
        'view_item'             => __( 'View Fine Art', 'text_domain' ),
        'view_items'            => __( 'View Fine Arts', 'text_domain' ),
        'search_items'          => __( 'Search Fine Art', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into Fine Art', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Fine Art', 'text_domain' ),
        'items_list'            => __( 'Fine Arts list', 'text_domain' ),
        'items_list_navigation' => __( 'Fine Arts list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter Fine Arts list', 'text_domain' ),
    );

    $args = array(
        'label'                 => __( 'Fine Art', 'text_domain' ),
        'description'           => __( 'Post type for Fine Art works', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array(
            'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields',
            'page-attributes' // enables Parent and Order (menu_order)
        ),
        'taxonomies'            => array( 'category', 'post_tag', 'ui_role' ),
        'hierarchical'          => true, // allow parent/child like Pages
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-art',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',  // permissions model like Pages
        'map_meta_cap'          => true,
        'show_in_rest'          => true,    // Gutenberg / REST
        'rewrite'               => array(
            'slug'          => 'fineart',
            'with_front'    => false,
            'hierarchical'  => true // pretty permalinks /fineart/parent/child
        ),
    );

    register_post_type( 'fineart', $args );
}
add_action( 'init', 'create_fineart_post_type' );