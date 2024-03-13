<?php

namespace MyClub\MyClubGroups\Services;

use WP_Screen;

class Taxonomy extends Base
{
    /**
     * Register the taxonomy. Registers all hooks and other items required for displaying the MyClub group custom posts.
     *
     * @return void
     * @since 1.0.0
     */
    public function register()
    {
        // Add required custom posts
        add_action( 'init', [
            $this,
            'initCPT'
        ], 5 );

        // Add required javascript and css for group pages in admin
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueueScripts'
        ] );

        add_filter( 'hidden_meta_boxes', [
            $this,
            'showGroupsInScreenOptions'
        ], 10, 2);
    }

    /**
     * Initialize CPT for myclub groups.
     *
     * This function registers the CPT myclub-groups at the slug that was set in the settings page. It also adds a taxonomy for groups news.
     *
     * @return void
     * @since 1.0.0
     */
    public function initCPT()
    {
        $slug = get_option( 'myclub_groups_group_slug' );
        if ( empty( $slug ) ) {
            $slug = 'groups';
        }

        $group_news_slug = get_option( 'myclub_groups_group_news_slug' );
        if ( empty( $group_news_slug ) ) {
            $group_news_slug = 'group-news';
        }

        // Add custom post type for groups in the plugin
        register_post_type(
            'myclub-groups',
            [
                'public'               => true,
                'labels'               => [
                    'name'          => __( 'Groups', 'myclub-groups' ),
                    'singular_name' => __( 'Group', 'myclub-groups' )
                ],
                'capabilities'         => [
                    'create_posts'           => 'do_not_allow',
                    'delete_posts'           => 'do_not_allow',
                    'delete_published_posts' => 'do_not_allow',
                ],
                'map_meta_cap'         => true,
                'has_archive'          => false,
                'menu_icon'            => 'dashicons-groups',
                'rewrite'              => [
                    'slug'       => $slug,
                    'with_front' => false,
                    'feeds'      => false,
                    'pages'      => true
                ],
                'register_meta_box_cb' => [
                    $this,
                    'registerMetaBox'
                ],
                'show_in_rest'         => true,
                'show_in_nav_menus'    => true,
                'supports'             => [
                    'custom-fields',
                    'title',
                    'editor',
                    'thumbnail',
                    'page-attributes'
                ]
            ]
        );

        register_post_meta( 'myclub-groups', 'activities', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( 'myclub-groups', 'members', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( 'myclub-groups', 'myclubGroupId', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( 'myclub-groups', 'contactName', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( 'myclub-groups', 'email', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( 'myclub-groups', 'phone', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        // Add custom taxonomy for sorting news connected to the groups
        register_taxonomy( 'myclub-group-news', 'post', [
            'capabilities' => [
                'edit_terms'   => 'do_not_allow',
                'delete_terms' => 'do_not_allow'
            ],
            'label'        => __( 'Group news', 'myclub-groups' ),
            'rewrite'      => array (
                'slug'       => $group_news_slug,
                // This controls the base slug that will display before each term
                'with_front' => false,
                // Don't display the category base before the slug
            ),
            'show_in_rest' => true,
        ] );

        flush_rewrite_rules();
    }

    /**
     * Enqueue the required scripts and css for displaying the group pages custom posts.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueueScripts()
    {
        $current_page = get_current_screen();

        if ( $current_page->post_type === 'myclub-groups' ) {
            // Register admin scripts and styles
            wp_register_style( 'myclub_groups_tabs_css', $this->pluginUrl . 'assets/css/myclub_groups.css' );
            wp_register_script( 'myclub_groups_tabs_ui', $this->pluginUrl . 'assets/javascript/myclub_groups_tabs.js', [ 'jquery' ] );

            wp_enqueue_style( 'myclub_groups_tabs_css' );
            wp_enqueue_script( 'jquery-ui-tabs' );
            wp_enqueue_script( 'myclub_groups_tabs_ui' );
        }
    }

    /**
     * Register the custom meta box for the group pages custom posts.
     *
     * @return void
     * @since 1.0.0
     */
    public function registerMetaBox()
    {
        add_meta_box( 'myclub-groups-meta', __( 'MyClub group information', 'myclub-groups' ), [
            $this,
            'renderMetaBox'
        ], 'myclub-groups', 'normal', 'high' );
    }

    /**
     * Render the meta box for MyClub groups.
     *
     * This function includes and renders the template file for the admin metabox tabs of MyClub groups.
     *
     * @return void
     * @since 1.0.0
     */
    public function renderMetaBox()
    {
        return require_once( "$this->pluginPath/templates/admin/admin_myclub_groups_metabox_tabs.php" );
    }

    /**
     * Show groups in screen options for nav-menus.
     *
     * This function removes the 'add-post-type-myclub-groups' option from the hidden items in the screen options for
     * the nav-menus screen. This allows the 'add-post-type-myclub-groups' option to be displayed in the screen
     * options for nav-menus.
     *
     * @param array $hidden The array of hidden items in the screen options.
     * @param WP_Screen $screen The current screen object.
     * @return array The updated array of hidden items in the screen options.
     * @since 1.0.0
     */
    public function showGroupsInScreenOptions( array $hidden, WP_Screen $screen ): array {
        if ( $screen->id == 'nav-menus' ) {
            $index = array_search( 'add-post-type-myclub-groups', $hidden );

            if ( false !== $index ) {
                unset( $hidden[ $index ] );
            }
        }

        return $hidden;
    }
}