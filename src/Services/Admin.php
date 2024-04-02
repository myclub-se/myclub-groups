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
            'add_admin_menu'
        ] );
        add_action( 'admin_init', [
            $this,
            'add_myclub_groups_settings'
        ] );
        add_action( 'update_option_myclub_groups_api_key', [
            $this,
            'update_api_key'
        ], 10, 0 );
        add_action( 'update_option_myclub_groups_show_items_order', [
            $this,
            'update_show_order'
        ], 10, 2 );
        add_action( 'update_option_myclub_groups_page_template', [
            $this,
            'update_page_template'
        ], 10, 2 );
        add_action( 'wp_ajax_myclub_reload_groups', [
            $this,
            'ajax_reload_groups'
        ] );
        add_action( 'wp_ajax_myclub_reload_news', [
            $this,
            'ajax_reload_news'
        ] );
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueue_admin_JS'
        ] );
        add_action( 'manage_post_posts_columns', [
            $this,
            'add_group_news_column'
        ] );
        add_action( 'after_switch_theme', [
            $this,
            'update_theme_page_template'
        ] );
        add_action( 'wp_dashboard_setup', [
            $this,
            'setup_dashboard_widget'
        ] );

        add_filter( 'manage_post_posts_custom_column', [
            $this,
            'add_group_news_column_content'
        ], 10, 2 );
        add_filter( "plugin_action_links_" . plugin_basename( $this->plugin_path . '/myclub-groups.php' ), [
            $this,
            'add_plugin_settings_link'
        ] );
    }

    /**
     * Adds the admin menu for the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function add_admin_menu()
    {
        add_options_page(
            __( 'MyClub Groups plugin settings', 'myclub-groups' ),
            __( 'MyClub Groups', 'myclub-groups' ),
            'manage_options',
            'myclub-groups-settings',
            [
                $this,
                'admin_settings'
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
    public function add_group_news_column( array $columns ): array
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
     * @param int $post_id The ID of the post.
     * @return void
     * @since 1.0.0
     */
    public function add_group_news_column_content( string $column_key, int $post_id )
    {
        if ( $column_key === 'group_news' ) {
            $names = [];
            $terms = wp_get_post_terms( $post_id, NewsService::MYCLUB_GROUP_NEWS );
            foreach ( $terms as $term ) {
                $names[] = $term->name;
            }
            echo esc_attr( join( ', ', $names ) );
        }
    }

    /**
     * Registers the MyClub Groups plugin settings page and adds all the settings to the page.
     *
     * @return void
     * @since 1.0.0
     */
    public function add_myclub_groups_settings()
    {
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_api_key', [
            'sanitize_callback' => [
                $this,
                'sanitize_api_key'
            ],
            'default'           => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_group_slug', [
            'sanitize_callback' => [
                $this,
                'sanitize_group_slug'
            ],
            'default'           => 'groups'
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_group_news_slug', [
            'sanitize_callback' => [
                $this,
                'sanitize_group_news_slug'
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
                'sanitize_calendar_title'
            ],
            'default'           => __( 'Calendar', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_coming_games_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_coming_games_title'
            ],
            'default'           => __( 'Upcoming games', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_leaders_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_leaders_title'
            ],
            'default'           => __( 'Leaders', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_members_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_members_title'
            ],
            'default'           => __( 'Members', 'myclub-groups' ),
            'show_in_rest'      => true
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_news_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_news_title'
            ],
            'default'           => __( 'News', 'myclub-groups' )
        ] );
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_club_news_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_club_news_title'
            ],
            'default'           => __( 'News', 'myclub-groups' )
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_template', [
            'sanitize_callback' => [
                $this,
                'sanitize_page_template'
            ],
            'default'           => ''
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_calendar', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_navigation', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_members', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_leaders', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_menu', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_news', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_title', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_picture', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_page_coming_games', [
            'sanitize_callback' => [
                $this,
                'sanitize_checkbox'
            ],
            'default'           => '1'
        ] );
        register_setting( 'myclub_groups_settings_tab3', 'myclub_groups_show_items_order', [
            'sanitize_callback' => [
                $this,
                'sanitize_show_items_order'
            ],
            'default'           => array (
                'default',
            )
        ] );

        add_settings_section( 'myclub_groups_main', __( 'MyClub Groups Main Settings', 'myclub-groups' ), function () {
        }, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_sync', __( 'Synchronization information', 'myclub-groups' ), function () {
        }, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_title_settings', __( 'Title settings', 'myclub-groups' ), function () {
        }, 'myclub_groups_settings_tab2' );
        add_settings_section( 'myclub_groups_display_settings', __( 'Display settings', 'myclub-groups' ), function () {
        }, 'myclub_groups_settings_tab3' );
        add_settings_field( 'myclub_groups_api_key', __( 'MyClub API Key', 'myclub-groups' ), [
            $this,
            'render_api_key'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_api_key' ] );
        add_settings_field( 'myclub_groups_group_slug', __( 'Slug for group pages', 'myclub-groups' ), [
            $this,
            'render_group_slug'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_group_slug' ] );
        add_settings_field( 'myclub_groups_group_news_slug', __( 'Slug for group news posts', 'myclub-groups' ), [
            $this,
            'render_group_news_slug'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_group_news_slug' ] );
        add_settings_field( 'myclub_groups_last_news_sync', __( 'News last synchronized', 'myclub-groups' ), [
            $this,
            'render_news_last_sync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_last_groups_sync', __( 'Groups last synchronized', 'myclub-groups' ), [
            $this,
            'render_groups_last_sync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_calendar_title', __( 'Title for calendar field', 'myclub-groups' ), [
            $this,
            'render_calendar_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_calendar_title' ] );
        add_settings_field( 'myclub_groups_coming_games_title', __( 'Title for upcoming games field', 'myclub-groups' ), [
            $this,
            'render_coming_games_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_coming_games_title' ] );
        add_settings_field( 'myclub_groups_leaders_title', __( 'Title for leaders field', 'myclub-groups' ), [
            $this,
            'render_leaders_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_leaders_title' ] );
        add_settings_field( 'myclub_groups_members_title', __( 'Title for members field', 'myclub-groups' ), [
            $this,
            'render_members_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_members_title' ] );
        add_settings_field( 'myclub_groups_news_title', __( 'Title for news field', 'myclub-groups' ), [
            $this,
            'render_news_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_news_title' ] );
        add_settings_field( 'myclub_groups_club_news_title', __( 'Title for club news field', 'myclub-groups' ), [
            $this,
            'render_club_news_title'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_club_news_title' ] );
        if ( wp_is_block_theme() ) {
            add_settings_field( 'myclub_groups_page_template', __( 'Template for group pages', 'myclub-groups' ), [
                $this,
                'render_page_template'
            ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_template' ] );
        }
        add_settings_field( 'myclub_groups_page_title', __( 'Show group title', 'myclub-groups' ), [
            $this,
            'render_page_title'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_title' ] );
        add_settings_field( 'myclub_groups_page_picture', __( 'Show group picture', 'myclub-groups' ), [
            $this,
            'render_page_picture'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_picture' ] );
        add_settings_field( 'myclub_groups_page_menu', __( 'Show groups menu', 'myclub-groups' ), [
            $this,
            'render_page_menu'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_menu' ] );
        add_settings_field( 'myclub_groups_page_navigation', __( 'Show group page navigation', 'myclub-groups' ), [
            $this,
            'render_page_navigation'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_navigation' ] );
        add_settings_field( 'myclub_groups_page_calendar', __( 'Show group calendar', 'myclub-groups' ), [
            $this,
            'render_page_calendar'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_calendar' ] );
        add_settings_field( 'myclub_groups_page_leaders', __( 'Show group members', 'myclub-groups' ), [
            $this,
            'render_page_members'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_leaders' ] );
        add_settings_field( 'myclub_groups_page_members', __( 'Show group leaders', 'myclub-groups' ), [
            $this,
            'render_page_leaders'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_members' ] );
        add_settings_field( 'myclub_groups_page_news', __( 'Show group news', 'myclub-groups' ), [
            $this,
            'render_page_news'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_news' ] );
        add_settings_field( 'myclub_groups_page_coming_games', __( 'Show group upcoming games', 'myclub-groups' ), [
            $this,
            'render_page_coming_games'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_coming_games' ] );
        add_settings_field( 'myclub_groups_show_items_order', __( 'Shown items order', 'myclub-groups' ), [
            $this,
            'render_show_items_order'
        ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_show_items_order' ] );
    }

    /**
     * Adds the plugin settings link to the list of plugin action links.
     *
     * This method accepts an array of links and adds a link to the plugin settings page.
     *
     * @param array $links An array of existing plugin action links.
     * @return array An array of modified plugin action links with the added settings link.
     * @since 1.0.0
     */
    public function add_plugin_settings_link( array $links ): array
    {
        $settings_link = '<a href="options-general.php?page=myclub-groups-settings">' . __( 'Settings' ) . '</a>';
        $links[] = $settings_link;
        return $links;
    }

    /**
     * Loads the admin settings page for the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function admin_settings()
    {
        return require_once( "$this->plugin_path/templates/admin/admin_settings.php" );
    }

    /**
     * Enqueues the JavaScript file for the admin page of MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_admin_JS()
    {
        $current_page = get_current_screen();

        if ( $current_page->base === 'settings_page_myclub-groups-settings' ) {
            wp_register_script( 'myclub_groups_settings_js', $this->plugin_url . 'resources/javascript/myclub_groups_settings.js', [], MYCLUB_GROUPS_PLUGIN_VERSION, true );
            wp_register_style( 'myclub_groups_settings_css', $this->plugin_url . 'resources/css/myclub_groups_settings.css', [], MYCLUB_GROUPS_PLUGIN_VERSION );
            wp_set_script_translations( 'myclub_groups_settings_js', 'myclub-groups', $this->plugin_path . 'languages' );

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
    public function ajax_reload_groups()
    {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'Permission denied', 'myclub-groups' )
            ] );
        }

        $service = new GroupService();
        $service->reload_groups();

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
    public function ajax_reload_news()
    {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'Permission denied', 'myclub-groups' )
            ] );
        }

        $service = new NewsService();
        $service->reload_news();

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
    public function render_api_key( array $args )
    {
        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_api_key" value="' . esc_attr( get_option( 'myclub_groups_api_key' ) ) . '" />';
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
    public function render_dashboard_widget()
    {
        // Count the number of group posts in WordPress
        $args = array (
            'post_type'      => 'myclub-groups',
            'post_status'    => 'publish',
            'posts_per_page' => -1
        );
        $query = new WP_Query( $args );
        $groups_count = $query->found_posts;

        // Count the number of news items imported to WordPress
        $args = array (
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array (
                array (
                    'key'     => 'myclub_news_id',
                    'compare' => 'EXISTS'
                ),
            ),
        );
        $query = new WP_Query( $args );
        $news_count = $query->found_posts;

        /* translators: 1: number of groups */
        echo sprintf( __( 'There is currently <strong>%1$s groups</strong> loaded from the MyClub member system.', 'myclub-groups' ), esc_attr( $groups_count ) );
        echo '<br>';
        /* translators: 1: number of news items */
        echo sprintf( __( 'There is currently <strong>%1$s group news items</strong> loaded from the MyClub member system.', 'myclub-groups' ), esc_attr( $news_count ) );
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
    public function render_group_slug( array $args )
    {
        $group_slug = get_option( 'myclub_groups_group_slug' );
        if ( empty( $group_slug ) ) {
            $group_slug = 'groups';
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_group_slug" value="' . esc_attr( $group_slug ) . '" />';
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
    public function render_group_news_slug( array $args )
    {
        $group_news_slug = get_option( 'myclub_groups_group_news_slug' );
        if ( empty( $group_news_slug ) ) {
            $group_news_slug = 'group-news';
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_group_news_slug" value="' . esc_attr( $group_news_slug ) . '" />';
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
    public function render_calendar_title( array $args )
    {
        $calendar_title = get_option( 'myclub_groups_calendar_title' );
        if ( empty( $calendar_title ) ) {
            $calendar_title = __( 'Calendar', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_calendar_title" value="' . esc_attr( $calendar_title ) . '" />';
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
    public function render_club_news_title( array $args )
    {
        $club_news_title = get_option( 'myclub_groups_club_news_title' );
        if ( empty( $club_news_title ) ) {
            $club_news_title = __( 'News', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_club_news_title" value="' . esc_attr( $club_news_title ) . '" />';
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
    public function render_coming_games_title( array $args )
    {
        $coming_games_title = get_option( 'myclub_groups_coming_games_title' );
        if ( empty( $coming_games_title ) ) {
            $coming_games_title = __( 'Upcoming games', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_coming_games_title" value="' . esc_attr( $coming_games_title ) . '" />';
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
    public function render_leaders_title( array $args )
    {
        $leaders_title = get_option( 'myclub_groups_leaders_title' );
        if ( empty( $leaders_title ) ) {
            $leaders_title = __( 'Leaders', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_leaders_title" value="' . esc_attr( $leaders_title ) . '" />';
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
    public function render_members_title( array $args )
    {
        $members_title = get_option( 'myclub_groups_members_title' );
        if ( empty( $members_title ) ) {
            $members_title = __( 'Members', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_members_title" value="' . esc_attr( $members_title ) . '" />';
    }

    /**
     * Renders the last news sync field in the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function render_news_last_sync()
    {
        $this->render_date_time_field( 'myclub_groups_last_news_sync' );
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
    public function render_news_title( array $args )
    {
        $news_title = get_option( 'myclub_groups_news_title' );
        if ( empty( $news_title ) ) {
            $news_title = __( 'News', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_news_title" value="' . esc_attr( $news_title ) . '" />';
    }

    /**
     * Renders the page template select field.
     *
     * @param array $args The arguments for rendering the page template select field.
     *                    - 'label_for': The ID attribute for the select field.
     *
     * @since 1.0.0
     */
    public function render_page_template( array $args )
    {
        $templates = wp_get_theme()->get_page_templates();
        $options = array ();
        foreach ( $templates as $template => $name ) {
            $options[ $template ] = $name;
        }
        echo '<select id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_page_template">';
        foreach ( $options as $value => $name ) {
            $selected = selected( get_option( 'myclub_groups_page_template' ), $value, false );
            echo '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_attr( $name ) . '</option>';
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
    public function render_page_leaders( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_leaders', 'leaders', __( 'Leaders', 'myclub-groups' ) );
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
    public function render_page_calendar( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_calendar', 'calendar', __( 'Calendar', 'myclub-groups' ) );
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
    public function render_page_members( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_members', 'members', __( 'Members', 'myclub-groups' ) );
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
    public function render_page_menu( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_menu', 'menu', __( 'Menu', 'myclub-groups' ) );
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
    public function render_page_navigation( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_navigation', 'navigation', __( 'Navigation', 'myclub-groups' ) );
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
    public function render_page_news( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_news', 'news', __( 'News', 'myclub-groups' ) );
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
    public function render_page_title( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_title' );
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
    public function render_page_picture( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_picture' );
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
    public function render_page_coming_games( array $args )
    {
        $this->render_checkbox( $args, 'myclub_groups_page_coming_games', 'coming-games', __( 'Upcoming games', 'myclub-groups' ) );
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
    public function render_show_items_order( array $args )
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

        $sort_names = [
            'calendar'     => __( 'Calendar', 'myclub-groups' ),
            'coming-games' => __( 'Upcoming games', 'myclub-groups' ),
            'leaders'      => __( 'Leaders', 'myclub-groups' ),
            'members'      => __( 'Members', 'myclub-groups' ),
            'menu'         => __( 'Menu', 'myclub-groups' ),
            'navigation'   => __( 'Navigation', 'myclub-groups' ),
            'news'         => __( 'News', 'myclub-groups' )
        ];

        echo '<ul id="' . esc_attr( $args[ 'label_for' ] ) . '">';

        foreach ( $items as $item ) {
            echo '<li><input type="hidden" value="' . esc_attr( $item ) . '" name="myclub_groups_show_items_order[]" />' . esc_attr( $sort_names[ $item ] ) . '</li>';
        }

        echo '</ul>';
    }

    /**
     * Renders the last groups sync field in the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function render_groups_last_sync()
    {
        $this->render_date_time_field( 'myclub_groups_last_groups_sync' );
    }

    /**
     * Sanitizes the provided API key and verifies its validity.
     *
     * @param string $input The API key to be sanitized.
     *
     * @return string The sanitized API key, or the previously stored API key if the new key is invalid.
     * @since 1.0.0
     */
    public function sanitize_api_key( string $input ): string
    {
        $input = sanitize_text_field( $input );

        $api = new RestApi( $input );
        if ( $api->load_menu_items()->status !== 200 ) {
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
    public function sanitize_group_slug( string $input ): string
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
    public function sanitize_group_news_slug( string $input ): string
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
    public function sanitize_calendar_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_calendar_title', 'empty-value', __( 'You have to enter title for the calendar field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_calendar_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_coming_games_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_coming_games_title', 'empty-value', __( 'You have to enter title for the upcoming games field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_coming_games_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_leaders_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_leaders_title', 'empty-value', __( 'You have to enter title for the members field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_leaders_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_members_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_members_title', 'empty-value', __( 'You have to enter title for the leaders field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_members_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_news_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_news_title', 'empty-value', __( 'You must enter a title for the news field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_news_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_club_news_title( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_club_news_title', 'empty-value', __( 'You must enter a title for the club news field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_club_news_title' );
        } else {
            return sanitize_text_field( $input );
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
    public function sanitize_show_items_order( array $items ): array
    {
        $allowed_items = [
            'calendar',
            'coming-games',
            'leaders',
            'members',
            'menu',
            'navigation',
            'news'
        ];

        return array_intersect( Utils::sanitize_array( $items ), $allowed_items );
    }

    /**
     * Sanitizes the input for a page template option.
     *
     * @param string $input The input to be sanitized.
     *
     * @return string The sanitized input. If the input does not exist in the list of available templates, an error message is added and the input is set to 'page.php' (default template).
     * @since 1.0.0
     */
    public function sanitize_page_template( string $input ): string
    {
        $templates = get_page_templates();

        $input = sanitize_text_field( $input );

        // Check if the selected template exists in the list of available templates
        if ( !in_array( $input, $templates ) ) {
            // If the template doesn't exist, output an error message and revert the setting to default
            add_settings_error( 'myclub_groups_page_template', esc_attr( 'settings_updated' ), __( 'The selected template was not found.', 'myclub-groups' ) );
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
    public function sanitize_checkbox( $input ): string
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
    public function setup_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'myclub_groups_dashboard_widget',
            __( 'MyClub Groups', 'myclub-groups' ),
            [
                $this,
                'render_dashboard_widget'
            ]
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
    public function update_api_key()
    {
        $service = new GroupService();
        $service->reload_groups();
    }

    /**
     * Updates the page template value for all posts of the "myclub-groups" post type.
     *
     * @param mixed $old_value The old value of the page template.
     * @param mixed $new_value The new value of the page template.
     *
     * @return void
     * @since 1.0.0
     */
    public function update_page_template( $old_value, $new_value )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();
                update_post_meta( $query->post->ID, '_wp_page_template', $new_value );
            }
        }
    }

    /**
     * Updates the shown order of all 'myclub-groups' posts with the new value.
     *
     * @param array $old_value The old value of the shown order.
     * @param array $new_value The new value of the shown order.
     *
     * @return void
     * @since 1.0.0
     */
    public function update_show_order( array $old_value, array $new_value )
    {
        $args = array (
            'post_type'      => 'myclub-groups',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();
                GroupService::update_group_page_contents( $query->post->ID, Utils::sanitize_array( $new_value ) );
            }
        }
    }

    /**
     * Updates the page template for MyClub Groups.
     *
     * This method updates the page template for MyClub Groups based on the current WordPress block theme.
     * If there are available page templates, it will update the template and set the 'myclub_groups_page_template' option.
     * If the block theme is not enabled or there are no available templates, it will delete the page template meta for 'myclub-groups' post type.
     *
     * @return void
     * @since 1.0.0
     */
    public function update_theme_page_template()
    {
        if ( wp_is_block_theme() ) {
            $templates = wp_get_theme()->get_page_templates();

            if ( count( $templates ) ) {
                $template = key( $templates );

                $this->update_page_template( null, $template );
                get_option( 'myclub_groups_page_template' ) === false ? add_option( 'myclub_groups_page_template', $template, '', 'no' ) : update_option( 'myclub_groups_page_template', $template, 'no' );
                return;
            }
        }

        global $wpdb;

        $wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND p.post_type = %s", '_wp_page_template', 'myclub-groups' ) );
    }

    /**
     * Renders a datetime field.
     *
     * @param string $field_name The name of the option field.
     *
     * @return void
     * @since 1.0.0
     */
    private function render_date_time_field( string $field_name )
    {
        $last_sync = esc_attr( get_option( $field_name ) );

        $output = empty( $last_sync ) ? __( 'Not synchronized yet', 'myclub-groups' ) : Utils::format_date_time( $last_sync );

        echo '<div>' . esc_attr( $output ) . '</div>';
    }

    /**
     * Renders a checkbox element with the given arguments and field name.
     *
     * @param array $args An array of arguments for the checkbox element.
     * @param string $field_name The name of the field associated with the checkbox.
     * @param string|null $name The name of the field in the sorting box.
     * @param string|null $display_name The display name of the field in the sorting box.
     *
     * @return void
     * @since 1.0.0
     */
    private function render_checkbox( array $args, string $field_name, string $name = null, string $display_name = null )
    {
        $checked = get_option( $field_name ) === '1' ? ' checked="checked"' : '';
        $class = $name ? ' class="sort-item-setter"' : '';

        echo '<input type="checkbox" id="' . esc_attr( $args[ 'label_for' ] ) . '" data-name="' . esc_attr( $name ) . '" data-display-name="' . esc_attr( $display_name ) . '" name="' . esc_attr( $field_name ) . '" value="1" ' . $checked . $class . ' />';
    }
}