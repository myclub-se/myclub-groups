<?php

namespace MyClub\MyClubGroups\Services;

use DateTime;
use DateTimeZone;
use Exception;
use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\RefreshNewsTask;
use MyClub\MyClubGroups\Utils;
use WP_Query;

/**
 * Class NewsService
 *
 * This class is responsible for refreshing news items and adding them to WordPress posts.
 * It extends the Groups class.
 */
class NewsService extends Groups
{
    const MYCLUB_GROUP_NEWS = 'myclub-group-news';

    private $myclubTimezone;
    private $timezone;
    private $utcTimezone;

    /**
     * Constructor method for the class.
     *
     * Initializes the class properties with the appropriate timezones.
     * It sets the $myclubTimezone property to the 'Europe/Stockholm' timezone,
     * the $timezone property to the timezone string returned by the `get_option()`
     * function, and the $utcTimezone property to the 'UTC' timezone.
     *
     * @return void
     *
     */
    public function __construct()
    {
        try {
            $this->myclubTimezone = new DateTimeZone( 'Europe/Stockholm' );
            $this->timezone = new DateTimeZone( get_option( 'timezone_string' ) );
            $this->utcTimezone = new DateTimeZone( 'UTC' );
        } catch (Exception $e) {
            error_log( 'Unable to get timezones' );
        }
    }

