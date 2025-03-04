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
            wp_enqueue_style('code-mirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.css', array(), '5.62.0');
            wp_enqueue_style('code-mirror-theme', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/theme/monokai.min.css', array(), '5.62.0');
            
            wp_enqueue_script('code-mirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.js', array('jquery'), '5.62.0', true);
            wp_enqueue_script('code-mirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/css/css.min.js', array('code-mirror'), '5.62.0', true);
            wp_enqueue_script('code-mirror-js', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/javascript/javascript.min.js', array('code-mirror'), '5.62.0', true);
        }
        
        // Enqueue admin script
        wp_enqueue_script('wpchild-admin', WPCHILD_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), WPCHILD_VERSION, true);
        
        // Localize script
        wp_localize_script('wpchild-admin', 'wpchild_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpchild-nonce'),
            'generating' => __('Generating file list...', 'wp-child-theme-pro'),
            'generating_error' => __('Error generating child theme. Please try again.', 'wp-child-theme-pro'),
            'saving_error' => __('Error saving changes. Please try again.', 'wp-child-theme-pro'),
            'confirm_delete' => __('Are you sure you want to delete this? This action cannot be undone.', 'wp-child-theme-pro')
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Child Theme Pro', 'wp-child-theme-pro'),
            __('Child Theme Pro', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro',
            array($this, 'display_main_page'),
            'dashicons-admin-appearance',
            60
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            __('Generate Child Theme', 'wp-child-theme-pro'),
            __('Generate Child Theme', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro',
            array($this, 'display_main_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            __('Customize Child Theme', 'wp-child-theme-pro'),
            __('Customize', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-customize',
            array($this, 'display_customize_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            __('Backup & Restore', 'wp-child-theme-pro'),
            __('Backup & Restore', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-backup',
            array($this, 'display_backup_page')
        );
        
        add_submenu_page(
            'wp-child-theme-pro',
            __('Settings', 'wp-child-theme-pro'),
            __('Settings', 'wp-child-theme-pro'),
            'manage_options',
            'wp-child-theme-pro-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wp_child_theme_pro_options', 'wp_child_theme_pro_options');
        
        add_settings_section(
            'wp_child_theme_pro_general',
            __('General Settings', 'wp-child-theme-pro'),
            array($this, 'settings_section_callback'),
            'wp_child_theme_pro_settings'
        );
        
        add_settings_field(
            'copy_parent_settings',
            __('Copy Parent Settings', 'wp-child-theme-pro'),
            array($this, 'copy_parent_settings_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
        
        add_settings_field(
            'auto_backup',
            __('Auto Backup', 'wp-child-theme-pro'),
            array($this, 'auto_backup_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
        
        add_settings_field(
            'default_author',
            __('Default Author', 'wp-child-theme-pro'),
            array($this, 'default_author_callback'),
            'wp_child_theme_pro_settings',
            'wp_child_theme_pro_general'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the default settings for WP Child Theme Pro.', 'wp-child-theme-pro') . '</p>';
    }

    /**
     * Copy parent settings callback
     */
    public function copy_parent_settings_callback() {
        $options = get_option('wp_child_theme_pro_options');
        $copy_parent_settings = isset($options['copy_parent_settings']) ? $options['copy_parent_settings'] : true;
        
        echo '<input type="checkbox" id="copy_parent_settings" name="wp_child_theme_pro_options[copy_parent_settings]" value="1" ' . checked(1, $copy_parent_settings, false) . '/>';
        echo '<label for="copy_parent_settings">' . __('Copy parent theme settings when creating a child theme', 'wp-child-theme-pro') . '</label>';
    }

    /**
     * Auto backup callback
     */
    public function auto_backup_callback() {
        $options = get_option('wp_child_theme_pro_options');
        $auto_backup = isset($options['auto_backup']) ? $options['auto_backup'] : true;
        
        echo '<input type="checkbox" id="auto_backup" name="wp_child_theme_pro_options[auto_backup]" value="1" ' . checked(1, $auto_backup, false) . '/>';
        echo '<label for="auto_backup">' . __('Automatically backup parent theme before creating a child theme', 'wp-child-theme-pro') . '</label>';
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
            // Verify nonce
            if (!wp_verify_nonce($_POST['wpchild_export_nonce'], 'wpchild-export-nonce')) {
                wp_die(__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Get theme to export
            $theme_to_export = isset($_POST['theme_to_export']) ? sanitize_text_field($_POST['theme_to_export']) : '';
            
            if (empty($theme_to_export)) {
                add_settings_error('wpchild_export', 'wpchild-export-error', __('Please select a theme to export.', 'wp-child-theme-pro'), 'error');
                return;
            }
            
            // Export theme
            $result = $this->plugin->backup->export_theme($theme_to_export);
            
            if (is_wp_error($result)) {
                add_settings_error('wpchild_export', 'wpchild-export-error', $result->get_error_message(), 'error');
                return;
            }
            
            // Send file to browser for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . basename($result));
            header('Content-Length: ' . filesize($result));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($result);
            exit;
        }
        
        // Process theme import
        if (isset($_POST['wpchild_import']) && isset($_POST['wpchild_import_nonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_POST['wpchild_import_nonce'], 'wpchild-import-nonce')) {
                wp_die(__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['theme_zip']) || $_FILES['theme_zip']['error'] != UPLOAD_ERR_OK) {
                add_settings_error('wpchild_import', 'wpchild-import-error', __('Error uploading file.', 'wp-child-theme-pro'), 'error');
                return;
            }
            
            // Import theme from uploaded file
            $uploaded_file = $_FILES['theme_zip']['tmp_name'];
            
            // Copy file to backups directory temporarily
            $temp_file = $this->plugin->backup->backup_dir . 'temp-import-' . time() . '.zip';
            move_uploaded_file($uploaded_file, $temp_file);
            
            // Process import
            $result = $this->plugin->backup->import_theme($temp_file);
            
            // Delete temporary file
            unlink($temp_file);
            
            if (is_wp_error($result)) {
                add_settings_error('wpchild_import', 'wpchild-import-error', $result->get_error_message(), 'error');
                return;
            }
            
            // Redirect to themes page
            wp_redirect(admin_url('themes.php'));
            exit;
        }
    }

    /**
     * Handle backup page actions (download, delete, restore)
     */
    public function handle_backup_actions() {
        // Check for backup actions
        if (isset($_GET['page']) && $_GET['page'] == 'wp-child-theme-pro-backup' && isset($_GET['action']) && isset($_GET['file'])) {
            // Verify nonce
            if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'wpchild-download-' . $_GET['file'])) {
                wp_die(__('Security check failed.', 'wp-child-theme-pro'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
            }
            
            // Get backup file
            $backup_file = sanitize_text_field($_GET['file']);
            $backup_path = $this->plugin->backup->backup_dir . '/' . $backup_file;
            
            // Check if file exists
            if (!file_exists($backup_path)) {
                wp_die(__('Backup file not found.', 'wp-child-theme-pro'));
            }
            
            // Send file to browser for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $backup_file);
            header('Content-Length: ' . filesize($backup_path));
            header('Pragma: no-cache');
            readfile($backup_path);
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme
        $parent_theme = isset($_POST['parent_theme']) ? sanitize_text_field($_POST['parent_theme']) : '';
        
        if (empty($parent_theme)) {
            wp_send_json_error(__('Parent theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme files
        $files = $this->plugin->generator->get_parent_theme_files($parent_theme);
        
        if (empty($files)) {
            wp_send_json_error(__('No files found in parent theme.', 'wp-child-theme-pro'));
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        $theme = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';
        
        if (empty($theme)) {
            wp_send_json_error(__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Switch theme
        switch_theme($theme);
        
        wp_send_json_success(__('Theme activated successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for deleting a theme
     */
    public function ajax_delete_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('delete_themes')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        $theme = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';
        
        if (empty($theme)) {
            wp_send_json_error(__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Check if theme is active
        if (get_stylesheet() == $theme) {
            wp_send_json_error(__('Cannot delete the active theme.', 'wp-child-theme-pro'));
        }
        
        // Delete theme
        $theme_dir = get_theme_root() . '/' . $theme;
        
        if (!is_dir($theme_dir)) {
            wp_send_json_error(__('Theme directory not found.', 'wp-child-theme-pro'));
        }
        
        // Use WP_Filesystem
        global $wp_filesystem;
        
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        WP_Filesystem();
        
        if (!$wp_filesystem->rmdir($theme_dir, true)) {
            wp_send_json_error(__('Failed to delete theme.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(__('Theme deleted successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for deleting a backup
     */
    public function ajax_delete_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-child-theme-pro')));
            return;
        }
        
        // Get backup file
        $backup_file = isset($_POST['backup_file']) ? sanitize_text_field($_POST['backup_file']) : '';
        
        if (empty($backup_file)) {
            wp_send_json_error(array('message' => __('Backup file not specified.', 'wp-child-theme-pro')));
            return;
        }
        
        // Delete backup
        $result = $this->plugin->backup->delete_backup($backup_file);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => __('Backup deleted successfully.', 'wp-child-theme-pro')));
    }

    /**
     * AJAX handler for restoring a backup
     */
    public function ajax_restore_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-child-theme-pro')));
            return;
        }
        
        // Get backup file
        $backup_file = isset($_POST['backup_file']) ? sanitize_text_field($_POST['backup_file']) : '';
        
        if (empty($backup_file)) {
            wp_send_json_error(array('message' => __('Backup file not specified.', 'wp-child-theme-pro')));
            return;
        }
        
        // Restore backup
        $result = $this->plugin->backup->restore_backup($backup_file);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => __('Backup restored successfully.', 'wp-child-theme-pro')));
    }

    /**
     * AJAX handler for backing up a theme
     */
    public function ajax_backup_theme() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get theme
        $theme = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';
        
        if (empty($theme)) {
            wp_send_json_error(__('Theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Backup theme
        if (!$this->plugin->backup->backup_theme($theme)) {
            wp_send_json_error(__('Failed to backup theme.', 'wp-child-theme-pro'));
        }
        
        wp_send_json_success(__('Theme backed up successfully.', 'wp-child-theme-pro'));
    }

    /**
     * AJAX handler for downloading a backup
     */
    public function ajax_download_backup() {
        // Verify nonce
        check_ajax_referer('wpchild-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get backup file
        $backup_file = isset($_GET['backup_file']) ? sanitize_text_field($_GET['backup_file']) : '';
        
        if (empty($backup_file)) {
            wp_die(__('Backup file not specified.', 'wp-child-theme-pro'));
        }
        
        // Get file path
        $file_path = $this->plugin->backup->backup_dir . $backup_file;
        
        // Check if file exists
        if (!file_exists($file_path)) {
            wp_die(__('Backup file not found.', 'wp-child-theme-pro'));
        }
        
        // Send file to browser for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $backup_file);
        header('Content-Length: ' . filesize($file_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file_path);
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-child-theme-pro'));
        }
        
        // Get parent theme
        $parent_theme = isset($_POST['parent_theme']) ? sanitize_text_field($_POST['parent_theme']) : '';
        
        if (empty($parent_theme)) {
            wp_send_json_error(__('Parent theme not specified.', 'wp-child-theme-pro'));
        }
        
        // Get child theme details
        $child_name = isset($_POST['child_name']) ? sanitize_text_field($_POST['child_name']) : '';
        $child_desc = isset($_POST['child_desc']) ? sanitize_text_field($_POST['child_desc']) : '';
        $child_author = isset($_POST['child_author']) ? sanitize_text_field($_POST['child_author']) : '';
        $child_version = isset($_POST['child_version']) ? sanitize_text_field($_POST['child_version']) : '1.0.0';
        $copy_settings = isset($_POST['copy_settings']) ? (bool) $_POST['copy_settings'] : false;
        
        if (empty($child_name)) {
            wp_send_json_error(__('Child theme name not specified.', 'wp-child-theme-pro'));
        }
        
        // Get selected files
        $selected_files = isset($_POST['selected_files']) ? (array) $_POST['selected_files'] : array();
        
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
            'message' => __('Child theme generated successfully.', 'wp-child-theme-pro'),
            'child_theme' => $result
        ));
    }
}
