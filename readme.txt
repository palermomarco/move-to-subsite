=== Move Post/Page to Subsite ===
Contributors: palermomarco
Tags: move, multisite, migrate, export, import
Requires at least: 3.3.2
Tested up to: 3.6
Stable tag: 0.2

Wordpress multisite plugin to move posts in a category and/or a page hierarchy to a new subsite, with seamless redirects. 
It copy also post attachments.

== Description ==

Many older WP sites used categories and/or pages to organize what were essentially subsites without proper access control and other site-specific goodies like themes, widgets, and plugins.

This plugin lets you move that content to another site on your WordPress network with a single click, with seamless redirects.

I needed it on www.robadadonne.it when moving old contents from root domain to subsites.

This is **beta** software. Back stuff up first.

This plugin is forked from http://wordpress.org/plugins/move-to-subsite/

Please contribute on Github https://github.com/palermomarco/move-to-subsite


== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Screenshots ==

1. The admin page for moving to a subsite.

== Frequently Asked Questions ==

= Why not just use WP's built-in export/import functions? =

Because they won't 1) remove the content on the originating site, 2) handle redirects, or 3) do it so damn fast. And I'm into re-inventing the wheel.

= What about my comments, metadata, and categories/tags? =

What about them? Oh, yeah, I bring that stuff over. It's all good.

= What about mattached images? =

In 0.2 version it bring also attachments.

== Changelog ==

= 0.1 =

* Initial release

= 0.2 =

* Added support for post attachments move

* Bug fixes on redirect
