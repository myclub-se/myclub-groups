<?php

namespace MyClub\MyClubGroups\Services;

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
            'registerShortcodes'
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
        add_shortcode( 'myclub-groups-calendar', [
            $this,
            'renderMyClubGroupsCalendar'
        ] );

        add_shortcode( 'myclub-groups-club-news', [
            $this,
            'renderMyClubGroupsClubNews'
        ] );

        add_shortcode( 'myclub-groups-coming-games', [
            $this,
            'renderMyClubGroupsComingGames'
        ] );

        add_shortcode( 'myclub-groups-leaders', [
            $this,
            'renderMyClubGroupsLeaders'
        ] );

        add_shortcode( 'myclub-groups-members', [
            $this,
            'renderMyClubGroupsMembers'
        ] );

        add_shortcode( 'myclub-groups-navigation', [
            $this,
            'renderMyClubGroupsNavigation'
        ] );

        add_shortcode( 'myclub-groups-news', [
            $this,
            'renderMyClubGroupsNews'
        ] );

        add_shortcode( 'myclub-groups-title', [
            $this,
            'renderMyClubGroupsTitle'
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
    public function renderMyClubGroupsCalendar( array $attrs = [], string $content = null ): string
    {
        $service = new Blocks();
        return $service->renderCalendar( $this->getShortcodeAttrs( $attrs, 'myclub-groups-calendar' ), $content );
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
    public function renderMyClubGroupsClubNews( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-club-news' );
        $filePath = $this->pluginPath . 'blocks/build/club-news/render.php';

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
    public function renderMyClubGroupsComingGames( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-coming-games' );
        $filePath = $this->pluginPath . 'blocks/build/coming-games/render.php';

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
    public function renderMyClubGroupsLeaders( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-leaders' );
        $filePath = $this->pluginPath . 'blocks/build/leaders/render.php';

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
    public function renderMyClubGroupsMembers( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-members' );
        $filePath = $this->pluginPath . 'blocks/build/members/render.php';

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
    public function renderMyClubGroupsNavigation( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-navigation' );
        $filePath = $this->pluginPath . 'blocks/build/navigation/render.php';

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
    public function renderMyClubGroupsNews( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-news' );
        $filePath = $this->pluginPath . 'blocks/build/news/render.php';

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
    public function renderMyClubGroupsTitle( array $attrs = [], string $content = null ): string
    {
        $attributes = $this->getShortcodeAttrs( $attrs, 'myclub-groups-title' );
        $filePath = $this->pluginPath . 'blocks/build/title/render.php';

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
    private function getShortcodeAttrs( array $attrs, string $shortCode ): array
    {
        return shortcode_atts([
            'postId' => ''
        ], $attrs, $shortCode);
    }
}