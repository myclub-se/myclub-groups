<?php

namespace MyClub\MyClubGroups\Services;


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

    /**
     * Renders the calendar block.
     *
     * @param array $attributes The attributes for the calendar block.
     * @param string $content The content within the calendar block.
     *
     * @return string The rendered HTML content of the calendar block.
     * @since 1.0.0
     */
    public function renderCalendar( array $attributes, string $content = '' ): string
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
            'registerBlocks'
        ] );
        // Add custom category to blocks chooser
        add_filter( 'block_categories_all', [
            $this,
            'registerMyClubCategory'
        ] );
    }


    /**
     * Registers all blocks for the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function registerBlocks()
    {
        foreach ( Blocks::BLOCKS as $block ) {
            if ( $block !== 'calendar' ) {
                if ( !register_block_type( $this->plugin_path . 'blocks/build/' . $block ) ) {
                    error_log( "Unable to register block $block" );
                }
            } else {
                if ( !register_block_type( $this->plugin_path . 'blocks/build/' . $block, [
                    'render_callback' => [ $this, 'renderCalendar' ]
                ] ) ) {
                    error_log( "Unable to register block $block" );
                }
            }
        }

        wp_register_script( 'fullcalendar-js', $this->plugin_url . 'assets/javascript/fullcalendar.6.1.11.min.js' );
    }

    /**
     * Registers a custom block category for the plugin.
     *
     * @param array $categories The existing block categories list.
     * @return array The updated block categories list.
     * @since 1.0.0
     */
    public function registerMyClubCategory( array $categories ): array
    {
        $categories[] = array (
            'slug' => 'myclub',
            'title' => 'MyClub'
        );

        return $categories;
    }
}