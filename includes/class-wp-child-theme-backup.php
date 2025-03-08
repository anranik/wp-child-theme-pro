<?php
/**
 * Child Theme Backup Class
 * 
 * @package WP_Child_Theme_Pro
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Child_Theme_Backup {

    /**
     * Backup directory
     *
     * @var string
     */
    public $backup_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->backup_dir = WPCHILD_PLUGIN_DIR . 'backups/';
        
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }
    }

    /**
     * Backup a child theme
     *
     * @param string $theme_slug Theme slug to backup
     * @return string|WP_Error Backup filename or error
     */
    public function backup_theme($theme_slug) {
        // Check if theme exists
        $theme = wp_get_theme($theme_slug);
        if (!$theme->exists()) {
            return new WP_Error('theme_not_found', __('The selected theme does not exist.', 'wp-child-theme-pro'));
        }
        
        // Create backup filename (uses timestamp to ensure uniqueness)
        $backup_filename = $theme_slug . '-' . gmdate('YmdHis') . '.zip';
        $backup_filepath = $this->backup_dir . $backup_filename;
        
        // Check if ZipArchive class exists
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip_support', __('ZipArchive class is missing. Please enable the zip extension on your server.', 'wp-child-theme-pro'));
        }
        
        // Get theme directory
        $theme_dir = $theme->get_stylesheet_directory();
        
        // Create ZIP file
        $zip = new ZipArchive();
        if ($zip->open($backup_filepath, ZipArchive::CREATE) !== TRUE) {
            return new WP_Error('zip_creation_failed', __('Failed to create ZIP file.', 'wp-child-theme-pro'));
        }
        
        // Add theme files to ZIP
        $files = $this->get_all_theme_files($theme_dir);
        foreach ($files as $file) {
            $relative_path = str_replace($theme_dir . '/', '', $file);
            $zip->addFile($file, $relative_path);
        }
        
        // Close ZIP
        $zip->close();
        
        // Return backup filename
        return $backup_filename;
    }
    
    /**
     * Restore a backup
     *
     * @param string $backup_file Backup file to restore
     * @return bool|WP_Error True on success or error
     */
    public function restore_backup($backup_file) {
        // Check if backup file exists
        $backup_path = $this->backup_dir . $backup_file;
        if (!file_exists($backup_path)) {
            return new WP_Error('backup_not_found', __('Backup file not found.', 'wp-child-theme-pro'));
        }
        
        // Check if ZipArchive class exists
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip_support', __('ZipArchive class is missing. Please enable the zip extension on your server.', 'wp-child-theme-pro'));
        }
        
        // Open ZIP
        $zip = new ZipArchive();
        if ($zip->open($backup_path) !== TRUE) {
            return new WP_Error('zip_open_failed', __('Failed to open backup file.', 'wp-child-theme-pro'));
        }
        
        // Find style.css to get theme info
        $style_index = -1;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (basename($filename) === 'style.css' && dirname($filename) === '.') {
                $style_index = $i;
                break;
            }
        }
        
        // Check if style.css exists
        if ($style_index === -1) {
            $zip->close();
            return new WP_Error('invalid_backup', __('The backup does not contain a valid theme.', 'wp-child-theme-pro'));
        }
        
        // Extract style.css to get theme info
        $style_content = $zip->getFromIndex($style_index);
        preg_match('/Theme Name:\s*(.+)$/mi', $style_content, $name_matches);
        preg_match('/Text Domain:\s*(.+)$/mi', $style_content, $textdomain_matches);
        
        // Get theme folder name from style.css
        $theme_folder = !empty($textdomain_matches[1]) ? 
                        sanitize_file_name($textdomain_matches[1]) : 
                        sanitize_file_name(strtolower(str_replace(' ', '-', $name_matches[1])));
        
        // Get themes directory
        $themes_dir = get_theme_root();
        
        // Check if themes directory is writable
        global $wp_filesystem;
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        if ( ! $wp_filesystem->is_writable($themes_dir) ) {
            $zip->close();
            return new WP_Error('themes_dir_not_writable', __('The themes directory is not writable.', 'wp-child-theme-pro'));
        }
        
        // Check if theme directory exists and remove it
        $theme_path = $themes_dir . '/' . $theme_folder;
        if (is_dir($theme_path)) {
            $this->delete_theme_directory($theme_path);
        }
        
        // Extract theme to themes directory
        if (!$zip->extractTo($themes_dir)) {
            $zip->close();
            return new WP_Error('extract_failed', __('Failed to extract theme files.', 'wp-child-theme-pro'));
        }
        
        // Close ZIP
        $zip->close();
        
        // Refresh theme cache
        wp_clean_themes_cache();
        
        return true;
    }
    
    /**
     * Delete a backup
     *
     * @param string $backup_file Backup file to delete
     * @return bool|WP_Error True on success or error
     */
    public function delete_backup($backup_file) {
        // Check if backup file exists
        $backup_path = $this->backup_dir . $backup_file;
        if (!file_exists($backup_path)) {
            return new WP_Error('backup_not_found', __('Backup file not found.', 'wp-child-theme-pro'));
        }
        
        // Delete file
        if (!wp_delete_file($backup_path)) {
            return new WP_Error('delete_failed', __('Failed to delete backup file.', 'wp-child-theme-pro'));
        }
        
        return true;
    }

    /**
     * Get all files in a directory recursively
     *
     * @param string $dir Directory to scan
     * @return array List of files
     */
    private function get_all_theme_files($dir) {
        $files = array();
        
        $dir_iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }

    /**
     * Delete a directory and all its contents
     *
     * @param string $dir Directory to delete
     * @return bool True on success
     */
    private function delete_theme_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->delete_theme_directory($path);
            } else {
                wp_delete_file($path);
            }
        }
        
        global $wp_filesystem;
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        return $wp_filesystem->rmdir($dir);
    }

    /**
     * Get a list of all backup files
     * 
     * @return array List of backup files with details
     */
    public function get_backups() {
        $backups = array();
        
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            return $backups; // Return empty array if directory was just created
        }
        
        // Get files in backup directory
        $files = scandir($this->backup_dir);
        
        foreach ($files as $file) {
            // Skip directories and non-zip files
            if (is_dir($this->backup_dir . $file) || pathinfo($file, PATHINFO_EXTENSION) !== 'zip') {
                continue;
            }
            
            // Extract theme name and timestamp from filename
            if (preg_match('/^([a-zA-Z0-9_-]+)-(\d{14})\.zip$/', $file, $matches)) {
                $theme_name = $matches[1];
                $timestamp = $matches[2];
                
                // Format date
                $date = DateTime::createFromFormat('YmdHis', $timestamp);
                
                // Add to backups array
                $backups[] = array(
                    'file' => $file,
                    'theme' => $theme_name,
                    'date' => $date ? $date->format(get_option('date_format') . ' ' . get_option('time_format')) : $timestamp,
                    'size' => size_format(filesize($this->backup_dir . $file)),
                );
            }
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strcmp($b['file'], $a['file']);
        });
        
        return $backups;
    }

    /**
     * Export a theme as a ZIP file
     *
     * @param string $theme_slug The theme to export
     * @return string|WP_Error Path to the exported ZIP file or error
     */
    public function export_theme($theme_slug) {
        // Check if theme exists
        $theme = wp_get_theme($theme_slug);
        if (!$theme->exists()) {
            return new WP_Error('theme_not_found', __('The selected theme does not exist.', 'wp-child-theme-pro'));
        }
        
        // Check if ZipArchive class exists
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip_support', __('ZipArchive class is missing. Please enable the zip extension on your server.', 'wp-child-theme-pro'));
        }
        
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }
        
        // Create export filename (uses timestamp to ensure uniqueness)
        $export_filename = $theme_slug . '-export-' . gmdate('YmdHis') . '.zip';
        $export_filepath = $this->backup_dir . $export_filename;
        
        // Get theme directory
        $theme_dir = $theme->get_stylesheet_directory();
        
        // Create ZIP file
        $zip = new ZipArchive();
        if ($zip->open($export_filepath, ZipArchive::CREATE) !== TRUE) {
            return new WP_Error('zip_creation_failed', __('Failed to create ZIP file.', 'wp-child-theme-pro'));
        }
        
        // Add theme files to ZIP
        $files = $this->get_all_theme_files($theme_dir);
        foreach ($files as $file) {
            $relative_path = str_replace($theme_dir . '/', '', $file);
            $zip->addFile($file, $relative_path);
        }
        
        // Close ZIP
        $zip->close();
        
        // Return path to ZIP file
        return $export_filepath;
    }
}
