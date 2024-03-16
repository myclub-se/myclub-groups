<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\MyClubGroups\Api\RestApi;
use MyClub\MyClubGroups\Utils;
use WP_Query;

/**
 * Class Admin
 *
 * This class is responsible for handling the admin-related functionality of the plugin.
 */
class Admin extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registers various actions and filters related to the plugin.
     *
     * This method registers multiple actions and filters that are necessary for the plugin to function properly.
     * It adds the admin menu, MyClub groups settings, update API key, reload groups, reload news, enqueue admin JS,
     * add group news column, and add group news column content.
     *
     * @since 1.0.0
     */
    public function register()
    {
        add_action( 'admin_menu', [
            $this,
            'addAdminMenu'
        ] );
        add_action( 'admin_init', [
            $this,
            'addMyClubGroupsSettings'
        ] );
        add_action( 'update_option_myclub_groups_api_key', [
            $this,
            'updateApiKey'
        ], 10, 0 );
        add_action( 'update_option_myclub_groups_show_items_order', [
            $this,
            'updateShownOrder'
        ], 10, 2 );
        add_action( 'update_option_myclub_groups_page_template', [
            $this,
            'updatePageTemplate'
        ], 10, 2 );
        add_action( 'wp_ajax_myclub_reload_groups', [
            $this,
            'ajaxReloadGroups'
        ] );
        add_action( 'wp_ajax_myclub_reload_news', [
            $this,
            'ajaxReloadNews'
        ] );
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueueAdminJS'
        ] );
        add_action( 'manage_post_posts_columns', [
            $this,
            'addGroupNewsColumn'
        ] );
        add_filter( 'manage_post_posts_custom_column', [
            $this,
            'addGroupNewsColumnContent'
        ], 10, 2 );

        add_action( 'wp_dashboard_setup', [ $this, 'setupDashboardWidget' ] );

        add_filter("plugin_action_links_myclub-groups/myclub-groups.php", [ $this, 'addPluginSettingsLink' ] );
    }

    /**
     * Adds the admin menu for the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function addAdminMenu()
    {
        add_options_page(
            __( 'MyClub Groups plugin settings', 'myclub-groups' ),
            __( 'MyClub Groups', 'myclub-groups' ),
            'manage_options',
            'myclub-groups-settings',
            [
                $this,
                'adminSettings'
            ]
        );
    }

    /**
     * Adds the "Group news" taxonomy column to the post listing.
     *
     * @param array $columns Array that contains the existing columns for the post listings.
     * @return array Updated array with the "Group news" column added.
     * @since 1.0.0
     */
    public function addGroupNewsColumn( array $columns ): array
    {
        $index = array_search( 'author', array_keys( $columns ) );

        if ( $index && count( $columns ) > $index ) {
            return array_merge(
                array_slice( $columns, 0, $index + 1 ),
                [ 'group_news' => __( 'Group news', 'myclub-groups' ) ],
                array_slice( $columns, $index + 1, count( $columns ) )
            );
        } else {
            return array_merge( $columns, [ 'group_news' => __( 'Group news', 'myclub-groups' ) ] );
        }
    }

    /**
     * Adds the content for the 'group_news' column for post listing page.
     *
     * @param string $column_key The key of the column.
     * @param int $postId The ID of the post.
     * @return void
     * @since 1.0.0
     */
    public function addGroupNewsColumnContent( string $column_key, int $postId )
    {
        if ( $column_key === 'group_news' ) {
            $names = [];
            $terms = wp_get_post_terms( $postId, NewsService::MYCLUB_GROUP_NEWS );
            foreach ( $terms as $term ) {
                $names[] = $term->name;
            }
            echo join( ', ', $names );
        }
    }

    /**
     * Registers the MyClub Groups plugin settings page and adds all the settings to the page.
     *
     * @return void
     * @since 1.0.0
     */
    public function addMyClubGroupsSettings()
    {
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_api_key', [
            'sanitize_callback' => [
                $this,
                'sanitizeApiKey'
            ],
            'default'           => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_group_slug', [
            'sanitize_callback' => [
                $this,
                'sanitizeGroupSlug'
            ],
            'default'           => 'groups'
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_group_news_slug', [
            'sanitize_callback' => [
                $this,
                'sanitizeGroupNewsSlug'
            ],
            'default'           => 'group-news'
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_last_news_sync', [
            'default' => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_last_groups_sync', [
            'default' => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_calendar_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeCalendarTitle'
            ],
            'default'           => __( 'Calendar', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_coming_games_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeComingGamesTitle'
            ],
            'default'           => __( 'Upcoming games', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_leaders_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeLeadersTitle'
            ],
            'default'           => __( 'Leaders', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_members_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeMembersTitle'
            ],
            'default'           => __( 'Members', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_news_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeNewsTitle'
            ],
            'default'           => __( 'News', 'myclub-groups' )
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_club_news_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeClubNewsTitle'
            ],
            'default'           => __( 'News', 'myclub-groups' )
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_template', [
            'sanitize_callback' => [
                $this,
                'sanitizePageTemplate'
            ],
            'default'           => ''
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_calendar', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_navigation', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_members', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_leaders', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_menu', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_news', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_title', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_picture', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_coming_games', [
            'sanitize_callback' => [
                $this,
                'sanitizeCheckbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_show_items_order', [
            'sanitize_callback' => [
                $this,
                'sanitizeShowItemsOrder'
            ],
            'default'           => array (
                'default',
            )
        ] );

        add_settings_section( 'myclub_groups_main', __( 'MyClub Groups Main Settings', 'myclub-groups' ), function(){}, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_sync', __( 'Synchronization information', 'myclub-groups' ), function(){}, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_title_settings', __( 'Title settings', 'myclub-groups' ), function(){}, 'myclub_groups_settings_tab2' );
        add_settings_section( 'myclub_groups_display_settings', __( 'Display settings', 'myclub-groups' ), function(){}, 'myclub_groups_settings_tab3' );
        add_settings_field( 'myclub_groups_api_key', __( 'MyClub API Key', 'myclub-groups' ), [
            $this,
            'renderApiKey'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_api_key' ] );
        add_settings_field( 'myclub_groups_group_slug', __( 'Slug for group pages', 'myclub-groups' ), [
            $this,
            'renderGroupSlug'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_group_slug' ] );
        add_settings_field( 'myclub_groups_group_news_slug', __( 'Slug for group news posts', 'myclub-groups' ), [
            $this,
            'renderGroupNewsSlug'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_group_news_slug' ] );
        add_settings_field( 'myclub_groups_last_news_sync', __( 'News last synchronized', 'myclub-groups' ), [
            $this,
            'renderNewsLastSync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_last_groups_sync', __( 'Groups last synchronized', 'myclub-groups' ), [
            $this,
            'renderGroupsLastSync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_calendar_title', __( 'Title for calendar field', 'myclub-groups' ), [
            $this,
            'renderCalendarTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_calendar_title' ] );
        add_settings_field( 'myclub_groups_coming_games_title', __( 'Title for upcoming games field', 'myclub-groups' ), [
            $this,
            'renderComingGamesTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_coming_games_title' ] );
        add_settings_field( 'myclub_groups_leaders_title', __( 'Title for leaders field', 'myclub-groups' ), [
            $this,
            'renderLeadersTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_leaders_title' ] );
        add_settings_field( 'myclub_groups_members_title', __( 'Title for members field', 'myclub-groups' ), [
            $this,
            'renderMembersTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_members_title' ] );
        add_settings_field( 'myclub_groups_news_title', __( 'Title for news field', 'myclub-groups' ), [
            $this,
            'renderNewsTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_news_title' ] );
        add_settings_field( 'myclub_groups_club_news_title', __( 'Title for club news field', 'myclub-groups' ), [
            $this,
            'renderClubNewsTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_club_news_title' ] );
        add_settings_field( 'myclub_groups_page_template', __( 'Template for group pages', 'myclub-groups' ), [
            $this,
            'renderPageTemplate'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_template' ] );
        add_settings_field( 'myclub_groups_page_title', __( 'Show group title', 'myclub-groups' ), [
            $this,
            'renderPageTitle'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_title' ] );
        add_settings_field( 'myclub_groups_page_picture', __( 'Show group picture', 'myclub-groups' ), [
            $this,
            'renderPagePicture'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_picture' ] );
        add_settings_field( 'myclub_groups_page_menu', __( 'Show groups menu', 'myclub-groups' ), [
            $this,
            'renderPageMenu'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_menu' ] );
        add_settings_field( 'myclub_groups_page_navigation', __( 'Show group page navigation', 'myclub-groups' ), [
            $this,
            'renderPageNavigation'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_navigation' ] );
        add_settings_field( 'myclub_groups_page_calendar', __( 'Show group calendar', 'myclub-groups' ), [
            $this,
            'renderPageCalendar'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_calendar' ] );
        add_settings_field( 'myclub_groups_page_leaders', __( 'Show group members', 'myclub-groups' ), [
            $this,
            'renderPageMembers'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_leaders' ] );
        add_settings_field( 'myclub_groups_page_members', __( 'Show group leaders', 'myclub-groups' ), [
            $this,
            'renderPageLeaders'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_members' ] );
        add_settings_field( 'myclub_groups_page_news', __( 'Show group news', 'myclub-groups' ), [
            $this,
            'renderPageNews'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_news' ] );
        add_settings_field( 'myclub_groups_page_coming_games', __( 'Show group upcoming games', 'myclub-groups' ), [
            $this,
            'renderPageComingGames'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_coming_games' ] );
        add_settings_field( 'myclub_groups_show_items_order', __( 'Shown items order', 'myclub-groups' ), [
            $this,
            'renderShowItemsOrder'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_show_items_order' ] );
    }

    public function addPluginSettingsLink( $links )
    {
        $settings_link = '<a href="options-general.php?page=myclub-groups-settings">' . __( 'Settings' ) . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Loads the admin settings page for the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function adminSettings()
    {
        return require_once( "$this->pluginPath/templates/admin/admin_settings.php" );
    }

    /**
     * Enqueues the JavaScript file for the admin page of MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueueAdminJS()
    {
        $current_page = get_current_screen();

        if ( $current_page->base === 'settings_page_myclub-groups-settings' ) {
            wp_register_script( 'myclub_groups_settings_js', $this->pluginUrl . 'assets/javascript/myclub_groups_settings.js' );
            wp_register_style( 'myclub_groups_settings_css', $this->pluginUrl . 'assets/css/myclub_groups_settings.css' );
            wp_set_script_translations( 'myclub_groups_settings_js', 'myclub-groups', $this->pluginPath . 'languages' );

            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'myclub_groups_settings_js' );
            wp_enqueue_style( 'myclub_groups_settings_css' );
        }
    }

    /**
     * Reloads the groups for the MyClub Groups plugin.
     *
     * Note: Only users with 'manage_options' capability can execute this method.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajaxReloadGroups()
    {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'Permission denied', 'myclub-groups' )
            ] );
        }

        $service = new GroupService();
        $service->reloadGroups();

        wp_send_json_success( [
            'message' => __( 'Successfully queued groups reloading', 'myclub-groups' )
        ] );
    }

    /**
     * Reloads the news for the MyClub Groups plugin.
     *
     * Note: Only users with 'manage_options' capability can execute this method.
     *
     * @return void
     * @since 1.0.0
     */
    public function ajaxReloadNews()
    {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'Permission denied', 'myclub-groups' )
            ] );
        }

        $service = new NewsService();
        $service->reloadNews();

        wp_send_json_success( [
            'message' => __( 'Successfully queued news reloading', 'myclub-groups' )
        ] );
    }

    /**
     * Renders the input field for the API key in the plugin settings page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderApiKey( array $args )
    {
        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_api_key" value="' . esc_attr( get_option( 'myclub_groups_api_key' ) ) . '" />';
    }

    /**
     * Renders the dashboard widget.
     *
     * This method counts the number of group posts in WordPress and the number
     * of news items imported to WordPress from the MyClub member system. It
     * then outputs the counts in a formatted HTML string.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderDashboardWidget()
    {
        // Count the number of group posts in WordPress
        $args = array(
            'post_type'=> 'myclub-groups',
            'post_status'=> 'publish',
            'posts_per_page' => -1
        );
        $query = new WP_Query($args);
        $groups_count = $query->found_posts;

        // Count the number of news items imported to WordPress
        $args = array(
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'myclubNewsId',
                    'compare' => 'EXISTS'
                ),
            ),
        );
        $query = new WP_Query($args);
        $news_count = $query->found_posts;

        echo sprintf( __( 'There is currently <strong>%1$s groups</strong> loaded from the MyClub member system.', 'myclub-groups' ), $groups_count );
        echo '<br>';
        echo sprintf( __( 'There is currently <strong>%1$s group news items</strong> loaded from the MyClub member system.', 'myclub-groups' ), $news_count );
    }

    /**
     * Renders the input field for the group slug in the plugin settings page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderGroupSlug( array $args )
    {
        $groupSlug = get_option( 'myclub_groups_group_slug' );
        if ( empty( $groupSlug ) ) {
            $groupSlug = 'groups';
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_group_slug" value="' . esc_attr( $groupSlug ) . '" />';
    }

    /**
     * Renders the input field for the group news slug setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderGroupNewsSlug( array $args )
    {
        $groupNewsSlug = get_option( 'myclub_groups_group_news_slug' );
        if ( empty( $groupNewsSlug ) ) {
            $groupNewsSlug = 'group-news';
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_group_news_slug" value="' . esc_attr( $groupNewsSlug ) . '" />';
    }

    /**
     * Renders the input field for the group calendar title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderCalendarTitle( array $args )
    {
        $calendarTitle = get_option( 'myclub_groups_calendar_title' );
        if ( empty( $calendarTitle ) ) {
            $calendarTitle = __( 'Calendar', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_calendar_title" value="' . esc_attr( $calendarTitle ) . '" />';
    }

    /**
     * Renders the input field for the group upcoming games title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderComingGamesTitle( array $args )
    {
        $comingGamesTitle = get_option( 'myclub_groups_coming_games_title' );
        if ( empty( $comingGamesTitle ) ) {
            $comingGamesTitle = __( 'Upcoming games', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_coming_games_title" value="' . esc_attr( $comingGamesTitle ) . '" />';
    }

    /**
     * Renders the input field for the group leaders title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderLeadersTitle( array $args )
    {
        $leadersTitle = get_option( 'myclub_groups_leaders_title' );
        if ( empty( $leadersTitle ) ) {
            $leadersTitle = __( 'Leaders', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_leaders_title" value="' . esc_attr( $leadersTitle ) . '" />';
    }

    /**
     * Renders the input field for the group members title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderMembersTitle( array $args )
    {
        $membersTitle = get_option( 'myclub_groups_members_title' );
        if ( empty( $membersTitle ) ) {
            $membersTitle = __( 'Members', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_members_title" value="' . esc_attr( $membersTitle ) . '" />';
    }

    /**
     * Renders the input field for the group news title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderNewsTitle( array $args )
    {
        $newsTitle = get_option( 'myclub_groups_news_title' );
        if ( empty( $newsTitle ) ) {
            $newsTitle = __( 'News', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_news_title" value="' . esc_attr( $newsTitle ) . '" />';
    }

    /**
     * Renders the input field for the club news title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderClubNewsTitle( array $args )
    {
        $newsTitle = get_option( 'myclub_groups_club_news_title' );
        if ( empty( $newsTitle ) ) {
            $newsTitle = __( 'News', 'myclub-groups' );
        }

        echo '<input type="text" id="' . $args[ 'label_for' ] . '" name="myclub_groups_club_news_title" value="' . esc_attr( $newsTitle ) . '" />';
    }

    /**
     * Renders the last news sync field in the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderNewsLastSync()
    {
        $this->renderDateTimeField( 'myclub_groups_last_news_sync' );
    }

    /**
     * Renders the page template select field.
     *
     * @param array $args The arguments for rendering the page template select field.
     *                    - 'label_for': The ID attribute for the select field.
     *
     * @since 1.0.0
     */
    public function renderPageTemplate( array $args )
    {
        $templates = wp_get_theme()->get_page_templates();
        $options = array ();
        foreach ( $templates as $template => $name ) {
            $options[ $template ] = $name;
        }
        echo '<select id="' . $args[ 'label_for' ] . '" name="myclub_groups_page_template">';
        foreach ( $options as $value => $name ) {
            $selected = selected( get_option( 'myclub_groups_page_template' ), $value, false );
            echo '<option value="' . $value . '"' . $selected . '>' . $name . '</option>';
        }
        echo '</select>';
    }

    /**
     * Renders the page leaders option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageLeaders( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_leaders', 'leaders', __( 'Leaders', 'myclub-groups' ) );
    }

    /**
     * Renders the page calendar option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageCalendar( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_calendar', 'calendar', __( 'Calendar', 'myclub-groups' ) );
    }

    /**
     * Renders the page members option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageMembers( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_members', 'members', __( 'Members', 'myclub-groups' ) );
    }

    /**
     * Renders the page menu option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageMenu( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_menu', 'menu', __( 'Menu', 'myclub-groups' ) );
    }

    /**
     * Renders the page navigation option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageNavigation( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_navigation', 'navigation', __( 'Navigation', 'myclub-groups' ) );
    }

    /**
     * Renders the page news option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageNews( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_news', 'news', __( 'News', 'myclub-groups' ) );
    }

    /**
     * Renders the page title option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageTitle( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_title' );
    }

    /**
     * Renders the page picture option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPagePicture( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_picture' );
    }

    /**
     * Renders the page coming games option for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderPageComingGames( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_page_coming_games', 'coming-games', __( 'Upcoming games', 'myclub-groups' ) );
    }

    /**
     * Renders the show items order for the MyClub Groups plugin.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderShowItemsOrder( array $args )
    {
        $items = get_option( 'myclub_groups_show_items_order', array () );
        if ( in_array( 'default', $items ) ) {
            $items = array (
                'menu',
                'navigation',
                'calendar',
                'members',
                'leaders',
                'news',
                'coming-games'
            );
        }

        $sortNames = [
            'calendar'     => __( 'Calendar', 'myclub-groups' ),
            'coming-games' => __( 'Upcoming games', 'myclub-groups' ),
            'leaders'      => __( 'Leaders', 'myclub-groups' ),
            'members'      => __( 'Members', 'myclub-groups' ),
            'menu'         => __( 'Menu', 'myclub-groups' ),
            'navigation'   => __( 'Navigation', 'myclub-groups' ),
            'news'         => __( 'News', 'myclub-groups' )
        ];

        echo '<ul id="' . $args[ 'label_for' ] . '">';

        foreach ( $items as $item ) {
            echo '<li><input type="hidden" value="' . $item . '" name="myclub_groups_show_items_order[]" />' . $sortNames[ $item ] . '</li>';
        }

        echo '</ul>';
    }

    /**
     * Renders the last groups sync field in the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderGroupsLastSync()
    {
        $this->renderDateTimeField( 'myclub_groups_last_groups_sync' );
    }

    /**
     * Sanitizes the provided API key and verifies its validity.
     *
     * @param string $input The API key to be sanitized.
     *
     * @return string The sanitized API key, or the previously stored API key if the new key is invalid.
     * @since 1.0.0
     */
    public function sanitizeApiKey( string $input ): string
    {
        $input = sanitize_text_field( $input );

        $api = new RestApi( $input );
        if ( $api->loadMenuItems()->status !== 200 ) {
            add_settings_error( 'myclub_groups_api_key', 'invalid-api-key', __( 'Invalid API key entered', 'myclub-groups' ) );
            return get_option( 'myclub_groups_api_key' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the given group slug.
     *
     * @param string $input The group slug to sanitize.
     *
     * @return string The sanitized group slug.
     * @since 1.0.0
     */
    public function sanitizeGroupSlug( string $input ): string
    {
        $input = sanitize_title( $input );

        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_group_slug', 'empty-slug', __( 'You have to enter a valid slug', 'myclub-groups' ) );
            return get_option( 'myclub_groups_group_slug' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the group news slug.
     *
     * @param string $input The input slug to be sanitized.
     *
     * @return string The sanitized version of the input slug.
     * @since 1.0.0
     */
    public function sanitizeGroupNewsSlug( string $input ): string
    {
        $input = sanitize_title( $input );

        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_group_news_slug', 'empty-slug', __( 'You have to enter a valid slug', 'myclub-groups' ) );
            return get_option( 'myclub_groups_group_news_slug' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the calendar field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeCalendarTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_calendar_title', 'empty-value', __( 'You have to enter title for the calendar field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_calendar_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the upcoming games field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeComingGamesTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_coming_games_title', 'empty-value', __( 'You have to enter title for the upcoming games field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_coming_games_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the leaders field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeLeadersTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_leaders_title', 'empty-value', __( 'You have to enter title for the members field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_leaders_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the members field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeMembersTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_members_title', 'empty-value', __( 'You have to enter title for the leaders field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_members_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the news field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeNewsTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_news_title', 'empty-value', __( 'You must enter a title for the news field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_news_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input title for the club news field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.0.0
     */
    public function sanitizeClubNewsTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_club_news_title', 'empty-value', __( 'You must enter a title for the club news field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_club_news_title' );
        } else {
            return $input;
        }
    }

    /**
     * Sanitizes the input sorted fields for displaying the fields on the groups page.
     *
     * @param array $items The items to be sanitized
     *
     * @return array The sanitized array.
     * @since 1.0.0
     */
    public function sanitizeShowItemsOrder( array $items ): array
    {
        $allowedItems = [
            'calendar',
            'coming-games',
            'leaders',
            'members',
            'menu',
            'navigation',
            'news'
        ];

        return array_intersect( $items, $allowedItems );
    }

    /**
     * Sanitizes the input for a page template option.
     *
     * @param string $input The input to be sanitized.
     *
     * @return string The sanitized input. If the input does not exist in the list of available templates, an error message is added and the input is set to 'page.php' (default template).
     * @since 1.0.0
     */
    public function sanitizePageTemplate( string $input ): string
    {
        $templates = get_page_templates();

        // Check if the selected template exists in the list of available templates
        if ( !in_array( $input, $templates ) ) {
            // If the template doesn't exist, output an error message and revert the setting to default
            add_settings_error( 'myclub_groups_page_template', esc_attr( 'settings_updated' ), __( 'The selected template was not found.', 'myclub-groups' ) );

            $input = 'page.php';
        }

        return $input;
    }

    /**
     * Sanitizes the input for a checkbox option.
     *
     * @param mixed $input The input to be sanitized.
     *
     * @return string The sanitized input. Returns '1' if the input is equal to '1', otherwise returns '0'.
     * @since 1.0.0
     */
    public function sanitizeCheckbox( $input ): string
    {
        return $input === '1' ?: '0';
    }

    /**
     * Sets up the dashboard widget for MyClub Groups.
     *
     * This method adds a dashboard widget to the WordPress admin dashboard for MyClub Groups.
     *
     * @return void
     * @since 1.0.0
     */
    public function setupDashboardWidget()
    {
        wp_add_dashboard_widget(
            'myclub_groups_dashboard_widget',
            __( 'MyClub Groups', 'myclub-groups' ),
            [ $this, 'renderDashboardWidget' ]
        );
    }

    /**
     * Callback for API key update.
     *
     * This method reloads the groups if the API key has changed.
     *
     * @return void
     * @since 1.0.0
     */
    public function updateApiKey()
    {
        $service = new GroupService();
        $service->reloadGroups();
    }

    /**
     * Updates the shown order of all 'myclub-groups' posts with the new value.
     *
     * @param array $oldValue The old value of the shown order.
     * @param array $newValue The new value of the shown order.
     *
     * @return void
     * @since 1.0.0
     */
    public function updateShownOrder( array $oldValue, array $newValue )
    {
        $pageTemplate = get_option( 'myclub_groups_page_template' );

        $args = array (
            'post_type'      => 'myclub-groups',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();
                $postId = $query->post->ID;
                GroupService::updateGroupPageContents( $postId, $newValue, $pageTemplate );
            }
        }
    }

    /**
     * Updates the page template value for all posts of the "myclub-groups" post type.
     *
     * @param mixed $oldValue The old value of the page template.
     * @param mixed $newValue The new value of the page template.
     *
     * @return void
     * @since 1.0.0
     */
    public function updatePageTemplate( $oldValue, $newValue )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            if ( wp_is_block_theme() ) {
                while ( $query->have_posts() ) {
                    $query->next_post();
                    $postId = $query->post->ID;

                    update_post_meta( $postId, '_wp_page_template', $newValue );
                }
            } else {
                while ( $query->have_posts() ) {
                    $query->next_post();
                    $postId = $query->post->ID;

                    // Update the post into the database
                    wp_update_post( array (
                        'ID'            => $postId,
                        'page_template' => $newValue,
                    ) );
                }
            }
        }
    }

    /**
     * Renders a datetime field.
     *
     * @param string $fieldName The name of the option field.
     *
     * @return void
     * @since 1.0.0
     */
    private function renderDateTimeField( string $fieldName )
    {
        $lastSync = esc_attr( get_option( $fieldName ) );

        if ( empty( $lastSync ) ) {
            $output = __( 'Not synchronized yet', 'myclub-groups' );
        } else {
            $output = Utils::formatDateTime( $lastSync );
        }

        echo '<div>' . $output . '</div>';
    }

    /**
     * Renders a checkbox element with the given arguments and field name.
     *
     * @param array $args An array of arguments for the checkbox element.
     * @param string $fieldName The name of the field associated with the checkbox.
     * @param string $name The name of the field in the sorting box.
     * @param string $displayName The display name of the field in the sorting box.
     *
     * @return void
     * @since 1.0.0
     */
    private function renderCheckbox( array $args, string $fieldName, string $name = null, string $displayName = null )
    {
        $checked = get_option( $fieldName ) === '1' ? ' checked="checked"' : '';
        $class = $name ? ' class="sort-item-setter"' : '';

        echo '<input type="checkbox" id="' . $args[ 'label_for' ] . '" data-name="' . $name . '" data-display-name="' . $displayName . '" name="' . $fieldName . '" value="1" ' . $checked . $class . ' />';
    }
}