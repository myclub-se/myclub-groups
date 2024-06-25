<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\Api\RestApi;
use stdClass;
use WP_Term;

/**
 * Class MenuService
 *
 * The MenuService class provides methods for managing menus.
 */
class MenuService extends Groups
{
    private RestApi $api;
    private array $current_menus;
    private WP_Term $menu;

    public function __construct()
    {
        $this->api = new RestApi();
        $menu_object = wp_get_nav_menu_object( 'MyClub Groups Menu' );

        if ( !empty( $menu_object ) ) {
            $this->menu = $menu_object;
        }
    }

    /**
     * Deletes the "MyClub Groups Menu" if it exists.
     *
     * @return void
     * @since 1.0.0
     */
    public function delete_all_menus()
    {
        $menu_object = wp_get_nav_menu_object( 'MyClub Groups Menu' );

        if ( $menu_object ) {
            wp_delete_nav_menu( $menu_object->term_id );
        }
    }

    /**
     * Refreshes the menus by loading current menus, loading menu items from member backend,
     * adding menus if they exist, and deleting unused menus.
     *
     * @return void
     * @since 1.0.0
     */
    public function refresh_menus()
    {
        $this->load_current_menus();

        // Load menu items from member backend
        $response = $this->api->load_menu_items();

        if ( $response->status === 200 ) {
            $menu_items = $response->result;

            if ( $this->menu_items_exist( $menu_items ) && !empty( $this->menu ) ) {
                $this->add_menus( 0, $menu_items );

                $this->delete_unused_menus();
            }
        }
    }

    /**
     * Adds menus to the WordPress navigation menu.
     *
     * @param int $parent_item The ID of the parent menu item.
     * @param object $api_menu The API menu object.
     * @param int $position The position of the menu item. Default is 1.
     *
     * @return int The updated position after adding menus.
     * @since 1.0.0
     */
    private function add_menus( int $parent_item, object $api_menu, int $position = 1 ): int
    {
        foreach ( $api_menu->child_menus as $child_menu ) {
            // Check if menu item already exists - overwrite in that case
            $menu_item_id = $this->query_menu_items( $child_menu->name, $parent_item );
            $menu_item_info = $this->create_menu_item_args( $child_menu->name, $parent_item, $position );

            $menu_id = wp_update_nav_menu_item( $this->menu->term_id, $menu_item_id, $menu_item_info );

            $position += 1;

            if ( !is_wp_error( $menu_id ) ) {
                // Update current menu items
                $this->update_current_menus( 'menu' . $menu_id, $menu_id, $parent_item, $child_menu->name );

                $position = $this->add_menus( $menu_id, $child_menu, $position );
            } else {
                error_log( 'Unable to create menus' );
            }
        }

        foreach ( $api_menu->teams as $group ) {
            if ( !empty( $group->id ) ) {
                // Check if menu already exists and update
                $menu_item_id = key_exists( $group->id, $this->current_menus ) ? $this->current_menus[ $group->id ]->id : 0;
                $post_id = $this->get_group_post_id( $group->id ) ?: 0;
                $menu_item_info = $this->create_menu_item_args( $group->name, $parent_item, $position, $post_id );

                $menu_id = wp_update_nav_menu_item( $this->menu->term_id, $menu_item_id, $menu_item_info );
                update_post_meta( $menu_id, 'myclub_group_id', $group->id );

                // Update current menu items
                $this->update_current_menus( $group->id, $menu_id, $parent_item, $group->name, $group->id );

                $position += 1;
            }
        }

        return $position;
    }

