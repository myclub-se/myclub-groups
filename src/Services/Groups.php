<?php

namespace MyClub\MyClubGroups\Services;

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
    protected function getAllGroupIds(): stdClass
    {
        $api = new RestApi();

        $returnValue = new stdClass();
        $returnValue->ids = [];
        $returnValue->success = true;

        // Load menuItems items from member backend
        $response = $api->loadMenuItems();

        if ( $response->status === 200 ) {
            $menuItems = $response->result;

            $returnValue->ids = $this->getGroupIds( $menuItems, [] );
        } else {
            $returnValue->success = false;
        }

        $response = $api->loadOtherTeams();
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

    /**
     * Retrieves the IDs of the teams in a menu and its child menus recursively.
     *
     * @param Object $menu The menu object.
     * @param array $teamIds The array of team IDs to add the ids to.
     * @return array The updated array of team IDs.
     *
     * @since 1.0.0
     */
    protected function getGroupIds( $menu, array $teamIds ): array
    {
        if ( property_exists( $menu, 'teams' ) ) {
            foreach ( $menu->teams as $team ) {
                if ( !empty( $team->id ) && !in_array( $team->id, $teamIds ) ) {
                    $teamIds[] = $team->id;
                }
            }
        }

        if ( property_exists( $menu, 'child_menus' ) ) {
            foreach ( $menu->child_menus as $childMenu ) {
                $teamIds = $this->getGroupIds( $childMenu, $teamIds );
            }
        }

        return $teamIds;
    }

    /**
     * Retrieves the post ID of the group post with the given myclubGroupId.
     *
     * @param string $myclubGroupId The myclubGroupId to search for.
     *
     * @return int|false The ID of the group post if found, false otherwise.
     */
    protected function getGroupPostId( string $myclubGroupId )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'meta_query'     => array (
                array (
                    'key'     => 'myclubGroupId',
                    'value'   => $myclubGroupId,
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
     * @param Object $menuItems The menuItems object to check.
     * @return bool Returns true if there are menu items, false otherwise.
     *
     * @since 1.0.0
     */
    protected function menuItemsExist( $menuItems ): bool
    {
        if ( !empty ( $menuItems ) ) {
            if ( ( property_exists( $menuItems, 'teams' ) && count( $menuItems->teams ) ) || ( property_exists( $menuItems, 'child_menus' ) && count( $menuItems->child_menus ) ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
