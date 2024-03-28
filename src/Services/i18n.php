<?php

namespace MyClub\MyClubGroups\Services;

class i18n extends Base
{
    /**
     * Registers the plugin's functionality.
     * This method is responsible for registering the plugin's functionality by adding an action hook to the 'plugins_loaded' event.
     *
     * @return void
     * @since 1.0.0
     */
    public function register()
    {
        add_action( 'plugins_loaded', [ $this, 'load_plugin_text_domain' ] );
    }

    /**
     * Loads the text domain for the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function load_plugin_text_domain()
    {
        load_plugin_textdomain( 'myclub-groups', false,  plugin_basename( dirname( __FILE__, 3 ) ) . '/languages/' );
    }
}