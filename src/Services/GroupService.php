<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Utils;
use stdClass;
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
     * Retrieves the content for a MyClub group post.
     *
     * @param int $postId The ID of the post.
     * @param array|null $selectedBlocks Optional. The selected blocks to include in the content. Default is null.
     * @return string The new content of the MyClub group post.
     * @since 1.0.0
     */
    public static function getPostContent( int $postId, array $selectedBlocks = null ): string
    {
        $optionNames = [
            'menu'         => 'myclub_groups_page_menu',
            'calendar'     => 'myclub_groups_page_calendar',
            'members'      => 'myclub_groups_page_members',
            'leaders'      => 'myclub_groups_page_leaders',
            'news'         => 'myclub_groups_page_news',
            'navigation'   => 'myclub_groups_page_navigation',
            'coming-games' => 'myclub_groups_page_coming_games'
        ];

        $postIdString = wp_is_block_theme() ? ' {"postId":"' . $postId . '"}' : ' "postId"="' . $postId . '"';

        if ( empty( $selectedBlocks ) ) {
            $selectedBlocks = get_option( 'myclub_groups_show_items_order' );

            if ( in_array( 'default', $selectedBlocks ) ) {
                $selectedBlocks = array (
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

        // TODO: This needs to be checked if this is correct - we could perhaps ONLY work with the Gutenberg blocks?
        if ( wp_is_block_theme() ) {
            if ( get_option( 'myclub_groups_page_title', true ) ) {
                $content = '<!-- wp:myclub-groups/title' . $postIdString . ' /-->';
            } else {
                $content = '';
            }

            foreach ( $selectedBlocks as $block ) {
                if ( get_option( $optionNames[ $block ], true ) ) {
                    $content .= '<!-- wp:myclub-groups/' . $block . $postIdString . ' /-->';
                }
            }
        } else {
            if ( get_option( 'myclub_groups_page_title', true ) ) {
                $content = '[myclub-groups-title ' . $postIdString . ']';
            } else {
                $content = '';
            }

            foreach ( $selectedBlocks as $block ) {
                if ( get_option( $optionNames[ $block ], true ) ) {
                    $content .= '[myclub-groups-' . $block . $postIdString . ']';
                }
            }
        }

        return $content;
    }

    /**
     * Updates the content and page template of a group page.
     *
     * @param int $postId The post ID of the group page.
     * @param array $pageContents The new content for the group page.
     * @param string $pageTemplate The new page template for the group page.
     * @return void
     */
    public static function updateGroupPageContents( int $postId, array $pageContents, string $pageTemplate )
    {
        $content = GroupService::getPostContent( $postId, $pageContents );
        $isBlockTheme = wp_is_block_theme();
        $postContent = array (
            'ID'            => $postId,
            'post_content'  => $content,
            'page_template' => $isBlockTheme ? '' : $pageTemplate,
        );

        // Update the post into the database
        $result = wp_update_post( $postContent, true );

        if ( is_wp_error( $result ) ) {
            error_log( "Unable to update post $postId" );
            error_log( $result->get_error_message() );
        }

        if ( $isBlockTheme ) {
            update_post_meta( $postId, '_wp_page_template', $pageTemplate );
        }
    }

    public function __construct()
    {
        $this->api = new RestApi();
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
        $args = array(
            'post_type'  => 'myclub-groups',
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();

                $postId = $query->post->ID;

                Utils::deletePost( $postId );
            }
        }
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
        $groupIds = $this->getAllGroupIds();

        if ( $groupIds->success ) {
            $process = RefreshGroupsTask::init();

            foreach ( $groupIds->ids as $id ) {
                $process->push_to_queue( $id );
            }

            // Enqueue and start the background task
            $process->save()->dispatch();
        }
    }

    /**
     * Removes unused group pages from the database.
     *
     * Queries the database to find group pages with 'myclubGroupId' meta key
     * that are not in the current list of group IDs. Deletes these group pages.
     *
     * @return void
     */
    public function removeUnusedGroupPages()
    {
        $groupIds = $this->getAllGroupIds();

        if ( $groupIds->success ) {
            global $wpdb;

            $existingIds = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON (p.ID = pm.post_id) WHERE pm.meta_key= %s and p.post_type = %s",
                    'myclubGroupId', 'myclub-groups'
                )
            );

            $oldIds = array_diff( $existingIds, $groupIds->ids );

            // Check so that there are any posts that should be deleted.
            if ( count( $oldIds ) ) {
                $args = array (
                    'post_type'  => 'myclub-groups',
                    'meta_query' => array (
                        array (
                            'key'     => 'myclubGroupId',
                            'value'   => $oldIds,
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

    public function updateGroupPage( $id )
    {
        $pageTemplate = get_option( 'myclub_groups_page_template' );
        $response = $this->api->loadGroup( $id );

        if ( !is_wp_error( $response ) && $response !== false && $response->status === 200 ) {
            $group = $response->result;
            $postId = $this->getGroupPostId( $id );

            $postId = $postId ? wp_update_post( $this->createPostArgs( $group, $postId, $pageTemplate ) ) : wp_insert_post( $this->createPostArgs( $group, 0, $pageTemplate ) );

            if ( !is_wp_error( $postId ) ) {
                Utils::addFeaturedImage( $postId, $group->team_image );
                $this->addMembers( $postId, $group );
                $this->addActivities( $postId, $group );
                if ( wp_is_block_theme() ) {
                    update_post_meta( $postId, '_wp_page_template', $pageTemplate );
                }
                update_post_meta( $postId, 'lastUpdated', date( "c" ) );
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
     * Creates an array of arguments for creating or updating a post.
     *
     * @param mixed $group The group object.
     * @param int $postId The post ID.
     * @param string $pageTemplate The page template.
     * @return array The array of arguments for creating or updating a post.
     */
    private function createPostArgs( $group, int $postId, string $pageTemplate ): array
    {
        $args = [
            'post_title'    => $group->name,
            'post_name'     => sanitize_title( $group->name ),
            'post_status'   => 'publish',
            'post_type'     => 'myclub-groups',
            'post_content'  => $postId ? $this->getPostContent( $postId ) : '',
            'page_template' => wp_is_block_theme() ? $pageTemplate : '',
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

    /**
     * Retrieves an array of all group IDs from the MyClub backend.
     *
     * @return stdClass An object with an array of ids and a success flag.
     */
    private function getAllGroupIds(): stdClass
    {
        $returnValue = new stdClass();
        $returnValue->ids = [];
        $returnValue->success = true;

        // Load menuItems items from member backend
        $response = $this->api->loadMenuItems();

        if ( $response->status === 200 ) {
            $menuItems = $response->result;

            $returnValue->ids = $this->getGroupIds( $menuItems, [] );
        } else {
            $returnValue->success = false;
        }

        $response = $this->api->loadOtherTeams();
        if ( $response->status === 200 ) {
            $otherTeams = $response->result->results;
            foreach ( $otherTeams as $otherTeam ) {
                $returnValue->ids[] = $otherTeam->id;
            }
        } else {
            $returnValue->success = false;
        }

        return $returnValue;
    }
}