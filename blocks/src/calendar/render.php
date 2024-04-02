<?php
$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = get_post_meta( $post_id, 'activities', true );

if ( !empty( $meta ) ) {
    $header = get_option( 'myclub_groups_calendar_title' );
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
        $description = str_replace( '<br /><br />', '<br />', $activity->description );
        if ( empty( trim( wp_strip_all_tags ( $description ) ) ) ) {
            $description = '';
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
                'description'   => $description,
                'endTime'       => $activity->end_time,
                'meetUpTime'    => $meetUpTime,
                'meetUpPlace'   => $activity->meet_up_place,
                'startTime'     => $activity->start_time,
                'type'          => $activity->type
            )
        );
    }
    ?>

    <div class="myclub-groups-calendar" id="calendar">
        <div class="myclub-groups-calendar-container">
            <h3 class="myclub-groups-header"><?= $header ?></h3>
            <div id="calendar-div"
                 data-events="<?= htmlspecialchars( json_encode( $events, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>"
                 data-labels="<?= htmlspecialchars( json_encode( $labels, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>"></div>
        </div>
        <div class="calendar-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
<?php } ?>