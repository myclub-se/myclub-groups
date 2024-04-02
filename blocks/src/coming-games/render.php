<?php
$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = json_decode( get_post_meta( $post_id, 'activities', true ) );

if ( !empty( $meta ) ) {
    $activities = array_filter( $meta, function($activity) {
        return $activity->day > gmdate( 'Y-m-d' ) && $activity->base_type === 'match';
    });
    $activities = array_values( $activities );
    ?>

    <div class="myclub-groups-coming-games" id="coming-games">
        <div class="myclub-groups-coming-games-container">
            <h3><?php echo esc_attr( get_option( 'myclub_groups_coming_games_title' ) ); ?></h3>
            <div class="coming-games-list">
                <?php
                if ( !empty( $activities ) ) {
                $hidden_added = false;
                foreach ( $activities as $key=>$activity ) { ?>
                    <div class="myclub-groups-coming-game">
                        <div class="title">
                            <div class="group-name"><?php echo esc_attr( get_the_title( $post_id ) ); ?></div>
                            <?php echo esc_attr( $activity->title ); ?>
                        </div>
                        <div class="venue"><?php echo esc_attr( $activity->location ); ?></div>
                        <div class="date">
                            <?php echo esc_attr( $activity->day ); ?>
                            <div class="time"><?php echo esc_attr( substr( $activity->start_time, 0, 5 ) . ' - ' . substr( $activity->end_time, 0, 5 ) ); ?></div>
                        </div>
                    </div>
                    <?php
                    if ( $key === 3) {
                        echo '<div class="hidden extended-list">';
                        $hidden_added = true;
                    }
                }

                if ($hidden_added) {
                ?>
            </div>
            <div class="coming-game-show-more"><?php echo esc_attr__( 'Show more', 'myclub-groups' ); ?></div>
            <div class="coming-game-show-less hidden"><?php echo esc_attr__( 'Show less', 'myclub-groups' ); ?></div>
            <?php  }
            } else {
            ?>
            <div class="myclub-groups-no-coming-games"><?php echo esc_attr__( 'No upcoming games', 'myclub-groups' ); ?></div>
            <?php
            }
            ?>
        </div>
    </div>
    </div>
<?php }