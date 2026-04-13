<?php

use MyClub\MyClubGroups\Services\NewsService;
use MyClub\MyClubGroups\Utils;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$myclub_groups_news_title = get_option( 'myclub_groups_news_title' ) ?: __( 'News', 'myclub-groups' );
$myclub_groups_ingress_word_length = (int) get_option( 'myclub_groups_news_ingress_word_length' ) ?: 0;

?>
<div class="myclub-groups-news" id="news">
    <div class="myclub-groups-news-container">
        <h3 class="myclub-groups-header"><?php echo esc_html( $myclub_groups_news_title ) ?></h3>
        <?php

        if ( !empty( $attributes ) ) {
            $post_id = Utils::getPostId( $attributes );
        }

        if ( empty ( $post_id ) || $post_id == 0 ) {
            echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
        } else {
        $meta = get_post_meta( $post_id, 'myclub_groups_id' );

        ?>
        <?php
        if ( !empty( $meta ) ) {
        $myclub_group_id = $meta[ 0 ];
        $query_args = array (
                'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
                'meta_query' => [
                        [
                                'key'     => 'myclub_groups_id',
                                'value'   => $myclub_group_id,
                                'compare' => '='
                        ]
                ]
        );

        $terms = get_terms( $query_args );
        if ( !empty( $terms ) ) {
        $term_id = $terms[ 0 ]->term_id;
        $args = array (
                'post_type'   => 'post',
                'post_status' => 'publish',
                'tax_query'   => array (
                        array (
                                'taxonomy' => NewsService::MYCLUB_GROUP_NEWS,
                                'field'    => 'term_id',
                                'terms'    => array ( $term_id )
                        ),
                ),
                'orderby'     => 'date',
                'order'       => 'DESC',
                'numberposts' => 3
        );

        $posts = get_posts( $args );

        if ( !empty( $posts ) ) {
        ?>
        <div class="myclub-groups-news-list">
            <?php
            foreach ( $posts as $post ) {
                    $myclub_groups_image_html = get_the_post_thumbnail(
                        $post->ID,
                        'medium_large',
                        array(
                            'alt' => esc_attr( $post->post_title ),
                        )
                    );
                    $myclub_groups_image_caption = get_the_post_thumbnail_caption( $post->ID );
                    ?>
                    <div class="myclub-news-item">
                        <a href="<?php echo esc_attr( get_permalink( $post->ID ) ); ?>">
                            <h4>
                                <?php echo esc_html( $post->post_title ); ?>
                            </h4>

                            <?php if ( $myclub_groups_image_html ) { ?>
                                <div class="myclub-news-image">
                                    <?php echo wp_kses_post( $myclub_groups_image_html ); ?>
                                </div>
                            <?php } ?>

                            <?php if ( $myclub_groups_image_caption ) { ?>
                                <div class="myclub-news-image-caption"><?php echo esc_html( $myclub_groups_image_caption ); ?></div>
                            <?php } ?>
                        </a>
                        <div class="myclub-news-ingress">
                            <?php
                            $content = $post->post_excerpt ?: $post->post_content;

                            // Render Gutenberg blocks if any, and shortcodes
                            $content = do_blocks( $content );
                            $content = do_shortcode( $content );

                            if ( $myclub_groups_ingress_word_length > 0 ) {
                                $content = wp_trim_words( wp_strip_all_tags( $content ), $myclub_groups_ingress_word_length, '...' );
                            }

                            // Output safely
                            echo wp_kses_post( $content );
                            ?>
                        </div>
                    </div>
                <?php
            }
            $term_link = get_term_link( $term_id, NewsService::MYCLUB_GROUP_NEWS );

            $args = array (
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                    'tax_query'   => array (
                            array (
                                    'taxonomy' => NewsService::MYCLUB_GROUP_NEWS,
                                    'field'    => 'term_id',
                                    'terms'    => array ( $term_id )
                            ),
                    ),
            );

            $query = new WP_Query( $args );
            $total_posts = $query->found_posts;

            if ( !is_wp_error( $term_link ) && $total_posts > 3 ) {
                echo '<div class="myclub-more-news"><a href="' . esc_url( $term_link ) . '">' . esc_attr__( 'Show more news', 'myclub-groups' ) . '</a></div>';
            }
            echo '</div>';
            } else {
                echo '<div class="no-news">' . esc_attr__( 'No news found', 'myclub-groups' ) . '</div>';
            }
            } else {
                echo '<div class="no-news">' . esc_attr__( 'No news found', 'myclub-groups' ) . '</div>';
            }
            } else {
                echo '<div class="no-news">' . esc_attr__( 'No news found', 'myclub-groups' ) . '</div>';
            }
            }
            ?>
        </div>
    </div>
