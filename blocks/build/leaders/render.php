<?php

use MyClub\MyClubGroups\Services\MemberService;
use MyClub\MyClubGroups\Utils;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !empty( $attributes ) ) {
    $post_id = Utils::getPostId( $attributes );
}

if ( empty ( $post_id ) || $post_id == 0 ) {
    echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
} else {
    $leader_title = get_option( 'myclub_groups_leaders_title' );
    $leaders = MemberService::listGroupMembers( $post_id, true );

    if ( !empty( $leaders ) ):
        ?>
        <div class="myclub-groups-leaders-list" id="leaders">
            <div class="myclub-groups-leaders-container">
                <h3 class="myclub-groups-header"><?php echo esc_attr( $leader_title ); ?></h3>

                <?php

                $hidden_added = false;
                $labels = [
                        'age'   => __( 'Age', 'myclub-groups' ),
                        'email' => __( 'E-mail', 'myclub-groups' ),
                        'role'  => __( 'Role', 'myclub-groups' ),
                        'phone' => __( 'Phone', 'myclub-groups' )
                ];

                ?>
                <div class="leaders-list"
                     data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>">
                    <?php
                    foreach ( $leaders as $key => $leader ) {
                        $leader->dynamic_fields = json_decode( $leader->dynamic_fields );
                        $leader->name = str_replace( 'u0022', '\"', $leader->name );
                        if ( isset ( $leader->role ) ) {
                            $leader->role = str_replace( 'u0022', '\"', $leader->role );
                        }
                        ?>
                        <div class="leader"
                             data-leader="<?php echo esc_attr( wp_json_encode( $leader, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>">
                            <?php
                            if ( $leader->image_id ) {
                                $leader->image_url = Utils::changeHostName( $leader->image_url );

                                ?>
                                <div class="leader-picture">
                                    <img src="<?php echo esc_url( $leader->image_url ); ?>"
                                         alt="<?php echo esc_attr( $leader->name ); ?>"/>
                                </div>
                                <?php

                            } else {
                                echo '<div class="leader-picture"></div>';
                            }
                            ?>
                            <div class="leader-name">
                                <?php echo esc_attr( $leader->name ); ?>
                                <div class="leader-role"><?php echo esc_attr( $leader->role ); ?></div>
                            </div>
                        </div>
                        <?php

                        if ( $key === 3 ) {
                            echo '<div class="hidden extended-list">';
                            $hidden_added = true;
                        }
                    }

                    if ( $hidden_added ) {
                    ?>
                </div>
                <div class="leader-show-more"><?php esc_attr_e( 'Show more', 'myclub-groups' ); ?></div>
                <div class="leader-show-less hidden"><?php esc_attr_e( 'Show less', 'myclub-groups' ); ?></div>
            <?php }
            ?>
            </div>
            <div class="leader-modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <div class="modal-body">
                        <div class="image"></div>
                        <div class="information"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    <?php
    endif;
}