=== WP-Flock ===
Contributors: alisdee
Tags: admin, post, posts, privacy, users, permissions, pages
Requires at least: 2.7.1
Tested up to: 2.7.1
Stable tag: 0.1.1

A plugin that provides LiveJournal-like custom security groups for posts and pages.

== Description ==

**WP-Flock** is a plugin that provides LiveJournal-like custom security groups for posts and pages. It's more flexible than Post Levels, and less complicated than Role Scoper. Plus it hooks into [JournalPress](http://beta.void-star.net/projects/journalpress/); what more could you want?

It is currently in its "stable beta" stage, and as such some features may not be available or a little wonky.

The latest updates about the plug-ins development can be found [in the project blog](http://beta.void-star.net/category/geeking/wordpress/wp-flock/ "WP-Flock @ beta.void-star.net").

= Version 0.1 =
* It's alive! It's alive!
* Basic friends-locking group functionality completed.

== Installation ==

1. Upload the `wp-flock` folder to the `/wp-content/plugins/` directory;
1. Activate the plugin through the 'Plugins' menu in WordPress; and
1. Visit **Settings > User Groups** to configure your overall post settings;
1. ...
1. Profit!

== Frequently Asked Questions ==

= Do you support MU? =

No. It's on the list.

= How do I find my LiveJournal group bitmask? =

With difficulty. You have to view-source on the **Update Journal** page, then look for the list with ids like `custom_bit_xx`. That final number is the bitmask *for that group*.

Yeah, I know it's awkward. I'm hoping to streamline it in future releases.