<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'ld_content_icon' ) ) {
    /**
     * Return a content icon, preferring sprite selections over uploads.
     *
     * @param int|null $post_id Optional post ID. Defaults to current post.
     * @param array    $attrs   Additional attributes for the rendered tag.
     *
     * @return string SVG or image HTML.
     */
    function ld_content_icon( $post_id = null, array $attrs = [] ): string {
        $post_id = $post_id ?: get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $name = function_exists( 'get_field' ) ? (string) get_field( 'post_icon_name', $post_id ) : '';
        if ( $name && function_exists( 'ld_icon' ) ) {
            return ld_icon( $name, '', $attrs );
        }

        $img_id = function_exists( 'get_field' ) ? (int) get_field( 'term_icon_media', $post_id ) : 0;

        return ld_image_or_svg_html( $img_id, 'full', $attrs );
    }
}

