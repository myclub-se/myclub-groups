<?php

namespace MyClub\MyClubGroups\Services;

use MyClub\Common\Services\BaseActivityService;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class ActivityService
 *
 * A group plugin service responsible for managing activities related to posts and club calendars. It provides methods
 * to create, update, delete, and retrieve activities stored in a database table. It also allows listing of
 * activities in various contexts like club calendars or specific post associations.
 */
class ActivityService extends BaseActivityService
{
    protected static string $activities_table_suffix = 'myclub_groups_activities';
    protected static string $activities_link_table_suffix = 'myclub_groups_post_activities';
    protected static string $activities_unique_key = 'myclub_groups_post_activity_unique';
}