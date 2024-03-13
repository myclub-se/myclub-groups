<?php
$members = json_decode( get_post_meta( get_the_ID(), 'members', true ) );
?>

<div class="member-box">
    <table class="members-table">
        <tr>
            <th><?php _e( 'Name', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Role', 'myclub-groups' ) ?></th>
            <th><?php _e( 'E-mail', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Phone', 'myclub-groups' ) ?></th>
            <th><?php _e( 'Age', 'myclub-groups' ) ?></th>
        </tr>
        <tr>
            <td colspan="5" class="member-title"><?php _e( 'Members', 'myclub-groups' ) ?></td>
        </tr>
        <?php foreach ( $members->members as $member ) { ?>
            <tr>
                <td><?= $member->name ?></td>
                <td><?= $member->role ?></td>
                <td><?= $member->email ?></td>
                <td><?= $member->phone ?></td>
                <td><?= $member->age ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="5" class="member-title"><?php _e( 'Leaders', 'myclub-groups' ) ?></td>
        </tr>
        <?php foreach ( $members->leaders as $leader ) { ?>
            <tr>
                <td><?= $leader->name ?></td>
                <td><?= $leader->role ?></td>
                <td><?= $leader->email ?></td>
                <td><?= $leader->phone ?></td>
                <td><?= $leader->age ?></td>
            </tr>
        <?php } ?>
    </table>
</div>