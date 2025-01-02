<?php

use MyClub\MyClubGroups\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<div class="myclub-groups-coming-games" id="coming-games">
    <div class="myclub-groups-coming-games-container">
        <h3><?php echo esc_attr( get_option( 'myclub_groups_coming_games_title' ) ); ?></h3>

        <?php

        if ( !empty( $attributes ) ) {
            $post_id = Utils::get_post_id( $attributes );
        }

        if ( empty ( $post_id ) || $post_id == 0 ):
            echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
        else:
            $meta = json_decode( get_post_meta( $post_id, 'myclub_groups_activities', true ) );

            if ( !empty( $meta ) ):
                $activities = array_filter( $meta, function($activity) {
                    return $activity->day > gmdate( 'Y-m-d' ) && $activity->base_type === 'match';
                });
                $activities = array_values( $activities );
                ?>
                <div class="coming-games-list">
                    <?php
                    if ( !empty( $activities ) ):
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
                            if ( $key === 3):
                                $hidden_added = true;
                                ?>
                                <div class="hidden extended-list">

                            <?php
                            endif;
                        }

                        if ($hidden_added):
                            ?>
                            </div>
                            <div class="coming-game-show-more"><?php echo esc_attr__( 'Show more', 'myclub-groups' ); ?></div>
                            <div class="coming-game-show-less hidden"><?php echo esc_attr__( 'Show less', 'myclub-groups' ); ?></div>
                        <?php
                        endif;
                    else:
                        ?>
                        <div class="myclub-groups-no-coming-games"><?php echo esc_attr__( 'No upcoming games', 'myclub-groups' ); ?></div>
                    <?php
                    endif;
                    ?>
                </div>
            <?php
            endif;
        endif;
        ?>
    </div>
</div>
