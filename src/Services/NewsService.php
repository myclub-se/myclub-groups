<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use DateTime;
use DateTimeZone;
use Exception;
use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\ImageTask;
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

    private DateTimeZone $myclub_timezone;
    private DateTimeZone $timezone;
    private DateTimeZone $utc_timezone;

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
            $this->myclub_timezone = new DateTimeZone( 'Europe/Stockholm' );
            $this->utc_timezone = new DateTimeZone( 'UTC' );
            $timezone_string = wp_timezone_string();
            if ( !$timezone_string ) {
                $timezone_string = 'Europe/Stockholm';
            }
            $this->timezone = new DateTimeZone( $timezone_string );
        } catch ( Exception $e ) {
            error_log( 'Unable to get timezones' );
        }
    }

    /**
     * Deletes all news posts that have a 'myclub_news_id' meta field.
     *
     * This method queries the WordPress database for all posts of type 'post' that have a 'myclub_news_id' meta field.
     * It then loops through the query results and deletes each post using the `Utils::deletePost` method. This
     * is a very destructive method and cannot be undone.
     *
     * @return void
     * @since 1.0.0
     */
    public function delete_all_news()
    {
        $group_news_taxonomy = 'myclub-group-news';
        $args = array (
            'post_type'      => 'post',
            'meta_query'     => array (
                array (
                    'key'     => 'myclub_news_id',
                    'compare' => 'EXISTS',
                ),
            ),
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();

                $post_id = $query->post->ID;

                Utils::delete_post( $post_id );
            }
        }

        // Delete custom news taxonomy
        $terms = get_terms( [
            'taxonomy'   => $group_news_taxonomy,
            'hide_empty' => false,
        ] );

        // Delete all terms associated with the custom news taxonomy
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                wp_delete_term( $term->term_id, $group_news_taxonomy );
            }
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
    public function reload_news()
    {
        // Load menu items from member backend
        $groups = $this->get_all_group_ids();

        if ( $groups->success ) {
            $process = RefreshNewsTask::init();
            $process->push_to_queue( null );

            foreach ( $groups->ids as $id ) {
                $process->push_to_queue( $id );
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }
    }

    public function load_news( string $group_id = null )
    {
        $api = new RestApi();
        $response = $api->load_news( $group_id );
        $group = null;

        if ( $group_id !== null ) {
            $groupName = $this->get_group_name( $group_id );

            if ( $groupName !== null ) {
                $group = array (
                    'id'   => $group_id,
                    'name' => $groupName
                );
            }
        }

        if ( !is_wp_error( $response ) && $response->status === 200 ) {
            foreach ( $response->result->results as $newsItem ) {
                $this->add_news( $newsItem, $group );
            }
        }
    }

    /**
     * Adds the default category 'News' to a post.
     *
     * This method checks if the category 'News' exists in the 'category' taxonomy. If the category does not
     * exist, it creates the category using the 'wp_insert_term' function and assigns the generated category ID to the
     * `$categoryId` variable. If the category already exists, it retrieves the category ID using the 'get_term_by' function.
     *
     * If the category ID is not null, the method sets the post categories using the 'wp_set_post_categories' function,
     * assigning the 'News' category to the specified post identified by the `$post_id` parameter.
     *
     * @param int $post_id The ID of the post to add the default category to.
     * @return void
     * @since 1.0.0
     */
    public function add_default_category( int $post_id )
    {
        $category = get_term_by( 'name', __( 'News', 'myclub-groups' ), 'category' );

        if ( $category === false ) {
            $category_id = wp_insert_term( __( 'News', 'myclub-groups' ), 'category' );
            if ( $category_id == 0 || is_wp_error( $category_id ) ) {
                error_log( 'Unable to add default category' );
                $category_id = null;
            }
        } else {
            $category_id = $category->term_id;
        }

        if ( $category_id !== null ) {
            wp_set_post_categories( $post_id, array ( $category_id ) );
        }
    }

    /**
     * Adds a news item to the database.
     *
     * Retrieves an existing news item from the database if it already exists based on the 'myclub_news_id' meta value.
     * If the news item already exists, updates the existing post using the 'wp_update_post()' function.
     * If the news item does not exist, creates a new post using the 'wp_insert_post()' function.
     *
     * Adds a featured image to the news item post using the 'Utils::addFeaturedImage()' method.
     *
     * If a myclub group is specified, checks if a term with the 'myclub_groups_id' meta value exists in the 'RefreshNews::MYCLUB_GROUP_NEWS' taxonomy.
     * If the term exists, assigns the term to the news item post using the 'wp_set_post_terms()' function.
     * If the term does not exist, inserts a new term with the myclub group name and assigns it to the news item post.
     *
     * @param object $news_item The news item to be added to the database.
     * @param array|null $myclub_group Optional. The myclub group to assign to the news item.
     *
     * @return void
     * @since 1.0.0
     *
     */
    private function add_news( object $news_item, array $myclub_group = null )
    {
        $query_args = array (
            'posts_per_page' => 1,
            'post_type'      => 'post',
            'meta_query'     => [
                [
                    'key'     => 'myclub_news_id',
                    'value'   => $news_item->id,
                    'compare' => '='
                ]
            ]
        );

        $query_results = new WP_Query( $query_args );

        $post_id = $query_results->have_posts() ? wp_update_post( $this->create_news_args( $news_item, $query_results->posts[ 0 ]->ID ) ) : wp_insert_post( $this->create_news_args( $news_item ) );

        if ( isset( $news_item->news_image ) ) {
            $image_task = ImageTask::init();

            $image_task->push_to_queue(
                wp_json_encode( array (
                    'post_id' => $post_id,
                    'type'    => 'news',
                    'image'   => $news_item->news_image,
                    'news_id' => $news_item->id
                ), JSON_UNESCAPED_UNICODE )
            );
            $image_task->save()->dispatch();
        }

        $this->add_default_category( $post_id );

        if ( $myclub_group ) {
            $query_args = array (
                'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
                'meta_query' => [
                    [
                        'key'     => 'myclub_groups_id',
                        'value'   => $myclub_group[ 'id' ],
                        'compare' => '='
                    ]
                ]
            );

            $terms = get_terms( $query_args );

            if ( !empty( $terms ) ) {
                $term_id = $terms[ 0 ]->term_id;
            } else {
                $term = wp_insert_term( $myclub_group[ 'name' ], NewsService::MYCLUB_GROUP_NEWS );

                if ( is_wp_error( $term ) ) {
                    $term_id = 0;

                    if ( 'term_exists' === $term->get_error_code() ) {
                        $term = get_term_by( 'name', $myclub_group[ 'name' ], NewsService::MYCLUB_GROUP_NEWS );

                        if ( $term && !is_wp_error( $term ) ) {
                            $term_id = $term->term_id;
                        }
                    }
                } else {
                    $term_id = $term[ 'term_id' ];
                }

                if ( $term_id ) {
                    add_term_meta( $term_id, 'myclub_groups_id', $myclub_group[ 'id' ] );
                }
            }

            $terms = wp_get_post_terms( $post_id, NewsService::MYCLUB_GROUP_NEWS );

            if ( !is_wp_error( $terms ) && $term_id ) {
                $term_ids = array_map( function ( $term ) {
                    return (int)$term->term_id;
                }, $terms );

                if ( !array_search( $term_id, $term_ids ) ) {
                    $term_ids[] = (int)$term_id;
                    wp_set_post_terms( $post_id, $term_ids, NewsService::MYCLUB_GROUP_NEWS );
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
     * @param object $news_item The news item object containing the necessary data.
     * @param int|null $post_id The ID of the post to update, or null to create a new post.
     *
     * @return array The arguments array for creating or updating a news post.
     * @since 1.0.0
     */
    private function create_news_args( object $news_item, int $post_id = null ): array
    {
        $time = str_replace( 'T', ' ', str_replace( 'Z', '', $news_item->published_date ) );

        // Get time for post and make sure that the time is correct with utc time as well
        try {
            $date_time_utc = new DateTime( $time, $this->myclub_timezone );
            $date_time_utc->setTimezone( $this->utc_timezone );
            $gmtTime = $date_time_utc->format( 'Y-m-d H:i:s' );

            $date_time_utc->setTimezone( $this->timezone );
            $time = $date_time_utc->format( 'Y-m-d H:i:s' );
        } catch ( Exception $e ) {
            $gmtTime = $time;
        }

        $post_content = $news_item->ingress;

        if ( !empty ( wp_strip_all_tags( $news_item->text ) ) ) {
            $post_content .= $news_item->text;
        }

        $args = [
            'post_date'     => $time,
            'post_date_gmt' => $gmtTime,
            'post_title'    => $news_item->title,
            'post_name'     => sanitize_title( $news_item->title ),
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_excerpt'  => $news_item->ingress,
            'post_content'  => $post_content,
            'meta_input'    => [
                'myclub_news_id' => $news_item->id,
            ]
        ];

        if ( $post_id !== null ) {
            $args[ 'ID' ] = $post_id;
        }

        return $args;
    }

    /**
     * Returns the name of a group based on its ID.
     *
     * This method queries the WordPress database for a post of type 'myclub-groups' that has a 'myclub_groups_id'
     * meta field matching the provided group ID. If a matching group is found, its name is returned. If no matching
     * group is found, null is returned.
     *
     * @param string $group_id The ID of the group to retrieve the name for.
     * @return string|null The name of the group, or null if no matching group is found.
     * @since 1.0.0
     */
    private function get_group_name( string $group_id )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'meta_query'     => array (
                array (
                    'key'     => 'myclub_groups_id',
                    'value'   => $group_id,
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
