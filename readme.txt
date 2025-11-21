=== List YARPP Block ===
Contributors: MarcDK
Tags: Gutenberg, block, yarpp, full-site-editing, related posts
Requires at least: 5.9
Donate link: https://marc.tv/out/donate
Tested up to: 6.8
Stable tag: 2.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a block that lists yarpp related posts. 

== Description ==

Adds a block that lists yarpp related posts in Twenty-Twenty-Three. Also provides an option to display the latest posts without the related posts. Works best with full site editing themes.

This block **requires** the [YARPP plugin](https://wordpress.org/plugins/yet-another-related-posts-plugin/) and relies on the *yarpp_get_related()* function.

= Features =

* Gutenberg column width support.
* The block is not visible in the frontend if no related posts are found.
* Minimum html and css that is based on the standard Gutenberg Latest Posts block.
* Valid HTML output.
* Inherits the style of your theme for links and headlines.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/game-review` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Changelog ==

= 2.5 = 
Fix: Width of images is now 100% within the padding limits.

= 2.3 = 
Fix: Image width set to auto.

= 2.2 =
Fix: Target blank broken link
Fix: image width won't be saved.

= 2.1 =
* Feature: New coding standards
* Feature: Automatic WordPress repository deployment with GitHub actions.

= 1.5 =
* Feature: Added option to open links in new tab.

= 1.4 =
* Feature: Added YARPP required notice. 

= 1.2 =
* Fixed: fatal error if not enough related posts are found.

= 1.0 =
* Inital version.

== Screenshots ==
1. List YARPP block in Gutenberg editor.
2. List YARPP block in the Theme Twenty-Twenty-Two
3. List YARPP block supports custom width.