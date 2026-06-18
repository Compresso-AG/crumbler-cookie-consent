<?php
/**
 * Uninstall routine for Crumbler – Cookie Consent.
 *
 * Removes all plugin options when the plugin is deleted via the WordPress
 * admin. No data is sent anywhere; this only cleans up the local database.
 *
 * @package Crumbler_Cookie_Consent
 */

// Exit if accessed directly or not called by WordPress during uninstall.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$crumbler_cc_options = array(
    'crumbler_cc_enabled',
    'crumbler_cc_site_key',
    'crumbler_cc_language',
    'crumbler_cc_widget_url',
    'crumbler_cc_hide_for_admins',
);

foreach ($crumbler_cc_options as $crumbler_cc_option) {
    delete_option($crumbler_cc_option);
}
