<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$myclub_groups_valid_tabs = [
        'tab1',
        'tab2',
        'tab3',
        'tab4',
        'tab5',
        'tab6'
];
$myclub_groups_active_tab = sanitize_text_field( wp_unslash( $_GET[ 'tab' ] ?? 'tab1' ) );
$myclub_groups_valid_action_tabs = [
        'tab1',
        'tab2',
        'tab3',
        'tab4'
];

if ( !in_array( $myclub_groups_active_tab, $myclub_groups_valid_tabs ) ) {
    $myclub_groups_active_tab = 'tab1';
}

function myclub_groups_allow_code_html( $translated_string )
{
    echo wp_kses( $translated_string, array ( 'code' => array () ) );
}

/**
 * Renders a label containing the last synchronization time or status message for a specific field.
 *
 * This method determines the last synchronization time for the specified field, or provides a
 * status message if a related cron job is running. It then outputs the result in a div element.
 *
 * @param string $field_name The name of the field to retrieve the last synchronization data for.
 * @return void This method does not return a value. It directly outputs the content to the browser.
 */
function renderDateTimeLabel( string $field_name ): void
{
    $last_sync = esc_attr( get_option( $field_name ) );
    $cron_job_name = '';
    $output = '';

    if ( $field_name === 'myclub_groups_last_news_sync' ) {
        $cron_job_name = 'myclub_groups_refresh_news_task_cron';
        $cron_job_type = __( 'news', 'myclub-groups' );
    }

    if ( $field_name === 'myclub_groups_last_groups_sync' ) {
        $cron_job_name = 'myclub_groups_refresh_groups_task_cron';
        $cron_job_type = __( 'groups', 'myclub-groups' );
    }

    if ( !empty( $cron_job_name ) && isset( $cron_job_type ) ) {
        $next_scheduled = wp_next_scheduled( $cron_job_name );
        if ( $next_scheduled ) {
            /* translators: 1: the type of update cron job that is running */
            $output = sprintf( __( 'The %1$s update task is currently running.', 'myclub-groups' ), esc_attr( $cron_job_type ) );
        }
    }

    if ( empty ( $output ) ) {
        $output = empty( $last_sync ) ? __( 'Not synchronized yet', 'myclub-groups' ) : Utils::formatDateTime( $last_sync );
    }

    echo '<div id="' . esc_attr( $field_name ) . '">' . esc_html( $output ) . '</div>';
}

?>

