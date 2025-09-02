<?php
/**
 * Register Custom Post Type: config
 */

function lowdesign_register_config_post_type()
{
    $labels = array(
        'name'                  => _x('Configurations', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Configuration', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Config', 'text_domain'),
        'name_admin_bar'        => __('Config', 'text_domain'),
        'archives'              => __('Config Archives', 'text_domain'),
        'attributes'            => __('Config Attributes', 'text_domain'),
        'parent_item_colon'     => __('Parent Config:', 'text_domain'),
        'all_items'             => __('All Configs', 'text_domain'),
        'add_new_item'          => __('Add New Config', 'text_domain'),
        'add_new'               => __('Add New', 'text_domain'),
        'new_item'              => __('New Config', 'text_domain'),
        'edit_item'             => __('Edit Config', 'text_domain'),
        'update_item'           => __('Update Config', 'text_domain'),
        'view_item'             => __('View Config', 'text_domain'),
        'view_items'            => __('View Configs', 'text_domain'),
        'search_items'          => __('Search Config', 'text_domain'),
        'not_found'             => __('Not found', 'text_domain'),
        'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
        'featured_image'        => __('Featured Image', 'text_domain'),
        'set_featured_image'    => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image'    => __('Use as featured image', 'text_domain'),
        'insert_into_item'      => __('Insert into config', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this config', 'text_domain'),
        'items_list'            => __('Configs list', 'text_domain'),
        'items_list_navigation' => __('Configs list navigation', 'text_domain'),
        'filter_items_list'     => __('Filter configs list', 'text_domain'),
    );
    $args = array(
        'label'                 => __('Config', 'text_domain'),
        'description'           => __('System Configurations', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'custom-fields'),
        'taxonomies'            => array('ui_role'),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 80,
        'menu_icon'             => 'dashicons-admin-generic',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    register_post_type('config', $args);
}

add_action('init', 'lowdesign_register_config_post_type', 0);