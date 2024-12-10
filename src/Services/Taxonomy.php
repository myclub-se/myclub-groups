<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use WP_Screen;

/**
 * Class Taxonomy
 *
 * Register and manage taxonomy for MyClub group custom posts.
 *
 * @since 1.0.0
 */
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
            'init_CPT'
        ], 5 );

        // Add required javascript and css for group pages in admin
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueue_scripts'
        ] );

        add_action( 'template_redirect', [
            $this,
            'check_group_in_menus'
        ] );

        add_filter( 'body_class', [
            $this,
            'add_body_class'
        ] );

        add_filter( 'hidden_meta_boxes', [
            $this,
            'show_groups_in_screen_options'
        ], 10, 2);

        add_filter( 'single_template', [
            $this,
            'show_single_group'
        ], 20 );
    }

    /**
     * Initialize CPT for myclub groups.
     *
     * This function registers the CPT myclub-groups at the slug that was set in the settings page. It also adds a taxonomy for groups news.
     *
     * @return void
     * @since 1.0.0
     */
    public function init_CPT()
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
            GroupService::MYCLUB_GROUPS,
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
                    'register_meta_box'
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

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_activities', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_members', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_id', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_contact_name', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_email', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_info_text', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        register_post_meta( GroupService::MYCLUB_GROUPS, 'myclub_groups_phone', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string'
        ] );

        // Add custom taxonomy for sorting news connected to the groups
        register_taxonomy( NewsService::MYCLUB_GROUP_NEWS, 'post', [
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
     * Adds the body class for MyClub Groups posts.
     *
     * @param array $classes The array of body classes.
     * @return array The updated array of body classes.
     * @since 1.0.0
     */
    public function add_body_class( array $classes ): array
    {
        global $post;

        if (isset($post) && GroupService::MYCLUB_GROUPS == $post->post_type) {
            $classes[] = $post->post_name;
        }

        return $classes;
    }

    /**
     * Checks if the current MyClub Group post is linked in any menu items.
     * If not linked, it triggers a 404 redirect.
     *
     * @return void
     * @since 1.0.5
     */
    public function check_group_in_menus()
    {
        if ( is_singular( GroupService::MYCLUB_GROUPS ) ) {
            global $post;

            // Directly query for menu items linked to the current post
            $args = [
                'post_type'   => 'nav_menu_item',
                'meta_key'    => '_menu_item_object_id',
                'meta_value'  => $post->ID,
                'numberposts' => 1
                // Only need to find one to verify presence
            ];

            $menu_items = get_posts( $args );

            if ( empty( $menu_items ) ) {
                // Redirect to 404 if not found in any menu
                global $wp_query;
                status_header( 404 );
                $wp_query->set_404();
                return;
            }
        }
    }

    /**
     * Enqueue the required scripts and css for displaying the group pages custom posts.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        $current_page = get_current_screen();

        if ( $current_page->post_type === GroupService::MYCLUB_GROUPS ) {
            // Register admin scripts and styles
            wp_register_style( 'myclub_groups_tabs_css', $this->plugin_url . 'resources/css/myclub_groups.css', [], MYCLUB_GROUPS_PLUGIN_VERSION );
            wp_register_script( 'myclub_groups_tabs_ui', $this->plugin_url . 'resources/javascript/myclub_groups_tabs.js', [ 'jquery' ], MYCLUB_GROUPS_PLUGIN_VERSION, true );

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
    public function register_meta_box()
    {
        add_meta_box( 'myclub-groups-meta', __( 'MyClub group information', 'myclub-groups' ), [
            $this,
            'render_meta_box'
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
    public function render_meta_box()
    {
        return require_once( "$this->plugin_path/templates/admin/admin_myclub_groups_metabox_tabs.php" );
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
    public function show_groups_in_screen_options( array $hidden, WP_Screen $screen ): array {
        if ( $screen->id == 'nav-menus' ) {
            $index = array_search( 'add-post-type-myclub-groups', $hidden );

            if ( false !== $index ) {
                unset( $hidden[ $index ] );
            }
        }

        return $hidden;
    }

    /**
     * Displays the single group template file.
     *
     * @param mixed $single The current single template file.
     * @return string The single group template file path or the current single template file if the condition is not met.
     */
    public function show_single_group( $single ): string
    {
        if ( !wp_is_block_theme() ) {
            $templateName = 'single-myclub-group.php';

            if ( is_singular( GroupService::MYCLUB_GROUPS ) ) {
                if ( $template = locate_template( $templateName ) ) {
                    return $template;
                } else {
                    return $this->plugin_path . 'templates/' . $templateName;
                }
            }
        }

        return $single;
    }
}