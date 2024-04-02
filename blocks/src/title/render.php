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
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ) ?>">
                </div>
            <?php } ?>
            <div class="myclub-groups-title-information">
                <div class="myclub-groups-title-name<?php echo empty( $info_text ) ? '' : ' with-info-text' ?>"><?php echo esc_attr( $title ) ?></div>
                <?php if ( !empty( $info_text ) ) { ?>
                    <div class="myclub-groups-info-text"><?php echo esc_attr( $info_text ) ?></div>
                <?php }
                if ( !empty( $contact_name ) ) { ?>
                    <div class="myclub-groups-information"><div class="label"><?php echo esc_attr__( 'Contact person', 'myclub-groups' ); ?></div><div class="value"><?php echo esc_attr( $contact_name ); ?></div></div>
                <?php }
                if ( !empty( $phone ) ) { ?>
                    <div class="myclub-groups-information"><div class="label"><?php echo esc_attr__( 'Telephone', 'myclub-groups' ); ?></div><div class="value"><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_attr( $phone ); ?></a></div></div>
                <?php }
                if ( !empty( $email ) ) { ?>
                    <div class="myclub-groups-information"><div class="label"><?php echo esc_attr__( 'E-mail', 'myclub-groups' ); ?></div><div class="value"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_attr( $email ); ?></a></div></div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
