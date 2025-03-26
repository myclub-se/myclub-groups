<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Utils;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CalendarService
{
    private RestApi $api;

    public function __construct()
    {
        $this->api = new RestApi();
    }

    /**
     * Retrieves a list of activities from the specified database table.
     *
     * @return array Array of activities as retrieved from the database. Returns an empty array if an error occurs.
     * @since 1.3.0
     */
    static public function ListActivities(): array
    {
        $value = json_decode( get_option( 'myclub_groups_club_activities' ) );

        return !empty( $value ) ? $value : [];
    }

    /**
     * Retrieves and processes a list of club events from the external API.
     * Updates, creates, or deletes activities based on the response data.
     *
     * @return void
     * @since 1.3.0
     */
    public function reload_club_events(): void
    {
        $response = $this->api->load_club_calendar();

        if ( !is_wp_error( $response ) && $response->status === 200 ) {
            foreach ( $response->result->results as $activity ) {
                $activity->description = str_replace( "\n", '<br />', htmlspecialchars( $activity->description, ENT_QUOTES, 'UTF-8' ) );
            }

            $club_events_json = Utils::prepare_activities_json( $response->result->results );
            if ( Utils::update_or_create_option( 'myclub_groups_club_activities', $club_events_json, 'no', true ) === false) {
                $post_ids = Utils::get_club_calendar_posts();

                foreach ( $post_ids as $post_id ) {
                    Utils::clear_cache_for_page( $post_id );
                }
            }
        }
        Utils::update_or_create_option( 'myclub_groups_last_club_calendar_sync', gmdate( "c" ), 'no' );
    }
}