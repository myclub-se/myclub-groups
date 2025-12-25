<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @since      2.1.1
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load the autoloader so we can use our Service classes
if ( file_exists( __DIR__ . '/lib/autoload.php' ) ) {
    require_once __DIR__ . '/lib/autoload.php';
}

use MyClub\MyClubGroups\Activation;

/**
 * Perform the cleanup.
 * We instantiate the Activation class and call its uninstall method,
 * which handles options and data removal.
 */
$activation = new Activation();
$activation->uninstall();