    /**
     * Creates an array of menu item arguments based on the provided parameters.
     *
     * @param string $name The title of the menu item.
     * @param int $parent_item The ID of the parent menu item. Set to 0 for top-level items.
     * @param int $position The position of the menu item within its parent.
     * @param int $object_id The ID of the object associated with the menu item. Default value is 0.
     * @return array An array of menu item arguments.
     */
    private function create_menu_item_args( string $name, int $parent_item, int $position, int $object_id = 0 ): array
    {
        $menu_item_info = [
            'menu-item-title'  => $name,
            'menu-item-status' => 'publish'
        ];

        if ( $parent_item !== 0 ) {
            $menu_item_info[ 'menu-item-parent-id' ] = $parent_item;
        }

        if ( $object_id !== 0 ) {
            $menu_item_info[ 'menu-item-object-id' ] = $object_id;
            $menu_item_info[ 'menu-item-object' ] = 'myclub-groups';
            $menu_item_info[ 'menu-item-type' ] = 'post_type';
        }

        if ( $position !== 0 ) {
            $menu_item_info[ 'menu-item-position' ] = $position;
        }

        return $menu_item_info;
    }

    /**
     * Deletes unused menus. The menus don't exist on the MyClub backend anymore.
     *
     * @since 1.0.0
     */
    private function delete_unused_menus()
    {
        foreach ( $this->current_menus as $current_menu ) {
            if ( $current_menu->status === 0 ) {
                wp_delete_post( $current_menu->id, true );
            }
        }
    }

    /**
     * Loads the current menus and populates the `$this->currentMenus` array with menu items.
     *
     * @return void
     * @since 1.0.0
     */
    private function load_current_menus()
    {
        $this->current_menus = array ();
        if ( !empty( $this->menu ) ) {
            $all_menu_items = wp_get_nav_menu_items( $this->menu->term_id, [ 'numberofposts' => -1 ] );

            foreach ( $all_menu_items as $menu ) {
                $menu_item = new stdClass();
                $menu_item->id = $menu->ID;
                $menu_item->name = $menu->title;
                $menu_item->parent_id = $menu->menu_item_parent;
                $menu_item->myclub_group_id = $menu->myclub_group_id;
                // Menu status:
                // 0 - present - not updated (default)
                // 1 - present - updated
                // 2 - new group menu
                $menu_item->status = 0;

                $this->current_menus[ $menu_item->myclub_group_id ?: 'menu' . $menu_item->id ] = $menu_item;
            }
        }
    }

    /**
     * Query menu items based on name and parent item.
     *
     * @param string $name The name of the menu item.
     * @param int $parent_item The ID of the parent menu item. Use 0 for top-level items.
     *
     * @return int The ID of the found menu item, or 0 if not found.
     *
     * @since 1.0.0
     */
    private function query_menu_items( string $name, int $parent_item ): int
    {
        $query = [
            'post_type'              => 'nav_menu_item',
            'post_status'            => 'publish',
            'name'                   => $name,
            'post_parent'            => $parent_item !== 0 ?: 0,
            'update_menu_item_cache' => true,
            'tax_query'              => array (
                array (
                    'taxonomy' => 'nav_menu',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $this->menu->term_taxonomy_id,
                ),
            ),
            'posts_per_page'         => 1
        ];

        // Query menu items
        $posts = get_posts( $query );

        if ( !empty( $posts ) ) {
            return $posts[ 0 ]->ID;
        }
        return 0;
    }

    /**
     * Updates the current menus array with a new or existing menu item.
     *
     * @param string $key The key for the menu item in the currentMenus array.
     * @param int $menu_id The ID of the menu item.
     * @param int $parent_item The ID of the parent menu item.
     * @param string $name The name of the menu item.
     * @param string $myclub_group_id The ID of the MyClub group associated with the menu item (optional).
     * @return void
     * @since 1.0.0
     */
    private function update_current_menus( string $key, int $menu_id, int $parent_item, string $name, string $myclub_group_id = '' )
    {
        if ( key_exists( $key, $this->current_menus ) ) {
            $this->current_menus[ $key ]->status = 1;
        } else {
            $menuItem = new stdClass();
            $menuItem->id = $menu_id;
            $menuItem->name = $name;
            $menuItem->parent_id = $parent_item ?: 0;
            $menuItem->myclub_group_id = $myclub_group_id;
            $menuItem->status = 2;
            $this->current_menus[ $key ] = $menuItem;
        }
    }
}
