<?php

use MyClub\MyClubGroups\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$header = get_option( 'myclub_groups_calendar_title' );

?>
    <div class="myclub-groups-calendar">
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

        <div id="calendar-div"
             data-events="<?php echo esc_attr( wp_json_encode( $activities, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>"
             data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>"
             data-locale="<?php echo esc_attr( get_locale() ); ?>"
             data-first-day-of-week="<?php echo esc_attr( get_option( 'start_of_week', 1 ) ); ?>"
        ></div>
    <?php
    endif;
}
?>
    </div>
    <div id="calendar-modal" class="calendar-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
