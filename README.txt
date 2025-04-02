=== MyClub Groups ===
Contributors: myclubse
Donate link: https://www.myclub.se
Tags: groups, members, administration
Requires at least: 6.4
Tested up to: 6.7.1
Stable tag: 1.3.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Retrieves group information from the MyClub member administration platform. Generates pages for groups defined in the MyClub platform.

== Description ==

This plugin is intended for associations and organizations that use the MyClub membership system and need to fetch data to display on their website. Examples of such data include calendars, news, and groups.

Please ensure that your server is running on PHP 7.4 or higher and your WordPress version is at least 6.4 to utilize this plugin fully.

=== Components ===
The components fetch objects from MyClub and store them in WordPress. These objects are retrieved continuously and automatically, but not in real time. You can choose to display the various components in different places on your website. The available components are:
* News
* Calendar
* Upcoming Activities
* Groups
* Group Members
* Group Menu

See the blocks below for more information.

Within MyClub, you configure which groups and in what order they should be displayed for the plugin. The plugin can then create a menu so visitors can navigate between the association's groups.

=== Appearance ===
All components are minimally designed to make them easier to customize and fit your website’s design. All headers, tables, images, and similar items have their own CSS classes, allowing you to style them according to your preferences.

== Dependencies ==

The plugin has no external plugin dependencies. All requirements are bundled in the plugin itself. However we are using the following opensource library (which is included in the plugin):
* FullCalendar (v5.11.5), which can be seen [here](https://fullcalendar.io/). All source to the plugin is available [here](https://github.com/fullcalendar/fullcalendar). No data is being sent to the FullCalendar plugin website.

== Installation ==

To fetch data from MyClub, you must first install this plugin:
1. Login to your WordPress Dashboard
2. Go to Plugins -> Add New
3. Search for MyClub groups plugin
4. Install the MyClub groups plugin
5. Activate it.
6. Add your API key to the plugin settings.

You can generate an API key within MyClub under Productions and prices in MyClub. Please note that once the key is generated, you need to save it immediately and paste it into the newly installed plugin.

Once the plugin is installed with the API key, you can begin using it. The plugin consists of various components that can be added to any page via either Gutenberg blocks, Shortcodes, or group-specific pages that are designed using a template and then applied to all groups. For example, you can place a calendar at the top and news below it. This setup will look the same across all group pages, but the plugin dynamically determines which calendar to display based on the group currently being viewed.

== Frequently Asked Questions ==

=== Caching ===
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

For unsupported cache systems, please contact us to request integration.

== Changelog ==
= 1.3.5 =
* Rewrote handling of post datetime

= 1.3.4 =
* Add more descriptions to settings pages

= 1.3.3 =
* Add caption to news images (if present)
* Add ability to remove news posts from WordPress if deleted in MyClub
* Fixed bug in news block handling

= 1.3.2 =
* Add handling for HTML on calendar events
* Fix bug in last sync dates

= 1.3.1 =
* Add setting for creating news categories for group news

= 1.3.0 =
* Add club calendar.
* Update media handling.
* Minor bugfixes.

= 1.2.6 =
* Fixed bug in calendar block.

= 1.2.5 =
* Minor CSS changes

= 1.2.4 =
* Rewrote coming games block to fix all bugs.

= 1.2.3 =
* Fixed bug in coming games block.

= 1.2.2 =
* Updated readme

= 1.2.1 =
* Updated readme

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
= 1.2.0 =
Ensure caching plugins are configured properly for updates to dynamic page clearing.

= 1.1.0 =
Dynamic fields added for certain pop-ups; no critical changes required.
