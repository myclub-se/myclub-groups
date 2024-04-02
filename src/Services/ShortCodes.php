<?php

namespace MyClub\MyClubGroups\Services;

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
            'register_short_codes'
        ] );
    }

    /**
     * Registers the shortcodes for MyClub groups.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register_short_codes()
    {
        $available_blocks = array(
            'calendar',
            'club-news',
            'coming-games',
            'leaders',
            'members',
            'menu',
            'navigation',
            'news',
            'title'
        );

        add_shortcode( 'myclub-groups-calendar', [
            $this,
            'render_myclub_groups_calendar'
        ] );

        add_shortcode( 'myclub-groups-club-news', [
            $this,
            'render_myclub_groups_club_news'
        ] );

        add_shortcode( 'myclub-groups-coming-games', [
            $this,
            'render_myclub_groups_coming_games'
        ] );

        add_shortcode( 'myclub-groups-leaders', [
            $this,
            'render_myclub_groups_leaders'
        ] );

        add_shortcode( 'myclub-groups-members', [
            $this,
            'render_myclub_groups_members'
        ] );

        add_shortcode( 'myclub-groups-menu', [
            $this,
            'render_myclub_groups_menu'
        ] );

        add_shortcode( 'myclub-groups-navigation', [
            $this,
            'render_myclub_groups_navigation'
        ] );

        add_shortcode( 'myclub-groups-news', [
            $this,
            'render_myclub_groups_news'
        ] );

        add_shortcode( 'myclub-groups-title', [
            $this,
            'render_myclub_groups_title'
        ] );

        foreach( $available_blocks as $block ) {
            if ( file_exists ( $this->plugin_path . 'blocks/build/' . $block . '/view.js' ) ) {
                wp_register_script( 'myclub-groups-' . $block . '-js', $this->plugin_url . 'blocks/build/' . $block . '/view.js', [], MYCLUB_GROUPS_PLUGIN_VERSION, true );
            }
            if ( file_exists ( $this->plugin_path . 'blocks/build/' . $block . '/style-index.css' ) ) {
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
    public function render_myclub_groups_calendar( array $attrs = [], string $content = null ): string
    {
        $service = new Blocks();

        wp_enqueue_script( 'myclub-groups-calendar-js' );
        wp_enqueue_style( 'myclub-groups-calendar-css' );

        return $service->render_calendar( $this->get_shortcode_attrs( $attrs, 'myclub-groups-calendar' ), $content );
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
    public function render_myclub_groups_club_news( $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-club-news', 'club-news', __( 'The MyClub club news block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_coming_games( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-coming-games', 'coming-games', __( 'The MyClub coming games block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_leaders( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-leaders', 'leaders', __( 'The MyClub leaders block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_members( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-members', 'members', __( 'The MyClub members block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_menu( $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-menu', 'menu', __( 'The MyClub menu block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_navigation( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-navigation', 'navigation', __( 'The MyClub navigation block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_news( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-news', 'news', __( 'The MyClub news block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    public function render_myclub_groups_title( array $attrs = [], string $content = null ): string
    {
        return $this->render_shortcode( 'myclub-groups-title', 'title', __( 'The MyClub title block couldn\'t be found', 'myclub-groups' ), $attrs, $content );
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
    private function get_shortcode_attrs( array $attrs, string $shortCode ): array
    {
        return shortcode_atts([
            'post_id' => ''
        ], $attrs, $shortCode);
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
    private function render_shortcode( string $short_code, string $block_path, string $error_string, $attrs = [], string $content = null): string
    {
        $attributes = $this->get_shortcode_attrs( is_array( $attrs ) ? $attrs : [], $short_code );
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