<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\ImageTask;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Utils;
use stdClass;
use WP_Query;

/**
 * Class GroupService
 *
 * This class is used to reload and update groups.
 */
class GroupService extends Groups
{
    const DEFAULT_PICTURES = [
        'https://myclub-member.s3.eu-west-1.amazonaws.com/media/webpage/person.png',
        'https://myclub-member.s3.eu-west-1.amazonaws.com/media/webpage/default_user_woman.png',
        'https://myclub-member.s3.eu-west-1.amazonaws.com/media/webpage/default_user_man.png',
    ];

    private $api;
    private $image_task;

    public function __construct()
    {
        $this->api = new RestApi();
    }

    /**
     * Retrieves the content for a MyClub group post.
     *
     * @param int $post_id The ID of the post.
     * @param mixed $selected_blocks Optional. The selected blocks to include in the content. Default is null.
     * @return string The new content of the MyClub group post.
     * @since 1.0.0
     */
    public static function create_post_content( int $post_id, $selected_blocks = null ): string
    {
        $option_names = [
            'calendar'     => 'myclub_groups_page_calendar',
            'coming-games' => 'myclub_groups_page_coming_games',
            'leaders'      => 'myclub_groups_page_leaders',
            'members'      => 'myclub_groups_page_members',
            'menu'         => 'myclub_groups_page_menu',
            'navigation'   => 'myclub_groups_page_navigation',
            'news'         => 'myclub_groups_page_news',
        ];

        $post_id_string = ' {"postId":"' . $post_id . '"}';

        if ( empty( $selected_blocks ) ) {
            $selected_blocks = get_option( 'myclub_groups_show_items_order' );

            if ( in_array( 'default', $selected_blocks ) ) {
                $selected_blocks = array (
                    'menu',
                    'navigation',
                    'calendar',
                    'members',
                    'leaders',
                    'news',
                    'coming-games'
                );
            }
        }

        if ( get_option( 'myclub_groups_page_title', true ) ) {
            $content = '<!-- wp:myclub-groups/title' . $post_id_string . ' /-->';
        } else {
            $content = '';
        }

        foreach ( $selected_blocks as $block ) {
            if ( get_option( $option_names[ $block ], true ) ) {
                $content .= '<!-- wp:myclub-groups/' . $block . $post_id_string . ' /-->';
            }
        }

        return $content;
    }

    /**
     * Updates the content and page template of a group page.
     *
     * @param int $post_id The post ID of the group page.
     * @param mixed $page_contents The new content for the group page.
     * @return void
     */
    public static function update_group_page_contents( int $post_id, $page_contents )
    {
        $post_content = array (
            'ID'           => $post_id,
            'post_content' => GroupService::create_post_content( $post_id, $page_contents ),
        );

        // Update the post into the database
        $result = wp_update_post( $post_content, true );

        if ( is_wp_error( $result ) ) {
            error_log( "Unable to update post $post_id" );
            error_log( $result->get_error_message() );
        }
    }

