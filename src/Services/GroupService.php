<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Utils;
use WP_Query;

/**
 * Class GroupService
 *
 * This class is used to reload and update groups.
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

    /**
     * Generates the post content for MyClub group post.
     *
     * @param array|null $selectedBlocks The array of selected blocks. Default is null.
     * @return string The post content for MyClub group post.
     * @since 1.0.0
     */
    public static function getPostContent( array $selectedBlocks = null ): string {
        $optionNames = [
            'menu'         => 'myclub_groups_page_menu',
            'calendar'     => 'myclub_groups_page_calendar',
            'members'      => 'myclub_groups_page_members',
            'leaders'      => 'myclub_groups_page_leaders',
            'news'         => 'myclub_groups_page_news',
            'navigation'   => 'myclub_groups_page_navigation',
            'coming-games' => 'myclub_groups_page_coming_games'
        ];

        if ( empty( $selectedBlocks ) ) {
            $selectedBlocks = get_option( 'myclub_groups_show_items_order' );
        }

        if ( get_option( 'myclub_groups_page_title', true ) ) {
            $content = '<!-- wp:myclub-groups/title /-->';
        } else {
            $content = '';
        }

        foreach ( $selectedBlocks as $block ) {
            if ( get_option( $optionNames[ $block ], true ) ) {
                $content .= '<!-- wp:myclub-groups/' . $block . ' /-->';
            }
        }

        return $content;
    }

    public function __construct()
    {
        $this->api = new RestApi();
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
        $menu = $this->api->loadMenuItems()->result;

        if ( $this->menuItemsExist( $menu ) ) {
            // Get the ids from the member backend
            $ids = $this->getGroupIds( $menu, [] );

            $process = RefreshGroupsTask::init();

            foreach( $ids as $id ) {
                $process->push_to_queue($id);
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }
    }

    public function removeUnusedGroupPages()
    {
        // Load menuItems items from member backend
        $menuItems = $this->api->loadMenuItems()->result;

        if ( $this->menuItemsExist( $menuItems ) ) {
            global $wpdb;

            // Get the ids from the member backend
            $ids = $this->getGroupIds( $menuItems, [] );

            $existingIds = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON (p.ID = pm.post_id) WHERE pm.meta_key= %s and p.post_type = %s",
                    'myclubGroupId', 'myclub-groups'
                )
            );

            $oldIds = array_diff( $existingIds, $ids );
            $args = array(
                'post_type'  => 'myclub-groups',
                'meta_query' => array(
                    array(
                        'key'     => 'your_meta_key',
                        'value'   => $oldIds,
                        'compare' => 'IN'
                    ),
                ),
            );

            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                foreach ($query->posts as $post_id) {
                    wp_delete_post( $post_id, true );
                }
            }
        }
    }

    public function updateGroupPage( $id )
    {
        $page_template = get_option( 'myclub_groups_page_template' );
        $response = $this->api->loadGroup( $id );

        if ( !is_wp_error( $response ) && $response !== false && $response->status === 200 ) {
            $group = $response->result;
            $postId = $this->getGroupPostId( $id );

            if ( $postId ) {
                $postId = wp_update_post( $this->createPostArgs( $group, $postId ) );
            } else {
                $postId = wp_insert_post( $this->createPostArgs( $group ) );
            }

            Utils::addFeaturedImage( $postId, $group->team_image );
            $this->addMembers( $postId, $group );
            $this->addActivities( $postId, $group );
            update_post_meta( $postId, '_wp_page_template', $page_template );
            update_post_meta( $postId, 'lastUpdated', date( "c" ) );
        }
    }

    /**
     * Adds activities to a post by performing the following steps:
     * - Encodes the activities array into a JSON string
     * - Checks if the 'activities' custom field already exists for the post
     *   - If it exists, updates the 'activities' custom field with the JSON string
     *   - If it does not exist, adds the 'activities' custom field with the JSON string
     *
     * @param int $postId The ID of the post to add activities to
     * @param object $group The group object containing the activities array
     * @return void
     * @since 1.0.0
     */
    private function addActivities( int $postId, $group )
    {
        $activities_json = wp_json_encode( $group->activities, JSON_UNESCAPED_UNICODE );

        if ( get_post_meta( $postId, 'activities' ) ) {
            update_post_meta( $postId, 'activities', $activities_json );
        } else {
            add_post_meta( $postId, 'activities', $activities_json );
        }
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
     * @param int $postId The post ID to add members to
     * @param object $group The group object that contains the members
     * @return void
     * @since 1.0.0
     */
    private function addMembers( int $postId, $group )
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
                $url = $member->member_image->raw->url;

                if ( in_array( $url, GroupService::DEFAULT_PICTURES ) ) {
                    // Save non personal image (reuse image if present)
                    $member->member_image = Utils::addImage( $member->member_image->raw->url );
                } else {
                    // Save image and save attachment id
                    $member->member_image = Utils::addImage( $member->member_image->raw->url, $member->id . '_' );
                }

                if ( $member->is_leader ) {
                    $leaders[] = $member;
                } else {
                    $members[] = $member;
                }
            }
        }

        array_multisort( array_column( $members, 'name' ), SORT_ASC, $members );
        array_multisort( array_column( $leaders, 'name' ), SORT_ASC, $leaders );

        $member_json = wp_json_encode( [
            'members' => $members,
            'leaders' => $leaders
        ], JSON_UNESCAPED_UNICODE );

        if ( get_post_meta( $postId, 'members' ) ) {
            update_post_meta( $postId, 'members', $member_json );
        } else {
            add_post_meta( $postId, 'members', $member_json );
        }
    }

    /**
     * Creates an array of arguments for creating a MyClub group post.
     *
     * @param Object $group The group object.
     * @return array The array of arguments for creating a post.
     * @since 1.0.0
     */
    private function createPostArgs( $group, string $postId = null ): array
    {
        $args = [
            'post_title'    => $group->name,
            'post_name'     => sanitize_title( $group->name ),
            'post_status'   => 'publish',
            'post_type'     => 'myclub-groups',
            'post_content'  => $this->getPostContent(),
            'page_template' => '',
            'meta_input'    => [
                'myclubGroupId' => $group->id,
                'phone'         => $group->phone,
                'email'         => $group->email,
                'contactName'   => $group->contact_name,
                'infoText'      => $group->info_text
            ]
        ];

        if ( $postId ) {
            $args[ 'ID' ] = $postId;
        }

        return $args;
    }
}