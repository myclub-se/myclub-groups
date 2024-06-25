<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\Utils;

$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = get_post_meta( $post_id, 'members', true );

if ( !empty ( $meta ) ) {
    $hidden_added = false;
    $members = json_decode( $meta )->members;
    $member_title = get_option( 'myclub_groups_members_title' );
    $labels = [
        'age'   => __( 'Age', 'myclub-groups' ),
        'email' => __( 'E-mail', 'myclub-groups' ),
        'role'  => __( 'Role', 'myclub-groups' ),
        'phone' => __( 'Phone', 'myclub-groups' )
    ];
    ?>
<div class="myclub-groups-members-list" id="members">
    <div class="myclub-groups-members-container">
        <h3 class="myclub-groups-header"><?php echo esc_attr( $member_title ) ?></h3>
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
                    <div class="member-name"><?php echo esc_attr( $member->name ); ?></div>
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
        <?php } ?>
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
<?php
}