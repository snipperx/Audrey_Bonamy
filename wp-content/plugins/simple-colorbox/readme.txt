=== Simple Colorbox ===
Contributors: ryanhellyer
Donate link: https://geek.hellyer.kiwi/donate/
Tags: colorbox, simple, lightbox, hover, layover, slideshow, jquery, image, images, picture, pictures, gallery, galleries
Requires at least: 3.9
Tested up to: 4.4
Stable tag: 1.6.1

Description: Adds a very simple Colorbox to your linked images.

== Description ==

Adds a very simple Colorbox to your linked images. This plugin is intended as an easy to use alternative to other lightbox / Colorbox 
alternatives. Unlike most other plugins with similar functionality, this one does not have any settings or requirements whatsoever, 
simple install, activate and yer done :)

<strong>More information can be found on the <a href="https://geek.hellyer.kiwi/products/simple-colorbox/">Simple Colorbox plugin page</a>.</strong>

== Installation ==

Simply install and activate the plugin. No settings necessary.

= Advanced users (if you don't understand this just ignore it) =
Fine grained control of how the Colorbox works can be achieved through the use of filters. You can find the filters and associated documentation inside the plugin files. Modifying how the plugin works by usign these filters is for developers only and is not aimed at beginners.

== Frequently Asked Questions ==

= Why should I use this plugin? =

If you want an uber simple easy to use (and extensible) Colorbox solution.

= Does it work for WordPress version x.x.x? =

I only provide support for the latest version of WordPress.

= Can I use this with anything but images? =

Yes. Anything which you link to with a class of .colorbox should appear in a Colorbox.

= How do I add image captions? =
Simply add title tags toy our images.

= Why did the image captions in the plugin break when updating WordPress? =
It didn't! WordPress altered the way the media uploader handled title tags, which in turn confused users who were attempting to add image captions via the media uploader. The plugin did, does and should keep working with captions into the future.

== Changelog ==

= 1.6.1 =
* Support for latest versions of WordPress

= 1.6 =
* Upgraded to latest version of Colorbox

= 1.5.2 =
* Added global variable to allow for modification of functionality
* Improved some variable names ready for stable release

= 1.5.1 beta =
* Moved inline script to wp_localize_script

= 1.5 beta =
* Upgraded to the latest version of Colorbox
* Added translation support
* Added Bokmål translation
* Replaced the old constants configuration with a more extensible filter system
* Simplified the naming of some methods
* Placed all constant declarations into a single method

= 1.4 beta =
* Added support for .colorbox class

= 1.3.1 =
* Returned files which disappeared in version 1.3

= 1.3 =
* Instantiated class into variable for easy unhooking of CSS
* Upgraded the plugin documentation

= 1.2.4 =
* Added missing files back in

= 1.2.3 =
* Added missing files back in

= 1.2.1 =
* Minor upgrade
* Corrected grossly incorrect documentation in PHP file
* No need to upgrade if you don't want to

= 1.2 =
* Added support for slideshows

= 1.1 =
* Added support for BMP files
* Added support for uppercase file extensions

= 1.0.1 =
* Repair of corrupted initial commit

= 1.0 =
* Initial plugin release

== Credits ==

Thanks to the following (in no particular order) for help with the development of this plugin:<br />
* <a href="http://t.co/hCpn8iLgFo">Milan Dinić</a> - Added extensive upgrade patch for increasing extensibility<br />
* <a href="http://arnsteinlarsen.no/">Arnstein Larsen</a> - Motivated me to add slideshow support<br />
* <a href="http://utkar.sh/">Utkarsh</a> - Assistance with jQuery bug<br />
* <a href="http://ronalfy.com/">Ronalfy</a> - Suggested I use Colorbox<br />
