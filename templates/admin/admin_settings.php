<?php
    $active_tab = $_GET[ 'tab' ] ?? 'tab1';
?>

<div class="wrap">
    <h1><?php esc_attr_e( 'MyClub Groups settings', 'myclub-groups' ) ?></h1>
    <div class="nav-tab-wrapper">
        <a href="?page=myclub-groups-settings&tab=tab1" class="nav-tab<?php echo $active_tab === 'tab1' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'General settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab2" class="nav-tab<?php echo $active_tab === 'tab2' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Title settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab3" class="nav-tab<?php echo $active_tab === 'tab3' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Display settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab4" class="nav-tab<?php echo $active_tab === 'tab4' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Gutenberg blocks', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab5" class="nav-tab<?php echo $active_tab === 'tab5' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Shortcodes', 'myclub-groups' ) ?></a>
    </div>

    <form method="post" action="options.php" id="myclub-settings-form">
        <?php
        if( $active_tab === 'tab1' ) {
            settings_fields( 'myclub_groups_settings_tab1' );
            do_settings_sections( 'myclub_groups_settings_tab1' );
        } else if( $active_tab === 'tab2' ) {
            settings_fields( 'myclub_groups_settings_tab2' );
            do_settings_sections( 'myclub_groups_settings_tab2' );
        } else if( $active_tab === 'tab3' ) {
            settings_fields( 'myclub_groups_settings_tab3' );
            do_settings_sections( 'myclub_groups_settings_tab3' );
        } else if( $active_tab === 'tab4' ) {
            ?> <h2><?php esc_attr_e( 'Gutenberg blocks', 'myclub-groups') ?></h2>
            <div><?php esc_attr_e( 'Here are the Gutenberg blocks available from the MyClub groups plugin', 'myclub-groups' )?></div>
            <ul>
                <li><strong><?php esc_attr_e( 'Calendar', 'myclub-groups' ) ?></strong> - <?php _e( 'The calendar block will display a group calendar. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the calendar from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Club news', 'myclub-groups' ) ?></strong> - <?php _e( "The club news block will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Upcoming games', 'myclub-groups' ) ?></strong> - <?php _e( 'The coming-games block will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the activities from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Leaders', 'myclub-groups' ) ?></strong> - <?php _e( 'The leaders block will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the leaders from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Members', 'myclub-groups' ) ?></strong> - <?php _e( 'The members block will display the members for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the members from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Menu', 'myclub-groups' ) ?></strong> - <?php _e( "The menu block will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Navigation', 'myclub-groups' ) ?></strong> - <?php _e( 'The navigation block will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the navigation from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'News', 'myclub-groups' ) ?></strong> - <?php _e( 'The news block will display the group page news. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the news for. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?php esc_attr_e( 'Title', 'myclub-groups' ) ?></strong> - <?php _e( 'The title block will display the group page title. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the title from. The default is to use the current page.', 'myclub-groups' ) ?></li>
            </ul>
            <?php
        } else { ?>
            <h2><?php esc_attr_e( 'Shortcodes', 'myclub-groups' ) ?></h2>
            <div><?php esc_attr_e( 'Here are the shortcodes available from the MyClub groups plugin', 'myclub-groups' ) ?></div>
            <ul>
                <li><code>[myclub-groups-calendar]</code> - <?php _e( 'The calendar shortcode will display a group calendar. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the calendar from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-club-news]</code> - <?php _e( "The club news shortcode will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-coming-games]</code> - <?php _e( 'The coming-games shortcode will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the activities from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-leaders]</code> - <?php _e( 'The leaders shortcode will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the leaders from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-members]</code> - <?php _e( 'The members shortcode will display the members for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the members from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-menu]</code> - <?php _e( "The menu shortcode will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-navigation]</code> - <?php _e( 'The navigation shortcode will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the navigation from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-news]</code> - <?php _e( 'The news shortcode will display the group page news. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the news for. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-title]</code> - <?php _e( 'The title shortcode will display the group page title. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the title from. The default is to use the current page.', 'myclub-groups' ) ?></li>
            </ul>
    <?php } ?>
    <?php if ( in_array( $active_tab, [ 'tab1', 'tab2', 'tab3' ] ) ) { ?>
        <div>
            <?php if( $active_tab === 'tab1' ) { ?>
                <button type="button" id="myclub-reload-news-button"
                        class="button"><?php esc_attr_e( 'Reload news', 'myclub-groups' ) ?></button>
                <button type="button" id="myclub-reload-groups-button"
                    class="button"><?php esc_attr_e( 'Reload groups', 'myclub-groups' ) ?></button>
            <?php }
            submit_button( __( 'Save Changes' ), 'primary', 'save', false ); ?>
        </div>
    <?php } ?>
    </form>
</div>