    /**
     * Deletes all group pages from the database.
     *
     * Queries the database to retrieve all group pages with 'myclub-groups' post type.
     * Deletes each group page using the Utils class' deletePost method. This is a very
     * destructive method and can't be undone.
     *
     * @return void
     * @since 1.0.0
     */
    public function delete_all_groups()
    {
        $args = array (
            'post_type'      => 'myclub-groups',
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
    }

    /**
     * Reloads the groups by fetching and processing menu items from the member backend.
     *
     * @return void
     * @since 1.0.0
     */
    public function reload_groups()
    {
        // Load menu items from member backend
        $groups = $this->get_all_group_ids();

        if ( $groups->success ) {
            $process = RefreshGroupsTask::init();

            foreach ( $groups->ids as $id ) {
                $process->push_to_queue( $id );
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }
    }

    /**
     * Removes unused group pages from the database.
     *
     * Queries the database to find group pages with 'myclub_group_id' meta key
     * that are not in the current list of group IDs. Deletes these group pages.
     *
     * @return void
     */
    public function remove_unused_group_pages()
    {
        $group_ids = $this->get_all_group_ids();

        if ( $group_ids->success ) {
            global $wpdb;

            $existing_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON (p.ID = pm.post_id) WHERE pm.meta_key= %s and p.post_type = %s",
                    'myclub_group_id', 'myclub-groups'
                )
            );

            $old_ids = array_diff( $existing_ids, $group_ids->ids );

            // Check so that there are any posts that should be deleted.
            if ( count( $old_ids ) ) {
                $args = array (
                    'post_type'      => 'myclub-groups',
                    'meta_query'     => array (
                        array (
                            'key'     => 'myclub_group_id',
                            'value'   => $old_ids,
                            'compare' => 'IN'
                        ),
                    ),
                    'posts_per_page' => -1
                );

                $query = new WP_Query( $args );

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->next_post();
                        wp_delete_post( $query->post->ID, true );
                    }
                }
            }
        }
    }

    /**
     * Updates the group page in the database.
     *
     * Initializes the ImageTask object, retrieves the page template
     * from the 'myclub_groups_page_template' option, and loads the group
     * using the provided ID. If the response is successful, updates the
     * existing group post if it exists, otherwise creates a new group post.
     * If the update or insert is successful, processes the group image,
     * adds members and activities to the group, updates the page template
     * if the theme supports blocks, updates the 'lastUpdated' meta value,
     * and saves the ImageTask queue and dispatches it.
     *
     * @param string $id The ID of the group.
     * @return void
     * @since 1.0.0
     */
    public function update_group_page( string $id )
    {
        $this->image_task = ImageTask::init();

        $page_template = get_option( 'myclub_groups_page_template' );
        $response = $this->api->load_group( $id );

        if ( !is_wp_error( $response ) && $response !== false && $response->status === 200 ) {
            $group = $response->result;
            $post_id = $this->get_group_post_id( $id );

            if ( !$post_id ) {
                $post_id = wp_insert_post( $this->create_post_args( $group, 0, $page_template ) );

                if ( $post_id && !is_wp_error( $post_id ) ) {
                    $this::update_group_page_contents( $post_id, null );
                }
            } else {
                $post_id = wp_update_post( $this->create_post_args( $group, $post_id, $page_template ) );
            }

            if ( $post_id && !is_wp_error( $post_id ) ) {
                if ( isset( $group->team_image ) ) {
                    $this->image_task->push_to_queue(
                        wp_json_encode( array (
                            'post_id'  => $post_id,
                            'type'     => 'group',
                            'group_id' => $group->id,
                            'image'    => $group->team_image
                        ), JSON_UNESCAPED_UNICODE )
                    );
                }
                $this->add_members( $post_id, $group );
                $this->add_activities( $post_id, $group );
                update_post_meta( $post_id, 'last_updated', date( "c" ) );

                $this->image_task->save()->dispatch();
            }
        }
    }

    /**
     * Adds activities to a post by performing the following steps:
     * - Encodes the activities array into a JSON string
     * - Checks if the 'activities' custom field already exists for the post
     *   - If it exists, updates the 'activities' custom field with the JSON string
     *   - If it does not exist, adds the 'activities' custom field with the JSON string
     *
     * @param int $post_id The ID of the post to add activities to
     * @param object $group The group object containing the activities array
     * @return void
     * @since 1.0.0
     */
    private function add_activities( int $post_id, $group )
    {
        $activities_json = wp_json_encode( $group->activities, JSON_UNESCAPED_UNICODE );

        update_post_meta( $post_id, 'activities', $activities_json );
    }

    /**
     * Adds members to a group by performing the following steps:
     * - Initializes two empty arrays for members and leaders
     * - Iterates over each member in the group
     *   - If the member has a member_image property
     *     - If the member_image URL is in the DEFAULT_PICTURES array,
     *       saves the non-personal image by reusing the image if present
     *     - Otherwise, saves the image and attachment ID with a prefix
     *     - If the member is a leader, adds the member to the leaders array
     *     - Otherwise, adds the member to the members array
     * - Encodes the members and leaders arrays into JSON format
     * - If the 'members' meta key exists for the post ID,
     *   updates the 'members' meta value with the encoded JSON
     * - Otherwise, adds the 'members' meta key with the encoded JSON
     *
     * @param int $post_id The post ID to add members to
     * @param object $group The group object that contains the members
     * @return void
     * @since 1.0.0
     */
    private function add_members( int $post_id, $group )
    {
        $members = array ();
        $leaders = array ();

        foreach ( $group->members as $member ) {
            $nameArray = [];
            if ( $member->first_name ) {
                $nameArray[] = $member->first_name;
            }
            if ( $member->last_name ) {
                $nameArray[] = $member->last_name;
            }
            $member->name = implode( ' ', $nameArray );
            unset( $member->first_name );
            unset( $member->last_name );

            if ( isset( $member->member_image ) ) {
                $this->image_task->push_to_queue(
                    wp_json_encode( array (
                        'post_id'     => $post_id,
                        'type'        => 'member',
                        'member_id'   => $member->id,
                        'member_type' => $member->is_leader ? 'leaders' : 'members',
                        'image'       => $member->member_image
                    ), JSON_UNESCAPED_UNICODE )
                );
            }

            unset( $member->member_image );

            if ( $member->is_leader ) {
                $leaders[] = $member;
            } else {
                $members[] = $member;
            }
        }

        array_multisort( array_column( $members, 'name' ), SORT_ASC, $members );
        array_multisort( array_column( $leaders, 'name' ), SORT_ASC, $leaders );

        $this->update_members( $post_id, $members, $leaders );
    }

    /**
     * Creates an array of arguments for creating or updating a post.
     *
     * @param mixed $group The group object.
     * @param int $post_id The post ID.
     * @param string $page_template The page template.
     * @return array The array of arguments for creating or updating a post.
     */
    private function create_post_args( $group, int $post_id, string $page_template ): array
    {
        $args = [
            'post_title'    => sanitize_text_field( $group->name ),
            'post_name'     => sanitize_title( $group->name ),
            'post_status'   => 'publish',
            'post_type'     => 'myclub-groups',
            'post_content'  => $post_id ? $this->create_post_content( $post_id ) : '',
            'page_template' => wp_is_block_theme() ? $page_template : '',
            'meta_input' => [
                'myclub_group_id' => sanitize_text_field( $group->id ),
                'phone'           => sanitize_text_field( $group->phone ),
                'email'           => sanitize_text_field( $group->email ),
                'contact_name'    => sanitize_text_field( $group->contact_name ),
                'info_text'       => sanitize_text_field( $group->info_text )
            ]
        ];

        if ( $post_id ) {
            $args[ 'ID' ] = $post_id;
        }

        return $args;
    }

    /**
     * Updates the members of a specific post.
     * Retrieves the existing members from the post meta with the key 'members',
     * maps them into an associative array called $mappedEntities,
     * then updates the members and leaders with the provided $members and $leaders arrays.
     * Finally, updates the post meta with the updated members data.
     *
     * @param int $post_id The ID of the post to update the member metadata for.
     * @param array $members The loaded array of members.
     * @param array $leaders The loaded array of leaders.
     * @return void
     * @since 1.0.0
     */
    private function update_members( int $post_id, array $members, array $leaders )
    {
        $metadata = get_post_meta( $post_id, 'members', true );

        if ( !empty( $metadata ) ) {
            $metadata_json = json_decode( $metadata );
            $mapped_entities = [
                'leaders' => [],
                'members' => [],
            ];

            foreach ( $metadata_json as $type => $entities ) {
                foreach ( $entities as $entity ) {
                    $mapped_entities[ $type ][ $entity->id ] = $entity;
                }
            }

            $updated_metadata = [
                'members' => $this->update_member_entities( $members, $mapped_entities[ 'members' ] ),
                'leaders' => $this->update_member_entities( $leaders, $mapped_entities[ 'leaders' ] ),
            ];
        } else {
            $updated_metadata = [
                'members' => $members,
                'leaders' => $leaders
            ];
        }

        update_post_meta( $post_id, 'members', wp_json_encode( $updated_metadata, JSON_UNESCAPED_UNICODE ) );
    }

    /**
     * Updates the member entities by mapping their member_image property
     * based on the provided mapped entities.
     *
     * Iterates over the given entities and checks if the mappedEntities array
     * contains a member_image property for each entity. If a member_image property
     * is found, the corresponding entity's member_image property gets updated with
     * the mapped value.
     *
     * @param array $entities The array of member entities to update.
     * @param array $mapped_entities The array of mapped entities containing the
     *                              member_image property.
     * @return array The updated array of member entities.
     * @since 1.0.0
     */
    private function update_member_entities( array $entities, array $mapped_entities ): array
    {
        foreach ( $entities as $entity ) {
            if ( isset( $mapped_entities[ $entity->id ]->member_image ) ) {
                $entity->member_image = $mapped_entities[ $entity->id ]->member_image;
            }
        }

        return $entities;
    }
}