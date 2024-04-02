<?php
$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = json_decode( get_post_meta( $post_id, 'activities', true ) );

if ( !empty( $meta ) ) {
    $activities = array_filter( $meta, function($activity) {
        return $activity->day > date('Y-m-d') && $activity->base_type === 'match';
    });
    $activities = array_values( $activities );
    ?>

    <div class="myclub-groups-coming-games" id="coming-games">
        <div class="myclub-groups-coming-games-container">
            <h3><?= get_option( 'myclub_groups_coming_games_title' ) ?></h3>
            <div class="coming-games-list">
                <?php
                if ( !empty( $activities ) ) {
                $hidden_added = false;
                foreach ( $activities as $key=>$activity ) { ?>
                    <div class="myclub-groups-coming-game">
                        <div class="title">
                            <div class="group-name"><?= get_the_title( $post_id ) ?></div>
                            <?= $activity->title ?>
                        </div>
                        <div class="venue"><?= $activity->location ?></div>
                        <div class="date">
                            <?= $activity->day ?>
                            <div class="time"><?= substr( $activity->start_time, 0, 5 ) . ' - ' . substr( $activity->end_time, 0, 5 ) ?></div>
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
            <div class="coming-game-show-more"><?= __( 'Show more', 'myclub-groups' ) ?></div>
            <div class="coming-game-show-less hidden"><?= __( 'Show less', 'myclub-groups' ) ?></div>
            <?php  }
            } else {
            ?>
            <div class="myclub-groups-no-coming-games"><?= __( 'No upcoming games', 'myclub-groups' ) ?></div>
            <?php
            }
            ?>
        </div>
    </div>
    </div>
<?php }