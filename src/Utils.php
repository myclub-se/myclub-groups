<?php

namespace MyClub\MyClubGroups;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\Services\GroupService;
use MyClub\MyClubGroups\Services\MemberService;

use MyClub\Common\Utils as CommonUtils;

/**
 * A utility class for managing images, URLs, cache, and posts in a WordPress environment.
 */
class Utils extends CommonUtils
{
    /**
     * Delete a post and related attachments and metadata from the WordPress database.
     *
     * @param int $post_id The ID of the post to delete.
     *
     * @return void
     * @since 1.0.0
     */
    static function deletePost( int $post_id, bool $check_sections = false )
    {
        if ( $check_sections ) {
            // Make sure that posts containing sections are not deleted
            $section_id = get_post_meta( $post_id, 'myclub_sections_id', true );

            if ( ! empty( $section_id ) ) {
                // The news item has a myclub_sections_id value
                return;
            }
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $attachment_id = get_post_thumbnail_id( $post_id );
            delete_post_thumbnail( $post_id );
            wp_delete_attachment( $attachment_id, true );
        }

        MemberService::deleteGroupMembers( $post_id );
        wp_delete_post( $post_id, true );

        $other_cached_post_ids = Utils::getOtherCachedPosts( $post_id );

        foreach ( $other_cached_post_ids as $cached_post_id ) {
            Utils::clearCacheForPage( $cached_post_id );
        }

        Utils::clearCacheForPage( $post_id );
    }

    /**
     * Get the post ID based on the given attributes.
     *
     * @param array $attributes The attributes used to determine the post ID.
     *                         Supported attributes:
     *                         - post_id: The specific post ID to retrieve.
     *                         - group_id: The group ID used to retrieve the post ID from the database.
     *
     * @return int The retrieved post ID.
     * @since 1.0.0
     */
    static function getPostId( array $attributes ): int
    {
        if ( !empty( $attributes[ 'post_id' ] ) ) {
            $post_id = (int)$attributes[ 'post_id' ];
        } else if ( !empty( $attributes[ 'group_id' ] ) ) {
            $args = array (
                'post_type'  => GroupService::MYCLUB_GROUPS,
                'meta_key'   => 'myclub_groups_id',
                'meta_value' => $attributes[ 'group_id' ]
            );
            $posts = get_posts( $args );

            // If posts were found.
            if ( !empty( $posts ) ) {
                $post_id = $posts[ 0 ]->ID;
            }
        }

        return empty( $post_id ) ? 0 : $post_id;
    }

    /**
     * Retrieves a list of post IDs that contain specific club calendar content within their post content.
     *
     * The method searches for posts where the content matches specified strings and applies a filter for published status.
     *
     * @return array An array of post IDs that match the specified conditions.
     * @since 1.3.0
     */
    static function getClubCalendarPosts(): array
    {
        global $wpdb;

        $like_clauses = [
            "post_content LIKE 'wp:myclub-groups/club-calendar'",
            "post_content LIKE '[myclub-groups-club-calendar]'"
        ];

        // Combine conditions with 'OR' and prepare query
        $where_clause = implode( ' OR ', $like_clauses );
        $post_status = 'publish';

        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE ($where_clause) AND post_status = %s",
            $post_status
        );

        return $wpdb->get_col( $query );
    }

    /*
     * Retrieves a list of post IDs matching specific content (shortcodes, blocks, or both).
     *
     * @param int|null $post_id The post ID to match in content (optional).
     * @param string|null $group_id The group ID to match in content (optional).
     *
     * @return array An array of matching post IDs, or an empty array if no matches are found.
     * @since 1.2.0
     */
    static function getOtherCachedPosts( ?int $post_id = null, ?string $group_id = null ): array
    {
        global $wpdb;

        if ( !$post_id && !$group_id ) {
            return [];
        }

        $like_clauses = [];

        if ( $post_id ) {
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%post_id="' . esc_sql( $post_id ) . '"%'
            );
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%"post_id":"' . esc_sql( $post_id ) . '"%'
            );
        }

        if ( $group_id ) {
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%group_id="' . esc_sql( $group_id ) . '"%'
            );
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%"group_id":"' . esc_sql( $group_id ) . '"%'
            );
        }

        // Combine conditions with 'OR' and prepare query
        $where_clause = implode( ' OR ', $like_clauses );
        $post_type_exclusion = esc_sql( GroupService::MYCLUB_GROUPS );
        $post_status = 'publish';

        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE ($where_clause) AND post_status = %s AND post_type != %s",
            $post_status,
            $post_type_exclusion
        );

        return $wpdb->get_col( $query );
    }

    /**
     * Processes a list of activities by modifying their descriptions and returns the data in JSON format.
     *
     * @param array $activities An array of activity objects, each containing a description property to clean and format.
     * @return string A JSON-encoded string representation of the processed activities.
     * @since 1.3.0
     */
    static function prepareActivitiesJson( array $activities ): string
    {
        foreach ( $activities as $activity ) {
            $activity->id = $activity->uid;
            unset( $activity->uid );
            $activity->description = str_replace( '<br /> <br />', '<br />', $activity->description );
            $activity->description = str_replace( '<br /><br />', '<br />', $activity->description );
            $activity->description = addslashes( str_replace( '<br /><br /><br />', '<br /><br />', $activity->description ) );
            if ( empty( trim( wp_strip_all_tags( $activity->description ) ) ) ) {
                $activity->description = '';
            }
        }

        return wp_json_encode( $activities, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT );
    }

    /**
     * Processes a list of members by decoding their dynamic fields and returns the data in JSON format.
     *
     * @param array $members An array of member objects, each containing a dynamic_fields property to decode.
     * @return string A JSON-encoded string representation of the processed members.
     * @since 2.0.0
     */
    static function prepareMembersJson( array $members ): string
    {
        foreach ( $members as $member ) {
            $member->dynamic_fields = json_decode( $member->dynamic_fields );
        }

        return wp_json_encode( $members, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT );
    }
}