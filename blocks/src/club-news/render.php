<?php

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$myclub_groups_club_news_title = get_option( 'myclub_groups_club_news_title' ) ?: __( 'News', 'myclub-groups' );
$myclub_groups_ingress_word_length = (int) get_option( 'myclub_groups_news_ingress_word_length' ) ?: 0;

?>

<div class="myclub-groups-club-news" id="news">
    <div class="myclub-groups-club-news-container">
        <h3 class="myclub-groups-header"><?php echo esc_html( $myclub_groups_club_news_title ); ?></h3>

        <?php

        $club_news_category = get_term_by( 'name', __( 'Club news', 'myclub-groups' ), 'category' );

        $args = array (
                'post_type'   => 'post',
                'post_status' => 'publish',
                'orderby'     => 'date',
                'order'       => 'DESC',
                'numberposts' => 3,
                'tax_query'   => array (
                        array (
                                'taxonomy' => 'category',
                                'field'    => 'term_id',
                                'terms'    => $club_news_category ? $club_news_category->term_id : 0,
                        ),
                ),
        );

        $posts = get_posts( $args );

        if ( !empty( $posts ) ) {
        ?>
        <div class="myclub-groups-club-news-list">

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
            if ( $club_news_category ) {
                $category_link = get_category_link( $club_news_category->term_id );

                if ( !is_wp_error( $category_link ) ) {
                    $args = array (
                            'category__in' => array ( $club_news_category->term_id ),
                            'post_type'    => 'post',
                            'post_status'  => 'publish',
                    );

                    $query = new WP_Query( $args );
                    $total_posts = $query->found_posts;

                    if ( $total_posts > 3 ) {
                        echo '<div class="myclub-more-club-news"><a href="' . esc_url( $category_link ) . '">' . esc_attr__( 'Show more news', 'myclub-groups' ) . '</a></div>';
                    }
                }
            }
            echo '</div>';
            } else {
                echo '<div class="no-news">' . esc_attr__( 'No news found', 'myclub-groups' ) . '</div>';
            }
            ?>
        </div>
    </div>
