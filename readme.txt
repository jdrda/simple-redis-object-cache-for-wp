=== Simple Redis Object Cache for WP ===
Contributors: Jan Drda
Tags: redis, cache, object cache
Requires at least: 5.0
Tested up to: 6.5.2
Requires PHP: 7.0
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin changes transient storage from SQL to Redis, improving performance of your WordPress website.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. No configuration is needed. Plugin will automatically use Redis as transient storage.

== Frequently Asked Questions ==

= Does this plugin require PHP Redis extension? =

Yes, this plugin requires PHP Redis extension to be installed and enabled on your server.

= How do I know if PHP Redis extension is installed? =

You can check if PHP Redis extension is installed by going to WordPress dashboard > Tools > Site Health > Info tab > Server section.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
This is the initial release.