<?php

use MyClub\MyClubGroups\Services\CalendarService;

if ( !defined( 'ABSPATH' ) ) exit;

$myclub_groups_club_calendar_header = get_option( 'myclub_groups_club_calendar_title' );
$myclub_groups_calendar_desktop_views = get_option( 'myclub_groups_club_calendar_desktop_views', Utils::getCalendarDesktopViews() );
$myclub_groups_calendar_desktop_views_default = get_option( 'myclub_groups_club_calendar_desktop_views_default', Utils::getCalendarDesktopViewsDefault() );
$myclub_groups_calendar_mobile_views = get_option( 'myclub_groups_club_calendar_mobile_views', Utils::getCalendarMobileViews() );
$myclub_groups_calendar_mobile_views_default = get_option( 'myclub_groups_club_calendar_mobile_views_default', Utils::getCalendarMobileViewsDefault() );
$myclub_groups_calendar_show_week_numbers = get_option( 'myclub_groups_club_calendar_show_week_numbers', '1' );

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
            $activity->description = str_replace( '&quot;', 'u0022', $activity->description );
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
             data-first-day-of-week="<?php echo esc_attr( get_option( 'start_of_week', 1 ) ); ?>"></div>
    </div>
    <div class="club-calendar-modal" id="club-calendar-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>