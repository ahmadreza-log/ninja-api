<?php
/**
 * Uninstall script for Ninja API Explorer Plugin
 * 
 * This file is executed when the plugin is deleted through the WordPress admin.
 * It removes all plugin data from the database and filesystem.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
$options_to_remove = [
    'ninja_api_explorer_version',
    'ninja_api_explorer_settings',
    'ninja_api_explorer_cache',
    'ninja_api_explorer_stats'
];

foreach ($options_to_remove as $option) {
    delete_option($option);
}

// Remove plugin transients
$transients_to_remove = [
    'ninja_api_explorer_routes_cache',
    'ninja_api_explorer_stats_cache',
    'ninja_api_explorer_openapi_cache'
];

foreach ($transients_to_remove as $transient) {
    delete_transient($transient);
}

// Clear any cached data
wp_cache_flush();

// Remove custom tables
global $wpdb;

$tables_to_remove = [
    $wpdb->prefix . 'ninja_api_explorer_logs',
    $wpdb->prefix . 'ninja_api_explorer_cache'
];

foreach ($tables_to_remove as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Remove user meta
$wpdb->delete(
    $wpdb->usermeta,
    array('meta_key' => 'ninja_api_explorer_preferences')
);

// Remove any scheduled events
wp_clear_scheduled_hook('ninja_api_explorer_cleanup_cache');
wp_clear_scheduled_hook('ninja_api_explorer_cleanup_logs');
wp_clear_scheduled_hook('ninja_api_explorer_update_stats');

// Log the uninstall (optional)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Ninja API Explorer Plugin has been uninstalled and all data has been removed.');
}
