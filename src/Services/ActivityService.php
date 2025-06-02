<?php

namespace MyClub\MyClubGroups\Services;

use stdClass;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class ActivityService
 *
 * A service responsible for managing activities related to posts and club calendars. It provides methods
 * to create, update, delete, and retrieve activities stored in a database table. It also allows listing of
 * activities in various contexts like club calendars or specific post associations.
 */
class ActivityService
{
    private static $wpdb;
    private static string $activities_table_name;
    private static string $activities_link_table_name;

    /**
     * Initializes the database connection and sets up the table name for group activities.
     *
     * @return void
     * @since 2.0.0
     */
    public static function init()
    {
        global $wpdb;
        self::$wpdb = $wpdb;
        self::$activities_table_name = self::$wpdb->prefix . 'myclub_groups_activities';
        self::$activities_link_table_name = self::$wpdb->prefix . 'myclub_groups_post_activities';
    }

    /**
     * Creates the activities database tables with the specified schema.
     * The table includes fields for activity details such as title, date, time, location, and more.
     * It incorporates constraints like unique identification, foreign keys, and default values.
     *
     * @return void
     * @since 2.0.0
     */
    static function createActivityTables()
    {
        $charset_collate = self::$wpdb->get_charset_collate();

        $activities_table_sql = "CREATE TABLE " . self::$activities_table_name . " (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            uid varchar(20) NOT NULL UNIQUE,
            show_on_club_calendar tinyint(1) DEFAULT 0,
            title varchar(255) DEFAULT '',
            day DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            location varchar(255) DEFAULT '',
            description TEXT DEFAULT '',
            calendar_name varchar(255) DEFAULT '',
            type varchar(255) DEFAULT '',
            base_type varchar(255) DEFAULT '',
            meet_up_time int(11) DEFAULT NULL,
            meet_up_place varchar(255) DEFAULT '',
            PRIMARY KEY (id)
        ) $charset_collate;";

