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

    private $handles = [];

    /**
     * Enqueues scripts and sets script translations for registered blocks.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function enqueueScripts()
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
        // Enqueue js scripts for translations
        add_action( 'admin_enqueue_scripts', [
            $this,
            'enqueueScripts'
        ] );
    }


    /**
     * Registers all blocks for the plugin.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function registerBlocks()
    {
        foreach ( Blocks::BLOCKS as $block ) {
            $this->registerBlock( $block );
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
    private function registerBlock( string $block )
    {
        if ( $block !== 'calendar' ) {
            $blockType = register_block_type( $this->plugin_path . 'blocks/build/' . $block );
        } else {
            $blockType = register_block_type( $this->plugin_path . 'blocks/build/' . $block, [
                'render_callback' => [
                    $this,
                    'renderCalendar'
                ]
            ] );
        }

        if ( !$blockType ) {
            error_log( "Unable to register block $block" );
        } else {
            array_push( $this->handles, ...$blockType->view_script_handles, ...$blockType->editor_script_handles );
        }
    }
}