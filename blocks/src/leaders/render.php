<?php

use MyClub\MyClubGroups\Utils;

$post_id = $attributes[ 'postId' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$meta = get_post_meta( $post_id, 'members', true );
$domain_name = $_SERVER['HTTP_HOST'];

if ( !empty( $meta ) ) {
    $hidden_added = false;
    $leaders = json_decode( $meta )->leaders;
    $leader_title = get_option( 'myclub_groups_leaders_title' );
    $labels = [
        'age'   => __( 'Age', 'myclub-groups' ),
        'email' => __( 'E-mail', 'myclub-groups' ),
        'role'  => __( 'Role', 'myclub-groups' ),
        'phone' => __( 'Phone', 'mcylub-groups' )
    ];

    ?>
<div class="myclub-groups-leaders-list" id="leaders">
    <div class="myclub-groups-leaders-container">
        <h3 class="myclub-groups-header"><?= $leader_title ?></h3>
        <div class="leaders-list" data-labels="<?= htmlspecialchars( json_encode( $labels, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
        <?php
        foreach ( $leaders as $key=>$leader ) {
            if( $leader->member_image ) {

                $leader->member_image->url = Utils::change_host_name( $leader->member_image->url );
                ?>
                <div class="leader" data-leader="<?= htmlspecialchars( json_encode( $leader, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
                    <div class="leader-picture">
                        <img src="<?= $leader->member_image->url ?>" alt="<?= $leader->name ?>" />
                    </div>
                    <div class="leader-name">
                        <?=$leader->name ?>
                        <div class="leader-role"><?= $leader->role ?></div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="leader" data-member="<?=  htmlspecialchars( json_encode( $leader, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
                    <div class="leader-picture"></div>
                    <div class="leader-name">
                        <?=$leader->name ?>
                        <div class="leader-role"><?= $leader->role ?></div>
                    </div>
                </div>
                <?php
            }

            if ( $key  === 3) {
                echo '<div class="hidden extended-list">';
                $hidden_added = true;
            }
        }

        if ($hidden_added) {
            ?>
            </div>
            <div class="leader-show-more"><?= __( 'Show more', 'myclub-groups' ) ?></div>
            <div class="leader-show-less hidden"><?= __( 'Show less', 'myclub-groups' ) ?></div>
        <?php  } ?>
        </div>
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
<?php
    }