<div class="wrap">
    <h1><?php esc_attr_e( 'MyClub Groups settings', 'myclub-groups' ) ?></h1>
    <div class="nav-tab-wrapper">
        <a href="?page=myclub-groups-settings&tab=tab1"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab1' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'General settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab2"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab2' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Title settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab3"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab3' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Display settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab4"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab4' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Calendar settings', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab5"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab5' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Gutenberg blocks', 'myclub-groups' ) ?></a>
        <a href="?page=myclub-groups-settings&tab=tab6"
           class="nav-tab<?php echo $myclub_groups_active_tab === 'tab6' ? ' nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Shortcodes', 'myclub-groups' ) ?></a>
    </div>

    <form method="post" action="options.php" id="myclub-settings-form">
        <?php
        if ( $myclub_groups_active_tab === 'tab1' ) {
            settings_fields( 'myclub_groups_settings_tab1' );
            do_settings_sections( 'myclub_groups_settings_tab1' );
        } else if ( $myclub_groups_active_tab === 'tab2' ) {
            settings_fields( 'myclub_groups_settings_tab2' );
            do_settings_sections( 'myclub_groups_settings_tab2' );
        } else if ( $myclub_groups_active_tab === 'tab3' ) {
            settings_fields( 'myclub_groups_settings_tab3' );
            do_settings_sections( 'myclub_groups_settings_tab3' );
        } else if ( $myclub_groups_active_tab === 'tab4' ) {
            settings_fields( 'myclub_groups_settings_tab4' );
            do_settings_sections( 'myclub_groups_settings_tab4' );
        } else if ( $myclub_groups_active_tab === 'tab5' ) {
            ?> <h2><?php esc_attr_e( 'Gutenberg blocks', 'myclub-groups' ) ?></h2>
            <div><?php esc_attr_e( 'Here are the Gutenberg blocks available from the MyClub groups plugin', 'myclub-groups' ) ?></div>
            <div><?php esc_attr_e( 'The group Gutenberg blocks require a post_id or a group_id parameter (the club blocks do not). The post_id parameter is the ID of the MyClub Groups page that the plugin creates for the Group. The group_id parameter is found on the MyClub Groups page under the MyClub group information tab - the property `MyClub group id`', 'myclub-groups' ) ?></div>
            <ul>
                <li><strong><?php esc_attr_e( 'Calendar', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The calendar block will display a group calendar. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the calendar from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Club calendar', 'myclub-groups' ) ?></strong>
                    - <?php esc_html_e( "The club calendar block will display the club calendar. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Club news', 'myclub-groups' ) ?></strong>
                    - <?php esc_html_e( "The club news block will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Upcoming games', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The coming-games block will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the activities from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Leaders', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The leaders block will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the leaders from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Members', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The members block will display the members for a group. The available attributes are <code>post_id</code> which can be set to the group page that you want to get the members from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Menu', 'myclub-groups' ) ?></strong>
                    - <?php esc_html_e( "The menu block will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Navigation', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The navigation block will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the navigation from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'News', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The news block will display the group page news. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the news for or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><strong><?php esc_attr_e( 'Title', 'myclub-groups' ) ?></strong>
                    - <?php myclub_groups_allow_code_html( __( 'The title block will display the group page title. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the title from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
            </ul>
            <?php
        } else { ?>
            <h2><?php esc_attr_e( 'Shortcodes', 'myclub-groups' ) ?></h2>
            <div><?php esc_attr_e( 'Here are the shortcodes available from the MyClub groups plugin', 'myclub-groups' ) ?></div>
            <div><?php esc_attr_e( 'The group shortcodes require a post_id or a group_id parameter (the club shortcodes do not). The post_id parameter is the ID of the MyClub Groups page that the plugin creates for the Group. The group_id parameter is found on the MyClub Groups page under the MyClub group information tab - the property `MyClub group id`', 'myclub-groups' ) ?></div>
            <ul>
                <li><code>[myclub-groups-calendar]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The calendar shortcode will display a group calendar. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the calendar from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-club-calendar]</code>
                    - <?php esc_html_e( "The club calendar shortcode will display the club calendar. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><code>[myclub-groups-club-news]</code>
                    - <?php esc_html_e( "The club news shortcode will display all club news. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><code>[myclub-groups-coming-games]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The coming-games shortcode will display the upcoming games for a group. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the activities from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-leaders]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The leaders shortcode will display the leaders for a group. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the leaders from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-members]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The members shortcode will display the members for a group. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the members from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-menu]</code>
                    - <?php esc_html_e( "The menu shortcode will display the group menu. This block doesn't require any attributes.", 'myclub-groups' ) ?>
                </li>
                <li><code>[myclub-groups-navigation]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The navigation shortcode will display the group page navigation. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the navigation from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-news]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The news shortcode will display the group page news. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the news for or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
                <li><code>[myclub-groups-title]</code>
                    - <?php myclub_groups_allow_code_html( __( 'The title shortcode will display the group page title. The available attributes are <code>post_id</code> which can be set to the WordPress post id of the group page that you want to get the title from or <code>group_id</code> which is the MyClub group id for the group page. The default is to use the current page.', 'myclub-groups' ) ) ?>
                </li>
            </ul>
        <?php } ?>
        <?php if ( in_array( $myclub_groups_active_tab, $myclub_groups_valid_action_tabs ) ) { ?>
            <div>
                <?php if ( $myclub_groups_active_tab === 'tab1' ) { ?>
                    <h2><?php esc_html_e( 'Synchronization information', 'myclub-groups' ) ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'News last synchronized', 'myclub-groups' ) ?></th>
                            <td>
                                <?php renderDateTimeLabel( 'myclub_groups_last_news_sync' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Groups last synchronized', 'myclub-groups' ) ?></th>
                            <td>
                                <?php renderDateTimeLabel( 'myclub_groups_last_groups_sync' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Club calendar last synchronized', 'myclub-groups' ) ?></th>
                            <td>
                                <?php renderDateTimeLabel( 'myclub_groups_last_club_calendar_sync' ) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div>
                        <button type="button" id="myclub-reload-news-button" class="button">
                            <?php esc_attr_e( 'Reload news', 'myclub-groups' ) ?>
                        </button>
                        <button type="button" id="myclub-reload-groups-button" class="button">
                            <?php esc_attr_e( 'Reload groups', 'myclub-groups' ) ?>
                        </button>
                        <button type="button" id="myclub-sync-club-calendar-button" class="button">
                            <?php esc_attr_e( 'Resync club calendar', 'myclub-groups' ) ?>
                        </button>
                    </div>
                <?php } ?>
                <p>
                    <?php submit_button( esc_html__( 'Save Changes' ), 'primary', 'save', false ); ?>
                </p>
            </div>
        <?php } ?>
    </form>
</div>