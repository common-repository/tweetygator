<?php
/*
Plugin Name: TweetyGator
Plugin URI: http://theanalogguy.be/software/wordpress/plugin/tweetygator
Description: A twitter aggregator
Version: 0.1
Author: Tom Van Herreweghe
Author URI: http://theanalogguy.be
License: GPL2
*/

/*  Copyright 2010  Tom Van Herreweghe  (email : tom@theanalogguy.be)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('TweetyGator')) {
    require_once 'includes/TweetyGator.php';
    require_once 'includes/TweetyGatorWidget.php';
    require_once 'includes/TweetyGatorTemplate.php';
    $tweetyGator = new TweetyGator();
}

if (isset($tweetyGator)) {
    /**
     * STYLES
     */
    $myStyleUrl = WP_PLUGIN_URL . '/tweetygator/css/style.css';
    $myStyleFile = WP_PLUGIN_DIR . '/tweetygator/css/style.css';
    if ( file_exists($myStyleFile) ) {
        wp_register_style('myStyleSheets', $myStyleUrl);
        wp_enqueue_style( 'myStyleSheets');
    }

    add_action('init', array(&$tweetyGator, 'initWidget'), 1);
    add_action('admin_menu', array(&$tweetyGator, 'adminMenu'));
    add_action('admin_init', array(&$tweetyGator, 'initAdmin'));
}

/**
 * Shows the main admin page for TweetyGator
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_show_settings_page ()
{
    global $tweetyGator;

    $tweetyGator->showSettingsPage();
}

/**
 * Display the main options section in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_section_text ()
{
    global $tweetyGator;

    $tweetyGator->showSettingsSection(TweetyGator::SECTION_MAIN);
}

/**
 * Display the main options section in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_section_misc_text ()
{
    global $tweetyGator;

    $tweetyGator->showSettingsSection(TweetyGator::SECTION_MISC);
}

/**
 * Show the user field in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_show_users_field ()
{
    global $tweetyGator;

    $tweetyGator->showField(TweetyGator::FIELD_USERS);
}

/**
 * Show the hashes field in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_show_hashes_field ()
{
    global $tweetyGator;

    $tweetyGator->showField(TweetyGator::FIELD_HASHES);
}

/**
 * Show the user field in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_show_keywords_field ()
{
    global $tweetyGator;

    $tweetyGator->showField(TweetyGator::FIELD_KEYWORDS);
}

/**
 * Show the user field in the admin panel
 *
 * @global TweetyGator $tweetyGator
 */
function tweetygator_show_cache_field ()
{
    global $tweetyGator;

    $tweetyGator->showField(TweetyGator::FIELD_CACHE);
}

/**
 * Validate the incoming options
 */
function tweetygator_options_validate ($input)
{
    global $tweetyGator;

    $newinput = $tweetyGator->validateOptions($input);

    return $newinput;
}