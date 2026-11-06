<?php

namespace MyClub\MyClubGroups\Services;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class NewsAuthor
 *
 * Shows the author from MyClub on the news posts that are imported from MyClub. The imported posts are not
 * written by a WordPress user, so the author name is stored in the post meta field 'myclub_news_author' and
 * used in place of the display name of the post author.
 *
 * @since 2.9.0
 */
class NewsAuthor
{
    const AUTHOR_META_KEY = 'myclub_news_author';

    /**
     * Register the filters that replace the author name of imported news posts.
     *
     * @return void
     */
    public function register()
    {
        add_filter( 'the_author', [
            $this,
            'filterAuthorName'
        ] );
        add_filter( 'get_the_author_display_name', [
            $this,
            'filterAuthorName'
        ] );
        add_filter( 'render_block_core/post-author-name', [
            $this,
            'renderPostAuthorNameBlock'
        ], 10, 3 );
    }

    /**
     * Get the MyClub author name of a news post.
     *
     * @param int|\WP_Post|null $post The post or post id. Defaults to the current post.
     *
     * @return string The author name or an empty string if the post has no MyClub author.
     */
    public static function getAuthor( $post = null ): string
    {
        $post = get_post( $post );

        if ( !$post ) {
            return '';
        }

        return (string)get_post_meta( $post->ID, self::AUTHOR_META_KEY, true );
    }

    /**
     * Replace the author name with the MyClub author name for news posts imported from MyClub.
     *
     * The admin and the author archives are left untouched, since they are about the WordPress user.
     *
     * @param string|null $display_name The display name of the post author.
     *
     * @return string|null The MyClub author name if set, otherwise the original display name.
     */
    public function filterAuthorName( $display_name )
    {
        if ( is_admin() || is_author() ) {
            return $display_name;
        }

        $author = self::getAuthor();

        return $author !== '' ? $author : $display_name;
    }

    /**
     * Render the post author name block for news posts imported from MyClub.
     *
     * The imported posts have no WordPress user as author, and the core block renders nothing when the post
     * has no author. Render the MyClub author name instead in that case.
     *
     * @param string $block_content The rendered block content.
     * @param array $block The parsed block.
     * @param \WP_Block|null $instance The block instance.
     *
     * @return string The block content.
     */
    public function renderPostAuthorNameBlock( $block_content, $block, $instance = null )
    {
        if ( trim( (string)$block_content ) !== '' ) {
            return $block_content;
        }

        $post_id = $instance->context[ 'postId' ] ?? get_the_ID();
        $author = $post_id ? self::getAuthor( $post_id ) : '';

        if ( $author === '' ) {
            return $block_content;
        }

        $classes = [ 'wp-block-post-author-name' ];
        if ( !empty( $block[ 'attrs' ][ 'textAlign' ] ) ) {
            $classes[] = 'has-text-align-' . sanitize_html_class( $block[ 'attrs' ][ 'textAlign' ] );
        }

        return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . esc_html( $author ) . '</div>';
    }
}
