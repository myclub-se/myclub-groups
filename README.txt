=== MyClub Groups ===
Contributors: myclubse
Donate link: https://www.myclub.se
Tags: groups, members, administration
Requires at least: 6.4
Tested up to: 6.7.1
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin retrieves group information from the MyClub member administration platform (https://member.myclub.se) and generates pages for groups defined in the platform.

== Description ==

The MyClub Groups plugin is designed to retrieve group information from the MyClub member administration platform. It automatically generates WordPress pages for each group, making it easy to manage and display organized data for all groups in your club. The plugin is dependent on you having an account and subscription to the MyClub member administration platform (https://member.myclub.se). Without a subscription and an API key to the MyClub member administration platform, the plugin will not work. For terms of usage please see [https://www.myclub.se/customeragreement](https://www.myclub.se/customeragreement)

The plugin automatically retrieves group information from the MyClub member administration platform and creates pages for all groups defined in your WordPress settings in MyClub. You can also use both Gutenberg blocks and shortcodes for adding data on other pages.

For the calendar views the plugin uses the open source version of the FullCalendar (v.5.11.5) plugin, which can be seen [here](https://fullcalendar.io/). All source to the plugin is available [here](https://github.com/fullcalendar/fullcalendar). No data is being sent to the FullCalendar plugin website.

Please ensure that your server is running on PHP 7.4 or higher and your WordPress version is at least 6.4 to utilize this plugin fully.

The plugin will try to clear cache on the following cache plugins for MyClub groups:
* Breeze
* Cache Enabler
* Hummingbird performance
* Hyper Cache
* LiteSpeed Cache
* SiteGround Optimizer
* Swift Performance
* WP Fastest Cache
* WP Rocket
* WP Super Cache
* W3 Total Cache
* Redis or Memcache cache

If you have any other cache solution, please reach out and we can try to add support for that as well.

== Dependencies ==

The plugin has no external plugin dependencies. All requirements are bundled in the plugin itself.

== Installation ==

1. Login to your WordPress Dashboard
2. Go to Plugins -> Add New
3. Search for MyClub groups plugin
4. Install the MyClub groups plugin
5. Activate it.
6. Add your API key to the plugin settings.

== Changelog ==
= 1.2.0 =
* Add code to clear cache on pages.
* Show role on member blocks

= 1.1.0 =
* Add dynamic fields to member and leader popups

= 1.0.6 =
* Updated how group pages that aren't in the menu are handled

= 1.0.5 =
* Check and update compatibility with WordPress 6.7
* Update so that all news items are removed when uninstalling the plugin

= 1.0.4 =
* Fixed how synchronization progress is shown.
* Add more declarative readme file.
* Updated calendar handling for mobile.
* Fixed bug in group menu on mobile.

= 1.0.3 =
* Bugfixes

= 1.0.2 =
* Add Gutenberg block source

= 1.0.1 =
* Fixed Gutenberg block inclusion

= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.4 =
* No issues.

= 1.0.3 =
* No issues.

= 1.0.2 =
* No issues.

= 1.0.1 =
* No issues.

= 1.0.0 =
* Initial release.
