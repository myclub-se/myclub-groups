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
    private $imageTask;

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
        $args = array (
            'post_type'      => 'myclub-groups',
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
        $groups = $this->getAllGroupIds();

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
                    'post_type'      => 'myclub-groups',
                    'meta_query'     => array (
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
        $this->imageTask = ImageTask::init();

        $pageTemplate = get_option( 'myclub_groups_page_template' );
        $response = $this->api->loadGroup( $id );

        if ( !is_wp_error( $response ) && $response !== false && $response->status === 200 ) {
            $group = $response->result;
            $postId = $this->getGroupPostId( $id );

            $postId = $postId ? wp_update_post( $this->createPostArgs( $group, $postId, $pageTemplate ) ) : wp_insert_post( $this->createPostArgs( $group, 0, $pageTemplate ) );

            if ( !is_wp_error( $postId ) ) {
                if ( isset( $group->team_image ) ) {
                    $this->imageTask->push_to_queue(
                        wp_json_encode( array (
                            'postId'  => $postId,
                            'type'    => 'group',
                            'groupId' => $group->id,
                            'image'   => $group->team_image
                        ), JSON_UNESCAPED_UNICODE )
                    );
                }
                $this->addMembers( $postId, $group );
                $this->addActivities( $postId, $group );
                if ( wp_is_block_theme() ) {
                    update_post_meta( $postId, '_wp_page_template', $pageTemplate );
                }
                update_post_meta( $postId, 'lastUpdated', date( "c" ) );

                $this->imageTask->save()->dispatch();
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
                $this->imageTask->push_to_queue(
                    wp_json_encode( array (
                        'postId'     => $postId,
                        'type'       => 'member',
                        'memberId'   => $member->id,
                        'memberType' => $member->is_leader ? 'leaders' : 'members',
                        'image'      => $member->member_image
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

        if ( metadata_exists( 'post', $postId, 'members' ) ) {
            $this->updateMembers( $postId, $members, $leaders );
        } else {
            $memberJson = wp_json_encode( [
                'members' => $members,
                'leaders' => $leaders
            ], JSON_UNESCAPED_UNICODE );

            add_post_meta( $postId, 'members', $memberJson );
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
     * Updates the members of a specific post.
     * Retrieves the existing members from the post meta with the key 'members',
     * maps them into an associative array called $mappedEntities,
     * then updates the members and leaders with the provided $members and $leaders arrays.
     * Finally, updates the post meta with the updated members data.
     *
     * @param int $postId The ID of the post to update the member metadata for.
     * @param array $members The loaded array of members.
     * @param array $leaders The loaded array of leaders.
     * @return void
     * @since 1.0.0
     */
    private function updateMembers( int $postId, array $members, array $leaders )
    {
        $metaJson = json_decode( get_post_meta( $postId, 'members', true ) );
        $mappedEntities = [
            'leaders' => [],
            'members' => [],
        ];

        foreach ( $metaJson as $type => $entities ) {
            foreach ( $entities as $entity ) {
                $mappedEntities[ $type ][ $entity->id ] = $entity;
            }
        }

        $returnData = [
            'members' => $this->updateMemberEntities( $members, $mappedEntities[ 'members' ] ),
            'leaders' => $this->updateMemberEntities( $leaders, $mappedEntities[ 'leaders' ] ),
        ];

        update_post_meta( $postId, 'members', wp_json_encode( $returnData, JSON_UNESCAPED_UNICODE ) );
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
     * @param array $mappedEntities The array of mapped entities containing the
     *                              member_image property.
     * @return array The updated array of member entities.
     * @since 1.0.0
     */
    private function updateMemberEntities( array $entities, array $mappedEntities ): array
    {
        foreach ( $entities as $entity ) {
            if ( isset( $mappedEntities[ $entity->id ]->member_image ) ) {
                $entity->member_image = $mappedEntities[ $entity->id ]->member_image;
            }
        }

        return $entities;
    }
}