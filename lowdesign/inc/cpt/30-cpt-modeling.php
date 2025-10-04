<?php
// Register Custom Post Type: Modeling (hierarchical)
function create_modeling_post_type() {

    $labels = array(
        'name'                  => _x( 'Modelings', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Modeling', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Modelings', 'text_domain' ),
        'name_admin_bar'        => __( 'Modeling', 'text_domain' ),
        'archives'              => __( 'Modeling Archives', 'text_domain' ),
        'attributes'            => __( 'Modeling Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Modeling:', 'text_domain' ),
        'all_items'             => __( 'All Modelings', 'text_domain' ),
        'add_new_item'          => __( 'Add New Modeling', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Modeling', 'text_domain' ),
        'edit_item'             => __( 'Edit Modeling', 'text_domain' ),
        'update_item'           => __( 'Update Modeling', 'text_domain' ),
        'view_item'             => __( 'View Modeling', 'text_domain' ),
        'view_items'            => __( 'View Modelings', 'text_domain' ),
        'search_items'          => __( 'Search Modeling', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into Modeling', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Modeling', 'text_domain' ),
        'items_list'            => __( 'Modelings list', 'text_domain' ),
        'items_list_navigation' => __( 'Modelings list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter Modelings list', 'text_domain' ),
    );

    $args = array(
        'label'                 => __( 'Modeling', 'text_domain' ),
        'description'           => __( 'Post type for Modeling works', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array(
            'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields',
            'page-attributes' // ВАЖНО: включает Parent и Order
        ),
        'taxonomies'            => array( 'category', 'post_tag', 'ui_role' ),
        'hierarchical'          => true,            // ← делаем иерархическим
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-camera',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',         // логично для иерархии (опционально)
        'map_meta_cap'          => true,
        'show_in_rest'          => true,
        'rewrite'               => array(
            'slug'       => 'modeling',
            'with_front' => false,
            'hierarchical' => true // ЧПУ как у страниц: /modeling/parent/child
        ),
    );

    register_post_type( 'modeling', $args );
}
add_action( 'init', 'create_modeling_post_type' );