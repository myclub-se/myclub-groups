<?php

namespace MyClub\MyClubGroups\Services;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
                'addAdminMenu'
        ] );
        add_action( 'admin_init', [
                $this,
                'addMyclubGroupsSettings'
        ] );
        add_action( 'update_option_myclub_groups_api_key', [
                $this,
                'updateApiKey'
        ], 10, 0 );
        add_action( 'update_option_myclub_groups_show_items_order', [
                $this,
                'updateShowOrder'
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
        add_action( 'wp_ajax_myclub_sync_club_calendar', [
                $this,
                'syncClubCalendar'
        ] );
        add_action( 'admin_enqueue_scripts', [
                $this,
                'enqueueAdminJS'
        ] );
        add_action( 'admin_notices', [
                $this,
                'wpCronAdminNotice'
        ] );
        add_action( 'manage_post_posts_columns', [
                $this,
                'addGroupNewsColumn'
        ] );
        add_action( 'after_switch_theme', [
                $this,
                'updateThemePageTemplate'
        ] );
        add_action( 'wp_dashboard_setup', [
                $this,
                'setupDashboardWidget'
        ] );

        add_filter( 'manage_post_posts_custom_column', [
                $this,
                'addGroupNewsColumnContent'
        ], 10, 2 );
        add_filter( "plugin_action_links_" . plugin_basename( $this->plugin_path . '/myclub-groups.php' ), [
                $this,
                'addPluginSettingsLink'
        ] );

        add_action( 'admin_enqueue_scripts', [
                $this,
                'enqueueMediaModalFilter'
        ] );
        add_filter( 'ajax_query_attachments_args', [
                $this,
                'applyImageTypeFilterToMediaModal'
        ] );
        add_filter( 'myclub_common_cron_interval_label', [
                $this,
                'applyCronIntervalLabel'
        ], 10, 2 );

        add_action( 'restrict_manage_posts', [ $this, 'renderMediaLibraryImageTypeFilter' ] );
        add_action( 'pre_get_posts', [ $this, 'applyMediaLibraryImageTypeFilterQuery' ] );
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
     * @param int $post_id The ID of the post.
     * @return void
     * @since 1.0.0
     */
    public function addGroupNewsColumnContent( string $column_key, int $post_id )
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
    public function addMyclubGroupsSettings()
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
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_add_news_categories', [
                'sanitize_callback' => [
                        $this,
                        'sanitizeCheckbox'
                ],
                'default'           => '0'
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_delete_unused_news', [
                'sanitize_callback' => [
                        $this,
                        'sanitizeCheckbox'
                ],
                'default'           => '0'
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_last_news_sync', [
                'default' => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_last_groups_sync', [
                'default' => NULL
        ] );
        register_setting( 'myclub_groups_settings_tab1', 'myclub_groups_last_club_calendar_sync', [
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
        register_setting( 'myclub_groups_settings_tab2', 'myclub_groups_club_calendar_title', [
                'sanitize_callback' => [
                        $this,
                        'sanitizeClubCalendarTitle'
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

        add_settings_section( 'myclub_groups_main', __( 'MyClub Groups Main Settings', 'myclub-groups' ), function () {
            echo '<p>';
            esc_attr_e(
                    'Here are the general settings available from the MyClub Groups plugin. The available Gutenberg blocks and their usage is described under the "Gutenberg blocks" tab. The available shortcodes and their usage are described under the "Shortcodes" tab. Please check the documentation there.',
                    'myclub-groups'
            );
            echo '</p>';
        }, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_sync', __( 'Synchronization information', 'myclub-groups' ), function () {
        }, 'myclub_groups_settings_tab1' );
        add_settings_section( 'myclub_groups_title_settings', __( 'Title settings', 'myclub-groups' ), function () {
            echo '<p>';
            esc_attr_e(
                    'Here you can set the titles for the fields that are displayed on the group pages. The titles are used in the Gutenberg blocks and shortcodes. You cannot leave the title field empty.',
                    'myclub-groups'
            );
            echo '</p>';
        }, 'myclub_groups_settings_tab2' );
        add_settings_section( 'myclub_groups_display_settings', __( 'Display settings', 'myclub-groups' ), function () {
            echo '<p>';
            esc_attr_e(
                    'Here you can set the display options for the group pages. You select which fields should be visible and then in which order. On a Gutenberg theme you can also choose which template should be used for the group pages.',
                    'myclub-groups'
            );
            echo '</p>';
        }, 'myclub_groups_settings_tab3' );
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
        add_settings_field( 'myclub_groups_add_news_categories', __( 'Add news categories for group news', 'myclub-groups' ), [
                $this,
                'renderAddNewsCategories'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_add_news_categories' ] );
        add_settings_field( 'myclub_groups_delete_unused_news', __( 'Delete posts for news deleted from MyClub', 'myclub-groups' ), [
                $this,
                'renderDeleteUnusedNews'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_main', [ 'label_for' => 'myclub_groups_delete_unused_news' ] );
        add_settings_field( 'myclub_groups_last_news_sync', __( 'News last synchronized', 'myclub-groups' ), [
                $this,
                'renderNewsLastSync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_last_groups_sync', __( 'Groups last synchronized', 'myclub-groups' ), [
                $this,
                'renderGroupsLastSync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_last_club_calendar_sync', __( 'Club calendar last synchronized', 'myclub-groups' ), [
                $this,
                'renderClubCalendarLastSync'
        ], 'myclub_groups_settings_tab1', 'myclub_groups_sync' );
        add_settings_field( 'myclub_groups_calendar_title', __( 'Title for calendar field', 'myclub-groups' ), [
                $this,
                'renderCalendarTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_calendar_title' ] );
        add_settings_field( 'myclub_groups_club_calendar_title', __( 'Title for club calendar field', 'myclub-groups' ), [
                $this,
                'renderClubCalendarTitle'
        ], 'myclub_groups_settings_tab2', 'myclub_groups_title_settings', [ 'label_for' => 'myclub_groups_club_calendar_title' ] );
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
        if ( wp_is_block_theme() ) {
            add_settings_field( 'myclub_groups_page_template', __( 'Template for group pages', 'myclub-groups' ), [
                    $this,
                    'renderPageTemplate'
            ], 'myclub_groups_settings_tab3', 'myclub_groups_display_settings', [ 'label_for' => 'myclub_groups_page_template' ] );
        }
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

    /**
     * Adds the plugin settings link to the list of plugin action links.
     *
     * This method accepts an array of links and adds a link to the plugin settings page.
     *
     * @param array $links An array of existing plugin action links.
     * @return array An array of modified plugin action links with the added settings link.
     * @since 1.0.0
     */
    public function addPluginSettingsLink( array $links ): array
    {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=myclub-groups-settings' ) ) . '">' . __( 'Settings', 'myclub-groups' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Add translations for the cron interval.
     *
     * @param string $label The existing label for the interval.
     * @param int $interval The interval value.
     * @return string The translated label.
     *
     * @since 2.2.0
     */
    public function applyCronIntervalLabel( string $label, int $interval ): string
    {
        // Example: French translation
        if ( $interval === 1 ) {
            return __( 'Every minute', 'myclub-groups' );
        }
        /* translators: Display string for the cron interval */
        return sprintf( __( 'Every %d minutes', 'myclub-groups' ), $interval );
    }

    /**
     * Enqueue inline JS that adds a filter to the media modal and defaults to "Standard images"
     * The labels are translated via wp.i18n and the plugin's textdomain.
     *
     * @param string $hook
     * @return void
     *
     * @since 2.1.0
     */
    public function enqueueMediaModalFilter( string $hook )
    {
        // Load only where the media modal can appear
        $allowed = [
                'post.php',
                'post-new.php',
                'upload.php',
                'site-editor.php',
                'widgets.php'
        ];
        if ( !in_array( $hook, $allowed, true ) ) {
            return;
        }

        wp_enqueue_media();

        // Register a small script handle so we can attach translations to it
        $handle = 'myclub-media-filters';
        wp_register_script( $handle, false, [
                'media-views',
                'wp-i18n'
        ], defined( 'MYCLUB_GROUPS_PLUGIN_VERSION' ) ? MYCLUB_GROUPS_PLUGIN_VERSION : false, true );
        wp_enqueue_script( $handle );

        // Load translations from your languages directory
        wp_set_script_translations( $handle, 'myclub-groups', $this->plugin_path . 'languages' );

        $script = "(function(wp){
            if (!wp || !wp.media || !wp.media.view || !wp.i18n || !wp.i18n.__) return;

            var __ = wp.i18n.__;

            var MyClubFilter = wp.media.view.AttachmentFilters.extend({
                id: 'myclub-image-type',
                createFilters: function() {
                    var filters = {};
                    // Default: Standard images (no taxonomy term)
                    filters.standard = {
                        text: __('Standard images (no MyClub)', 'myclub-groups'),
                        props: { myclub_image_type: 'none' },
                        priority: 10
                    };
                    // Build dynamic options from taxonomy terms so names/values stay in sync
                    try {
                        var terms = [];
                        if (window.myclubImageTypeTerms && Array.isArray(window.myclubImageTypeTerms)) {
                            terms = window.myclubImageTypeTerms;
                        } else if (wp && wp.data && wp.data.select) {
                            // Optional: could fetch via REST if preloaded
                        }
                        terms.forEach(function(t, idx){
                            // t.slug, t.name expected
                            filters['term_' + t.slug] = {
                                text: t.name,
                                props: { myclub_image_type: t.slug },
                                priority: 20 + idx
                            };
                        });
                    } catch(e){}
                    // Show everything (including library images)
                    filters.all = {
                        text: __('All images', 'myclub-groups'),
                        props: { myclub_image_type: 'all' },
                        priority: 100
                    };
                    this.filters = filters;
                }
            });

            var OrigBrowser = wp.media.view.AttachmentsBrowser;
            wp.media.view.AttachmentsBrowser = OrigBrowser.extend({
                createToolbar: function(){
                    OrigBrowser.prototype.createToolbar.apply(this, arguments);

                    var model = this.collection && this.collection.props ? this.collection.props : null;
                    if (!model) return;

                    // Default to 'Standard images' if not set
                    if (!model.get('myclub_image_type')) {
                        model.set('myclub_image_type', 'none');
                    }

                    this.toolbar.set('MyClubImageType', new MyClubFilter({
                        controller: this.controller,
                        model: model
                    }));
                }
            });
        })(window.wp);";

        // Localize taxonomy terms (slug/name) for dynamic filter creation
        $taxonomy = ImageService::MYCLUB_IMAGES;
        $terms = get_terms( [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'fields'     => 'all',
        ] );
        $localized = [];
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) {
                $localized[] = [
                        'slug' => $t->slug,
                        'name' => $t->name,
                ];
            }
        }
        wp_localize_script( $handle, 'myclubImageTypeTerms', $localized );

        // Attach our translated inline script to our handle
        wp_add_inline_script( $handle, $script );
    }

    /**
     * Apply the selected library filter to media modal queries.
     * Default is to exclude any attachments tagged with the MyClub image taxonomy (i.e., show only standard images).
     *
     * @param array $query
     * @return array
     *
     * @since 2.1.0
     */
    public function applyImageTypeFilterToMediaModal( array $query ): array
    {
        // Use the taxonomy registered for image libraries
        $taxonomy = ImageService::MYCLUB_IMAGES;

        $value = '';
        if ( isset( $_REQUEST[ 'query' ][ 'myclub_image_type' ] ) ) {
            $value = sanitize_text_field( (string)$_REQUEST[ 'query' ][ 'myclub_image_type' ] );
        }

        if ( $value === 'all' ) {
            return $query;
        }

        // Default to untagged on Media Library grid initial load (query-attachments without our param)
        if ( $value === '' && isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] === 'query-attachments' ) {
            $query[ 'tax_query' ] = [
                    [
                            'taxonomy' => $taxonomy,
                            'operator' => 'NOT EXISTS',
                    ],
            ];
            return $query;
        }

        if ( $value === 'none' || $value === '' ) {
            $query[ 'tax_query' ] = [
                    [
                            'taxonomy' => $taxonomy,
                            'operator' => 'NOT EXISTS',
                    ],
            ];
            return $query;
        }

        $query[ 'tax_query' ] = [
                [
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => [ $value ],
                        'operator' => 'IN',
                ],
        ];

        return $query;
    }

    /**
     * Applies the dropdown selection to the Media Library main query.
     *
     * @param WP_Query $query
     * @return void
     * @since 2.1.0
     */
    public function applyMediaLibraryImageTypeFilterQuery( WP_Query $query ): void
    {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( ! $screen || $screen->id !== 'upload' ) {
            return;
        }

        $taxonomy = ImageService::MYCLUB_IMAGES;
        $paramKey = $taxonomy . '-filter';

        $val = isset( $_GET[ $paramKey ] ) ? sanitize_text_field( (string) $_GET[ $paramKey ] ) : 'none';

        // Normalize base constraints so results aren’t accidentally empty
        $query->set( 'post_type', 'attachment' );
        $query->set( 'post_status', 'inherit' );

        if ( empty( $_GET['post_mime_type'] ) ) {
            $query->set( 'post_mime_type', '' );
        }
        if ( isset( $_GET['m'] ) && ( $_GET['m'] === '0' || $_GET['m'] === '' ) ) {
            $query->set( 'm', '' );
        }
        if ( isset( $_GET['attachment-filter'] ) && $_GET['attachment-filter'] === '' ) {
            $query->set( 'attachment-filter', '' );
        }
        if ( isset( $_GET['s'] ) && $_GET['s'] === '' ) {
            $query->set( 's', '' );
        }

        // Always replace tax_query to avoid sticky merges
        $query->set( 'tax_query', [] );

        if ( $val === 'all' ) {
            // No taxonomy restriction
            return;
        }

        if ( $val === 'none' || $val === '' ) {
            // Only untagged items: exclude all terms via NOT IN
            $term_ids = get_terms( [
                    'taxonomy'   => $taxonomy,
                    'fields'     => 'ids',
                    'hide_empty' => false,
            ] );

            if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
                // No terms exist: everything is "untagged" => no tax_query needed
                return;
            }

            $query->set( 'tax_query', [
                    'relation' => 'AND',
                    [
                            'taxonomy'         => $taxonomy,
                            'field'            => 'term_id',
                            'terms'            => array_map( 'intval', $term_ids ),
                            'operator'         => 'NOT IN',
                            'include_children' => false,
                    ],
            ] );
            return;
        }

        // Specific term
        $query->set( 'tax_query', [
                'relation' => 'AND',
                [
                        'taxonomy'         => $taxonomy,
                        'field'            => 'slug',
                        'terms'            => [ $val ],
                        'operator'         => 'IN',
                        'include_children' => false,
                ],
        ] );
    }

    /**
     * Loads the admin settings page for the MyClub Groups plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function adminSettings()
    {
        return require_once( "$this->plugin_path/templates/admin/admin_settings.php" );
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
     * Synchronizes the club calendar by reloading events from the calendar service.
     *
     * @return void
     * @since 1.3.0
     */
    public function syncClubCalendar()
    {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                    'message' => __( 'Permission denied', 'myclub-groups' )
            ] );
        }

        $service = new CalendarService();
        $service->reloadClubEvents();

        wp_send_json_success( [
                'message' => __( 'Successfully reloaded club calendar', 'myclub-groups' )
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
    public function renderDashboardWidget()
    {
        // Count the number of group posts in WordPress
        $args = array (
                'post_type'      => GroupService::MYCLUB_GROUPS,
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
        $allow_strong = array ( "strong" => array () );

        /* translators: 1: number of groups */
        echo wp_kses( sprintf( __( 'There is currently <strong>%1$s groups</strong> loaded from the MyClub member system.', 'myclub-groups' ), esc_attr( $groups_count ) ), $allow_strong );
        echo '<br>';
        /* translators: 1: number of news items */
        echo wp_kses( sprintf( __( 'There is currently <strong>%1$s group news items</strong> loaded from the MyClub member system.', 'myclub-groups' ), esc_attr( $news_count ) ), $allow_strong );
        if ( !wp_next_scheduled( 'wp_version_check' ) ) {
            echo '<br><br>';
            esc_html_e( 'WP Cron is not running. This is required for running the MyClub groups plugin.', 'myclub-groups' );
        }
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
    public function renderGroupNewsSlug( array $args )
    {
        $group_news_slug = get_option( 'myclub_groups_group_news_slug' );
        if ( empty( $group_news_slug ) ) {
            $group_news_slug = 'group-news';
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_group_news_slug" value="' . esc_attr( $group_news_slug ) . '" />';
    }

    /**
     * Renders a checkbox for adding news categories in group news settings.
     *
     * @param array $args Arguments passed for rendering the checkbox.
     * @return void
     * @since 1.3.1
     */
    public function renderAddNewsCategories( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_add_news_categories', 'news_categories', __( 'Add news categories for group news', 'myclub-groups' ) );
    }

    /**
     * Renders the checkbox option for deleting unused news posts from MyClub.
     *
     * @param array $args Arguments passed for rendering the checkbox.
     * @return void
     * @since 1.3.3
     */
    public function renderDeleteUnusedNews( array $args )
    {
        $this->renderCheckbox( $args, 'myclub_groups_delete_unused_news', 'delete_unused_news', __( 'Delete posts for news deleted from MyClub', 'myclub-groups' ) );
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
        $calendar_title = get_option( 'myclub_groups_calendar_title' );
        if ( empty( $calendar_title ) ) {
            $calendar_title = __( 'Calendar', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_calendar_title" value="' . esc_attr( $calendar_title ) . '" />';
    }

    /**
     * Renders the date and time field for the last sync of the club calendar.
     *
     * @return void
     * @since 1.3.1
     */
    public function renderClubCalendarLastSync()
    {
        $this->renderDateTimeField( 'myclub_groups_last_club_calendar_sync' );
    }

    /**
     * Renders the input field for the clubcalendar title setting in the admin page.
     *
     * @param array $args The arguments for rendering the input field.
     *                    - 'label_for' (string) The ID of the input field.
     *
     * @return void
     * @since 1.3.0
     */
    public function renderClubCalendarTitle( array $args )
    {
        $calendar_title = get_option( 'myclub_groups_club_calendar_title' );
        if ( empty( $calendar_title ) ) {
            $calendar_title = __( 'Calendar', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_club_calendar_title" value="' . esc_attr( $calendar_title ) . '" />';
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
    public function renderComingGamesTitle( array $args )
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
    public function renderLeadersTitle( array $args )
    {
        $leaders_title = get_option( 'myclub_groups_leaders_title' );
        if ( empty( $leaders_title ) ) {
            $leaders_title = __( 'Leaders', 'myclub-groups' );
        }

        echo '<input type="text" id="' . esc_attr( $args[ 'label_for' ] ) . '" name="myclub_groups_leaders_title" value="' . esc_attr( $leaders_title ) . '" />';
    }

    /**
     * Adds a dropdown filter for Image Type on Media Library (upload.php).
     *
     * @return void
     * @since 2.1.0
     */
    public function renderMediaLibraryImageTypeFilter(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( ! $screen || $screen->id !== 'upload' ) {
            return;
        }

        $taxonomy = ImageService::MYCLUB_IMAGES;
        $tax_obj  = get_taxonomy( $taxonomy );
        if ( ! $tax_obj ) {
            return;
        }

        // Use native param name to integrate with screen state
        $paramKey = $taxonomy . '-filter';
        $selected = isset( $_GET[ $paramKey ] ) ? sanitize_text_field( (string) $_GET[ $paramKey ] ) : 'none';

        echo '<label class="screen-reader-text" for="' . esc_attr( $paramKey ) . '">' . esc_html( $tax_obj->labels->menu_name ) . '</label>';
        echo '<select name="' . esc_attr( $paramKey ) . '" id="' . esc_attr( $paramKey ) . '" class="postform">';
        echo '<option value="none"' . selected( $selected, 'none', false ) . '>' . esc_html__( 'Standard images (no MyClub)', 'myclub-groups' ) . '</option>';

        $terms = get_terms( [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
        ] );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr( $term->slug ),
                        selected( $selected, $term->slug, false ),
                        esc_html( $term->name )
                );
            }
        }

        echo '<option value="all"' . selected( $selected, 'all', false ) . '>' . esc_html__( 'All images', 'myclub-groups' ) . '</option>';
        echo '</select>';
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
    public function renderNewsLastSync()
    {
        $this->renderDateTimeField( 'myclub_groups_last_news_sync' );
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
    public function renderPageTemplate( array $args )
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
            return sanitize_text_field( $input );
        }
    }

    /**
     * Sanitizes the input title for the club calendar field.
     *
     * @param string $input The input title to be sanitized.
     *
     * @return string The sanitized title.
     * @since 1.3.0
     */
    public function sanitizeClubCalendarTitle( string $input ): string
    {
        if ( empty ( $input ) ) {
            add_settings_error( 'myclub_groups_club_calendar_title', 'empty-value', __( 'You have to enter title for the club calendar field', 'myclub-groups' ) );
            return get_option( 'myclub_groups_club_calendar_title' );
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
    public function sanitizeComingGamesTitle( string $input ): string
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
    public function sanitizeLeadersTitle( string $input ): string
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
    public function sanitizeMembersTitle( string $input ): string
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
    public function sanitizeNewsTitle( string $input ): string
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
    public function sanitizeClubNewsTitle( string $input ): string
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
    public function sanitizeShowItemsOrder( array $items ): array
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

        return array_intersect( Utils::sanitizeArray( $items ), $allowed_items );
    }

    /**
     * Sanitizes the input for a page template option.
     *
     * @param mixed $input The input to be sanitized.
     *
     * @return string The sanitized input. If the input does not exist in the list of available templates, an error message is shown.
     * @since 1.0.0
     */
    public function sanitizePageTemplate( $input ): string
    {
        if ( wp_is_block_theme() ) {
            $templates = get_page_templates();
            $input = sanitize_text_field( $input );

            // Check if the selected template exists in the list of available templates
            if ( !in_array( $input, $templates ) ) {
                // If the template doesn't exist, output an error message and revert the setting to default
                add_settings_error( 'myclub_groups_page_template', esc_attr( 'settings_updated' ), __( 'The selected template was not found.', 'myclub-groups' ) );
                $input = '';
            }
        }

        return !empty( $input ) ? sanitize_text_field( $input ) : '';
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
                [
                        $this,
                        'renderDashboardWidget'
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
    public function updateApiKey()
    {
        $service = new GroupService();
        $service->reloadGroups();
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
    public function updatePageTemplate( $old_value, $new_value )
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
    public function updateShowOrder( array $old_value, array $new_value )
    {
        $args = array (
                'post_type'      => 'myclub-groups',
                'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->next_post();
                GroupService::updateGroupPageContents( $query->post->ID, Utils::sanitizeArray( $new_value ) );
                Utils::clearCacheForPage( $query->post->ID );
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
    public function updateThemePageTemplate()
    {
        if ( wp_is_block_theme() ) {
            $templates = wp_get_theme()->get_page_templates();

            if ( count( $templates ) ) {
                $template = key( $templates );

                $this->updatePageTemplate( null, $template );
                get_option( 'myclub_groups_page_template' ) === false ? add_option( 'myclub_groups_page_template', $template, '', 'no' ) : update_option( 'myclub_groups_page_template', $template, 'no' );
            }
        } else {
            global $wpdb;

            $wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND p.post_type = %s", '_wp_page_template', 'myclub-groups' ) );
        }
    }

    public function wpCronAdminNotice()
    {
        if ( !wp_next_scheduled( 'wp_version_check' ) ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php esc_html_e( 'WP Cron is not running. This is required for running the MyClub groups plugin.', 'myclub-groups' ); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Renders a datetime field.
     *
     * @param string $field_name The name of the option field.
     *
     * @return void
     * @since 1.0.0
     */
    private function renderDateTimeField( string $field_name )
    {
        $last_sync = esc_attr( get_option( $field_name ) );
        $cron_job_name = '';
        $output = '';

        if ( $field_name === 'myclub_groups_last_news_sync' ) {
            $cron_job_name = 'myclub_groups_refresh_news_task_cron';
            $cron_job_type = __( 'news', 'myclub-groups' );
        }

        if ( $field_name === 'myclub_groups_last_groups_sync' ) {
            $cron_job_name = 'myclub_groups_refresh_groups_task_cron';
            $cron_job_type = __( 'groups', 'myclub-groups' );
        }

        if ( !empty( $cron_job_name ) && isset( $cron_job_type ) ) {
            $next_scheduled = wp_next_scheduled( $cron_job_name );
            if ( $next_scheduled ) {
                /* translators: 1: the type of update cron job that is running */
                $output = sprintf( __( 'The %1$s update task is currently running.', 'myclub-groups' ), esc_attr( $cron_job_type ) );
            }
        }

        if ( empty ( $output ) ) {
            $output = empty( $last_sync ) ? __( 'Not synchronized yet', 'myclub-groups' ) : Utils::formatDateTime( $last_sync );
        }

        echo '<div id="' . $field_name . '">' . esc_attr( $output ) . '</div>';
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
    private function renderCheckbox( array $args, string $field_name, string $name = null, string $display_name = null )
    {
        $checked = get_option( $field_name ) === '1' ? ' checked="checked"' : '';
        $class = $name ? ' class="sort-item-setter"' : '';

        echo '<input type="checkbox" id="' . esc_attr( $args[ 'label_for' ] ) . '" data-name="' . esc_attr( $name ) . '" data-display-name="' . esc_attr( $display_name ) . '" name="' . esc_attr( $field_name ) . '" value="1" ' . $checked . $class . ' />';
    }
}