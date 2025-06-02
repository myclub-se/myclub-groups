<?php

namespace MyClub\MyClubGroups\Services;

use stdClass;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class MemberService
 *
 * Handles member-related operations, including managing database interaction
 * for members of a particular group, creating or deleting the member table,
 * and performing CRUD operations for members.
 */
class MemberService
{
    private static $wpdb;
    private static string $table_name;

    /**
     * Initializes the database connection and defines the table name used by the class.
     *
     * @return void
     * @since 2.0.0
     */
    public static function init()
    {
        global $wpdb;
        self::$wpdb = $wpdb;
        self::$table_name = self::$wpdb->prefix . "myclub_groups_members";
    }

    /**
     * Creates the member table in the database with the necessary schema and relationships.
     * This method defines the structure of the table and ensures proper indexing and foreign key constraints.
     *
     * @return void
     * @since 2.0.0
     */
    static function createMemberTable()
    {
        $charset_collate = self::$wpdb->get_charset_collate();

        $members_table_sql = "CREATE TABLE " . self::$table_name . " (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            member_id varchar(20) NOT NULL,
            name varchar(255) DEFAULT '',
            email varchar(255) DEFAULT '',
            phone varchar(255) DEFAULT '',
            member_type varchar(255) DEFAULT '',
            role varchar(255) DEFAULT '',
            age int(11) DEFAULT NULL,
            image_id BIGINT UNSIGNED DEFAULT NULL,
            image_url varchar(255) DEFAULT '',
            is_leader tinyint(1) DEFAULT 0,
            dynamic_fields TEXT DEFAULT '',
            UNIQUE KEY unique_post_member (post_id, member_id),
            PRIMARY KEY (id),
            INDEX (post_id),
            INDEX (member_id),
            INDEX (image_id),
            FOREIGN KEY (post_id) REFERENCES " . self::$wpdb->prefix . "posts(ID) ON DELETE CASCADE,
            FOREIGN KEY (image_id) REFERENCES " . self::$wpdb->prefix . "posts(ID) ON DELETE SET NULL
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $members_table_sql );
    }

    /**
     * Deletes the member table from the database if it exists.
     *
     * @return void
     * @since 2.0.0
     */
    static function deleteMemberTable()
    {
        self::$wpdb->query( "DROP TABLE IF EXISTS " . self::$table_name );
    }

    /**
     * Creates or updates a member entry in the database for a given post.
     *
     * This method checks if a member already exists in the database for the specified post.
     * If the member exists, it updates the entry with the provided member data.
     * If the member does not exist, it creates a new member entry.
     *
     * @param int $post_id The ID of the post associated with the member.
     * @param stdClass $member An object containing member information such as member_id, name, email, phone, member_type, role, age, is_leader, dynamic_fields, image_id, and image_url.
     *
     * @return bool Returns true if a new member was created or if an existing member was updated with changes; otherwise, false.
     * @since 2.0.0
     */
    static function createOrUpdateMember( int $post_id, stdClass $member ): bool
    {
        $data = [
            'post_id'        => $post_id,
            'member_id'      => $member->member_id,
            'name'           => $member->name,
            'email'          => $member->email,
            'phone'          => $member->phone,
            'member_type'    => $member->member_type,
            'role'           => $member->role,
            'age'            => $member->age,
            'is_leader'      => $member->is_leader,
            'dynamic_fields' => $member->dynamic_fields,
        ];

        if ( property_exists( $member, 'image_id' ) ) {
            $data[ 'image_id' ] = $member->image_id;
        }

        if ( property_exists( $member, 'image_url' ) ) {
            $data[ 'image_url' ] = $member->image_url;
        }

        $member_row = self::getMember( $post_id, $member->member_id );
        if ( !$member_row ) {
            self::$wpdb->insert( self::$table_name, $data );
            unset( $data );
            return true;
        } else {
            $compared_values = array_filter( [
                "name",
                "email",
                "phone",
                "member_type",
                "role",
                "age",
                "dynamic_fields",
                "is_leader",
                "image_id",
                "image_url",
            ], function ( $key ) use ( $member, $member_row ) {
                return isset( $member->{$key}, $member_row->{$key} ) && $member->{$key} !== $member_row->{$key};
            } );
            self::$wpdb->update( self::$table_name, $data, [
                'member_id' => $member->member_id,
                'post_id'   => $post_id
            ] );
            unset( $member_row, $data );
            return !empty( $compared_values );
        }
    }

    /**
     * Deletes a member from the database and, if applicable, removes the associated image attachment.
     *
     * @param int $post_id The ID of the post associated with the member.
     * @param string $member_id The unique identifier of the member to be deleted.
     *
     * @return void
     * @since 2.0.0
     */
    static function deleteMember( int $post_id, string $member_id )
    {
        $member_row = self::getMember( $post_id, $member_id );

        if ( $member_row && $member_row->image_id ) {
            $members = self::$wpdb->get_col(
                self::$wpdb->prepare(
                    "SELECT member_id FROM " . self::$table_name . " WHERE image_id = %d",
                    $member_row->image_id
                )
            );

            if ( !self::$wpdb->last_error && ( empty( $members ) || count( $members ) === 1 ) ) {
                if ( get_attached_file( $member_row->image_id ) ) {
                    wp_delete_attachment( $member_row->image_id, true );
                }
            }

            unset( $members );
        }

        self::$wpdb->delete( self::$table_name, [
            'member_id' => $member_id,
            'post_id'   => $post_id
        ] );

        unset( $member_row );
    }

    /**
     * Deletes all members associated with a specific group.
     *
     * @param int $post_id The ID of the group whose members are to be deleted.
     * @return void
     * @since 2.0.0
     */
    static function deleteGroupMembers( int $post_id )
    {
        $member_ids = self::listAllGroupMemberIds( $post_id );

        if ( !empty( $member_ids ) ) {
            foreach ( $member_ids as $member_id ) {
                self::deleteMember( $post_id, $member_id );
            }
        }

        unset( $member_ids );
    }

    /**
     * Retrieves a member record from the database based on the provided post ID and member ID.
     *
     * @param int $post_id The ID of the post associated with the member.
     * @param string $member_id The unique ID of the member to retrieve.
     * @return object|null The member record if found, or null if no record matches the criteria.
     * @since 2.0.0
     */
    public static function getMember( int $post_id, string $member_id ): ?object
    {
        $query = self::$wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE member_id = %s AND post_id = %d",
            $member_id,
            $post_id
        );
        return self::$wpdb->get_row( $query );
    }

    /**
     * Retrieves a list of group members from the database based on the provided post ID and leadership status.
     *
     * @param int $post_id The ID of the post associated with the group members.
     * @param bool $is_leader Optional. Indicates whether to filter the group members by leadership status.
     *                        Defaults to false (non-leader members).
     * @return array|object|null A list of group members matching the criteria, null if no records are found.
     * @since 2.0.0
     */
    public static function listGroupMembers( int $post_id, bool $is_leader = false )
    {
        return self::$wpdb->get_results(
            self::$wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " WHERE post_id = %d AND is_leader = %d ORDER BY name ASC",
                $post_id,
                $is_leader ? 1 : 0
            )
        );
    }

    static function listGroupMemberIds( int $post_id, bool $is_leader = false ): array
    {
        return self::$wpdb->get_col(
            self::$wpdb->prepare(
                "SELECT member_id FROM " . self::$table_name . " WHERE post_id = %d AND is_leader = %d ORDER BY name ASC",
                $post_id,
                $is_leader ? 1 : 0
            )
        );
    }

    static function listAllGroupMemberIds( int $post_id ): array
    {
        return self::$wpdb->get_col(
            self::$wpdb->prepare(
                "SELECT member_id FROM " . self::$table_name . " WHERE post_id = %d ORDER BY name ASC",
                $post_id
            )
        );
    }
}