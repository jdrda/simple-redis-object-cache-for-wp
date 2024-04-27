<?php
/*
Plugin Name: Simple Redis Object Cache for WP
Plugin URI: https://github.com/jdrda/simple-redis-object-cache-for-wp
Description: This plugin changes transient storage from SQL to Redis.
Version: 1.0
Author: Jan Drda
Author URI: https://www.onepix.cz
License: MIT
Text Domain: simple-redis-object-cache-for-wp
Requires PHP: 7.0
Requires at least: 5.0
Requires PHP Redis extension: Yes
Tested up to: 6.5.2
Support: https://github.com/jdrda/simple-redis-object-cache-for-wp/issues
GitHub Plugin URI: https://github.com/jdrda/simple-redis-object-cache-for-wp
Changelog URI: https://github.com/jdrda/simple-redis-object-cache-for-wp/releases
*/

// Check if PHP Redis extension is loaded
if (!extension_loaded('redis')) {
    add_action('admin_notices', 'sr_display_redis_extension_missing_notice');
    return;
}

// Initialize Redis connection
function sr_cache_maybe_init_redis() {
    global $sr_cache_redis;

    if (empty($sr_cache_redis)) {
        $redis_server = defined('WP_REDIS_SERVER') ? WP_REDIS_SERVER : '127.0.0.1';
        $redis_port = defined('WP_REDIS_PORT') ? WP_REDIS_PORT : 6379;
        $redis_username = defined('WP_REDIS_USERNAME') ? WP_REDIS_USERNAME : '';
        $redis_password = defined('WP_REDIS_PASSWORD') ? WP_REDIS_PASSWORD : '';
        $redis_database = defined('WP_REDIS_DB_ID') ? WP_REDIS_DB_ID : 0;

        $sr_cache_redis = new Redis();

        try {
            $sr_cache_redis->connect($redis_server, $redis_port);
            if (!empty($redis_username) && !empty($redis_password)) {
                $sr_cache_redis->auth($redis_username . ':' . $redis_password);
            }
            $sr_cache_redis->select($redis_database);
        } catch (Exception $e) {
            // Display error message if connection to Redis failed
            add_action('admin_notices', 'sr_display_redis_connection_error_notice');
        }
    }
}

// Override transient functions to use Redis
function sr_cache_set_transient($transient, $value, $expiration = 0) {
    global $sr_cache_redis;

    if (empty($sr_cache_redis)) {
        return set_transient($transient, $value, $expiration);
    }

    $cache_key = sr_cache_generate_key($transient);
    $sr_cache_redis->setex($cache_key, $expiration, maybe_serialize($value));
    return true;
}
add_filter('set_transient', 'sr_cache_set_transient', 10, 3);

function sr_cache_get_transient($transient) {
    global $sr_cache_redis;

    if (empty($sr_cache_redis)) {
        return get_transient($transient);
    }

    $cache_key = sr_cache_generate_key($transient);
    $value = $sr_cache_redis->get($cache_key);

    if (false === $value) {
        return false;
    }

    return maybe_unserialize($value);
}
add_filter('get_transient', 'sr_cache_get_transient');

function sr_cache_delete_transient($transient) {
    global $sr_cache_redis;

    if (empty($sr_cache_redis)) {
        return delete_transient($transient);
    }

    $cache_key = sr_cache_generate_key($transient);
    return $sr_cache_redis->del($cache_key);
}
add_filter('delete_transient', 'sr_cache_delete_transient');

// Generate cache key
function sr_cache_generate_key($key) {
    $prefix = '';
    if (defined('WP_REDIS_PREFIX')) {
        $prefix = WP_REDIS_PREFIX;
    } elseif (defined('WP_CACHE_KEY_SALT')) {
        $prefix = WP_CACHE_KEY_SALT;
    } else {
        $install_path = rtrim(ABSPATH, '/');
        $prefix = substr(md5($install_path), 0, 6); // Generate prefix from WordPress installation path
    }
    return $prefix . $key;
}

// Display notice if PHP Redis extension is missing
function sr_display_redis_extension_missing_notice() {
    echo '<div class="error"><p>';
    echo 'Simple Redis Object Cache for WP plugin requires PHP Redis extension. Please install or enable it to use this plugin.';
    echo '</p></div>';
}

// Display notice if connection to Redis server fails
function sr_display_redis_connection_error_notice() {
    echo '<div class="error"><p>';
    echo 'Simple Redis Object Cache for WP plugin failed to connect to the Redis server. Please check your Redis configuration.';
    echo '</p></div>';
}

// Activation hook
register_activation_hook(__FILE__, 'sr_activate_plugin');

// Activation function
function sr_activate_plugin() {
    // Display installation information
    add_action('admin_notices', 'sr_display_installation_notice');
}

// Display installation information notice
function sr_display_installation_notice() {
    echo '<div class="updated"><p>';
    echo 'Simple Redis Object Cache for WP plugin activated. No additional configuration is required.';
    echo '</p></div>';
}
