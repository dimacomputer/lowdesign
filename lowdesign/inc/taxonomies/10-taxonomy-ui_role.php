<?php
/**
 * Таксономия «UI Role» — для размечения сущностей ролями интерфейса:
 * примеры терминов: menu, footer, hero, card и т.п.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    $labels = [
        'name'          => _x('UI Roles', 'taxonomy general name', 'lowdesign-margo'),
        'singular_name' => _x('UI Role', 'taxonomy singular name', 'lowdesign-margo'),
        'search_items'  => __('Search UI Roles', 'lowdesign-margo'),
        'all_items'     => __('All UI Roles', 'lowdesign-margo'),
        'edit_item'     => __('Edit UI Role', 'lowdesign-margo'),
        'update_item'   => __('Update UI Role', 'lowdesign-margo'),
        'add_new_item'  => __('Add New UI Role', 'lowdesign-margo'),
        'new_item_name' => __('New UI Role', 'lowdesign-margo'),
        'menu_name'     => __('UI Role', 'lowdesign-margo'),
    ];

    // Привяжем к нужным типам записей
    $object_types = ['post', 'page'];
    if (post_type_exists('fineart'))  $object_types[] = 'fineart';
    if (post_type_exists('modeling')) $object_types[] = 'modeling';

    register_taxonomy('ui_role', $object_types, [
        'labels'            => $labels,
        'public'            => true,          // можно использовать в WP_Query
        'show_ui'           => true,
        'show_in_rest'      => true,          // редактор блоков + ACF REST
        'hierarchical'      => false,         // как теги (можно true, если нужно дерево)
        'show_admin_column' => true,          // колонка в админ-таблицах
        'query_var'         => true,
        'rewrite'           => false,         // без собственных URL (нам не нужно)
    ]);
}, 5); // ранний приоритет, чтобы был доступен в ранних запросах