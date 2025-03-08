<?php
/**
 * Admin interface for WP Child Theme Pro
 * 
 * @package WP_Child_Theme_Pro
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Child_Theme_Pro_Admin {

    /**
     * Plugin instance
     *
     * @var WP_Child_Theme_Pro
     */
    private $plugin;

    /**
     * Constructor
     *
     * @param WP_Child_Theme_Pro $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Initialize the class
     */
    public function init() {
        // Add menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Process export/import
        add_action('admin_init', array($this, 'process_export_import'));
        
        // Handle backup page actions
        add_action('admin_init', array($this, 'handle_backup_actions'));
        
        // Add AJAX handlers
        add_action('wp_ajax_wpchild_list_parent_files', array($this, 'ajax_list_parent_files'));
        add_action('wp_ajax_wpchild_activate_theme', array($this, 'ajax_activate_theme'));
        add_action('wp_ajax_wpchild_delete_theme', array($this, 'ajax_delete_theme'));
        add_action('wp_ajax_wpchild_delete_backup', array($this, 'ajax_delete_backup'));
        add_action('wp_ajax_wpchild_restore_backup', array($this, 'ajax_restore_backup'));
        add_action('wp_ajax_wpchild_backup_theme', array($this, 'ajax_backup_theme'));
        add_action('wp_ajax_wpchild_download_backup', array($this, 'ajax_download_backup'));
        add_action('wp_ajax_wpchild_generate_child_theme', array($this, 'ajax_generate_child_theme'));
        add_action('wp_ajax_wpchild_save_custom_css', array($this, 'ajax_save_custom_css'));
        add_action('wp_ajax_wpchild_save_custom_js', array($this, 'ajax_save_custom_js'));
        add_action('wp_ajax_wpchild_get_custom_css', array($this, 'ajax_get_custom_css'));
        add_action('wp_ajax_wpchild_get_custom_js', array($this, 'ajax_get_custom_js'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-child-theme-pro') === false) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style('wpchild-admin', WPCHILD_PLUGIN_URL . 'admin/css/admin.css', array(), WPCHILD_VERSION);
        
        // Enqueue CodeMirror if on the customize page
        if (strpos($hook, 'wp-child-theme-pro-customize') !== false) {
            wp_enqueue_style('code-mirror', WPCHILD_PLUGIN_URL . 'admin/css/codemirror.min.css', array(), '5.62.0');
            wp_enqueue_style('code-mirror-theme', WPCHILD_PLUGIN_URL . 'admin/css/monokai.min.css', array(), '5.62.0');
            
            wp_enqueue_script('code-mirror', WPCHILD_PLUGIN_URL . 'admin/js/codemirror.min.js', array('jquery'), '5.62.0', true);
            wp_enqueue_script('code-mirror-css', WPCHILD_PLUGIN_URL . 'admin/js/css.min.js', array('code-mirror'), '5.62.0', true);
            wp_enqueue_script('code-mirror-js', WPCHILD_PLUGIN_URL . 'admin/js/javascript.min.js', array('code-mirror'), '5.62.0', true);
        }
        
        // Enqueue admin script
        wp_enqueue_script('wpchild-admin', WPCHILD_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), WPCHILD_VERSION, true);
        
        // Localize script
        wp_localize_script('wpchild-admin', 'wpchild_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpchild-nonce'),
            'generating' => esc_html__('Generating file list...', 'wp-child-theme-pro'),
            'generating_error' => esc_html__('Error generating child theme. Please try again.', 'wp-child-theme-pro'),
            'saving_error' => esc_html__('Error saving changes. Please try again.', 'wp-child-theme-pro'),
            'confirm_delete' => esc_html__('Are you sure you want to delete this? This action cannot be undone.', 'wp-child-theme-pro'),
            'current_theme' => wp_get_theme()->get_stylesheet()
        ));

        // Replace external resources with local resources
        wp_enqueue_style('local-style', plugins_url('css/local-style.css', __FILE__));
        wp_enqueue_script('local-script', plugins_url('js/local-script.js', __FILE__));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__('WP Child Theme Pro', 'wp-child-theme-pro'),
            esc_html__('Child Theme Pro', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro',
            array($this, 'display_main_page'),
            'dashicons-admin-appearance',
            60
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            esc_html__('Generate Child Theme', 'wp-child-theme-pro'),
            esc_html__('Generate Child Theme', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro',
            array($this, 'display_main_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            esc_html__('Customize Child Theme', 'wp-child-theme-pro'),
            esc_html__('Customize', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-customize',
            array($this, 'display_customize_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            esc_html__('Backup & Restore', 'wp-child-theme-pro'),
            esc_html__('Backup & Restore', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-backup',
            array($this, 'display_backup_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            esc_html__('Settings', 'wp-child-theme-pro'),
            esc_html__('Settings', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wp_child_theme_pro_options', 'wp_child_theme_pro_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
        
        add_settings_section(
            'wp_child_theme_pro_general',
            esc_html__('General Settings', 'wp-child-theme-pro'),
            array($this, 'settings_section_callback'),
            'wp_child_theme_pro_settings'
        );
        
        add_settings_field(
            'copy_parent_settings',
            esc_html__('Copy Parent Settings', 'wp-child-theme-pro'),
            array($this, 'copy_parent_settings_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
        
        add_settings_field(
            'auto_backup',
            esc_html__('Auto Backup', 'wp-child-theme-pro'),
            array($this, 'auto_backup_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
        
        add_settings_field(
            'default_author',
            esc_html__('Default Author', 'wp-child-theme-pro'),
            array($this, 'default_author_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
    }

    /**
     * Sanitize options
     */
    public function sanitize_options($options) {
        $sanitized_options = array();
        if (isset($options['copy_parent_settings'])) {
            $sanitized_options['copy_parent_settings'] = absint($options['copy_parent_settings']);
        }
        if (isset($options['auto_backup'])) {
            $sanitized_options['auto_backup'] = absint($options['auto_backup']);
        }
        if (isset($options['default_author'])) {
            $sanitized_options['default_author'] = sanitize_text_field($options['default_author']);
        }
        return $sanitized_options;
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__('Configure the default settings for WP Child Theme Pro.', 'wp-child-theme-pro') . '</p>';
    }

    /**
     * Copy parent settings callback
     */
    public function copy_parent_settings_callback() {
        $options = get_option('wp_child_theme_pro_options');
        $copy_parent_settings = isset($options['copy_parent_settings']) ? $options['copy_parent_settings'] : true;
        
        echo '<input type="checkbox" id="copy_parent_settings" name="wp_child_theme_pro_options[copy_parent_settings]" value="1" ' . checked(1, $copy_parent_settings, false) . '/>';
        echo '<label for="copy_parent_settings">' . esc_html__('Copy parent theme settings when creating a child theme', 'wp-child-theme-pro') . '</label>';
    }

    /**
     * Auto backup callback
     */
    public function auto_backup_callback() {
        $options = get_option('wp_child_theme_pro_options');
        $auto_backup = isset($options['auto_backup']) ? $options['auto_backup'] : true;
        
        echo '<input type="checkbox" id="auto_backup" name="wp_child_theme_pro_options[auto_backup]" value="1" ' . checked(1, $auto_backup, false) . '/>';
        echo '<label for="auto_backup">' . esc_html__('Automatically backup parent theme before creating a child theme', 'wp-child-theme-pro') . '</label>';
    }

    /**
     * Default author callback
     */
    public function default_author_callback() {
        $options = get_option('wp_child_theme_pro_options');
        $default_author = isset($options['default_author']) ? $options['default_author'] : get_bloginfo('name');
        
        echo '<input type="text" id="default_author" name="wp_child_theme_pro_options[default_author]" value="' . esc_attr($default_author) . '" class="regular-text"/>';
    }

    /**
     * Display main page
     */
    public function display_main_page() {
        // Get available themes
        $themes = wp_get_themes();
        $parent_themes = array();
        
        foreach ($themes as $theme_slug => $theme) {
            if (!$theme->parent()) {
                $parent_themes[$theme_slug] = $theme;
            }
        }
        
        // Get options
        $options = get_option('wp_child_theme_pro_options');
        
        include WPCHILD_PLUGIN_DIR . 'admin/partials/main-page.php';
    }

    /**
     * Display customize page
     */
    public function display_customize_page() {
        // Get available child themes
        $themes = wp_get_themes();
        $child_themes = array();
        
        foreach ($themes as $theme_slug => $theme) {
            if ($theme->parent()) {
                $child_themes[$theme_slug] = $theme;
            }
        }
        
        include WPCHILD_PLUGIN_DIR . 'admin/partials/customize-page.php';
    }

    /**
     * Display backup page
     */
    public function display_backup_page() {
        // Get all themes
        $themes = wp_get_themes();
        
        // Filter child themes (has a parent)
        $child_themes = array();
        foreach ($themes as $theme_slug => $theme) {
            if ($theme->parent()) {
                $child_themes[$theme_slug] = $theme;
            }
        }
        
        // Get backup files
        $backups = $this->plugin->backup->get_backups();
        
        // Include template
        include(plugin_dir_path(__FILE__) . 'partials/backup-page.php');
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        include WPCHILD_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * Process export and import form submissions
     */
    public function process_export_import() {
        // Process theme export
        if (isset($_POST['wpchild_export']) && isset($_POST['wpchild_export_nonce'])) {
            // Sanitize $_POST['wpchild_export_nonce']
            $wpchild_export_nonce = isset($_POST['wpchild_export_nonce']) ? sanitize_text_field(wp_unslash($_POST['wpchild_export_nonce'])) : '';

            // Verify nonce
            if (!wp_verify_nonce($wpchild_export_nonce, 'wpchild-export-nonce')) {
                wp_die(esc_html__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Validate $_POST['theme_to_export']
            if (isset($_POST['theme_to_export'])) {
                $theme_to_export = sanitize_text_field(wp_unslash($_POST['theme_to_export']));
                if (empty($theme_to_export)) {
                    add_settings_error('wpchild_export', 'wpchild-export-error', esc_html__('Please select a theme to export.', 'wp-child-theme-pro'), 'error');
                    return;
                }
            }
            
            // Export theme
            $result = $this->plugin->backup->export_theme($theme_to_export);
            
            if (is_wp_error($result)) {
                add_settings_error('wpchild_export', 'wpchild-export-error', $result->get_error_message(), 'error');
                return;
            }
            
            // Send file to browser for download
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . basename($result));
            header('Content-Length: ' . filesize($result));
            header('Pragma: no-cache');
            header('Expires: 0');
            $wp_filesystem->get_contents($result);
            exit;
        }
        
        // Process theme import
        if (isset($_POST['wpchild_import']) && isset($_POST['wpchild_import_nonce'])) {
            // Sanitize $_POST['wpchild_import_nonce']
            $wpchild_import_nonce = isset($_POST['wpchild_import_nonce']) ? sanitize_text_field(wp_unslash($_POST['wpchild_import_nonce'])) : '';

            // Verify nonce
            if (!wp_verify_nonce($wpchild_import_nonce, 'wpchild-import-nonce')) {
                wp_die(esc_html__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Validate $_FILES['theme_zip']['tmp_name']
            if (isset($_FILES['theme_zip']['tmp_name'])) {
                $theme_zip_tmp_name = sanitize_text_field($_FILES['theme_zip']['tmp_name']);
                if (isset($_FILES['theme_zip']['error']) && UPLOAD_ERR_OK === $_FILES['theme_zip']['error']) {
                    // Import theme from uploaded file
                    $uploaded_file = $_FILES['theme_zip']['tmp_name'];
                    
                    // Copy file to backups directory temporarily
                    $temp_file = $this->plugin->backup->backup_dir . 'temp-import-' . time() . '.zip';
                    $wp_filesystem->move($theme_zip_tmp_name, $temp_file);
                    
                    // Process import
                    $result = $this->plugin->backup->import_theme($temp_file);
                    
                    // Delete temporary file
                    wp_delete_file($temp_file);
                    
                    if (is_wp_error($result)) {
                        add_settings_error('wpchild_import', 'wpchild-import-error', $result->get_error_message(), 'error');
                        return;
                    }
                    
                    // Redirect to themes page
                    wp_redirect(admin_url('themes.php'));
                    exit;
                }
            }
        }
    }

    /**
     * Handle backup page actions (download, delete, restore)
     */
    public function handle_backup_actions() {
        // Check for backup actions
        if (isset($_GET['page']) && $_GET['page'] == 'wp-child-theme-pro-backup' && isset($_GET['action']) && isset($_GET['file'])) {
            // Sanitize $_GET['nonce'] and $_GET['file'], and use wp_unslash() for $_GET['file']
            $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';
            $file = isset($_GET['file']) ? sanitize_text_field(wp_unslash($_GET['file'])) : '';

            // Verify nonce
            if (!isset($nonce) || !wp_verify_nonce($nonce, 'wpchild-download-' . $file)) {
                wp_die(esc_html__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Get backup file
            $backup_file = sanitize_file_name($file);
            $backup_path = $this->plugin->backup->backup_dir . '/' . $backup_file;
            
            // Check if file exists
            if (!file_exists($backup_path)) {
                wp_die(esc_html__('Backup file not found.', 'wp-child-theme-pro'));
            }
            
            // Send file to browser for download
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $backup_file);
            header('Content-Length: ' . filesize($backup_path));
            header('Pragma: no-cache');
            $wp_filesystem->get_contents($backup_path);
            exit;
        }
    }

    /**
     * AJAX handler for listing parent theme files
     */
    public function ajax_list_parent_files() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme
        if (isset($_POST['parent_theme'])) {
            $parent_theme = sanitize_text_field(wp_unslash($_POST['parent_theme']));
        }
        
        if (empty($parent_theme)) {
            wp_send_json_error(esc_html__('Parent theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme files
        $files = $this->plugin->generator->get_parent_theme_files($parent_theme);
        
        if (empty($files)) {
            wp_send_json_error(esc_html__('No files found in parent theme.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success($files);
    }

    /**
     * AJAX handler for activating a theme
     */
    public function ajax_activate_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('switch_themes')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        }
        
        if (empty($theme)) {
            wp_send_json_error(esc_html__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Switch theme
        switch_theme($theme);
        
        wp_send_json_success(esc_html__('Theme activated successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for deleting a theme
     */
    public function ajax_delete_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('delete_themes')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        }
        
        if (empty($theme)) {
            wp_send_json_error(esc_html__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Check if theme is active
        if (get_stylesheet() == $theme) {
            wp_send_json_error(esc_html__('Cannot delete the active theme.', 'wp-child-theme-pro'));
        }
        
        // Delete theme
        $theme_dir = get_theme_root() . '/' . $theme;
        
        if (!is_dir($theme_dir)) {
            wp_send_json_error(esc_html__('Theme directory not found.', 'wp-child-theme-pro'));
        }
        
        // Use WP_Filesystem
        global $wp_filesystem;
        
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        WP_Filesystem();
        
        if (!$wp_filesystem->rmdir($theme_dir, true)) {
            wp_send_json_error(esc_html__('Failed to delete theme.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(esc_html__('Theme deleted successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for deleting a backup
     */
    public function ajax_delete_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro')));
            return;
        }
        
        // Get backup file
        if (isset($_POST['backup_file'])) {
            $backup_file = sanitize_text_field(wp_unslash($_POST['backup_file']));
        }
        
        if (empty($backup_file)) {
            wp_send_json_error(array('message' => esc_html__('Backup file not specified.', 'wp-child-theme-pro')));
            return;
        }
        
        // Delete backup
        $result = $this->plugin->backup->delete_backup($backup_file);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => esc_html__('Backup deleted successfully.', 'wp-child-theme-pro')));
    }

    /**
     * AJAX handler for restoring a backup
     */
    public function ajax_restore_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro')));
            return;
        }
        
        // Get backup file
        if (isset($_POST['backup_file'])) {
            $backup_file = sanitize_text_field(wp_unslash($_POST['backup_file']));
        }
        
        if (empty($backup_file)) {
            wp_send_json_error(array('message' => esc_html__('Backup file not specified.', 'wp-child-theme-pro')));
            return;
        }
        
        // Restore backup
        $result = $this->plugin->backup->restore_backup($backup_file);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => esc_html__('Backup restored successfully.', 'wp-child-theme-pro')));
    }

    /**
     * AJAX handler for backing up a theme
     */
    public function ajax_backup_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        }
        
        if (empty($theme)) {
            wp_send_json_error(esc_html__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Backup theme
        if (!$this->plugin->backup->backup_theme($theme)) {
            wp_send_json_error(esc_html__('Failed to backup theme.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(esc_html__('Theme backed up successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for downloading a backup
     */
    public function ajax_download_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get backup file
        if (isset($_GET['backup_file'])) {
            $backup_file = sanitize_text_field(wp_unslash($_GET['backup_file']));
        }
        
        if (empty($backup_file)) {
            wp_die(esc_html__('Backup file not specified.', 'wp-child-theme-pro'));
        }
        
        // Get file path
        $file_path = $this->plugin->backup->backup_dir . $backup_file;
        
        // Check if file exists
        if (!file_exists($file_path)) {
            wp_die(esc_html__('Backup file not found.', 'wp-child-theme-pro'));
        }
        
        // Send file to browser for download
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $backup_file);
        header('Content-Length: ' . filesize($file_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        $wp_filesystem->get_contents($file_path);
        exit;
    }

    /**
     * AJAX handler for generating a child theme
     */
    public function ajax_generate_child_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme
        if (isset($_POST['parent_theme'])) {
            $parent_theme = sanitize_text_field(wp_unslash($_POST['parent_theme']));
        }
        
        if (empty($parent_theme)) {
            wp_send_json_error(esc_html__('Parent theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Get child theme details
        if (isset($_POST['child_name'])) {
            $child_name = sanitize_text_field(wp_unslash($_POST['child_name']));
        }
        if (isset($_POST['child_desc'])) {
            $child_desc = sanitize_textarea_field(wp_unslash($_POST['child_desc']));
        }
        if (isset($_POST['child_author'])) {
            $child_author = sanitize_text_field(wp_unslash($_POST['child_author']));
        }
        if (isset($_POST['child_version'])) {
            $child_version = sanitize_text_field(wp_unslash($_POST['child_version']));
        }
        $copy_settings = isset($_POST['copy_settings']) ? (bool) $_POST['copy_settings'] : false;
        
        if (empty($child_name)) {
            wp_send_json_error(esc_html__('Child theme name not specified.', 'wp-child-theme-pro'));
        }
        
        // Get selected files
        if (isset($_POST['selected_files'])) {
            $selected_files = array_map('sanitize_text_field', wp_unslash($_POST['selected_files']));
        }
        
        // Generate child theme
        $result = $this->plugin->generator->generate_child_theme(array(
            'parent_theme' => $parent_theme,
            'child_name' => $child_name,
            'child_desc' => $child_desc,
            'child_author' => $child_author,
            'child_version' => $child_version,
            'copy_settings' => $copy_settings,
            'selected_files' => $selected_files
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Return success
        wp_send_json_success(array(
            'message' => esc_html__('Child theme generated successfully.', 'wp-child-theme-pro'),
            'child_theme' => $result
        ));
    }

    /**
     * AJAX handler for saving custom CSS
     */
    public function ajax_save_custom_css() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get CSS content
        if (isset($_POST['css'])) {
            $css = sanitize_textarea_field(wp_unslash($_POST['css']));
        }
        
        // Get theme - either provided or current theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        } else {
            $theme = wp_get_theme()->get_stylesheet();
        }
        
        // Save CSS to theme mod
        set_theme_mod('wpchild_custom_css', $css);
        
        // Save CSS to file
        $themes_dir = get_theme_root();
        $theme_dir = trailingslashit($themes_dir) . $theme;
        $css_dir = trailingslashit($theme_dir) . 'assets/css';
        $css_file = trailingslashit($css_dir) . 'mytheme.css';
        
        // Create assets/css directory if it doesn't exist
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        // Use WordPress filesystem API
        global $wp_filesystem;
        
        // Initialize the WordPress filesystem
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        // Write CSS to file
        if (!$wp_filesystem->put_contents($css_file, $css)) {
            wp_send_json_error(esc_html__('Failed to save CSS file.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(esc_html__('CSS saved successfully.', 'wp-child-theme-pro'));
    }
    
    /**
     * AJAX handler for saving custom JS
     */
    public function ajax_save_custom_js() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get JS content
        if (isset($_POST['js'])) {
            $js = sanitize_textarea_field(wp_unslash($_POST['js']));
        }
        
        // Get theme - either provided or current theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        } else {
            $theme = wp_get_theme()->get_stylesheet();
        }
        
        // Save JS to theme mod
        set_theme_mod('wpchild_custom_js', $js);
        
        // Save JS to file
        $themes_dir = get_theme_root();
        $theme_dir = trailingslashit($themes_dir) . $theme;
        $js_dir = trailingslashit($theme_dir) . 'assets/js';
        $js_file = trailingslashit($js_dir) . 'mytheme.js';
        
        // Create assets/js directory if it doesn't exist
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }
        
        // Use WordPress filesystem API
        global $wp_filesystem;
        
        // Initialize the WordPress filesystem
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        // Write JS to file
        if (!$wp_filesystem->put_contents($js_file, $js)) {
            wp_send_json_error(esc_html__('Failed to save JS file.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(esc_html__('JS saved successfully.', 'wp-child-theme-pro'));
    }
    
    /**
     * AJAX handler for getting custom CSS
     */
    public function ajax_get_custom_css() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme - either provided or current theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        } else {
            $theme = wp_get_theme()->get_stylesheet();
        }
        
        // Get CSS from theme mod
        $css = get_theme_mod('wpchild_custom_css', '');
        
        // If empty, try to get from file
        if (empty($css)) {
            $themes_dir = get_theme_root();
            $theme_dir = trailingslashit($themes_dir) . $theme;
            $css_file = trailingslashit($theme_dir) . 'assets/css/mytheme.css';
            
            if (file_exists($css_file)) {
                // Use WordPress filesystem API
                global $wp_filesystem;
                
                // Initialize the WordPress filesystem
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                
                $css = $wp_filesystem->get_contents($css_file);
            }
        }
        
        wp_send_json_success($css);
    }
    
    /**
     * AJAX handler for getting custom JS
     */
    public function ajax_get_custom_js() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme - either provided or current theme
        if (isset($_POST['theme'])) {
            $theme = sanitize_text_field(wp_unslash($_POST['theme']));
        } else {
            $theme = wp_get_theme()->get_stylesheet();
        }
        
        // Get JS from theme mod
        $js = get_theme_mod('wpchild_custom_js', '');
        
        // If empty, try to get from file
        if (empty($js)) {
            $themes_dir = get_theme_root();
            $theme_dir = trailingslashit($themes_dir) . $theme;
            $js_file = trailingslashit($theme_dir) . 'assets/js/mytheme.js';
            
            if (file_exists($js_file)) {
                // Use WordPress filesystem API
                global $wp_filesystem;
                
                // Initialize the WordPress filesystem
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                
                $js = $wp_filesystem->get_contents($js_file);
            }
        }
        
        wp_send_json_success($js);
    }
}
