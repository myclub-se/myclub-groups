<?php

namespace MyClub\MyClubGroups\Services;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\ImageTask;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Utils;
use WP_Query;

/**
 * Class GroupService
 *
 * This class is used to reload and update groups.
 */
class GroupService extends Groups
{
    const MYCLUB_GROUPS = 'myclub-groups';

    private ImageTask $image_task;

    private bool $content_or_data_updated = false;

    /**
     * Retrieves the content for a MyClub group post.
     *
     * @param int $post_id The ID of the post.
     * @param mixed $selected_blocks Optional. The selected blocks to include in the content. Default is null.
     * @return string The new content of the MyClub group post.
     * @since 1.0.0
     */
    public static function createPostContent( int $post_id, $selected_blocks = null ): string
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

        $post_id_string = ' {"post_id":"' . $post_id . '"}';

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
     * @param bool $clear_cache Clear cache if set
     * @return void
     */
    public static function updateGroupPageContents( int $post_id, $page_contents, bool $clear_cache = true )
    {
        $post_content = array (
            'ID'           => $post_id,
            'post_content' => GroupService::createPostContent( $post_id, $page_contents ),
        );

        // Update the post into the database
        $result = wp_update_post( $post_content, true );

        if ( is_wp_error( $result ) ) {
            error_log( "Unable to update post $post_id" );
            error_log( $result->get_error_message() );
        }

        if ( $clear_cache ) {
            Utils::clearCacheForPage( $post_id );
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
    public function deleteAllGroups()
    {
        $args = array (
            'post_type'      => GroupService::MYCLUB_GROUPS,
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();

                $post_id = $query->post->ID;

                Utils::deletePost( $post_id );
            }
        }

        unset( $args, $query );
    }

    /**
     * Reloads the groups by fetching and processing menu items from the member backend.
     *
     * @return void
     * @since 1.0.0
     */
    public function reloadGroups()
    {
        // Load menu items from member backend
        $groups = $this->getAllGroupIds();

        if ( $groups->success ) {
            $process = RefreshGroupsTask::init();

            foreach ( $groups->ids as $id ) {
                $process->push_to_queue( $id );
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }

        unset( $groups );
    }

    /**
     * Removes unused group pages from the database.
     *
     * Queries the database to find group pages with 'myclub_groups_id' meta key
     * that are not in the current list of group IDs. Deletes these group pages.
     *
     * @return void
     */
    public function removeUnusedGroupPages()
    {
        $group_ids = $this->getAllGroupIds();

        if ( $group_ids->success ) {
            global $wpdb;

            $existing_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON (p.ID = pm.post_id) WHERE pm.meta_key= %s and p.post_type = %s",
                    'myclub_groups_id', GroupService::MYCLUB_GROUPS
                )
            );

            $old_ids = array_diff( $existing_ids, $group_ids->ids );

            // Check so that there are any posts that should be deleted.
            if ( count( $old_ids ) ) {
                $args = array (
                    'post_type'      => GroupService::MYCLUB_GROUPS,
                    'meta_query'     => array (
                        array (
                            'key'     => 'myclub_groups_id',
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
                        MemberService::deleteGroupMembers( $query->post->ID );
                        wp_delete_post( $query->post->ID, true );
                    }
                }

                unset( $old_ids, $args, $query );
            }

            unset( $existing_ids );
        }

        unset( $group_ids );
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
    public function updateGroupPage( string $id )
    {
        $this->content_or_data_updated = false;
        $this->image_task = ImageTask::init();

        $page_template = get_option( 'myclub_groups_page_template' );
        $response = $this->api->loadGroup( $id );

        if ( !is_wp_error( $response ) && $response !== false && $response->status === 200 ) {
            $group = $response->result;
            $post_id = $this->getGroupPostId( $id );

            if ( !$post_id ) {
                $post_id = wp_insert_post( $this->createPostArgs( $group, 0, $page_template ) );

                if ( $post_id && !is_wp_error( $post_id ) ) {
                    $this::updateGroupPageContents( $post_id, null, false );
                }
            } else {
                $post_id = wp_update_post( $this->createPostArgs( $group, $post_id, $page_template ) );
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
                $this->addMembers( $post_id, $group );
                $this->addActivities( $post_id, $group );
                update_post_meta( $post_id, 'myclub_groups_last_updated', gmdate( "c" ) );
                update_post_meta( $post_id, '_wp_page_template', $page_template );

                if ( $this->content_or_data_updated ) {
                    $other_cached_post_ids = Utils::getOtherCachedPosts( $post_id, $group->id );

                    foreach ( $other_cached_post_ids as $other_post_id ) {
                        Utils::clearCacheForPage( $other_post_id );
                    }

                    Utils::clearCacheForPage( $post_id );
                }

                $this->image_task->save()->dispatch();
            }

            unset( $group );
        }

        unset( $response  );

        if ( function_exists( 'gc_collect_cycles' ) ) {
            gc_collect_cycles();
        }
    }

    /**
     * Adds or updates activities for a given post and group, and removes obsolete activities.
     *
     * @param int $post_id The post ID associated with the activities.
     * @param object $group An object representing the group, which contains an array of activities.
     * @return void
     * @since 1.0.0
     */
    private function addActivities( int $post_id, object $group )
    {
        $remote_ids = array ();
        $update = false;

        foreach ( $group->activities as $activity ) {
            $remote_ids[] = $activity->uid;
            $activity->post_id = $post_id;
            $update = ActivityService::createOrUpdateActivity( $activity ) || $update;
        }

        $deletable_ids = array_diff( ActivityService::listPostActivityIds( $post_id ), $remote_ids );

        foreach ( $deletable_ids as $id ) {
            ActivityService::removeActivityFromPost( $post_id, $id );
            $update = true;
        }

        if ( $update ) {
            $this->content_or_data_updated = true;
        }

        unset( $deletable_ids, $remote_ids );
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
    private function addMembers( int $post_id, object $group )
    {
        $update = false;
        $remote_ids = array ();

        foreach ( $group->members as $member ) {
            $name_array = [];
            if ( $member->first_name ) {
                $name_array[] = $member->first_name;
            }
            if ( $member->last_name ) {
                $name_array[] = $member->last_name;
            }
            $member->name = implode( ' ', $name_array );
            $member->member_id = $member->id;

            $remote_ids[] = $member->id;

            if ( isset( $member->member_image ) ) {
                $this->image_task->push_to_queue(
                    wp_json_encode( array (
                        'post_id'   => $post_id,
                        'type'      => 'member',
                        'member_id' => $member->member_id,
                        'image'     => $member->member_image
                    ), JSON_UNESCAPED_UNICODE )
                );
            }

            unset( $member->id, $member->member_image, $member->first_name, $member->last_name, $name_array );
            $member->dynamic_fields = wp_json_encode( $member->dynamic_fields, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT );
            $update = MemberService::createOrUpdateMember( $post_id, $member ) || $update;
        }

        $deletable_ids = array_diff( MemberService::listAllGroupMemberIds( $post_id ), $remote_ids );

        foreach ( $deletable_ids as $id ) {
            MemberService::deleteMember( $post_id, $id );
            $update = true;
        }

        if ( $update ) {
            $this->content_or_data_updated = true;
        }

        unset( $deletable_ids, $remote_ids );
    }

    /**
     * Creates an array of arguments for creating or updating a post.
     *
     * @param mixed $group The group object.
     * @param int $post_id The post ID.
     * @param string $page_template The page template.
     * @return array The array of arguments for creating or updating a post.
     */
    private function createPostArgs( $group, int $post_id, string $page_template ): array
    {
        $args = [
            'post_title'    => sanitize_text_field( $group->name ),
            'post_name'     => sanitize_title( $group->name ),
            'post_status'   => 'publish',
            'post_type'     => GroupService::MYCLUB_GROUPS,
            'post_content'  => $post_id ? $this->createPostContent( $post_id ) : '',
            'page_template' => wp_is_block_theme() ? $page_template : '',
            'meta_input'    => [
                'myclub_groups_id'           => sanitize_text_field( $group->id ),
                'myclub_groups_phone'        => sanitize_text_field( $group->phone ),
                'myclub_groups_email'        => sanitize_text_field( $group->email ),
                'myclub_groups_contact_name' => sanitize_text_field( $group->contact_name ),
                'myclub_groups_info_text'    => sanitize_text_field( $group->info_text )
            ]
        ];

        if ( $post_id ) {
            $args[ 'ID' ] = $post_id;
            $existing_post = get_post( $post_id );
            $existing_meta = get_post_meta( $post_id );

            // Compare title
            if ( $existing_post && $existing_post->post_title !== $args[ 'post_title' ] ) {
                $this->content_or_data_updated = true;
            }

            // Compare content
            if ( $existing_post && $existing_post->post_content !== $args[ 'post_content' ] ) {
                $this->content_or_data_updated = true;
            }

            if ( $existing_meta ) {
                if ( isset( $existing_meta[ '_wp_page_template' ][ 0 ] ) &&
                    $existing_meta[ '_wp_page_template' ][ 0 ] !== $args[ 'page_template' ] ) {
                    $this->content_or_data_updated = true;
                }

                // Compare meta fields
                foreach ( $args[ 'meta_input' ] as $key => $value ) {
                    if ( !isset( $existing_meta[ $key ][ 0 ] ) || $existing_meta[ $key ][ 0 ] !== $value ) {
                        $this->content_or_data_updated = true;
                    }
                }
            } else {
                $this->content_or_data_updated = true;
            }
        }

        return $args;
    }
}