    /**
     * Reloads the news by initiating a background task to refresh the news data.
     *
     * It creates a new instance of the `RestApi` class and loads the menu items
     * using the `loadMenuItems()` method. If the `menuItemsExist()` method returns
     * true for the loaded menu items, it retrieves the group ids using the `getGroupIds()`
     * method. It then initializes the `RefreshNewsTask` and pushes null to the task queue.
     * Next, it iterates through the group ids and pushes each id to the task queue.
     * Finally, it saves and dispatches the task to start the background execution.
     *
     * @return void
     */
    public function reloadNews()
    {
        $api = new RestApi();

        $menuItems = $api->loadMenuItems()->result;

        if ( $this->menuItemsExist( $menuItems ) ) {
            $ids = $this->getGroupIds( $menuItems, [] );

            $process = RefreshNewsTask::init();
            $process->push_to_queue(null);

            foreach( $ids as $id ) {
                $process->push_to_queue($id);
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }
    }

    public function loadNews( string $groupId = null ) {
        $api = new RestApi();
        $response = $api->loadNews( $groupId );
        $group = null;

        if ( $groupId !== null ) {
            $groupName = $this->getGroupName( $groupId );

            if ( $groupName !== null ) {
                $group = array(
                    'id' => $groupId,
                    'name' => $groupName
                );
            }
        }

        if( !is_wp_error( $response ) && $response->status === 200 ) {
            foreach ( $response->result->results as $newsItem ) {
                $this->addNews( $newsItem, $group );
            }
        }
    }

    public function addDefaultCategory( $postId ) {
        $category = get_term_by( 'name', __( 'News', 'myclub-groups' ), 'category' );

        if ( $category === false ) {
            $categoryId = wp_insert_term( __( 'News', 'myclub-groups' ), 'category' );
            if ( $categoryId == 0 || is_wp_error( $categoryId ) ) {
                error_log( 'Unable to add default category' );
                $categoryId = null;
            }
        } else {
            $categoryId = $category->term_id;
        }

        if ( $categoryId !== null ) {
            wp_set_post_categories( $postId, array( $categoryId ) );
        }
    }

    /**
     * Adds a news item to the database.
     *
     * Retrieves an existing news item from the database if it already exists based on the 'myclubNewsId' meta value.
     * If the news item already exists, updates the existing post using the 'wp_update_post()' function.
     * If the news item does not exist, creates a new post using the 'wp_insert_post()' function.
     *
     * Adds a featured image to the news item post using the 'Utils::addFeaturedImage()' method.
     *
     * If a myclub group is specified, checks if a term with the 'myclubGroupId' meta value exists in the 'RefreshNews::MYCLUB_GROUP_NEWS' taxonomy.
     * If the term exists, assigns the term to the news item post using the 'wp_set_post_terms()' function.
     * If the term does not exist, inserts a new term with the myclub group name and assigns it to the news item post.
     *
     * @param object $newsItem The news item to be added to the database.
     * @param array|null $myclubGroup Optional. The myclub group to assign to the news item.
     *
     * @return void
     * @since 1.0.0
     *
     */
    private function addNews( $newsItem, array $myclubGroup = null )
    {
        $queryArgs = array (
            'posts_per_page' => 1,
            'post_type'      => 'post',
            'meta_query'     => [
                [
                    'key'     => 'myclubNewsId',
                    'value'   => $newsItem->id,
                    'compare' => '='
                ]
            ]
        );

        $queryResults = new WP_Query( $queryArgs );

        $postId = $queryResults->have_posts() ? wp_update_post( $this->createNewsArgs( $newsItem, $queryResults->posts[ 0 ]->ID ) ) : wp_insert_post( $this->createNewsArgs( $newsItem ) );

        Utils::addFeaturedImage( $postId, $newsItem->news_image, 'news_' . $newsItem->id );
        $this->addDefaultCategory( $postId );

        if ( $myclubGroup ) {
            $queryArgs = array (
                'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
                'meta_query' => [
                    [
                        'key'     => 'myclubGroupId',
                        'value'   => $myclubGroup[ 'id' ],
                        'compare' => '='
                    ]
                ]
            );

            $terms = get_terms( $queryArgs );

            if ( !empty( $terms ) ) {
                $termId = $terms[ 0 ]->term_id;
            } else {
                $term = wp_insert_term( $myclubGroup[ 'name' ], NewsService::MYCLUB_GROUP_NEWS );

                if ( is_wp_error( $term ) ) {
                    if ( 'post_exists' === $term->get_error_code() ) {
                        error_log('Post exists');
                    }
                }

                add_term_meta( $term[ 'term_id' ], 'myclubGroupId', $myclubGroup[ 'id' ] );
                $termId = $term[ 'term_id' ];
            }

            $terms = wp_get_post_terms( $postId, NewsService::MYCLUB_GROUP_NEWS );

            if ( !is_wp_error( $terms ) ) {
                $termIds = array_map( function ( $term ) {
                    return (int) $term->term_id;
                }, $terms );

                if ( !array_search( $termId, $termIds ) ) {
                    $termIds[] = (int) $termId;
                    wp_set_post_terms( $postId, $termIds, NewsService::MYCLUB_GROUP_NEWS );
                }
            }
        }
    }

    /**
     * Creates the arguments array for creating or updating a news post.
     *
     * This method takes a news item object and an optional post ID as parameters and
     * returns an array with the necessary arguments for creating or updating a news post.
     *
     * @param object $newsItem The news item object containing the necessary data.
     * @param int|null $postId The ID of the post to update, or null to create a new post.
     *
     * @return array The arguments array for creating or updating a news post.
     * @since 1.0.0
     */
    private function createNewsArgs( $newsItem, int $postId = null ): array
    {
        $time = str_replace( 'T', ' ', str_replace( 'Z', '', $newsItem->published_date ) );

        // Get time for post and make sure that the time is correct with utc time as well
        try {
            $dateTimeUtc = new DateTime( $time, $this->myclubTimezone );
            $dateTimeUtc->setTimezone( $this->utcTimezone );
            $gmtTime = $dateTimeUtc->format( 'Y-m-d H:i:s' );

            $dateTimeUtc->setTimezone( $this->timezone );
            $time = $dateTimeUtc->format( 'Y-m-d H:i:s' );
        } catch (Exception $e) {
            $gmtTime = $time;
        }

        $postContent = $newsItem->ingress;

        if ( !empty ( wp_strip_all_tags( $newsItem->text ) ) ) {
            $postContent .= $newsItem->text;
        }

        $args = [
            'post_date'     => $time,
            'post_date_gmt' => $gmtTime,
            'post_title'    => $newsItem->title,
            'post_name'     => sanitize_title( $newsItem->title ),
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_excerpt'  => $newsItem->ingress,
            'post_content'  => $postContent,
            'meta_input'    => [
                'myclubNewsId' => $newsItem->id,
            ]
        ];

        if ( $postId !== null ) {
            $args[ 'ID' ] = $postId;
        }

        return $args;
    }

    private function getGroupName( string $groupId ) {
        $args = array(
            'post_type'  => 'myclub-groups',
            'meta_query' => array(
                array(
                    'key'     => 'myclubGroupId',
                    'value'   => $groupId,
                    'compare' => '='
                ),
            ),
            'posts_per_page' => 1
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            return $query->posts[ 0 ]->post_title;
        } else {
            return null;
        }
    }
}
