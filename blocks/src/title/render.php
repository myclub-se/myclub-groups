<?php
$postId = $attributes[ 'postId' ] ?? null;

if ( empty( $postId ) ) {
    $postId = get_the_ID();
}

$title = get_the_title($postId);
$contactName = get_post_meta( $postId, 'contactName', true );
$email = get_post_meta( $postId, 'email', true );
$phone = get_post_meta( $postId, 'phone', true );
$infoText = get_post_meta( $postId, 'infoText', true );

$imageUrl = get_the_post_thumbnail_url( $postId );
$allowImage = get_option( 'myclub_groups_page_picture', '1' ) === '1';

?>
<div class="myclub-groups-title">
    <div class="myclub-groups-title-box">
        <?php if( $allowImage && !empty( $imageUrl ) ) { ?>
        <div class="myclub-groups-title-image">
            <img src="<?=$imageUrl?>" alt="<?=$title?>">
        </div>
        <?php } ?>
        <div class="myclub-groups-title-information">
            <div class="myclub-groups-title-name<?= empty( $infoText ) ? '' : ' with-info-text' ?>"><?=$title?></div>
            <?php if ( !empty( $infoText ) ) { ?>
            <div class="myclub-groups-info-text"><?=$infoText?></div>
            <?php }
            if ( !empty( $contactName ) ) { ?>
            <div class="myclub-groups-information"><div class="label"><?=__( 'Contact person', 'myclub-groups' )?></div><div class="value"><?=$contactName?></div></div>
            <?php }
            if ( !empty( $phone ) ) { ?>
                <div class="myclub-groups-information"><div class="label"><?=__( 'Telephone', 'myclub-groups' )?></div><div class="value"><a href="tel:<?=$phone?>"><?=$phone?></a></div></div>
            <?php }
            if ( !empty( $email ) ) { ?>
                <div class="myclub-groups-information"><div class="label"><?=__( 'E-mail', 'myclub-groups' )?></div><div class="value"><a href="mailto:<?=$email?>"><?=$email?></a></div></div>
            <?php } ?>
        </div>
    </div>
</div>
