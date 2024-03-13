<?php

namespace MyClub\MyClubGroups\Services;

/**
 * Class Base
 *
 * Represents a base class with plugin path and URL properties.
 */
class Base
{
    protected $pluginPath;
    protected $pluginUrl;

    /**
     * Class constructor.
     *
     * Initializes the class by setting the plugin path and URL.
     *
     * @return void
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->pluginPath = plugin_dir_path( dirname( __FILE__, 2 ) );
        $this->pluginUrl = plugin_dir_url( dirname( __FILE__, 2 ) );
    }
}