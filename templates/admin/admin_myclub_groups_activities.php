<?php
$activities = json_decode( get_post_meta( get_the_ID(), 'activities', true ) );
?>

<div class="activity-box">
    <table class="activities-table">
        <tr>
            <th><?php _e( 'Name', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Day', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Start time', 'myclub-groups' ) ?></th>
            <th><?php _e( 'End time', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Location', 'myclub-groups' ) ?></th>
        </tr>
        <?php
        if ( !empty( $activities ) ) {
        foreach ( $activities as $activity ) { ?>
            <tr>
                <td><?= $activity->title . '(' . $activity->type . ')' ?></td>
                <td><?= $activity->day ?></td>
                <td><?= substr( $activity->start_time, 0, 5 ) ?></td>
                <td><?= substr( $activity->end_time, 0, 5 ) ?></td>
                <td><?= $activity->location ?></td>
            </tr>
        <?php }
        }
        ?>
    </table>
</div>