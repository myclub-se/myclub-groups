<?php

/*
Plugin Name: MyClub Groups
Plugin URI: https://github.com/myclub-se/myclub-groups
Description: Retrieves group information from the MyClub member administration platform. Generates pages for groups defined in the MyClub platform.
Version: 2.1.1
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 7.4
Author: MyClub AB
Author URI: https://www.myclub.se
Text Domain: myclub-groups
Domain Path: /languages
License: GPLv2 or later
*/

use MyClub\MyClubGroups\Activation;
use MyClub\MyClubGroups\Migration;
use MyClub\MyClubGroups\Services;
use MyClub\MyClubGroups\Tasks\ImageTask;
use MyClub\MyClubGroups\Tasks\RefreshGroupsTask;
use MyClub\MyClubGroups\Tasks\RefreshMenusTask;
use MyClub\MyClubGroups\Tasks\RefreshNewsTask;

defined( 'ABSPATH' ) or die( 'Access denied' );

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    exit( "This plugin requires PHP 7.4 or higher. You're still on PHP " . PHP_VERSION );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/lib/autoload.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . '/lib/autoload.php' );
}

define( 'MYCLUB_GROUPS_PLUGIN_VERSION', '2.1.1' );

ImageTask::init();
RefreshGroupsTask::init();
RefreshMenusTask::init();
RefreshNewsTask::init();
Services\ActivityService::init();
Services\MemberService::init();

if ( file_exists( plugin_dir_path( __FILE__ ) . '/src/Activation.php' ) ) {
    function myclub_groups_activate()
    {
        $activation = new Activation();
        $activation->activate();
    }

    // Register activation code
    register_activation_hook( __FILE__, 'myclub_groups_activate' );

    function myclub_groups_deactivate()
    {
        $activation = new Activation();
        $activation->deactivate();
    }

    // Register deactivation code
    register_deactivation_hook( __FILE__, 'myclub_groups_deactivate' );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/src/Migration.php' ) ) {
    function myclub_groups_migration() {
        Migration::checkMigrations();
    }

    add_action( 'plugins_loaded', 'myclub_groups_migration');
}

if ( file_exists( plugin_dir_path( __FILE__) . '/src/Services.php' ) ) {
    // Register all plugin functionality
    Services::registerServices();
}
