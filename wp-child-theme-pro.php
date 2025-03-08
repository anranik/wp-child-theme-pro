<?php
/**
 * Plugin Name: WP Child Theme Pro
 * Plugin URI: https://example.com/wp-child-theme-pro
 * Description: A plugin to manage child themes.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL2
 * Text Domain: wp-child-theme-pro
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPCHILD_VERSION', '1.0.0');
define('WPCHILD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCHILD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPCHILD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPCHILD_PLUGIN_DIR . 'includes/class-wp-child-theme-pro.php';
require_once WPCHILD_PLUGIN_DIR . 'includes/class-wp-child-theme-generator.php';
require_once WPCHILD_PLUGIN_DIR . 'includes/class-wp-child-theme-customizer.php';
require_once WPCHILD_PLUGIN_DIR . 'includes/class-wp-child-theme-backup.php';

// Initialize the plugin
function wp_child_theme_pro_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('wp-child-theme-pro', false, dirname(WPCHILD_PLUGIN_BASENAME) . '/languages');
    
    // Initialize main plugin class
    $wp_child_theme_pro = new WP_Child_Theme_Pro();
    $wp_child_theme_pro->init();
}
add_action('plugins_loaded', 'wp_child_theme_pro_init');

// Register activation hook
register_activation_hook(__FILE__, 'wp_child_theme_pro_activate');
function wp_child_theme_pro_activate() {
    // Create necessary directories and files
    if (!file_exists(WPCHILD_PLUGIN_DIR . 'backups')) {
        wp_mkdir_p(WPCHILD_PLUGIN_DIR . 'backups');
    }
    
    // Set default options
    $default_options = array(
        'version' => WPCHILD_VERSION,
        'copy_parent_settings' => true,
        'auto_backup' => true,
        'default_author' => get_bloginfo('name'),
        'default_author_uri' => home_url(),
    );
    
    add_option('wp_child_theme_pro_options', $default_options);
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'wp_child_theme_pro_deactivate');
function wp_child_theme_pro_deactivate() {
    // Clean up temporary files
    $temp_dir = get_temp_dir() . 'wp-child-theme-pro/';
    if (file_exists($temp_dir)) {
        wp_delete_file($temp_dir);
    }
}

// Register uninstall hook
register_uninstall_hook(__FILE__, 'wp_child_theme_pro_uninstall');
function wp_child_theme_pro_uninstall() {
    // Remove plugin options
    delete_option('wp_child_theme_pro_options');
    
    // You might want to keep backups, so we're not deleting them by default
    // Users should be prompted before deletion of their child themes or backups
}
