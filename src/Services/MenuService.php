<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use stdClass;

/**
 * Class MenuService
 *
 * The MenuService class provides methods for managing menus.
 */
class MenuService extends Groups
{
    private $api;
    private $currentMenus;
    private $menu;

    public function __construct()
    {
        $this->api = new RestApi();
        $menuObject = wp_get_nav_menu_object( 'MyClub Groups Menu' );

        if ( !empty( $menuObject ) ) {
            $this->menu = $menuObject;
        }
    }

    /**
     * Deletes the "MyClub Groups Menu" if it exists.
     *
     * @return void
     * @since 1.0.0
     */
    public function deleteAllMenus()
    {
        $menuObject = wp_get_nav_menu_object( 'MyClub Groups Menu' );

        if ( $menuObject ) {
            wp_delete_nav_menu( $menuObject->term_id );
        }
    }

    /**
     * Refreshes the menus by loading current menus, loading menu items from member backend,
     * adding menus if they exist, and deleting unused menus.
     *
     * @return void
     * @since 1.0.0
     */
    public function refreshMenus()
    {
        $this->loadCurrentMenus();

        // Load menu items from member backend
        $response = $this->api->loadMenuItems();

        if ( $response->status === 200 ) {
            $menuItems = $response->result;

            if ( $this->menuItemsExist( $menuItems ) && $this->menu ) {
                $this->addMenus( 0, $menuItems );

                $this->deleteUnusedMenus();
            }
        }
    }

    /**
     * Adds menus to the WordPress navigation menu.
     *
     * @param int $parentItem The ID of the parent menu item.
     * @param object $apiMenu The API menu object.
     * @param int $position The position of the menu item. Default is 1.
     *
     * @return int The updated position after adding menus.
     * @since 1.0.0
     */
    private function addMenus( int $parentItem, $apiMenu, int $position = 1 ): int
    {
        foreach ( $apiMenu->child_menus as $childMenu ) {
            // Check if menu item already exists - overwrite in that case
            $menuItemId = $this->queryMenuItems( $childMenu->name, $parentItem );
            $menuItemInfo = $this->createMenuItemArgs( $childMenu->name, $parentItem, $position );

            $menuId = wp_update_nav_menu_item( $this->menu->term_id, $menuItemId, $menuItemInfo );

            $position += 1;

            if ( !is_wp_error( $menuId ) ) {
                // Update current menu items
                $this->updateCurrentMenus( 'menu' . $menuId, $menuId, $parentItem, $childMenu->name );

                $position = $this->addMenus( $menuId, $childMenu, $position );
            } else {
                error_log( 'Unable to create menus' );
            }
        }

        foreach ( $apiMenu->teams as $group ) {
            if ( !empty( $group->id ) ) {
                // Check if menu already exists and update
                $menuItemId = key_exists( $group->id, $this->currentMenus ) ? $this->currentMenus[ $group->id ]->id : 0;
                $postId = $this->getGroupPostId( $group->id ) ?: 0;
                $menuItemInfo = $this->createMenuItemArgs( $group->name, $parentItem, $position, $postId );

                $menuId = wp_update_nav_menu_item( $this->menu->term_id, $menuItemId, $menuItemInfo );
                update_post_meta( $menuId, 'myclubGroupId', $group->id );

                // Update current menu items
                $this->updateCurrentMenus( $group->id, $menuId, $parentItem, $group->name, $group->id );

                $position += 1;
            }
        }

        return $position;
    }

    /**
     * Creates an array of menu item arguments based on the provided parameters.
     *
     * @param string $name The title of the menu item.
     * @param int $parentItem The ID of the parent menu item. Set to 0 for top-level items.
     * @param int $position The position of the menu item within its parent.
     * @param int $objectId The ID of the object associated with the menu item. Default value is 0.
     * @return array An array of menu item arguments.
     */
    private function createMenuItemArgs( string $name, int $parentItem, int $position, int $objectId = 0 ): array
    {
        $menuItemInfo = [
            'menu-item-title'  => $name,
            'menu-item-status' => 'publish'
        ];

        if ( $parentItem !== 0 ) {
            $menuItemInfo[ 'menu-item-parent-id' ] = $parentItem;
        }

        if ( $objectId !== 0 ) {
            $menuItemInfo[ 'menu-item-object-id' ] = $objectId;
            $menuItemInfo[ 'menu-item-object' ] = 'myclub-groups';
            $menuItemInfo[ 'menu-item-type' ] = 'post_type';
        }

        if ( $position !== 0 ) {
            $menuItemInfo[ 'menu-item-position' ] = $position;
        }

        return $menuItemInfo;
    }

    /**
     * Deletes unused menus. The menus don't exist on the MyClub backend anymore.
     *
     * @since 1.0.0
     */
    private function deleteUnusedMenus()
    {
        foreach ( $this->currentMenus as $currentMenu ) {
            if ( $currentMenu->status === 0 ) {
                wp_delete_post( $currentMenu->id, true );
            }
        }
    }

    /**
     * Loads the current menus and populates the `$this->currentMenus` array with menu items.
     *
     * @return void
     * @since 1.0.0
     */
    private function loadCurrentMenus()
    {
        $this->currentMenus = array ();
        if ( !empty( $this->menu ) ) {
            $allMenuItems = wp_get_nav_menu_items( $this->menu->term_id, [ 'numberofposts' => -1 ] );

            foreach ( $allMenuItems as $menu ) {
                $menuItem = new stdClass();
                $menuItem->id = $menu->ID;
                $menuItem->name = $menu->title;
                $menuItem->parentId = $menu->menu_item_parent;
                $menuItem->myclubGroupId = $menu->myclubGroupId;
                // Menu status:
                // 0 - present - not updated (default)
                // 1 - present - updated
                // 2 - new group menu
                $menuItem->status = 0;

                $this->currentMenus[ $menuItem->myclubGroupId ?: 'menu' . $menuItem->id ] = $menuItem;
            }
        }
    }

    /**
     * Query menu items based on name and parent item.
     *
     * @param string $name The name of the menu item.
     * @param int $parentItem The ID of the parent menu item. Use 0 for top-level items.
     *
     * @return int The ID of the found menu item, or 0 if not found.
     *
     * @since 1.0.0
     */
    private function queryMenuItems( string $name, int $parentItem ): int
    {
        $query = [
            'post_type'              => 'nav_menu_item',
            'post_status'            => 'publish',
            'name'                   => $name,
            'post_parent'            => $parentItem !== 0 ?: 0,
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
     * @param int $menuId The ID of the menu item.
     * @param int $parentItem The ID of the parent menu item.
     * @param string $name The name of the menu item.
     * @param string $myclubGroupId The ID of the MyClub group associated with the menu item (optional).
     * @return void
     * @since 1.0.0
     */
    private function updateCurrentMenus( string $key, int $menuId, int $parentItem, string $name, string $myclubGroupId = '' )
    {
        if ( key_exists( $key, $this->currentMenus ) ) {
            $this->currentMenus[ $key ]->status = 1;
        } else {
            $menuItem = new stdClass();
            $menuItem->id = $menuId;
            $menuItem->name = $name;
            $menuItem->parentId = $parentItem ?: 0;
            $menuItem->myclubGroupId = $myclubGroupId;
            $menuItem->status = 2;
            $this->currentMenus[ $key ] = $menuItem;
        }
    }
}
