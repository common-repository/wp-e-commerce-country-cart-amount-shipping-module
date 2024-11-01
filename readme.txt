=== Plugin Name ===
Contributors: leewillis77
Tags: e-commerce, shipping
Requires at least: 2.8
Tested up to: 3.0.4
Stable tag: 1.2

Shipping module for the WP E-Commerce system that offers a matrix of countries, and cart amounts.

== Description ==

Shipping module for the WP E-Commerce system that offers a matrix of countries, and cart amounts, e.g.

U.K.

* &pound;0 and over - &pound;5.00
* &pound;10 and over - &pound;2.00
* &pound;25 and over - &pound;0.00

France

* &pound;0 and over - &pound;15.00
* &pound;10 and over - &pound;12.00
* &pound;25 and over - &pound;10.00

*NOTE:* This plugin is not compatible with the 3.8 series of WP e-Commerce.

== Installation ==

*You Must* already have the following plugin installed:

1. [WP e-Commerce](http://wordpress.org/extend/plugins/wp-e-commerce/)

Support for the right hooks is only available in 3.7.6 beta 3 or newer of WP E-Commerce. If you need to use this on an earlier version you'll need to apply a small change to core WP E-Commerce. The line to add is [documented here](http://plugins.trac.wordpress.org/changeset/198151/wp-e-commerce/trunk/wp-shopping-cart.php)

Make sure that the shipping method is selected ( Products >> Settings >> Shipping - Tick "Country / Cart Amount Shipping" )

Configure layers and rates for the coutries that you want to ship to.

Note: Your browser must support Javascript, and you must have it enabled to configure the shipping rates.

== Frequently Asked Questions ==

* I installed it, but nothing is showing up in my shipping settings?
Support for the right hooks is only available in 3.7.6 beta 3 or newer of WP E-Commerce. If you need to use this on an earlier version you'll need to apply a small change to core WP E-Commerce. The line to add is [documented here](http://plugins.trac.wordpress.org/changeset/198151/wp-e-commerce/trunk/wp-shopping-cart.php)

* This plugin is NOT compatible with the 3.8 series of WP e-Commerce

== Screenshots ==


== Changelog ==

= 1.2 = 
* Don't insist on saving empty rates

= 1.0 =
* Initial Release.
* Development kindly sponsored by [nostromo.nl web design](http://www.nostromo.nl/)
