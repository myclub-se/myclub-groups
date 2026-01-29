<?php

namespace MyClub\MyClubGroups\Services;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use DateTime;
use DateTimeZone;
use Exception;
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
        parent::__construct();
        try {
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
     * Class destructor to clean up resources.
     *
     * This method releases memory by unsetting properties related to time zones and the API instance.
     * It is automatically called when the object is destroyed or goes out of scope.
     *
     * @return void
     * @since 2.0.0
     */
    public function __destruct()
    {
        unset( $this->timezone, $this->utc_timezone, $this->api );
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
    public function addDefaultCategory( int $post_id )
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
     * Adds a team news category to a specified post.
     *
     * This method checks if the option to add news categories is enabled. If enabled, it ensures that
     * the corresponding category for the provided team group exists. If the category does not exist, it
     * creates a new one. Finally, it assigns the category to the given post if it is not already assigned.
     *
     * @param int $post_id The ID of the post to which the team news category will be added.
     * @param array $myclub_group An optional associative array containing group data, including the 'name' key.
     *                            The 'name' key is used to match or create the category.
     *
     * @return void
     * @since 1.3.1
     */
    public function addTeamNewsCategory( int $post_id, array $myclub_group )
    {
        $add_news_category = get_option( 'myclub_groups_add_news_categories' );

        if ( $add_news_category === '1' ) {
            $category = get_term_by( 'name', $myclub_group[ 'name' ], 'category' );

            if ( $category === false ) {
                $category_id = wp_insert_term( $myclub_group[ 'name' ], 'category' );
                if ( $category_id == 0 || is_wp_error( $category_id ) ) {
                    error_log( 'Unable to add team category' );
                    $category_id = null;
                }
            } else {
                $category_id = $category->term_id;
            }

            if ( $category_id !== null ) {
                $categories = wp_get_post_categories( $post_id );

                if ( !in_array( $category_id, $categories ) ) {
                    wp_set_post_categories( $post_id, array_merge( $categories, [ $category_id ] ) );
                }
            }
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
    public function deleteAllNews()
    {
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

                Utils::deletePost( $post_id, true );
            }
        }

        // Temporarily add the myclub-group-news taxonomy to be able to delete it
        register_taxonomy( NewsService::MYCLUB_GROUP_NEWS, 'post', [
            'label'        => __( 'Group news', 'myclub-groups' ),
            'show_in_rest' => true,
        ] );

        // Get all terms in the custom taxonomy
        $terms = get_terms( [
            'taxonomy'   => NewsService::MYCLUB_GROUP_NEWS,
            'hide_empty' => false,
        ] );

        if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
            global $wpdb; // Access the WordPress database

            foreach ( $terms as $term ) {
                // Manually update the count to zero
                $wpdb->update(
                    $wpdb->term_taxonomy,
                    [ 'count' => 0 ],
                    [ 'term_id' => $term->term_id ]
                );

                // Delete the term
                wp_delete_term( $term->term_id, NewsService::MYCLUB_GROUP_NEWS );
            }
        }

        unset( $args, $query, $terms );
    }

    /**
     * Loads news items from an external API and optionally associates them with a group.
     *
     * This method uses a REST API to fetch news items. If a group ID is supplied, it attempts to retrieve
     * the group name and associate the news items with the group. The fetched news items are processed
     * and added using the `add_news` method.
     *
     * @param string|null $group_id The ID of the group to associate with the news, or null if no group is specified.
     * @return void
     * @since 1.0.0
     */
    public function loadNews( string $group_id = null )
    {
        $response = $this->api->loadNews( $group_id );
        $group = null;

        if ( $group_id !== null ) {
            $groupName = $this->getGroupName( $group_id );

            if ( $groupName !== null ) {
                $group = array (
                    'id'   => $group_id,
                    'name' => $groupName
                );
            }
        }

        if ( !is_wp_error( $response ) && $response->status === 200 ) {
            foreach ( $response->result->results as $newsItem ) {
                $this->addNews( $newsItem, $group );
            }
        }

        unset( $response );
    }

    /**
     * Reloads news for all groups by initiating a background task.
     *
     * This method retrieves all group IDs from the backend and uses a background task process
     * to refresh the news for each group. It queues the tasks for processing and dispatches the task
     * to be executed asynchronously.
     *
     * @return void
     * @since 1.0.0
     */
    public function reloadNews()
    {
        // Load menu items from member backend
        $groups = $this->getAllGroupIds();

        if ( $groups->success ) {
            $process = RefreshNewsTask::init();
            $process->push_to_queue( null );

            foreach ( $groups->ids as $id ) {
                $process->push_to_queue( $id );
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }

        unset( $groups );
    }

    /**
     * Removes unused news posts that are not associated with any remote group or global news IDs.
     *
     * This method retrieves all group IDs and their corresponding news post IDs, then compares
     * these IDs against the local WordPress posts that have a 'myclub_news_id' meta field. Any
     * posts with meta IDs not found in the remote data are considered unused and are deleted
     * using the `Utils::deletePost` method. This ensures that the local database is
     * synchronized with the remote data and clears out obsolete news posts.
     *
     * @return void
     * @since 1.3.3
     */
    public function removeUnusedNewsItems(): void
    {
        if ( get_option( 'myclub_groups_remove_unused_news_items' ) !== '1' ) {
            $group_ids = $this->getAllGroupIds();
            $remote_news_ids = [];

            if ( $group_ids->success ) {
                foreach ( $group_ids->ids as $group_id ) {
                    $remote_news_ids = array_merge( $remote_news_ids, $this->getNewsIds( $group_id ) );
                }

                $remote_news_ids = array_merge( $remote_news_ids, $this->getNewsIds( null ) );

                $remote_news_ids = array_unique( $remote_news_ids );

                if ( !empty( $remote_news_ids ) ) {
                    $args = array (
                        'post_type'      => 'post',
                        'meta_query'     => array (
                            array (
                                'key'     => 'myclub_news_id',
                                'compare' => 'NOT IN',
                                'value'   => $remote_news_ids,
                            ),
                        ),
                        'posts_per_page' => -1,
                        'fields'         => 'ids'
                    );

                    $query = new WP_Query( $args );

                    if ( $query->have_posts() ) {
                        foreach ( $query->posts as $post_id ) {
                            Utils::deletePost( $post_id, true );
                        }
                    }
                }
            }

            unset( $group_ids, $remote_news_ids );;
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
     * If a myclub group is specified, checks if a term with the 'myclub_groups_id' meta value exists in the 'NewsService::MYCLUB_GROUP_NEWS' taxonomy.
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
    private function addNews( object $news_item, array $myclub_group = null )
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
        $group_id = $myclub_group !== null ? $myclub_group[ 'id' ] : null;

        $post_id = $query_results->have_posts() ? wp_update_post( $this->createNewsArgs( $news_item, $query_results->posts[ 0 ]->ID, $group_id ) ) : wp_insert_post( $this->createNewsArgs( $news_item, null, $group_id ) );

        if ( isset( $news_item->news_image ) ) {
            $image_task = ImageTask::init();

            $image_task->push_to_queue(
                wp_json_encode( array (
                    'post_id' => $post_id,
                    'type'    => 'news',
                    'image'   => $news_item->news_image,
                    'news_id' => $news_item->id,
                    'caption' => $news_item->news_image_text,
                ), JSON_UNESCAPED_UNICODE )
            );
            $image_task->save()->dispatch();
        }

        $this->addDefaultCategory( $post_id );

        if ( $myclub_group ) {
            $this->addTeamNewsCategory( $post_id, $myclub_group );

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

        unset( $query_args, $query_results );
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
    private function createNewsArgs( object $news_item, int $post_id = null, string $group_id = null ): array
    {
        // Get time for post and make sure that the time is correct with utc time as well
        try {
            $date_time = new DateTime( $news_item->published_date );

            // Convert the date to UTC timezone
            $date_time_utc = clone $date_time;
            $date_time_utc->setTimezone( $this->utc_timezone );
            $gmtTime = $date_time_utc->format( 'Y-m-d H:i:s' );

            // Convert the date to the site's local timezone
            $date_time->setTimezone( $this->timezone );
            $time = $date_time->format( 'Y-m-d H:i:s' );
        } catch ( Exception $e ) {
            // Fallback if parsing fails (use the original string without conversion)
            $time = $news_item->published_date;
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

        if ( $group_id !== null ) {
            $args[ 'meta_input' ][ 'myclub_groups_id' ] = $group_id;
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
    private function getGroupName( string $group_id ): ?string
    {
        $args = array (
            'post_type'      => GroupService::MYCLUB_GROUPS,
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

    /**
     * Retrieves a list of news IDs from an external source.
     *
     * This method utilizes the API to fetch news data for a specific group and extracts
     * the IDs of the news items if the API response is successful.
     *
     * @param string|null $group_id The ID of the group for which to load news. Can be null.
     * @return array An array of news IDs retrieved from the API. Returns an empty array if the API response is not successful.
     * @since 1.3.3
     */
    private function getNewsIds( ?string $group_id ): array
    {
        $ids = [];

        $response = $this->api->loadNews( $group_id );

        if ( !is_wp_error( $response ) && $response->status === 200 ) {
            foreach ( $response->result->results as $newsItem ) {
                $ids[] = $newsItem->id;
            }
        }

        unset( $response );

        return $ids;
    }
}
