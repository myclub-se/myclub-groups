<?php

namespace MyClub\MyClubGroups\Services;

/**
 * Class Menu
 *
 * This class initializes and registers a WordPress menu called 'Groups Menu'.
 */
class Menu
{
    /**
     * Registers the initialization of the groups' menu.
     *
     * This method adds an action hook to the 'init' event that calls the 'initGroupsMenu' method.
     *
     * @return void
     */
    public function register()
    {
        add_action( 'init', [
            $this,
            'init_groups_menu'
        ] );
    }

    /**
     * Initializes the groups' menu.
     *
     * This method registers a navigation menu called "myclub-groups-menu" with the label "Groups Menu".
     * It checks if the menu already exists, and if not, it creates the menu and assigns it to the "myclub-groups-menu" location.
     *
     * @return void
     */
    public function init_groups_menu()
    {
        register_nav_menu( 'myclub-groups-menu', 'MyClub Groups Menu Location' );

        $menu_exists = wp_get_nav_menu_object( 'MyClub Groups Menu' );

        if ( !$menu_exists ) {
            $menu_id = wp_create_nav_menu( 'MyClub Groups Menu' );

            $locations = get_theme_mod( 'nav_menu_locations' );
            $locations[ 'myclub-groups-menu' ] = $menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }
}