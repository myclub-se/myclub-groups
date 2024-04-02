<?php

namespace MyClub\MyClubGroups\Services;

/**
 * Class MyClubCron
 *
 * Provides functionality for scheduling cron jobs related to the MyClub plugin.
 */
class MyClubCron
{

    const REFRESH_GROUPS_HOOK = 'myclub_groups_refresh_groups';
    const REFRESH_NEWS_HOOK = 'myclub_groups_refresh_news';

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
        add_action( 'init', [
            $this,
            'setup_schedule'
        ] );
        add_action( MyClubCron::REFRESH_NEWS_HOOK, [
            $this,
            'reload_news'
        ] );
        add_action( MyClubCron::REFRESH_GROUPS_HOOK, [
            $this,
            'reload_groups'
        ] );
    }

    /**
     * Deactivates the plugin by removing the schedule for the specified events.
     *
     * The method checks if the 'myclub_groups_refresh_news' event is scheduled and removes the schedule it if it is.
     *
     * The method also checks if the 'myclub_groups_refresh_groups' event is scheduled and removes the schedule it if it is.
     *
     * @return void
     */
    public function deactivate()
    {
        if ( wp_next_scheduled( MyClubCron::REFRESH_NEWS_HOOK ) ) {
            wp_clear_scheduled_hook( MyClubCron::REFRESH_NEWS_HOOK );
        }

        if ( wp_next_scheduled( MyClubCron::REFRESH_GROUPS_HOOK ) ) {
            wp_clear_scheduled_hook( MyClubCron::REFRESH_GROUPS_HOOK );
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
    public function setup_schedule()
    {
        if ( !wp_next_scheduled( MyClubCron::REFRESH_NEWS_HOOK ) ) {
            wp_schedule_event( time(), 'hourly', MyClubCron::REFRESH_NEWS_HOOK );
        }

        if ( !wp_next_scheduled( MyClubCron::REFRESH_GROUPS_HOOK ) ) {
            wp_schedule_event( time(), 'hourly', MyClubCron::REFRESH_GROUPS_HOOK );
        }
    }

    /**
     * Reloads the groups by reloading them from the MyClub backend.
     *
     * The method creates a new instance of the GroupService class and calls the reloadGroups method on it.
     * This method is responsible for reloading the groups from the data source.
     *
     * @return void
     */
    public function reload_groups()
    {
        $service = new GroupService();
        $service->reload_groups();
    }

    /**
     * Reloads the news by reloading them from the MyClub backend.
     *
     * The method creates a new instance of the NewsService class and calls its reloadNews() method.
     * The reloadNews() method is responsible for reloading the news from the external source.
     *
     * @return void
     */
    public function reload_news()
    {
        $service = new NewsService();
        $service->reload_news();
    }
}
