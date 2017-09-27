=== Report Comments ===

Contributors: lefalque
Version: 1.2.3
Tags: comments, admin, ajax
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.2.3
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gives visitors the possibility to report inappropriate comments. Reported comments will show up in admin where they may be reviewed.

== Description == 

Adds a link next to the reply link below each comment, which allows visitors to flag comments as inappropriate. A sub page to comments in admin is added, where an administrator may review all the flagged comments and decide if they should be removed or not.

= Features =

* Ability for visitors to report comments they find offensive.
* Once a flagged comment has been deemed ok, it wont be able to be flagged again.
* Flagging is done via ajax for smoother experience for the visitors.
* Decide whether all visitors or only logged in users can report comments.
* Fully localized. Comes with English and Swedish translations.

== Installation ==

1. Install and activate **Report Comments** via the WordPress.org repository.
2. Flag comments at the front end.
3. Review flagged comments in wp-admin.

== Changelog == 

= 1.2.3 =
- Changed filter for how the report link was shown, now it appears even when threads are on their lowest level. You may need to style it (.report-comments) depending on your theme.
- Removed requirement for PHP 5.3.0.

= 1.2 =
- Added option to only let logged in users report comments (located at WordPress discussion settings).
- Added link to edit any reported comments.
- Added filter (report_comments_strings) to use if you want to change wording of any or all messages used by the plugin.

= 1.1 =
- Bugfixes.

= 1.0 = 
- Initial release.