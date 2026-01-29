<?php

namespace MyClub\MyClubGroups\Services;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Registers the shortcode for the plugin.
 *
 * This method hooks into the 'init' action and registers the shortcode for the plugin.
 * It calls the 'registerShortcodes' method to handle the shortcode registration.
 *
 * @since 1.0.0
 */
class ShortCodes extends Base
{
    const SHORT_CODES = [
        'myclub-groups-calendar',
        'myclub-groups-club-calendar',
        'myclub-groups-club-news',
        'myclub-groups-coming-games',
        'myclub-groups-leaders',
        'myclub-groups-members',
        'myclub-groups-menu',
        'myclub-groups-navigation',
        'myclub-groups-news',
        'myclub-groups-title'
    ];

    /**
     * Registers the shortcode for the plugin.
     *
     * This method hooks into the 'init' action and registers the shortcode for the plugin.
     * It calls the 'registerShortcodes' method to handle the shortcode registration.
     *
     * @since 1.0.0
     */
    public function register()
    {
        add_action( 'init', [
            $this,
            'registerShortCodes'
        ] );
    }

    /**
     * Registers the shortcodes for MyClub groups.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function registerShortCodes()
    {
        $available_blocks = Blocks::BLOCKS;

        add_shortcode( 'myclub-groups-calendar', [
            $this,
            'renderMyclubGroupsCalendar'
        ] );

        add_shortcode( 'myclub-groups-club-calendar', [
            $this,
            'renderMyclubGroupsClubCalendar'
        ] );

        add_shortcode( 'myclub-groups-club-news', [
            $this,
            'renderMyclubGroupsClubNews'
        ] );

        add_shortcode( 'myclub-groups-coming-games', [
            $this,
            'renderMyclubGroupsComingGames'
        ] );

        add_shortcode( 'myclub-groups-leaders', [
            $this,
            'renderMyclubGroupsLeaders'
        ] );

        add_shortcode( 'myclub-groups-members', [
            $this,
            'renderMyclubGroupsMembers'
        ] );

        add_shortcode( 'myclub-groups-menu', [
            $this,
            'renderMyclubGroupsMenu'
        ] );

        add_shortcode( 'myclub-groups-navigation', [
            $this,
            'renderMyclubGroupsNavigation'
        ] );

        add_shortcode( 'myclub-groups-news', [
            $this,
            'renderMyclubGroupsNews'
        ] );

        add_shortcode( 'myclub-groups-title', [
            $this,
            'renderMyclubGroupsTitle'
        ] );

        foreach ( $available_blocks as $block ) {
            if ( file_exists( $this->plugin_path . 'blocks/build/' . $block . '/view.js' ) ) {
                wp_register_script( 'myclub-groups-' . $block . '-js', $this->plugin_url . 'blocks/build/' . $block . '/view.js', [], MYCLUB_GROUPS_PLUGIN_VERSION, true );
            }
            if ( file_exists( $this->plugin_path . 'blocks/build/' . $block . '/style-index.css' ) ) {
                wp_register_style( 'myclub-groups-' . $block . '-css', $this->plugin_url . 'blocks/build/' . $block . '/style-index.css', [], MYCLUB_GROUPS_PLUGIN_VERSION );
            }
        }
    }

    /**
     * Renders the MyClub groups calendar.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered calendar HTML.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsCalendar( array $attrs = [], string $content = null ): string
    {
        $service = new Blocks();

        wp_enqueue_script( 'myclub-groups-calendar-js' );
        wp_enqueue_style( 'myclub-groups-calendar-css' );

        return $service->renderCalendar( $this->getShortcodeAttrs( $attrs, 'myclub-groups-calendar' ), $content );
    }

    /**
     * Renders the MyClub groups calendar.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered calendar HTML.
     *
     * @since 1.3.0
     */
    public function renderMyclubGroupsClubCalendar( array $attrs = [], string $content = null ): string
    {
        $service = new Blocks();

        wp_enqueue_script( 'myclub-groups-club-calendar-js' );
        wp_enqueue_style( 'myclub-groups-club-calendar-css' );

        return $service->renderClubCalendar( $this->getShortcodeAttrs( $attrs, 'myclub-groups-club-calendar' ), $content );
    }

    /**
     * Renders the My Club Groups Club News block.
     *
     * @param mixed $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsClubNews( $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-club-news', 'club-news', __( 'The MyClub club news block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the upcoming games for MyClub groups.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsComingGames( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-coming-games', 'coming-games', __( 'The MyClub coming games block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups Leaders block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsLeaders( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-leaders', 'leaders', __( 'The MyClub leaders block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups Members block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsMembers( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-members', 'members', __( 'The MyClub members block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups Menu block.
     *
     * @param mixed $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsMenu( $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-menu', 'menu', __( 'The MyClub menu block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups Navigation block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsNavigation( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-navigation', 'navigation', __( 'The MyClub navigation block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups News block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsNews( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-news', 'news', __( 'The MyClub news block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Renders the My Club Groups Title block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function renderMyclubGroupsTitle( array $attrs = [], string $content = null ): string
    {
        return $this->renderShortcode( 'myclub-groups-title', 'title', __( 'The MyClub title block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
    }

    /**
     * Retrieves the shortcode attributes for a given shortcode.
     *
     * @param array $attrs An array of attributes for the shortcode.
     * @param string $shortCode The shortcode to retrieve attributes for.
     *
     * @return array The array of shortcode attributes.
     *
     * @since 1.0.0
     */
    private function getShortcodeAttrs( array $attrs, string $shortCode ): array
    {
        return shortcode_atts( [
            'group_id' => '',
            'post_id'  => '',
        ], $attrs, $shortCode );
    }

    /**
     * Renders a shortcode block.
     *
     * @param string $short_code The shortcode identifier.
     * @param string $block_path The path of the block to render.
     * @param string $error_string The error message to display if the block file is not found.
     * @param mixed $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string|null $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block or the error message.
     * @since 1.0.0
     */
    private function renderShortcode( string $short_code, string $block_path, string $error_string, $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( is_array( $attrs ) ? $attrs : [], $short_code );
        $file_path = $this->plugin_path . 'blocks/build/' . $block_path . '/render.php';

        wp_enqueue_script( 'myclub-groups-' . $block_path . '-js' );
        wp_enqueue_style( 'myclub-groups-' . $block_path . '-css' );

        if ( file_exists( $file_path ) ) {
            ob_start();
            require( $file_path );
            return ob_get_clean();
        } else {
            return $error_string;
        }
    }
}