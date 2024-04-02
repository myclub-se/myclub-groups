<?php

use MyClub\MyClubGroups\Utils;

function render_meta_data_date( $post, $label, $name )
{
    $value = Utils::format_date_time( get_post_meta( $post, $name, true ) );

    echo '<div class="metadata-wrap">';
    echo '<p class="post-attributes-label-wrapper">';
    echo '<label class="post-attributes-label" for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label>';
    echo '</p>';
    echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" readonly class="widefat" />';
    echo '</div>';
}

/**
 * Render a read only text input box displayed in the meta box on group pages.
 *
 * @return void
 */
function render_meta_data_text( $post, $label, $name )
{
    $value = get_post_meta( $post, $name, true );

    echo '<div class="metadata-wrap">';
    echo '<p class="post-attributes-label-wrapper">';
    echo '<label class="post-attributes-label" for="' . esc_attr( $name ) . '">' . esc_attr( $label ) . '</label>';
    echo '</p>';
    echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" readonly class="widefat" />';
    echo '</div>';
}

/**
 * Render a read only textarea displayed in the meta box on group pages.
 *
 * @return void
 */
function render_meta_data_textarea( $post, $label, $name )
{
    $value = get_post_meta( $post, $name, true );

    echo '<div class="metadata-wrap">';
    echo '<p class="post-attributes-label-wrapper">';
    echo '<label class="post-attributes-label" for="' . esc_attr( $name ) . '">' . esc_attr( $label ) . '</label>';
    echo '</p>';
    echo '<textarea id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" readonly class="widefat" rows="10">' . esc_attr( $value ) . '</textarea>';
    echo '</div>';
}

$post = get_the_ID();

?>
    <div id="myclub-tabs">
    <ul>
        <li class="tabs"><a href="#myclub-tab1"><?php esc_attr_e( 'Standard information', 'myclub-groups' ) ?></a></li>
        <li class="tabs"><a href="#myclub-tab2"><?php esc_attr_e( 'Other information', 'myclub-groups' ) ?></a></li>
        <li class="tabs"><a href="#myclub-tab3"><?php esc_attr_e( 'Members', 'myclub-groups' ) ?></a></li>
        <li class="tabs"><a href="#myclub-tab4"><?php esc_attr_e( 'Activities', 'myclub-groups' ) ?></a></li>
    </ul>
    <div id="myclub-tab1" class="tabs-panel">
<?php
// All of these fields are readonly and will not be saved on post save.
render_meta_data_text( $post, __( 'MyClub group id', 'myclub-groups' ), 'myclub_group_id' );
render_meta_data_date( $post, __( 'Last updated', 'myclub-groups' ), 'last_updated' );
render_meta_data_text( $post, __( 'Contact person', 'myclub-groups' ), 'contact_name' );
render_meta_data_text( $post, __( 'E-mail address', 'myclub-groups' ), 'email' );
render_meta_data_text( $post, __( 'Phone', 'myclub-groups' ), 'phone' );
?>
    </div>
    <div id="myclub-tab2" class="hidden tabs-panel">
<?php
render_meta_data_textarea( $post, __( 'Other information', 'myclub-groups' ), 'info_text' );
?>
    </div>
    <div id="myclub-tab3" class="hidden tabs-panel">
<?php
require_once( $this->plugin_path . '/templates/admin/admin_myclub_groups_members.php' );
?>
    </div>
    <div id="myclub-tab4" class="hidden tabs-panel">
<?php
require_once( $this->plugin_path . '/templates/admin/admin_myclub_groups_activities.php' );
?>
    </div>
</div>
