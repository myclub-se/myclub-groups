<?php

$news_title = get_option( 'myclub_groups_club_news_title' ) ?:  __( 'News', 'myclub-groups' );

?>

<div class="myclub-groups-club-news" id="news">
    <div class="myclub-groups-club-news-container">
        <h3 class="myclub-groups-header"><?php echo esc_attr( $news_title ); ?></h3>

<?php

$args = array (
    'post_type'   => 'post',
    'post_status' => 'publish',
    'orderby'     => 'date',
    'order'       => 'DESC',
    'numberposts' => 3
);

$posts = get_posts( $args );

if ( !empty( $posts ) ) {
?>
        <div class="myclub-groups-club-news-list">

        <?php
        foreach ( $posts as $post ) {
        $image_url = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
        ?>
        <div class="myclub-club-news-item">
            <h4><a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo esc_attr( $post->post_title ); ?></a></h4>
            <?php if ( $image_url ) {?>
                <div class="myclub-club-news-image">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" />
                </div>
            <?php }
            if ( $post->post_excerpt ) {
                echo esc_attr( $post->post_excerpt );
            } else {
                echo esc_attr( $post->post_content );
            } ?>
        </div>
        <?php
            }
            $category_link = null;
            $category = get_term_by( 'name', __( 'News', 'myclub-groups' ), 'category' );

            if ( !is_wp_error( $category ) && isset( $category ) ) {
                $category_id = $category->term_id;
                $category_link = get_category_link( $category->term_id );

                if ( is_wp_error( $category_link ) ) {
                    $category_link = null;
                }
            }

            if ( !empty( $category_link ) && !empty( $category_id ) ) {
                $args = array(
                    'category__in' => array( $category_id),
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                );

                $query = new WP_Query($args);
                $total_posts = $query->found_posts;

                if ( $total_posts > 3 ) {
                    echo '<div class="myclub-more-club-news"><a href="' . esc_url( $category_link ) . '">' . esc_attr__( 'Show more news', 'myclub-groups' ) . '</a></div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="no-news">' . esc_attr__( 'No news found', 'myclub-groups' ) . '</div>';
        }
?>
        </div>
    </div>