        $activities_link_table_sql = "CREATE TABLE " . self::$activities_link_table_name . " (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            activity_uid varchar(20) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_post_activity (post_id, activity_uid),
            FOREIGN KEY (post_id) REFERENCES " . self::$wpdb->prefix . "posts(ID) ON DELETE CASCADE,
            FOREIGN KEY (activity_uid) REFERENCES " . self::$activities_table_name . "(uid) ON DELETE CASCADE
        ) $charset_collate;";


        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $activities_table_sql );
        dbDelta( $activities_link_table_sql );
    }

    /**
     * Deletes the activity tables from the database if it exists.
     *
     * @return void
     * @since 2.0.0
     */
    static function deleteActivityTables()
    {
        self::$wpdb->query( "DROP TABLE IF EXISTS " . self::$activities_link_table_name );
        self::$wpdb->query( "DROP TABLE IF EXISTS " . self::$activities_table_name );
    }

    /**
     * Adds an activity to a specified post in the database if it does not already exist.
     *
     * @param int $post_id The ID of the post to associate the activity with.
     * @param string $activity_id The unique identifier of the activity to be added.
     * @return bool Returns true if the activity was successfully added, or false if it already exists.
     * @since 2.0.0
     */
    static function addActivityToPost( int $post_id, string $activity_id ): bool
    {
        $existing_row = self::$wpdb->get_row(
            self::$wpdb->prepare(
                "SELECT id FROM " . self::$activities_link_table_name . " WHERE post_id = %d AND activity_uid = %s",
                $post_id,
                $activity_id
            )
        );

        if ( $existing_row ) {
            return false;
        }

        self::$wpdb->insert( self::$activities_link_table_name, [
            'post_id'      => $post_id,
            'activity_uid' => $activity_id
        ] );

        return true;
    }

    /**
     * Creates or updates an activity record in the database. If the activity exists, it updates its fields;
     * otherwise, it creates a new record.
     *
     * @param stdClass $activity Object containing the activity details such as title, day, start time, end time, etc.
     * @param array $calendar_array Optional array containing properties like 'show_on_club_calendar' for visibility settings.
     *
     * @return bool Returns true if a new activity is created or the activity is updated with changes. Returns false if there are no changes and the activity already exists.
     * @since 2.0.0
     */
    static function createOrUpdateActivity( stdClass $activity, array $calendar_array = [] ): bool
    {
        $data = [
            'uid'           => $activity->uid,
            'title'         => $activity->title,
            'day'           => $activity->day,
            'start_time'    => $activity->start_time,
            'end_time'      => $activity->end_time,
            'location'      => $activity->location,
            'description'   => htmlspecialchars( str_replace( "\n", "<br", $activity->description ), ENT_QUOTES, 'UTF-8' ),
            'calendar_name' => $activity->calendar_name,
            'type'          => $activity->type,
            'base_type'     => $activity->base_type,
            'meet_up_time'  => $activity->meet_up_time,
            'meet_up_place' => $activity->meet_up_place,
        ];

        if ( array_key_exists( "show_on_club_calendar", $calendar_array ) ) {
            $data[ 'show_on_club_calendar' ] = $calendar_array[ 'show_on_club_calendar' ];
        }

        $activity_row = self::getActivity( $activity->uid );

        if ( !$activity_row ) {
            self::$wpdb->insert( self::$activities_table_name, $data );
            unset( $data );
            $update = true;
        } else {
            $compared_values = array_filter( [
                "title",
                "description",
                "day",
                "start_time",
                "end_time",
                "location",
                "calendar_name",
                "type",
                "base_type",
                "meet_up_time",
                "meet_up_place",
                "show_on_club_calendar"
            ], function ( $key ) use ( $activity, $activity_row ) {
                return isset( $activity->{$key}, $activity_row->{$key} ) && $activity->{$key} !== $activity_row->{$key};
            } );
            self::$wpdb->update( self::$activities_table_name, $data, [ 'uid' => $activity->uid ] );
            $update = !empty( $compared_values );
            unset( $activity_row, $data, $compared_values );
        }

        if ( property_exists( $activity, 'post_id' ) ) {
            $update = self::addActivityToPost( $activity->post_id, $activity->uid ) || $update;
        }

        return $update;
    }

    /**
     * Deletes an activity from the database based on the provided activity ID.
     *
     * @param string $activity_id The unique identifier of the activity to be deleted.
     * @return void
     * @since 2.0.0
     */
    static function deleteActivity( string $activity_id )
    {
        self::$wpdb->delete( self::$activities_table_name, [ 'uid' => $activity_id ] );
    }

    static function removeActivityFromPost( int $post_id, string $activity_id )
    {
        self::$wpdb->delete( self::$activities_link_table_name, [ 'post_id' => $post_id, 'activity_uid' => $activity_id ] );

        if ( count( self::listActivityPostIds( $activity_id ) ) === 0 ) {
            $activity = self::getActivity( $activity_id );
            if ( !$activity->show_on_club_calendar ) {
                self::deleteActivity( $activity_id );
            }
        }
    }

    /**
     * Retrieves an activity from the database based on the provided activity ID.
     *
     * @param string $activity_id The unique identifier of the activity to retrieve.
     * @return object|null An object containing the activity data or null if no activity is found.
     * @since 2.0.0
     */
    static function getActivity( string $activity_id ): ?object
    {
        return self::$wpdb->get_row(
            self::$wpdb->prepare(
                "SELECT * FROM " . self::$activities_table_name . " WHERE uid = %s",
                $activity_id
            )
        );
    }

    /**
     * Retrieves a list of club activities to display on the club calendar. Each activity is included only if it is marked to be shown on the club calendar.
     *
     * @return array|object|null A list of activities that should be shown on the club calendar, ordered by day, start time, and end time.
     * @since 2.0.0
     */
    static function listClubActivities()
    {
        return self::$wpdb->get_results(
            "SELECT * FROM " . self::$activities_table_name . " WHERE show_on_club_calendar = 1 ORDER BY day, start_time, end_time",
        );
    }

    /**
     * Retrieves a list of activity IDs that are marked to be shown on the club calendar.
     *
     * @return array|object|null An array of activity IDs that are set to be visible on the club calendar.
     * @since 2.0.0
     */
    static function listClubActivityIds()
    {
        return self::$wpdb->get_col( "SELECT uid FROM " . self::$activities_table_name . " WHERE show_on_club_calendar = 1" );
    }

    /**
     * Retrieves a list of activities associated with a specific post from the database.
     *
     * @param int $post_id The unique identifier of the post for which activities are to be listed.
     * @return array|object|null A list of activities that are connected to the post, ordered by day, start time, and end time.
     * @since 2.0.0
     */
    static function listPostActivities( int $post_id ): array
    {
        {
            return self::$wpdb->get_results(
                self::$wpdb->prepare(
                    "SELECT a.* FROM " . self::$activities_table_name . " a INNER JOIN " . self::$activities_link_table_name . " pa ON a.uid = pa.activity_uid WHERE pa.post_id = %d ORDER BY day, start_time, end_time",
                    $post_id
                )
            );
        }
    }

    /**
     * Retrieves a list of activity IDs associated with a specific post.
     *
     * @param int $post_id The unique identifier of the post for which activity IDs are retrieved.
     * @return array|object|null An array of activity IDs related to the specified post.
     * @since 2.0.0
     */
    static function listPostActivityIds( int $post_id )
    {
        return self::$wpdb->get_col(
            self::$wpdb->prepare(
                "SELECT activity_uid FROM " . self::$activities_link_table_name . " WHERE post_id = %d",
                $post_id
            )
        );
    }

    /**
     * Retrieves a list of post IDs associated with a specific activity id.
     *
     * @param string $activity_id The unique identifier of the activity for which post ids are retrieved.
     * @return array|object|null An array of post IDs related to the specified post.
     * @since 2.0.0
     */
    static function listActivityPostIds( string $activity_id )
    {
        return self::$wpdb->get_col(
            self::$wpdb->prepare(
                "SELECT post_id FROM " . self::$activities_link_table_name . " WHERE activity_uid = %s",
                $activity_id
            )
        );
    }
}