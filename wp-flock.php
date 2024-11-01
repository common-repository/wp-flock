<?php
/*
Plugin Name: WP-Flock
Plugin URI: http://beta.void-star.net/projects/wp-flock/
Description: Basic, LiveJournal-style f-locking for WordPress posts and pages. Now with JournalPress support.
Version: 0.1.1
Author: Alis Dee
Author URI: http://void-star.net/

	Copyright (c) 2008 Alis Dee

	Permission is hereby granted, free of charge, to any person obtaining a
	copy of this software and associated documentation files (the "Software"),
	to deal in the Software without restriction, including without limitation
	the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the
	Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
	DEALINGS IN THE SOFTWARE.
*/

// this doesn't do anything at the moment; it's mostly just a placeholder
if( !defined( 'FL_DOMAIN' ) )
  define( 'FL_DOMAIN', '/wp-flock/lang/wp-flock' );
if( function_exists( 'load_plugin_textdomain' ) )
  load_plugin_textdomain( FL_DOMAIN );

if( !defined( 'FLDIR' ) )
  define( 'FLDIR', dirname(__FILE__) );

// include our other flock files
	include_once( FLDIR . '/flinstall.php' );
	include_once( FLDIR . '/flconfig.php' );
	include_once( FLDIR . '/flfunctions.php' );

//** INITIAL STUFFS **************************************************//
// add the extra databases 
$wpdb->flgroups = $wpdb->prefix . 'fl_groups';
$wpdb->flcaps = $wpdb->prefix . 'fl_caps';

// activation and deactivation
register_activation_hook( __FILE__, 'fl_install' );
register_deactivation_hook( __FILE__, 'fl_uninstall' );

// menu pages
add_action( 'admin_menu', 'fl_add_pages' );
add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), 'fl_links' );

// user pages
add_action( 'show_user_profile', 'fl_user_groups' );
add_action( 'edit_user_profile', 'fl_user_groups' );
add_action( 'profile_update', 'fl_update_groups' );

// initial options
add_option( 'fl_installed' );

// filters
add_filter( 'status_save_pre', 'fl_status_save' );
add_filter( 'user_has_cap', 'fl_has_cap', 10, 3 );
add_filter( 'query', 'fl_query' );

add_filter( 'the_content_rss', 'fl_content_rss' );
add_filter( 'the_excerpt_rss', 'fl_content_rss' );

// hookit!
// for proper JournalPress integration, this -has- to run early.
add_action( 'save_post', 'fl_save', 1 );

?>