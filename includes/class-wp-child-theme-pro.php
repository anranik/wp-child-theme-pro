<?php
/**
 * Main plugin class
 * 
 * @package WP_Child_Theme_Pro
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Child_Theme_Pro {

    /**
     * Plugin instance
     *
     * @var WP_Child_Theme_Pro
     */
    private static $instance = null;

    /**
     * Settings API instance
     *
     * @var object
     */
    public $settings;

    /**
     * Generator instance
     *
     * @var WP_Child_Theme_Generator
     */
    public $generator;

    /**
     * Customizer instance
     *
     * @var WP_Child_Theme_Customizer
     */
    public $customizer;

    /**
     * Backup instance
     *
     * @var WP_Child_Theme_Backup
     */
    public $backup;

    /**
     * Get plugin instance
     *
     * @return WP_Child_Theme_Pro
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        // Include admin files if in admin area
        if (is_admin()) {
            require_once WPCHILD_PLUGIN_DIR . 'admin/class-wp-child-theme-pro-admin.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register activation hook only (deactivation is handled in main plugin file)
        register_activation_hook(WPCHILD_PLUGIN_BASENAME, array($this, 'activate'));

        // Add actions
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize generator
        $this->generator = new WP_Child_Theme_Generator();
        
        // Initialize customizer
        $this->customizer = new WP_Child_Theme_Customizer();
        
        // Initialize backup
        $this->backup = new WP_Child_Theme_Backup();

        // Initialize admin if in admin area
        if (is_admin()) {
            $admin = new WP_Child_Theme_Pro_Admin($this);
            $admin->init();
        }
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Check if generator is initialized
        if (isset($this->generator) && method_exists($this->generator, 'admin_notices')) {
            $this->generator->admin_notices();
        }
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook_suffix Current admin page
     */
    public function enqueue_admin_assets($hook_suffix) {
        // Only enqueue on plugin pages
        if (strpos($hook_suffix, 'wp-child-theme-pro') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('wpchild-admin', WPCHILD_PLUGIN_URL . 'admin/css/admin.css', array(), WPCHILD_VERSION);
        
        // Enqueue CodeMirror for syntax highlighting
        wp_enqueue_style('wpchild-codemirror', WPCHILD_PLUGIN_URL . 'admin/css/codemirror.css', array(), WPCHILD_VERSION);
        
        // Enqueue JS
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('wpchild-codemirror', WPCHILD_PLUGIN_URL . 'admin/js/codemirror.js', array('jquery'), WPCHILD_VERSION, true);
        wp_enqueue_script('wpchild-admin', WPCHILD_PLUGIN_URL . 'admin/js/admin.js', array('jquery', 'wp-color-picker', 'wpchild-codemirror'), WPCHILD_VERSION, true);
        
        // Localize script
        wp_localize_script('wpchild-admin', 'wpchild_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpchild-nonce'),
            'generating' => __('Generating child theme...', 'wp-child-theme-pro'),
            'generating_success' => __('Child theme created successfully!', 'wp-child-theme-pro'),
            'generating_error' => __('Error creating child theme.', 'wp-child-theme-pro'),
            'saving' => __('Saving...', 'wp-child-theme-pro'),
            'saving_success' => __('Saved successfully!', 'wp-child-theme-pro'),
            'saving_error' => __('Error saving.', 'wp-child-theme-pro'),
            'confirm_delete' => __('Are you sure you want to delete this child theme? This action cannot be undone.', 'wp-child-theme-pro'),
        ));
    }

    /**
     * Deactivate the plugin
     */
    public function deactivate() {
        // Deactivation code here
    }
}
