<?php

namespace MyClub\MyClubGroups;

use MyClub\MyClubGroups\Services\MyClubCron;

/**
 * Class Activation
 *
 * This class provides methods to handle the activation, deactivation, and uninstallation of a software application.
 */
class Activation
{
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
        $this->addOption( 'myclub_groups_api_key', NULL );
        $this->addOption( 'myclub_groups_group_slug', 'groups' );
        $this->addOption( 'myclub_groups_group_news_slug', 'group-news' );
        $this->addOption( 'myclub_groups_last_news_sync', NULL );
        $this->addOption( 'myclub_groups_last_groups_sync', NULL );
        $this->addOption( 'myclub_groups_calendar_title',  __( 'Calendar', 'myclub-groups' ) );
        $this->addOption( 'myclub_groups_coming_games_title',  __( 'Upcoming games', 'myclub-groups' ) );
        $this->addOption( 'myclub_groups_leaders_title',  __( 'Leaders', 'myclub-groups' ) );
        $this->addOption( 'myclub_groups_members_title',  __( 'Members', 'myclub-groups' ) );
        $this->addOption( 'myclub_groups_news_title',  __( 'News', 'myclub-groups' ) );
        $this->addOption( 'myclub_groups_page_template',  '' );
        $this->addOption( 'myclub_groups_page_calendar', '1' );
        $this->addOption( 'myclub_groups_page_navigation', '1' );
        $this->addOption( 'myclub_groups_page_members', '1' );
        $this->addOption( 'myclub_groups_page_leaders', '1' );
        $this->addOption( 'myclub_groups_page_menu', '1' );
        $this->addOption( 'myclub_groups_page_news', '1' );
        $this->addOption( 'myclub_groups_page_title', '1' );
        $this->addOption( 'myclub_groups_page_coming_games', '1' );
        $this->addOption( 'myclub_groups_show_items_order', array (
            'menu',
            'navigation',
            'calendar',
            'members',
            'leaders',
            'news',
            'coming-games'
        ) );
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
    }

    /**
     * @return void
     */
    public function uninstall()
    {

    }


    /**
     * Adds an option to the WordPress database if it doesn't already exist.
     *
     * @param string $optionName The name of the option.
     * @param mixed $default The default value for the option.
     *
     * @return void
     */
    private function addOption( string $optionName, $default) {
        if ( get_option( $optionName ) === false ) {
            add_option( $optionName, $default );
        }
    }
}