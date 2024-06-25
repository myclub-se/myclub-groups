<?php

namespace MyClub\MyClubGroups\Services;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Blocks
 *
 * This class extends the Base class and is used to register custom MyClub blocks and add a custom category to the block's chooser.
 */
class Blocks extends Base
{
    const BLOCKS = [
        'calendar',
        'club-news',
        'coming-games',
        'leaders',
        'members',
        'menu',
        'navigation',
        'news',
        'title'
    ];

    private array $block_args = [];
    private array $handles = [];

    /**
     * Enqueues scripts and sets script translations for registered blocks.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        foreach ( $this->handles as $handle ) {
            wp_set_script_translations( $handle, 'myclub-groups', $this->plugin_path . 'languages' );
        }
    }

    /**
     * Renders the calendar block.
     *
     * @param array $attributes The attributes for the calendar block.
     * @param string $content The content within the calendar block.
     *
     * @return string The rendered HTML content of the calendar block.
     * @since 1.0.0
     */
    public function render_calendar( array $attributes, string $content = '' ): string
    {
        wp_enqueue_script( 'fullcalendar-js' );

        ob_start();
        require( $this->plugin_path . 'blocks/build/calendar/render.php' );
        return ob_get_clean();
    }

    /**
     * Registers custom MyClub blocks and adds a custom category to the block's chooser.
     *
     * This method is responsible for registering custom MyClub blocks and adding a custom category
     * to the blocks chooser in WordPress. It hooks into the 'init' action to register the blocks and
     * adds a filter to the 'block_categories_all' hook to add the custom category.
     *
     * @return void
     * @since 1.0.0
     *
     */
    public function register()
    {
        // Register custom MyClub blocks
        add_action( 'init', [
            $this,
            'register_blocks'
        ] );
        // Enqueue js scripts for translations
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueue_scripts'
        ] );

        // Add custom category to blocks chooser
        add_filter( 'block_categories_all', [
            $this,
            'register_myclub_category'
        ] );
    }


    /**
     * Registers all blocks for the plugin.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register_blocks()
    {
        $this->block_args = [
            'calendar' => [
                'description' => __( 'Display calendar for a selected group', 'myclub-groups' ),
                'render_callback' => [
                    $this,
                    'render_calendar'
                ],
                'title' => __( 'MyClub Group Calendar', 'myclub-groups' )
            ],
            'club-news' => [
                'description' => __( 'Display news for the entire Club', 'myclub-groups' ),
                'title' => __( 'MyClub Club News', 'myclub-groups')
            ],
            'coming-games' => [
                'description' => __( 'Display upcoming games for a selected group', 'myclub-groups' ),
                'title' => __( 'MyClub Group Upcoming games', 'myclub-groups')
            ],
            'leaders' => [
                'description' => __( 'Display leaders for a selected group', 'myclub-groups' ),
                'title' => __( 'MyClub Group Leaders', 'myclub-groups')
            ],
            'members' => [
                'description' => __( 'Display members for a selected group', 'myclub-groups' ),
                'title' => __( 'MyClub Group Members', 'myclub-groups')
            ],
            'menu' => [
                'description' => __( 'Display the MyClub Group menu items', 'myclub-groups' ),
                'title' => __( 'MyClub Groups Menu', 'myclub-groups')
            ],
            'navigation' => [
                'description' => __( 'Display the MyClub group page navigation', 'myclub-groups' ),
                'title' => __( 'MyClub Group Navigation', 'myclub-groups')
            ],
            'news' => [
                'description' => __( 'Display news for a selected group', 'myclub-groups' ),
                'title' => __( 'MyClub Group News', 'myclub-groups')
            ],
            'title' => [
                'description' => __( 'Display title for a selected group', 'myclub-groups' ),
                'title' => __( 'MyClub Group Title', 'myclub-groups')
            ]
        ];

        foreach ( Blocks::BLOCKS as $block ) {
            $this->register_block( $block );
        }

        wp_register_script( 'fullcalendar-js', $this->plugin_url . 'resources/javascript/fullcalendar.6.1.11.min.js', [], '6.1.11', true );
    }

    /**
     * Registers a custom block category for the plugin.
     *
     * @param array $categories The existing block categories list.
     * @return array The updated block categories list.
     * @since 1.0.0
     */
    public function register_myclub_category( array $categories ): array
    {
        $categories[] = array (
            'slug'  => 'myclub',
            'title' => 'MyClub'
        );

        return $categories;
    }

    /**
     * Registers a single block for the plugin.
     *
     * @param string $block The name of the block to register.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function register_block( string $block )
    {
        $block_type = register_block_type( $this->plugin_path . 'blocks/build/' . $block, $this->block_args[ $block ] );

        if ( !$block_type ) {
            error_log( "Unable to register block $block" );
        } else {
            array_push( $this->handles, ...$block_type->view_script_handles, ...$block_type->editor_script_handles );
        }
    }
}