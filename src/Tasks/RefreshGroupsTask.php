<?php

namespace MyClub\MyClubGroups\Tasks;


use MyClub\MyClubGroups\Utils;
use WP_Background_Process;
use MyClub\MyClubGroups\Services\GroupService;

class RefreshGroupsTask extends WP_Background_Process {
    protected $action = 'myclub_refresh_groups_task';

    private static $instance = null;

    /**
     * Initializes the class if it hasn't been initialized already.
     *
     * @return RefreshGroupsTask Returns an instance of the class. If the class has already been initialized, it returns the existing instance.
     * @since 1.0.0
     */
    public static function init(): RefreshGroupsTask {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Refreshes a group page for the group id sent to the method.
     *
     * @param mixed $item The data to be used by the task.
     * @return bool returns false to indicate that no further processing is required.
     * @since 1.0.0
     */
    protected function task( $item ): bool {
        $service = new GroupService();
        $service->update_group_page( $item );
        return false;
    }

    /**
     * Performs actions to complete the task.
     *
     * This method extends the "complete" method from the parent class and
     * performs additional actions to mark the task as complete. It removes
     * any unused group pages, refreshes the menus, and updates the last
     * groups sync option.
     *
     * @return void
     * @since 1.0.0
     */
    protected function complete()
    {
        parent::complete();

        $service = new GroupService();
        $service->remove_unused_group_pages();

        $process = RefreshMenusTask::init();
        $process->push_to_queue([])->save()->dispatch();

        Utils::update_or_create_option( 'myclub_groups_last_groups_sync', date( "c" ), 'no' );
    }
}