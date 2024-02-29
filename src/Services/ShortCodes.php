<?php

namespace MyClub\MyClubGroups\Services;

class ShortCodes extends Base
{
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
        return $service->renderCalendar( $attrs, $content );
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/coming-games/render.php' );
        return ob_get_clean();
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/leaders/render.php' );
        return ob_get_clean();
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/members/render.php' );
        return ob_get_clean();
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/navigation/render.php' );
        return ob_get_clean();
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/news/render.php' );
        return ob_get_clean();
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
        $attributes = $attrs;

        ob_start();
        require( $this->plugin_path . 'blocks/build/title/render.php' );
        return ob_get_clean();
    }
}