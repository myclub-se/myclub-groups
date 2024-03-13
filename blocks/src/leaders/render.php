<?php

use MyClub\MyClubGroups\Utils;

$postId = $attributes[ 'postId' ] ?? null;

if ( empty( $postId ) ) {
    $postId = get_the_ID();
}

$meta = get_post_meta( $postId, 'members', true );
$domain_name = $_SERVER['HTTP_HOST'];

if ( !empty( $meta ) ) {
    $hiddenAdded = false;
    $leaders = json_decode( $meta )->leaders;
    $leaderTitle = get_option( 'myclub_groups_leaders_title' );
    $labels = [
        'age'   => __( 'Age', 'myclub-groups' ),
        'email' => __( 'E-mail', 'myclub-groups' ),
        'role'  => __( 'Role', 'myclub-groups' ),
        'phone' => __( 'Phone', 'mcylub-groups' )
    ];

    ?>
<div class="myclub-groups-leaders-list" id="leaders">
    <h3 class="myclub-groups-header"><?= $leaderTitle ?></h3>
    <div class="leaders-list" data-labels="<?= htmlspecialchars( json_encode( $labels, JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) ?>">
    <?php
    foreach ( $leaders as $key=>$leader ) {
        $leader->member_image->url = Utils::changeHostName( $leader->member_image->url );
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
        if ( $key  === 3) {
            echo '<div class="hidden extended-list">';
            $hiddenAdded = true;
        }
    }

    if ($hiddenAdded) {
        ?>
        </div>
        <div class="leader-show-more"><?= __( 'Show more', 'myclub-groups' ) ?></div>
        <div class="leader-show-less hidden"><?= __( 'Show less', 'myclub-groups' ) ?></div>
    <?php  } ?>

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
    }