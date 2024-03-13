<?php

use MyClub\MyClubGroups\Utils;

$postId = $attributes[ 'postId' ] ?? null;

if ( empty( $postId ) ) {
    $postId = get_the_ID();
}

$meta = get_post_meta( $postId, 'members', true );

if ( !empty ( $meta ) ) {
    $hiddenAdded = false;
    $members = json_decode( $meta )->members;
    $memberTitle = get_option( 'myclub_groups_members_title' );
    $labels = [
        'age'   => __( 'Age', 'myclub-groups' ),
        'email' => __( 'E-mail', 'myclub-groups' ),
        'role'  => __( 'Role', 'myclub-groups' ),
        'phone' => __( 'Phone', 'mcylub-groups' )
    ];
    ?>
<div class="myclub-groups-members-list" id="members">
    <h3 class="myclub-groups-header"><?= $memberTitle ?></h3>
    <div class="members-list" data-labels="<?= htmlspecialchars( json_encode( $labels, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
    <?php
    foreach ( $members as $key=>$member ) {
        $member->member_image->url = Utils::changeHostName( $member->member_image->url );
        ?>
        <div class="member" data-member="<?=  htmlspecialchars( json_encode( $member, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
            <div class="member-picture">
                <img src="<?= $member->member_image->url ?>" alt="<?= $member->name ?>" />
            </div>
            <div class="member-name"><?=  $member->name ?></div>
        </div>
        <?php
        if ( $key  === 7) {
            echo '<div class="hidden extended-list">';
            $hiddenAdded = true;
        }
    }

    if ($hiddenAdded) {
        ?>
        </div>
        <div class="member-show-more"><?= __( 'Show more', 'myclub-groups' ) ?></div>
        <div class="member-show-less hidden"><?= __( 'Show less', 'myclub-groups' ) ?></div>
    <?php } ?>

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