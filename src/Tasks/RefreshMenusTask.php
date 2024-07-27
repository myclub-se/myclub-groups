<?php

namespace MyClub\MyClubGroups\Tasks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\BackgroundProcessing\Background_Process;
use MyClub\MyClubGroups\Services\MenuService;

/**
 * Class RefreshMenusTask
 *
 * This class extends the Background_Process class and is responsible for refreshing the group menu items.
 *
 * @since 1.0.0
 */
class RefreshMenusTask extends Background_Process
{
    protected $prefix = 'myclub_groups';
    protected $action = 'refresh_menus_task';
    private static $instance = null;

    /**
     * Initializes the class if it hasn't been initialized already.
     *
     * @return RefreshMenusTask Returns an instance of the class. If the class has already been initialized, it returns the existing instance.
     */
    public static function init(): RefreshMenusTask {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Refreshes the group menu items.
     *
     * @param mixed $item The data to be used by the task (empty).
     * @return mixed returns false to indicate that no further processing is required.
     * @since 1.0.0
     */
    protected function task( $item ) {
        $service = new MenuService();
        $service->refresh_menus();
        return false;
    }
}