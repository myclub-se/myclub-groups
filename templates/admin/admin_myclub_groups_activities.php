<?php
$activities = json_decode( get_post_meta( get_the_ID(), 'activities', true ) );
?>

<div class="activity-box">
    <table class="activities-table">
        <tr>
            <th><?php esc_attr_e( 'Name', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Day', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Start time', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'End time', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Location', 'myclub-groups' ); ?></th>
        </tr>
        <?php
        if ( !empty( $activities ) ) {
        foreach ( $activities as $activity ) { ?>
            <tr>
                <td><?php echo esc_attr( $activity->title . ' (' . $activity->type . ')' ); ?></td>
                <td><?php echo esc_attr( $activity->day ); ?></td>
                <td><?php echo esc_attr( substr( $activity->start_time, 0, 5 ) ); ?></td>
                <td><?php echo esc_attr( substr( $activity->end_time, 0, 5 ) ); ?></td>
                <td><?php echo esc_attr( $activity->location ); ?></td>
            </tr>
        <?php }
        }
        ?>
    </table>
</div>