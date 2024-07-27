<?php

use MyClub\MyClubGroups\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$header = get_option( 'myclub_groups_calendar_title' );

?>
    <div class="myclub-groups-calendar" id="calendar">
        <div class="myclub-groups-calendar-container">
            <h3 class="myclub-groups-header"><?php echo esc_attr( $header ) ?></h3>
<?php
if ( !empty( $attributes ) ) {
    $post_id = Utils::get_post_id( $attributes );
}

if ( empty ( $post_id ) || $post_id == 0 ) {
    echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
} else {
    $meta = get_post_meta( $post_id, 'myclub_groups_activities', true );

    if ( !empty( $meta ) ):
        $activities = json_decode( $meta );
        $events = [];
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
            switch ( $activity->base_type ) {
                case 'match':
                    $backgroundColor = '#c1272d';
                    break;
                case 'training':
                    $backgroundColor = '#009245';
                    break;
                case 'meeting':
                    $backgroundColor = '#396b9e';
                    break;
                default:
                    $backgroundColor = '#9e8c39';
            }
            $meetUpTime = $activity->start_time;

            if ( $activity->meet_up_time ) {
                try {
                    $dateTime = DateTime::createFromFormat( 'H:i:s', $activity->start_time );
                    $interval = new DateInterval( 'PT' . $activity->meet_up_time . 'M' );
                    $dateTime->sub( $interval );
                    $meetUpTime = $dateTime->format( 'H:i:s' );
                } catch ( Exception $e ) {
                }
            }

            $events[] = array (
                'title'           => $activity->title,
                'start'           => "$activity->day $activity->start_time",
                'end'             => "$activity->day $activity->end_time",
                'eventColor'      => $backgroundColor,
                'backgroundColor' => $backgroundColor,
                'borderColor'     => $backgroundColor,
                'color'           => '#fff',
                'display'         => 'block',
                'extendedProps'   => array (
                    'base_type'     => $activity->base_type,
                    'calendar_name' => $activity->calendar_name,
                    'location'      => $activity->location,
                    'description'   => str_replace( '&quot;', 'u0022', $activity->description ),
                    'endTime'       => $activity->end_time,
                    'meetUpTime'    => $meetUpTime,
                    'meetUpPlace'   => $activity->meet_up_place,
                    'startTime'     => $activity->start_time,
                    'type'          => $activity->type
                )
            );
        }
        ?>

        <div id="calendar-div"
             data-events="<?php echo esc_attr( wp_json_encode( $events, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>"
             data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>"></div>
    <?php
    endif;
}
?>
    </div>
    <div class="calendar-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
