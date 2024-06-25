<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="myclub-groups-menu">
    <div class="myclub-groups-menu-container">
        <div class="mobile-menu-button">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <?php echo wp_nav_menu( array (
            'theme_location' => 'myclub-groups-menu',
            'container'  => false,
            'echo' => false
        ) ); ?>
    </div>
</div>