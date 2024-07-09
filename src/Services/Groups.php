<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\Api\RestApi;
use stdClass;
use WP_Query;

/**
 * Class Groups
 *
 * This class is responsible for managing groups in WordPress, loaded from the MyClub backend.
 */
class Groups
{
    /**
     * Retrieves an array of all group IDs from the MyClub backend.
     *
     * @return stdClass An object with an array of ids and a success flag.
     */
    protected function get_all_group_ids(): stdClass
    {
        $api = new RestApi();

        $return_value = new stdClass();
        $return_value->ids = [];
        $return_value->success = true;

        // Load menu items from member backend
        $response = $api->load_menu_items();

        if ( $response->status === 200 ) {
            $menu_items = $response->result;

            $return_value->ids = $this->get_group_ids( $menu_items, [] );
        } else {
            $return_value->success = false;
        }

        $response = $api->load_other_teams();
        if ( $response->status === 200 ) {
            $other_teams = $response->result->results;
            foreach ( $other_teams as $other_team ) {
                $return_value->ids[] = $other_team->id;
            }
        } else {
            $return_value->success = false;
        }

        return $return_value;
    }

    /**
     * Retrieves the IDs of the teams in a menu and its child menus recursively.
     *
     * @param object $menu The menu object.
     * @param array $team_ids The array of team IDs to add the ids to.
     * @return array The updated array of team IDs.
     *
     * @since 1.0.0
     */
    protected function get_group_ids( object $menu, array $team_ids ): array
    {
        if ( property_exists( $menu, 'teams' ) ) {
            foreach ( $menu->teams as $team ) {
                if ( !empty( $team->id ) && !in_array( $team->id, $team_ids ) ) {
                    $team_ids[] = $team->id;
                }
            }
        }

        if ( property_exists( $menu, 'child_menus' ) ) {
            foreach ( $menu->child_menus as $child_menu ) {
                $team_ids = $this->get_group_ids( $child_menu, $team_ids );
            }
        }

        return $team_ids;
    }

    /**
     * Retrieves the post ID of the group post with the given myclub_groups_id.
     *
     * @param string $myclub_groups_id The myclub_groups_id to search for.
     *
     * @return int|false The ID of the group post if found, false otherwise.
     */
    protected function get_group_post_id( string $myclub_groups_id )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'meta_query'     => array (
                array (
                    'key'     => 'myclub_groups_id',
                    'value'   => $myclub_groups_id,
                    'compare' => '=',
                ),
            ),
            'posts_per_page' => 1
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            return $query->posts[ 0 ]->ID;
        } else {
            return false;
        }
    }

    /**
     * Checks if there are any menu items (teams or child menus) in the given menuItems object.
     *
     * @param object $menu_items The menuItems object to check.
     * @return bool Returns true if there are menu items, false otherwise.
     *
     * @since 1.0.0
     */
    protected function menu_items_exist( object $menu_items ): bool
    {
        if ( !empty ( $menu_items ) ) {
            if ( ( property_exists( $menu_items, 'teams' ) && count( $menu_items->teams ) ) || ( property_exists( $menu_items, 'child_menus' ) && count( $menu_items->child_menus ) ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
