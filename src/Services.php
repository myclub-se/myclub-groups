<?php

namespace MyClub\MyClubGroups;

use MyClub\MyClubGroups\Services\Admin;
use MyClub\MyClubGroups\Services\Api;
use MyClub\MyClubGroups\Services\Blocks;
use MyClub\MyClubGroups\Services\Menu;
use MyClub\MyClubGroups\Services\MyClubCron;
use MyClub\MyClubGroups\Services\ShortCodes;
use MyClub\MyClubGroups\Services\Taxonomy;

/**
 * Class Services
 *
 * This class is responsible for registering and instantiating services.
 *
 */
class Services
{
    const SERVICES = [
        Admin::class,
        Api::class,
        Blocks::class,
        Menu::class,
        MyClubCron::class,
        ShortCodes::class,
        Taxonomy::class,
    ];

    /**
     * Registers all services.
     *
     * This method iterates through the services obtained from the `SERVICES` constant and instantiates each service.
     * If the instantiated service has a `register` method, it is called to register the service.
     *
     * @return void
     * @since 1.0.0
     */
    public static function registerServices()
    {
        foreach ( self::SERVICES as $class ) {
            $service = new $class();
            if ( method_exists( $service, 'register' ) ) {
                $service->register();
            }
        }
    }
}