<?php

use MyClub\MyClubGroups\Services\CalendarService;
use MyClub\MyClubGroups\Utils;

if ( !defined( 'ABSPATH' ) ) exit;

$myclub_groups_club_calendar_header = get_option( 'myclub_groups_club_calendar_title' );
$myclub_groups_calendar_desktop_views = get_option( 'myclub_groups_club_calendar_desktop_views', Utils::getCalendarDesktopViews() );
$myclub_groups_calendar_desktop_views_default = get_option( 'myclub_groups_club_calendar_desktop_views_default', Utils::getCalendarDesktopViewsDefault() );
$myclub_groups_calendar_mobile_views = get_option( 'myclub_groups_club_calendar_mobile_views', Utils::getCalendarMobileViews() );
$myclub_groups_calendar_mobile_views_default = get_option( 'myclub_groups_club_calendar_mobile_views_default', Utils::getCalendarMobileViewsDefault() );
$myclub_groups_calendar_show_week_numbers = get_option( 'myclub_groups_club_calendar_show_week_numbers', '1' );
$myclub_groups_no_activities_message = get_option( 'myclub_groups_no_activities_message', esc_attr__( 'No activities to display', 'myclub-groups' ) );

$myclub_groups_show_subscribe_button = ( isset( $attributes['show_subscribe_button'] ) && $attributes['show_subscribe_button'] !== '' )
    ? $attributes['show_subscribe_button']
    : '0';

$myclub_groups_club_base_url = '';
if ( $myclub_groups_show_subscribe_button === '1' ) {
    $myclub_groups_club_base_url = get_option( 'myclub_groups_club_calendar_url', '' );
}

