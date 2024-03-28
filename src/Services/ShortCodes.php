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
    }

    /**
     * Renders the MyClub groups calendar.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered calendar HTML.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_calendar( array $attrs = [], string $content = null ): string
    {
        $service = new Blocks();
        return $service->render_calendar( $this->get_shortcode_attrs( $attrs, 'myclub-groups-calendar' ), $content );
    }

    /**
     * Renders the My Club Groups Club News block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_club_news( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-club-news' );
        $filePath = $this->plugin_path . 'blocks/build/club-news/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub club news block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the upcoming games for MyClub groups.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_coming_games( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-coming-games' );
        $filePath = $this->plugin_path . 'blocks/build/coming-games/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub coming games block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the My Club Groups Leaders block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_leaders( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-leaders' );
        $filePath = $this->plugin_path . 'blocks/build/leaders/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub leaders block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the My Club Groups Members block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_members( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-members' );
        $filePath = $this->plugin_path . 'blocks/build/members/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub members block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the My Club Groups Navigation block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_navigation( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-navigation' );
        $filePath = $this->plugin_path . 'blocks/build/navigation/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub navigation block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the My Club Groups News block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_news( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-news' );
        $filePath = $this->plugin_path . 'blocks/build/news/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub news block couldn\'t be found', 'myclub-groups' );
        }
    }

    /**
     * Renders the My Club Groups Title block.
     *
     * @param array $attrs Optional. An array of attributes for the block. Default is an empty array.
     * @param string $content Optional. The block content. Default is null.
     *
     * @return string The rendered HTML output of the block.
     *
     * @since 1.0.0
     */
    public function render_myclub_groups_title( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->get_shortcode_attrs( $attrs, 'myclub-groups-title' );
        $filePath = $this->plugin_path . 'blocks/build/title/render.php';

        if ( file_exists( $filePath ) ) {
            ob_start();
            require( $filePath );
            return ob_get_clean();
        } else {
            return __( 'The MyClub title block couldn\'t be found', 'myclub-groups' );
        }
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
            'postId' => ''
        ], $attrs, $shortCode);
    }
}