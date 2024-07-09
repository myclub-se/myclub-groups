<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Api
 *
 * The Api class handles the registration of routes for the REST API and provides methods to retrieve options.
 */
class Api
{
    /**
     * Registers the routes for the REST API.
     *
     * This method hooks into the 'rest_api_init' action and calls the 'registerRoutes' method.
     * It should be called to register the routes for the REST API.
     *
     * @return void
     * @since 1.0.0
     */
    public function register()
    {
        add_action( 'rest_api_init', [
            $this,
            'register_routes'
        ] );
    }

    /**
     * Registers a REST route for retrieving options.
     *
     * This method registers a REST route for retrieving options from the 'myclub/v1/options' endpoint.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route( 'myclub/v1', '/options', [
            'methods'             => 'GET',
            'callback'            => [
                $this,
                'return_options'
            ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ] );

        register_rest_route( 'myclub/v1', '/groups', [
            'methods'             => 'GET',
            'callback'            => [
                $this,
                'return_groups'
            ],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            }
        ] );

        register_rest_route( 'myclub/v1', '/groups/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [
                $this,
                'return_group'
            ],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
            'args'                => array (
                'id' => array (
                    'validate_callback' => function ( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
            )
        ] );
    }

    /**
     * Retrieves a group from the database.
     *
     * This method retrieves a group from the database based on the provided ID. The ID is obtained from the request object.
     * If the group is not found or is not published, a WP_Error object is returned. Otherwise, a WP_REST_Response object
     * containing the group details is returned.
     *
     * @param WP_REST_Request $request The request object.
     *
     * @return WP_REST_Response|WP_Error The group details as a WP_REST_Response object if found, or a WP_Error object if not found.
     *
     * @since 1.0.0
     *
     */
    public function return_group( WP_REST_Request $request ): WP_REST_Response
    {
        $post = get_post( $request[ 'id' ] );

        if ( empty( $post ) || $post->post_status !== 'publish' || $post->post_type !== 'myclub-groups' ) {
            return new WP_REST_Response(
                [
                    'message' => __( 'There is no MyClub Group post with that ID', 'myclub-groups' )
                ], 404
            );
        }

        $post_id = $post->ID;

        return new WP_REST_Response( [
            'activities'   => get_post_meta( $post_id, 'myclub_groups_activities', true ),
            'contact_name' => get_post_meta( $post_id, 'myclub_groups_contact_name', true ),
            'email'        => get_post_meta( $post_id, 'myclub_groups_email', true ),
            'info_text'    => get_post_meta( $post_id, 'myclub_groups_info_text', true ),
            'members'      => get_post_meta( $post_id, 'myclub_groups_members', true ),
            'phone'        => get_post_meta( $post_id, 'myclub_groups_phone', true ),
            'title'        => get_the_title( $post_id ),
        ], 200 );
    }

    /**
     * Get the list of MyClub groups.
     *
     * Retrieves the list of MyClub groups by querying the WordPress database. The groups are retrieved by their post type called "myclub-groups". The query retrieves all published group
     * posts and returns them as an array of objects with "post_id" and "post_title" properties.
     *
     * @return WP_REST_Response The response that contains the list of MyClub groups.
     *
     * @since 1.0.0
     */
    public function return_groups(): WP_REST_Response
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'title',
            'order'          => 'ASC'
        );

        $myclub_groups_query = new WP_Query( $args );

        $myclub_groups = array ();

        if ( $myclub_groups_query->have_posts() ) {
            foreach ( $myclub_groups_query->posts as $post_id ) {
                $myclub_groups[] = array (
                    'id'    => $post_id,
                    'title' => get_the_title( $post_id ),
                );
            }
        }

        return new WP_REST_Response( $myclub_groups, 200 );
    }

    /**
     * Returns an array of options.
     *
     * This method retrieves the values of the following options from the database and returns them as an array:
     * - 'myclub_groups_members_title' option
     * - 'myclub_groups_leaders_title' option
     * - 'myclub_groups_coming_games_title' option
     *
     * @return WP_REST_Response An array containing the values of the retrieved options.
     *
     * @since 1.0.0
     */
    public function return_options(): WP_REST_Response
    {
        return new WP_REST_Response( [
            'myclub_groups_calendar_title'     => esc_attr( get_option( 'myclub_groups_calendar_title' ) ),
            'myclub_groups_coming_games_title' => esc_attr( get_option( 'myclub_groups_coming_games_title' ) ),
            'myclub_groups_leaders_title'      => esc_attr( get_option( 'myclub_groups_leaders_title' ) ),
            'myclub_groups_members_title'      => esc_attr( get_option( 'myclub_groups_members_title' ) ),
            'myclub_groups_page_picture'       => esc_attr( get_option( 'myclub_groups_page_picture' ) )
        ], 200 );
    }
}