?>
<div class="myclub-groups-club-calendar">
    <div class="myclub-groups-club-calendar-container">
        <h3 class="myclub-groups-header"><?php echo esc_attr( $myclub_groups_club_calendar_header ) ?></h3>
        <?php

        $activities = CalendarService::ListActivities();

        $labels = [
                'calendar'       => __( 'Calendar', 'myclub-groups' ),
                'description'    => __( 'Information', 'myclub-groups' ),
                'name'           => __( 'Name', 'myclub-groups' ),
                'when'           => __( 'When', 'myclub-groups' ),
                'location'       => __( 'Location', 'myclub-groups' ),
                'meetUpLocation' => __( 'Gathering location', 'myclub-groups' ),
                'meetUpTime'     => __( 'Gathering time', 'myclub-groups' ),
                'today'          => __( 'today', 'myclub-groups' ),
                'day'            => __( 'day', 'myclub-groups' ),
                'month'          => __( 'month', 'myclub-groups' ),
                'week'           => __( 'week', 'myclub-groups' ),
                'list'           => __( 'list', 'myclub-groups' ),
                'weekText'       => __( 'W', 'myclub-groups' ),
                'weekTextLong'   => __( 'Week', 'myclub-groups' ),
        ];
        foreach ( $activities as $activity ) {
            $activity->title = str_replace( '&quot;', 'u0022', $activity->title );
        }
        ?>
        <div id="club-calendar-div"
             data-events="<?php echo esc_attr( wp_json_encode( $activities, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>"
             data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>"
             data-locale="<?php echo esc_attr( get_locale() ); ?>"
             data-calendar-desktop="<?php echo esc_attr( join( ',', $myclub_groups_calendar_desktop_views ) ); ?>"
             data-calendar-desktop-default="<?php echo esc_attr( $myclub_groups_calendar_desktop_views_default ); ?>"
             data-calendar-mobile="<?php echo esc_attr( join( ',', $myclub_groups_calendar_mobile_views ) ); ?>"
             data-calendar-mobile-default="<?php echo esc_attr( $myclub_groups_calendar_mobile_views_default ); ?>"
             data-calendar-week-numbers="<?php echo esc_attr( $myclub_groups_calendar_show_week_numbers ); ?>"
             data-first-day-of-week="<?php echo esc_attr( get_option( 'start_of_week', 1 ) ); ?>"
             data-no-events-content="<?php echo esc_attr( $myclub_groups_no_activities_message ); ?>"
        ></div>
        <?php
        if ( $myclub_groups_show_subscribe_button === '1' && !empty( $myclub_groups_club_base_url ) ) {
            $webcal_url  = 'webcal://' . $myclub_groups_club_base_url;
            $https_url   = 'https://' . $myclub_groups_club_base_url;
            ?>
            <div class="myclub-groups-subscribe-button-wrapper">
                <button class="myclub-groups-subscribe-button" data-subscribe-modal="club-calendar-subscribe-modal">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'Subscribe', 'myclub-groups' ); ?>
                </button>
            </div>
            <div id="club-calendar-subscribe-modal" class="club-calendar-modal">
                <div class="modal-content subscribe-modal-content">
                    <span class="close">&times;</span>
                    <div class="modal-body subscribe-modal-body">
                        <h3 class="subscribe-modal-title"><?php esc_html_e( 'Subscribe', 'myclub-groups' ); ?></h3>
                        <div class="subscribe-platform">
                            <div class="subscribe-platform-header">
                                <img src="<?php echo esc_url( plugins_url( '../../../resources/images/apple.svg', __FILE__ ) ); ?>" alt="Apple" class="subscribe-platform-icon" />
                                <strong><?php esc_html_e( 'iPhone, iPad, Mac', 'myclub-groups' ); ?></strong>
                            </div>
                            <ol>
                                <li><?php esc_html_e( 'Use the browser on the respective device', 'myclub-groups' ); ?></li>
                                <li><?php esc_html_e( 'Click the following link:', 'myclub-groups' ); ?> <a href="<?php echo esc_url( $webcal_url ); ?>"><?php echo esc_html( $webcal_url ); ?></a></li>
                                <li><?php esc_html_e( 'Click "Subscribe"', 'myclub-groups' ); ?></li>
                            </ol>
                        </div>
                        <div class="subscribe-platform">
                            <div class="subscribe-platform-header">
                                <img src="<?php echo esc_url( plugins_url( '../../../resources/images/android.svg', __FILE__ ) ); ?>" alt="Android" class="subscribe-platform-icon" />
                                <strong><?php esc_html_e( 'Android', 'myclub-groups' ); ?></strong>
                            </div>
                            <p><?php esc_html_e( 'Every Android device is associated with a Google account. Subscriptions in Android are done via Google, see below.', 'myclub-groups' ); ?></p>
                        </div>
                        <div class="subscribe-platform">
                            <div class="subscribe-platform-header">
                                <img src="<?php echo esc_url( plugins_url( '../../../resources/images/google.svg', __FILE__ ) ); ?>" alt="Google" class="subscribe-platform-icon" />
                                <strong><?php esc_html_e( 'Google', 'myclub-groups' ); ?></strong>
                            </div>
                            <ol>
                                <li><?php esc_html_e( 'Go to', 'myclub-groups' ); ?> <a href="https://www.google.com/calendar" target="_blank" rel="noopener">www.google.com/calendar</a> <?php esc_html_e( '(requires a Google account)', 'myclub-groups' ); ?></li>
                                <li><?php esc_html_e( 'Click the arrow to the right of "Other calendars" and then "Add web address"', 'myclub-groups' ); ?></li>
                                <li><?php esc_html_e( 'Enter the URL:', 'myclub-groups' ); ?> <a href="<?php echo esc_url( $https_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $https_url ); ?></a> <?php esc_html_e( 'and click "Add calendar"', 'myclub-groups' ); ?></li>
                            </ol>
                        </div>
                        <div class="subscribe-platform">
                            <div class="subscribe-platform-header">
                                <img src="<?php echo esc_url( plugins_url( '../../../resources/images/windows.svg', __FILE__ ) ); ?>" alt="Microsoft Outlook" class="subscribe-platform-icon" />
                                <strong><?php esc_html_e( 'Microsoft Outlook', 'myclub-groups' ); ?></strong>
                            </div>
                            <ol>
                                <li><?php esc_html_e( 'Click the following link:', 'myclub-groups' ); ?> <a href="<?php echo esc_url( $webcal_url ); ?>"><?php echo esc_html( $webcal_url ); ?></a></li>
                                <li><?php esc_html_e( 'Open the link with Outlook', 'myclub-groups' ); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="club-calendar-modal" id="club-calendar-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>