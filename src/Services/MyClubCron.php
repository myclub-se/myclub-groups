<?php

namespace MyClub\MyClubGroups\Services;

/**
 * Class MyClubCron
 *
 * Provides functionality for scheduling cron jobs related to the MyClub plugin.
 */
class MyClubCron {
    /**
     * Register the necessary actions for the plugin.
     *
     * This method registers the 'init' action with the 'setupSchedule' method,
     * and also registers the 'myclub_groups_refresh_news' and 'myclub_groups_refresh_groups'
     * actions with the 'refreshNews' and 'refreshGroups' methods respectively.
     *
     * @return void
     */
    public function register()
    {
        add_action( 'init', [ $this, 'setupSchedule' ] );
        add_action( 'myclub_groups_refresh_news', [ $this, 'refreshNews' ] );
        add_action( 'myclub_groups_refresh_groups', [ $this, 'refreshGroups' ] );
    }

    /**
     * Deactivates the plugin by unscheduling the specified events.
     *
     * The method checks if the 'myclub_groups_refresh_news' event is scheduled and unschedules it if it is.
     *
     * The method also checks if the 'myclub_groups_refresh_groups' event is scheduled and unschedules it if it is.
     *
     * @return void
     */
    public function deactivate()
    {
        if ( wp_next_scheduled( 'myclub_groups_refresh_news' ) ) {
            wp_unschedule_event( time(), 'myclub_groups_refresh_news' );
        }

        if ( wp_next_scheduled( 'myclub_groups_refresh_groups' ) ) {
            wp_unschedule_event( time(), 'myclub_groups_refresh_groups' );
        }
    }

    /**
     * Sets up the schedule for specified events if they are not already scheduled.
     *
     * The method checks if the 'myclub_groups_refresh_news' event is already scheduled.
     * If it is not, it schedules the event to run hourly using the wp_schedule_event() function.
     * It also logs a message to the error log indicating that the event has been scheduled.
     *
     * The method also checks if the 'myclub_groups_refresh_groups' event is already scheduled.
     * If it is not, it schedules the event to run hourly using the wp_schedule_event() function.
     * It also logs a message to the error log indicating that the event has been scheduled.
     *
     * @return void
     */
    public function setupSchedule()
    {
        if ( !wp_next_scheduled( 'myclub_groups_refresh_news' ) ) {
            wp_schedule_event( time(), 'hourly', 'myclub_groups_refresh_news' );
            error_log( 'Scheduling myclub_groups_refresh_news event.' );
        }

        if ( !wp_next_scheduled( 'myclub_groups_refresh_groups' ) ) {
            wp_schedule_event( time(), 'hourly', 'myclub_groups_refresh_groups' );
            error_log( 'Scheduling myclub_groups_refresh_groups event.' );
        }
    }

    public function refreshGroups()
    {
        $service = new GroupService();
        $service->reloadGroups();
    }

    public function refreshNews()
    {
        $service = new NewsService();
        $service->reloadNews();
    }
}
