<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$members = json_decode( get_post_meta( get_the_ID(), 'myclub_groups_members', true ) );
?>
<div class="member-box">
    <table class="members-table">
        <tr>
            <th><?php esc_attr_e( 'Name', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Role', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'E-mail', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Phone', 'myclub-groups' ); ?></th>
            <th><?php esc_attr_e( 'Age', 'myclub-groups' ); ?></th>
        </tr>
        <?php
        if ( !empty( $members ) && property_exists( $members, 'members' ) ) {
            ?>
            <tr>
                <td colspan="5" class="member-title"><?php esc_attr_e( 'Members', 'myclub-groups' ); ?></td>
            </tr>
        <?php
            foreach ( $members->members as $member ) { ?>
                <tr>
                    <td><?php echo esc_attr( str_replace( 'u0022', '"', $member->name ) ); ?></td>
                    <td><?php echo esc_attr( $member->role ? str_replace( 'u0022', '"', $member->role ) : '' ); ?></td>
                    <td><?php echo esc_attr( $member->email ); ?></td>
                    <td><?php echo esc_attr( $member->phone ); ?></td>
                    <td><?php echo esc_attr( $member->age ); ?></td>
                </tr>
            <?php }
        }

        if ( !empty( $members ) && property_exists( $members, 'leaders' ) ) {
        ?>
        <tr>
            <td colspan="5" class="member-title"><?php esc_attr_e( 'Leaders', 'myclub-groups' ); ?></td>
        </tr>
        <?php foreach ( $members->leaders as $leader ) { ?>
            <tr>
                <td><?php echo esc_attr( str_replace( 'u0022', '"', $leader->name ) ); ?></td>
                <td><?php echo esc_attr( $leader->role ? str_replace( 'u0022', '"', $leader->role ) : '' ); ?></td>
                <td><?php echo esc_attr( $leader->email ); ?></td>
                <td><?php echo esc_attr( $leader->phone ); ?></td>
                <td><?php echo esc_attr( $leader->age ); ?></td>
            </tr>
        <?php }
        }
        ?>
    </table>
</div>
