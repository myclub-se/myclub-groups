<?php

/*
Plugin Name: MyClub Groups
Plugin URI: https://github.com/myclub-se/myclub-groups
Description: Retrieves group information from the MyClub member administration platform. Generates pages for groups defined in the MyClub member administration platform.
Version: 1.0
Requires at least: 6.4
Requires PHP: 7.4
Author: MyClub AB
Author URI: https://www.myclub.se
Text Domain: myclub-groups
Domain Path: /languages
License: GPLv2 or later
*/

use MyClub\MyClubGroups\Activation;
use MyClub\MyClubGroups\Services;
use MyClub\MyClubGroups\Tasks\ImageTask;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Tasks\RefreshMenusTask;
use MyClub\MyClubGroups\Tasks\RefreshNewsTask;

defined( 'ABSPATH' ) or die( 'Access denied' );

if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
    exit( "This plugin requires PHP 7.4 or higher. You're still on PHP " . PHP_VERSION );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/lib/autoload.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . '/lib/autoload.php' );
}

ImageTask::init();
RefreshGroupsTask::init();
RefreshMenusTask::init();
RefreshNewsTask::init();

if ( file_exists( plugin_dir_path( __FILE__ ) . '/src/Activation.php' ) ) {
    function activate_myclub_groups()
    {
        $activation = new Activation();
        $activation->activate();
    }

    // Register activation code
    register_activation_hook( __FILE__, 'activate_myclub_groups' );

    function deactivate_myclub_groups()
    {
        $activation = new Activation();
        $activation->deactivate();
    }

    // Register deactivation code
    register_deactivation_hook( __FILE__, 'deactivate_myclub_groups' );

    function uninstall_myclub_groups()
    {
        $activation = new Activation();
        $activation->uninstall();
    }

    // Register uninstall code
    register_uninstall_hook( __FILE__, 'uninstall_myclub_groups' );
}

if ( file_exists( plugin_dir_path( __FILE__) . '/src/Services.php' ) ) {
    // Register all plugin functionality
    Services::register_services();
}
