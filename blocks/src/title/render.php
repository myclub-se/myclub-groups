<?php
$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$title = get_the_title($post_id);
$contact_name = get_post_meta( $post_id, 'contact_name', true );
$email = get_post_meta( $post_id, 'email', true );
$phone = get_post_meta( $post_id, 'phone', true );
$info_text = get_post_meta( $post_id, 'info_text', true );

$image_url = get_the_post_thumbnail_url( $post_id );
$allow_image = get_option( 'myclub_groups_page_picture', '1' ) === '1';

?>
<div class="myclub-groups-title">
    <div class="myclub-groups-title-container">
        <div class="myclub-groups-title-box">
            <?php if( $allow_image && !empty( $image_url ) ) { ?>
                <div class="myclub-groups-title-image">
                    <img src="<?=$image_url?>" alt="<?=$title?>">
                </div>
            <?php } ?>
            <div class="myclub-groups-title-information">
                <div class="myclub-groups-title-name<?= empty( $info_text ) ? '' : ' with-info-text' ?>"><?=$title?></div>
                <?php if ( !empty( $info_text ) ) { ?>
                    <div class="myclub-groups-info-text"><?=$info_text?></div>
                <?php }
                if ( !empty( $contact_name ) ) { ?>
                    <div class="myclub-groups-information"><div class="label"><?=__( 'Contact person', 'myclub-groups' )?></div><div class="value"><?=$contact_name?></div></div>
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
</div>
