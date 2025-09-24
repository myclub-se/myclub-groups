<?php

namespace MyClub\MyClubGroups;

use MyClub\MyClubGroups\Services\ActivityService;
use MyClub\MyClubGroups\Services\GroupService;
use MyClub\MyClubGroups\Services\ImageService;
use MyClub\MyClubGroups\Services\MemberService;
use WP_Query;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Handles database migrations for the MyClub Groups plugin.
 */
class Migration
{
    const VERSION_OPTION = 'myclub_groups_version';
    const CURRENT_VERSION = MYCLUB_GROUPS_PLUGIN_VERSION;

    /**
     * Checks if any migrations need to be executed by comparing the installed version
     * with the current version. If the installed version is less than the current version,
     * it triggers the migration process.
     *
     * @return void
     * @since 2.0.0
     */
    public static function checkMigrations()
    {
        $installed_version = get_option( self::VERSION_OPTION, '1.3.5' );

        // Normalize unexpected values (e.g., NULL stored in the DB)
        if ( ! is_string( $installed_version ) || $installed_version === '' ) {
            $installed_version = '1.3.5';
        }

        // Also ensure CURRENT_VERSION is a non-empty string before comparing
        $current_version = ( is_string( self::CURRENT_VERSION ) && self::CURRENT_VERSION !== '' )
            ? self::CURRENT_VERSION
            : '2.0.0';

        if ( version_compare( $installed_version, $current_version, '<' ) ) {
            self::migrate( $installed_version );
        }
    }

    /**
     * Migrates the system to ensure it is compatible with the current version.
     *
     * @param string $installed_version The currently installed version of the system.
     * @return void
     * @since 2.0.0
     */
    public static function migrate( string $installed_version )
    {
        if ( version_compare( $installed_version, '2.0.0', '<' ) ) {
            self::migrateMyclubGroupTables();
        }

        if ( version_compare( $installed_version, '2.1.0', '<' ) ) {
            self::migrateMyclubImages();
        }

        update_option( self::VERSION_OPTION, self::CURRENT_VERSION );
    }

    /**
     * Migrates the MyClub group tables by performing the following operations:
     * - Creates the required activity and member tables.
     * - Deletes old metadata fields associated with group activities and members.
     * - Reloads groups to reflect the updated structure.
     *
     * @return void
     * @since 2.0.0
     */
    private static function migrateMyclubGroupTables()
    {
        ActivityService::createActivityTables();
        MemberService::createMemberTable();

        // Remove the old metadata fields (if present)
        $query = new WP_Query( [
            'post_type'      => GroupService::MYCLUB_GROUPS,
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post_id ) {
                delete_post_meta( $post_id, 'myclub_groups_activities' );
                delete_post_meta( $post_id, 'myclub_groups_members' );
            }
        }

        ( new GroupService() )->reloadGroups();

        unset( $query );
    }

    /**
     * Migrates MyClub images by associating attachments with corresponding terms.
     * - Maps specific prefixes to terms (e.g., 'news_', 'member_', 'group_').
     * - Queries all attachments matching the defined prefixes.
     * - Assigns each attachment to appropriate taxonomy terms under the MyClub image taxonomy.
     *
     * @return void
     * @since 2.1.0
     */
    private static function migrateMyclubImages()
    {
        $map = [
            'news_'   => 'news',
            'member_' => 'member',
            'group_'  => 'group',
        ];

        foreach ( $map as $prefix => $term ) {
            $args = [
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'name__like'     => sanitize_title( $prefix ),
                'no_found_rows'  => true,
            ];

            $q = new WP_Query( $args );

            if ( ! empty( $q->posts ) ) {
                foreach ( $q->posts as $attachment_id ) {
                    wp_set_object_terms( (int) $attachment_id, $term, ImageService::MYCLUB_IMAGES, false );
                }
            }
        }
    }
}