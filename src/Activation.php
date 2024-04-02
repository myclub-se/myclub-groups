<?php

namespace MyClub\MyClubGroups;

use MyClub\MyClubGroups\Services\GroupService;
use MyClub\MyClubGroups\Services\MenuService;
use MyClub\MyClubGroups\Services\MyClubCron;
use MyClub\MyClubGroups\Services\NewsService;

/**
 * Class Activation
 *
 * This class provides methods to handle the activation, deactivation, and uninstallation of a software application.
 */
class Activation
{
    private array $options;
    
    public function __construct()
    {
        $this->options = [
            [
                'name'  => 'myclub_groups_api_key',
                'value' => null,
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_group_slug',
                'value' => 'groups',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_group_news_slug',
                'value' => 'group-news',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_last_news_sync',
                'value' => null,
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_last_groups_sync',
                'value' => null,
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_calendar_title',
                'value' => __( 'Calendar', 'myclub-groups' ),
                'autoload' => 'yes',
            ],
            [
                'name'  => 'myclub_groups_coming_games_title',
                'value' => __( 'Upcoming games', 'myclub-groups' ),
                'autoload' => 'yes',
            ],
            [
                'name'  => 'myclub_groups_leaders_title',
                'value' => __( 'Leaders', 'myclub-groups' ),
                'autoload' => 'yes',
            ],
            [
                'name'  => 'myclub_groups_members_title',
                'value' => __( 'Members', 'myclub-groups' ),
                'autoload' => 'yes',
            ],
            [
                'name'  => 'myclub_groups_news_title',
                'value' => __( 'News', 'myclub-groups' ),
                'autoload' => 'yes',
            ],
            [
                'name'  => 'myclub_groups_page_template',
                'value' => '',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_calendar',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_navigation',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_leaders',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_menu',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_news',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_title',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_picture',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_page_coming_games',
                'value' => '1',
                'autoload' => 'no',
            ],
            [
                'name'  => 'myclub_groups_show_items_order',
                'value' => array (
                    'default',
                ),
                'autoload' => 'no',
            ]
        ];
    }
    
    
    /**
     * Activates the plugin.
     *
     * This method adds default options for the plugin when it is activated.
     * It sets the default values for various settings and options.
     *
     * @return void
     * @since 1.0.0
     */
    public function activate()
    {
        foreach ( $this->options as $option ) {
            $this->addOption( $option[ 'name' ], $option[ 'value' ], $option[ 'autoload' ] );
        }
    }

    /**
     * Deactivates the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function deactivate()
    {
        $cron = new MyClubCron();
        $cron->deactivate();

        delete_option( 'myclub_groups_api_key' );
    }

    /**
     * Uninstalls the plugin.
     *
     * This method is responsible for deleting all the plugin options, deleting all news,
     * menus, and groups associated with the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function uninstall()
    {
        // Delete all plugin options
        foreach ( $this->options as $option ) {
            delete_option( $option[ 'name' ] );
        }

        $newsService = new NewsService();
        $newsService->delete_all_news();

        $menuService = new MenuService();
        $menuService->delete_all_menus();

        $groupsService = new GroupService();
        $groupsService->delete_all_groups();
    }


    /**
     * Adds an option to the WordPress database if it doesn't already exist.
     *
     * @param string $optionName The name of the option.
     * @param mixed $default The default value for the option.
     * @param string|null $autoload Sets if the option should be loaded.
     *
     * @return void
     */
    private function addOption( string $optionName, $default, string $autoload ) {
        if ( get_option( $optionName ) === false ) {
            add_option( $optionName, $default, '', $autoload );
        }
    }
}