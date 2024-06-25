<?php

namespace MyClub\MyClubGroups\Tasks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\BackgroundProcessing\Background_Process;
use MyClub\MyClubGroups\Services\NewsService;
use MyClub\MyClubGroups\Utils;

class RefreshNewsTask extends Background_Process {
    protected $prefix = 'myclub_groups';
    protected $action = 'refresh_news_task';

    private static $instance = null;

    /**
     * Initializes the class if it hasn't been initialized already.
     *
     * @return RefreshNewsTask Returns an instance of the class. If the class has already been initialized, it returns the existing instance.
     */
    public static function init(): RefreshNewsTask {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Refreshes news for the group id sent to the method or for the club if item is null
     *
     * @param mixed $item The groupId of the news to get or null
     * @return mixed returns false to indicate that no further processing is required.
     * @since 1.0.0
     */
    protected function task( $item ) {
        $service = new NewsService();
        $service->load_news( $item );
        return false;
    }

    /**
     * Completes the processing of retrieving news for the group id sent to the method or for the club if item is null
     *
     * @return void
     * @since 1.0.0
     */
    protected function complete()
    {
        parent::complete();
        Utils::update_or_create_option( 'myclub_groups_last_news_sync', gmdate( "c" ), 'no' );
    }
}