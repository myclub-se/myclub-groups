<?php

namespace MyClub\MyClubGroups\Tasks;

use MyClub\MyClubGroups\Services\MenuService;
use WP_Background_Process;

/**
 * Class RefreshMenusTask
 *
 * This class extends the WP_Background_Process class and is responsible for refreshing the group menu items.
 *
 * @since 1.0.0
 */
class RefreshMenusTask extends WP_Background_Process
{
    private static $instance = null;

    /**
     * Initializes the class if it hasn't been initialized already.
     *
     * @return object Returns an instance of the class. If the class has already been initialized, it returns the existing instance.
     */
    public static function init() {
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
        $service->refreshMenus();
        return false;
    }
}