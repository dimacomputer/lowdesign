<?php
if (! defined( 'ABSPATH' ) ) {
    exit;
}

// inc/extensions/icon-system.php
// Helpers and admin integration for SVG sprite icons.

if ( ! function_exists( 'ld_sprite_path' ) ) {
    function ld_sprite_path(): string {
        return get_stylesheet_directory() . '/assets/icons/sprite.svg';
    }
}

if ( ! function_exists( 'ld_sprite_choices_full' ) ) {
    function ld_sprite_choices_full(): array {
        static $choices;

        if ( isset( $choices ) ) {
            return $choices;
        }

        $file = ld_sprite_path();
        if ( ! is_file( $file ) ) {
            return [];
        }

        $svg = file_get_contents( $file );
        if ( ! $svg ) {
            return [];
        }

        preg_match_all( '/<symbol[^>]+id="([^"]+)"/i', $svg, $matches );
        $choices = [];
        foreach ( $matches[1] ?? [] as $id ) {
            $choices[ $id ] = $id;
        }

        return $choices;
    }
}

if ( ! function_exists( 'ld__sprite_load_field_full' ) ) {
    function ld__sprite_load_field_full( $field ) {
        $field['choices'] = ld_sprite_choices_full();
        $field['ui']      = 1;

        return $field;
    }
}

add_filter( 'acf/load_field/name=menu_icon', 'ld__sprite_load_field_full' );
add_filter( 'acf/load_field/name=post_icon_name', 'ld__sprite_load_field_full' );
add_filter( 'acf/load_field/name=term_icon_name', 'ld__sprite_load_field_full' );

add_action( 'admin_enqueue_scripts', function () {
    wp_enqueue_style( 'ld-icon-preview', get_stylesheet_directory_uri() . '/assets/admin/icon-preview.css', [], null );
    wp_enqueue_script( 'ld-icon-preview', get_stylesheet_directory_uri() . '/assets/admin/icon-preview.js', [], null, true );
} );

add_action( 'admin_footer', function () {
    $file = ld_sprite_path();
    if ( ! is_file( $file ) ) {
        return;
    }

    $svg = file_get_contents( $file );
    if ( ! $svg ) {
        return;
    }

    echo '<div style="display:none" class="ld-admin-sprite">' . $svg . '</div>';
} );

add_filter('upload_mimes', function($m){
    if (current_user_can('manage_options')) $m['svg'] = 'image/svg+xml';
    return $m;
});

add_filter( 'walker_nav_menu_start_el', function ( $item_output, $item, $depth, $args ) {
    if ( ! function_exists( 'get_field' ) ) {
        return $item_output;
    }

    $icon = get_field( 'menu_icon', $item );
    if ( ! $icon ) {
        return $item_output;
    }

    $svg = ld_icon( $icon, 'menu__icon' );

    return preg_replace( '/(<a[^>]*>)/', '$1' . $svg, $item_output, 1 );
}, 10, 4 );

if ( ! function_exists( 'ld_term_icon_html' ) ) {
    /**
     * Render a term icon, preferring sprite selections over uploaded images.
     *
     * @param int|WP_Term|null $term  Term ID or object. Falls back to the queried term.
     * @param string           $class Optional additional class names.
     * @param array            $attrs Extra attributes for the rendered tag.
     */
    function ld_term_icon_html( $term = null, string $class = '', array $attrs = [] ): string {
        if ( ! function_exists( 'get_field' ) ) {
            return '';
        }

        if ( ! $term && ( is_tax() || is_category() || is_tag() ) ) {
            $term = get_queried_object();
        }

        if ( $term instanceof WP_Term ) {
            $term_id = (int) $term->term_id;
        } else {
            $term_id = (int) $term;
        }

        if ( ! $term_id ) {
            return '';
        }

        $icon = get_field( 'term_icon_name', 'term_' . $term_id );
        if ( $icon ) {
            return ld_icon( $icon, $class, $attrs );
        }

        $media = (int) get_field( 'term_icon_media', 'term_' . $term_id );
        if ( $media ) {
            $attrs = array_merge( [ 'class' => trim( 'icon ' . $class ) ], $attrs );

            return ld_image_or_svg_html( $media, [ 24, 24 ], $attrs );
        }

        return '';
    }
}

add_filter( 'manage_edit-category_columns', fn( $cols ) => [ 'icon' => __( 'Icon', 'ld' ) ] + $cols );
add_filter(
    'manage_category_custom_column',
    function ( $out, $col, $term_id ) {
        if ( 'icon' !== $col ) {
            return $out;
        }

        $html = ld_term_icon_html( $term_id, '', [ 'style' => 'font-size:18px' ] );

        return $html ?: '—';
    },
    10,
    3
);

add_filter( 'manage_edit-post_tag_columns', fn( $cols ) => [ 'icon' => __( 'Icon', 'ld' ) ] + $cols );
add_filter(
    'manage_post_tag_custom_column',
    function ( $out, $col, $term_id ) {
        if ( 'icon' !== $col ) {
            return $out;
        }

        $html = ld_term_icon_html( $term_id, '', [ 'style' => 'font-size:18px' ] );

        return $html ?: '—';
    },
    10,
    3
);
