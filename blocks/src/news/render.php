<?php

use MyClub\MyClubGroups\Services\NewsService;

$postId = $attributes[ 'postId' ] ?? null;

if ( empty( $postId ) ) {
    $postId = get_the_ID();
}

$meta = get_post_meta( $postId, 'myclubGroupId' );

$newsTitle = get_option( 'myclub_groups_news_title' ) ?:  __( 'News', 'myclub-groups' );

$emptyNews = '<div class="no-news">' . __( 'No news found', 'myclub-groups' ) . '</div>';

?>

<div class="myclub-groups-news" id="news">
    <h3 class="myclub-groups-header"><?= $newsTitle ?></h3>

<?php
if ( !empty( $meta ) ) {
    $myclubGroupId = $meta[ 0 ];
    $queryArgs = array (
        'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
        'meta_query' => [
            [
                'key'     => 'myclubGroupId',
                'value'   => $myclubGroupId,
                'compare' => '='
            ]
        ]
    );

    $terms = get_terms( $queryArgs );
    if ( !empty( $terms ) ) {
        $termId = $terms[ 0 ]->term_id;
        $args = array (
            'post_type'   => 'post',
            'post_status' => 'publish',
            'tax_query'   => array (
                array (
                    'taxonomy' => NewsService::MYCLUB_GROUP_NEWS,
                    'field'    => 'term_id',
                    'terms'    => array ( $termId )
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
                $imageUrl = get_the_post_thumbnail_url($post->ID, 'thumbnail');
                ?>
        <div class="myclub-news-item">
            <h4><a href="<?= get_permalink($post->ID) ?>"><?= $post->post_title ?></a></h4>
            <?php if ( $imageUrl ) {?>
            <div class="myclub-news-image">
                <img src="<?= $imageUrl ?>" alt="<?= $post->post_title ?>" />
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
            $termLink = get_term_link( $termId, NewsService::MYCLUB_GROUP_NEWS );

            $args = array(
                'post_type'   => 'post',
                'post_status' => 'publish',
                'tax_query'   => array (
                    array (
                        'taxonomy' => NewsService::MYCLUB_GROUP_NEWS,
                        'field'    => 'term_id',
                        'terms'    => array ( $termId )
                    ),
                ),
            );

            $query = new WP_Query($args);
            $total_posts = $query->found_posts;

            if ( !is_wp_error( $termLink ) && $total_posts > 3 ) {
                echo '<div class="myclub-more-news"><a href="' . $termLink . '">' . __( 'Show more news', 'myclub-groups' ) . '</a></div>';
            }
            echo '</div>';
        } else {
            echo $emptyNews;
        }
    } else {
        echo $emptyNews;
    }
} else {
    echo $emptyNews;
}
?>
</div>
