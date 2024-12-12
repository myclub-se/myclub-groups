<?php

use MyClub\MyClubGroups\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$member_title = get_option( 'myclub_groups_members_title' );
?>
<div class="myclub-groups-members-list" id="members">
    <div class="myclub-groups-members-container">
        <h3 class="myclub-groups-header"><?php echo esc_attr( $member_title ) ?></h3>

<?php

if ( !empty( $attributes ) ) {
    $post_id = Utils::get_post_id( $attributes );
}

if ( empty ( $post_id ) || $post_id == 0 ) {
    echo esc_html__( 'No group page found. Invalid post_id or group_id.', 'myclub-groups' );
} else {
    $meta = get_post_meta( $post_id, 'myclub_groups_members', true );

    if ( !empty ( $meta ) ) {
        $hidden_added = false;
        $members = json_decode( $meta )->members;
        $labels = [
            'age'   => __( 'Age', 'myclub-groups' ),
            'email' => __( 'E-mail', 'myclub-groups' ),
            'role'  => __( 'Role', 'myclub-groups' ),
            'phone' => __( 'Phone', 'myclub-groups' )
        ];
        ?>
            <div class="members-list" data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>">
            <?php
            foreach ( $members as $key=>$member ) {
                $member->name = str_replace( 'u0022', '\"', $member->name );
                if ( isset ( $member->role ) ) {
                    $member->role = str_replace( 'u0022', '\"', $member->role );
                }
                ?>
                    <div class="member" data-member="<?php echo esc_attr( wp_json_encode( $member, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT ) ); ?>">
                        <?php
                            if ( $member->member_image ) {
                                $member->member_image->url = Utils::change_host_name( $member->member_image->url );

                                ?>
                                <div class="member-picture">
                                    <img src="<?php echo esc_url( $member->member_image->url ); ?>" alt="<?php echo esc_attr( $member->name ); ?>" />
                                </div>
                                <?php

                            } else {
                                echo '<div class="member-picture"></div>';
                            }
                        ?>
                        <div class="member-name">
                            <?php echo esc_attr( $member->name ); ?>
                            <div class="member-role"><?php echo esc_attr( $member->role ); ?></div>
                        </div>
                    </div>
                <?php
                if ( $key  === 7) {
                    echo '<div class="hidden extended-list">';
                    $hidden_added = true;
                }
            }

            if ($hidden_added) {
                ?>
                </div>
                <div class="member-show-more"><?php echo esc_attr__( 'Show more', 'myclub-groups' ); ?></div>
                <div class="member-show-less hidden"><?php echo esc_attr__( 'Show less', 'myclub-groups' ); ?></div>
            <?php }
    }
}
?>
        </div>
        <div class="member-modal">
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
