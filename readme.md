# SlavicD's fork of Report Comments for Wordpress
This is a fork of Peter Berglund's [Report Comments](https://wordpress.org/plugins/reportcomments) Wordpress plugin that addresses
some shortcomings of the original plugin. See changelog for more info.

The fork is from version 1.2.3 of the plugin. 

Original readme [file](./readme.txt).

## Changelog

### 1.2.6
* fix plugin not working for ajax-loaded comments

### 1.2.5
* use a separte filter "comment_meta" instead of "comment_text" to append the controls

### 1.2.4
* do not alter comments page in wp-admin
* fix fatal error "Call to undefined function is_user_logged_in()" for members-only setting
