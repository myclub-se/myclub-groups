<?php

use MyClub\MyClubGroups\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$leader_title = get_option( 'myclub_groups_leaders_title' );

?>
    <div class="myclub-groups-leaders-list" id="leaders">
        <div class="myclub-groups-leaders-container">
            <h3 class="myclub-groups-header"><?php echo esc_attr( $leader_title ); ?></h3>

<?php

if ( !empty( $attributes ) ) {
    $post_id = Utils::get_post_id( $attributes );
}

if ( empty ( $post_id ) || $post_id == 0 ) {
    echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
} else {
    $meta = get_post_meta( $post_id, 'myclub_groups_members', true );

    if ( !empty( $meta ) ) {
        $hidden_added = false;
        $leaders = json_decode( $meta )->leaders;
        $labels = [
            'age'   => __( 'Age', 'myclub-groups' ),
            'email' => __( 'E-mail', 'myclub-groups' ),
            'role'  => __( 'Role', 'myclub-groups' ),
            'phone' => __( 'Phone', 'myclub-groups' )
        ];

        ?>
            <div class="leaders-list" data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>">
            <?php
            foreach ( $leaders as $key=>$leader ) {
                $leader->name = str_replace( 'u0022', '\"', $leader->name );
                if ( isset ( $leader->role ) ) {
                    $leader->role = str_replace( 'u0022', '\"', $leader->role );
                }
                ?>
                    <div class="leader" data-leader="<?php echo esc_attr( wp_json_encode( $leader, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>">
                        <?php
                            if ( $leader->member_image ) {
                                $leader->member_image->url = Utils::change_host_name( $leader->member_image->url );

                                ?>
                                <div class="leader-picture">
                                    <img src="<?php echo esc_url( $leader->member_image->url ); ?>" alt="<?php echo esc_attr( $leader->name ); ?>" />
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

                if ( $key  === 3) {
                    echo '<div class="hidden extended-list">';
                    $hidden_added = true;
                }
            }

            if ($hidden_added) {
                ?>
                </div>
                <div class="leader-show-more"><?php esc_attr_e( 'Show more', 'myclub-groups' ); ?></div>
                <div class="leader-show-less hidden"><?php esc_attr_e( 'Show less', 'myclub-groups' ); ?></div>
            <?php  }
    }
}
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
