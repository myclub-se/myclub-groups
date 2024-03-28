<?php

use MyClub\MyClubGroups\Services\NewsService;

$post_id = $attributes[ 'postId' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = get_post_meta( $post_id, 'myclub_group_id' );

$news_title = get_option( 'myclub_groups_news_title' ) ?:  __( 'News', 'myclub-groups' );

$empty_news = '<div class="no-news">' . __( 'No news found', 'myclub-groups' ) . '</div>';

?>

<div class="myclub-groups-news" id="news">
    <div class="myclub-groups-news-container">
        <h3 class="myclub-groups-header"><?= $news_title ?></h3>

<?php
if ( !empty( $meta ) ) {
    $myclub_group_id = $meta[ 0 ];
    $query_args = array (
        'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
        'meta_query' => [
            [
                'key'     => 'myclub_group_id',
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
                $image_url = get_the_post_thumbnail_url($post->ID, 'thumbnail');
                ?>
        <div class="myclub-news-item">
            <h4><a href="<?= get_permalink($post->ID) ?>"><?= $post->post_title ?></a></h4>
            <?php if ( $image_url ) {?>
            <div class="myclub-news-image">
                <img src="<?= $image_url ?>" alt="<?= $post->post_title ?>" />
            </div>
            <?php }
                if ( $post->post_excerpt ) {
                    echo $post->post_excerpt;
                } else {
                    echo $post->post_content;
                } ?>
        </div>
        <?php
            }
            $term_link = get_term_link( $term_id, NewsService::MYCLUB_GROUP_NEWS );

            $args = array(
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

            $query = new WP_Query($args);
            $total_posts = $query->found_posts;

            if ( !is_wp_error( $term_link ) && $total_posts > 3 ) {
                echo '<div class="myclub-more-news"><a href="' . $term_link . '">' . __( 'Show more news', 'myclub-groups' ) . '</a></div>';
            }
            echo '</div>';
        } else {
            echo $empty_news;
        }
    } else {
        echo $empty_news;
    }
} else {
    echo $empty_news;
}
?>
    </div>
</div>
