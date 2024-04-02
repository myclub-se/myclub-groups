<?php
    $active_tab = $_GET[ 'tab' ] ?? 'tab1';
?>

<div class="wrap">
    <h1><?= __( 'MyClub Groups settings', 'myclub-groups' ) ?></h1>
    <div class="nav-tab-wrapper">
        <a href="?page=myclub-groups-settings&tab=tab1" class="nav-tab<?php echo $active_tab === 'tab1' ? ' nav-tab-active' : ''; ?>"><?= __( 'General settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab2" class="nav-tab<?php echo $active_tab === 'tab2' ? ' nav-tab-active' : ''; ?>"><?= __( 'Title settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab3" class="nav-tab<?php echo $active_tab === 'tab3' ? ' nav-tab-active' : ''; ?>"><?= __( 'Display settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab4" class="nav-tab<?php echo $active_tab === 'tab4' ? ' nav-tab-active' : ''; ?>"><?= __( 'Gutenberg blocks', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab5" class="nav-tab<?php echo $active_tab === 'tab5' ? ' nav-tab-active' : ''; ?>"><?= __( 'Shortcodes', 'myclub-groups' ) ?></a>
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
            ?> <h2><?= __( 'Gutenberg blocks', 'myclub-groups') ?></h2>
            <div><?= __( 'Here are the Gutenberg blocks available from the MyClub groups plugin', 'myclub-groups' )?></div>
            <ul>
                <li><strong><?= __( 'Calendar', 'myclub-groups' ) ?></strong> - <?= __( 'The calendar block will display a group calendar. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the calendar from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Club news', 'myclub-groups' ) ?></strong> - <?= __( "The club news block will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Upcoming games', 'myclub-groups' ) ?></strong> - <?= __( 'The coming-games block will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the activities from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Leaders', 'myclub-groups' ) ?></strong> - <?= __( 'The leaders block will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the leaders from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Members', 'myclub-groups' ) ?></strong> - <?= __( 'The members block will display the members for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the members from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Menu', 'myclub-groups' ) ?></strong> - <?= __( "The menu block will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Navigation', 'myclub-groups' ) ?></strong> - <?= __( 'The navigation block will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the navigation from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'News', 'myclub-groups' ) ?></strong> - <?= __( 'The news block will display the group page news. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the news for. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><strong><?= __( 'Title', 'myclub-groups' ) ?></strong> - <?= __( 'The title block will display the group page title. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the title from. The default is to use the current page.', 'myclub-groups' ) ?></li>
            </ul>
            <?php
        } else { ?>
            <h2><?= __( 'Shortcodes', 'myclub-groups' ) ?></h2>
            <div><?= __( 'Here are the shortcodes available from the MyClub groups plugin', 'myclub-groups' ) ?></div>
            <ul>
                <li><code>[myclub-groups-calendar]</code> - <?= __( 'The calendar shortcode will display a group calendar. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the calendar from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-club-news]</code> - <?= __( "The club news shortcode will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-coming-games]</code> - <?= __( 'The coming-games shortcode will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the activities from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-leaders]</code> - <?= __( 'The leaders shortcode will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the leaders from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-members]</code> - <?= __( 'The members shortcode will display the members for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the members from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-menu]</code> - <?= __( "The menu shortcode will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-navigation]</code> - <?= __( 'The navigation shortcode will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the navigation from. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-news]</code> - <?= __( 'The news shortcode will display the group page news. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the news for. The default is to use the current page.', 'myclub-groups' ) ?></li>
                <li><code>[myclub-groups-title]</code> - <?= __( 'The title shortcode will display the group page title. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the title from. The default is to use the current page.', 'myclub-groups' ) ?></li>
            </ul>
    <?php } ?>
    <?php if ( in_array( $active_tab, [ 'tab1', 'tab2', 'tab3' ] ) ) { ?>
        <div>
            <?php if( $active_tab === 'tab1' ) { ?>
                <button type="button" id="myclub-reload-news-button"
                        class="button"><?= __( 'Reload news', 'myclub-groups' ) ?></button>
                <button type="button" id="myclub-reload-groups-button"
                    class="button"><?= __( 'Reload groups', 'myclub-groups' ) ?></button>
            <?php }
            submit_button( __( 'Save Changes' ), 'primary', 'save', false ); ?>
        </div>
    <?php } ?>
    </form>
</div>