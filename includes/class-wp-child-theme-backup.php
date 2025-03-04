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
    private $backup_dir;

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
     * Backup a theme
     *
     * @param string $theme_slug Theme slug to backup
     * @return string|WP_Error Backup file path or error
     */
    public function backup_theme($theme_slug) {
        // Get theme directory
        $theme_dir = get_theme_root() . '/' . $theme_slug;
        
        // Check if theme exists
        if (!is_dir($theme_dir)) {
            return new WP_Error('invalid_theme', __('Theme does not exist.', 'wp-child-theme-pro'));
        }
        
        // Get theme data
        $theme_data = wp_get_theme($theme_slug);
        
        // Create backup file name with timestamp
        $timestamp = date('Y-m-d-H-i-s');
        $backup_file = $this->backup_dir . $theme_slug . '-' . $timestamp . '.zip';
        
        // Create ZIP archive
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip', __('PHP ZipArchive extension is not available.', 'wp-child-theme-pro'));
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            return new WP_Error('zip_failed', __('Failed to create ZIP archive.', 'wp-child-theme-pro'));
        }
        
        // Add all theme files to ZIP
        $this->add_dir_to_zip($zip, $theme_dir, $theme_slug);
        
        // Close ZIP
        $zip->close();
        
        // Check if backup file was created
        if (!file_exists($backup_file)) {
            return new WP_Error('backup_failed', __('Failed to create backup file.', 'wp-child-theme-pro'));
        }
        
        // Create theme settings backup
        $this->backup_theme_settings($theme_slug, $timestamp);
        
        return $backup_file;
    }

    /**
     * Add directory to ZIP recursively
     *
     * @param ZipArchive $zip ZIP archive
     * @param string $dir Directory to add
     * @param string $theme_slug Theme slug
     * @param string $local_dir Local directory in ZIP
     */
    private function add_dir_to_zip($zip, $dir, $theme_slug, $local_dir = '') {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $file_path = $dir . '/' . $file;
            $local_path = $local_dir ? $local_dir . '/' . $file : $file;
            
            if (is_dir($file_path)) {
                // Add directory
                $zip->addEmptyDir($theme_slug . '/' . $local_path);
                
                // Add directory contents
                $this->add_dir_to_zip($zip, $file_path, $theme_slug, $local_path);
            } else {
                // Add file
                $zip->addFile($file_path, $theme_slug . '/' . $local_path);
            }
        }
    }

    /**
     * Backup theme settings
     *
     * @param string $theme_slug Theme slug
     * @param string $timestamp Timestamp
     */
    private function backup_theme_settings($theme_slug, $timestamp) {
        // Get theme mods
        $theme_mods = get_option('theme_mods_' . $theme_slug);
        
        if ($theme_mods) {
            // Create settings file
            $settings_file = $this->backup_dir . $theme_slug . '-settings-' . $timestamp . '.json';
            
            // Save settings as JSON
            file_put_contents($settings_file, json_encode($theme_mods));
        }
    }

    /**
     * Get list of backups
     *
     * @param string $theme_slug Theme slug to filter backups (optional)
     * @return array List of backup files
     */
    public function get_backups($theme_slug = '') {
        $backups = array();
        
        // Get all files in backup directory
        $files = scandir($this->backup_dir);
        
        foreach ($files as $file) {
            // Skip dots and non-zip files
            if ($file == '.' || $file == '..' || !preg_match('/\.zip$/', $file)) {
                continue;
            }
            
            // Extract theme slug and timestamp from filename
            if (preg_match('/^([a-z0-9-]+)-(\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2})\.zip$/', $file, $matches)) {
                $backup_theme = $matches[1];
                $backup_time = $matches[2];
                
                // Skip if filtering by theme and not matching
                if ($theme_slug && $backup_theme != $theme_slug) {
                    continue;
                }
                
                // Format timestamp
                $timestamp = strtotime(str_replace('-', ' ', preg_replace('/(\d{4})-(\d{2})-(\d{2})-(\d{2})-(\d{2})-(\d{2})/', '$1-$2-$3 $4:$5:$6', $backup_time)));
                
                // Add to list
                $backups[] = array(
                    'file' => $file,
                    'theme' => $backup_theme,
                    'timestamp' => $timestamp,
                    'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp),
                    'size' => filesize($this->backup_dir . $file),
                    'path' => $this->backup_dir . $file,
                );
            }
        }
        
        // Sort by timestamp descending
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $backups;
    }

    /**
     * Delete a backup
     *
     * @param string $backup_file Backup file name
     * @return bool Success or failure
     */
    public function delete_backup($backup_file) {
        // Sanitize filename
        $backup_file = sanitize_file_name($backup_file);
        
        // Check if file exists
        $file_path = $this->backup_dir . $backup_file;
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Delete backup file
        return unlink($file_path);
    }

    /**
     * Restore a theme from backup
     *
     * @param string $backup_file Backup file name
     * @return string|WP_Error Theme slug or error
     */
    public function restore_theme($backup_file) {
        // Sanitize filename
        $backup_file = sanitize_file_name($backup_file);
        
        // Check if file exists
        $file_path = $this->backup_dir . $backup_file;
        
        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', __('Backup file not found.', 'wp-child-theme-pro'));
        }
        
        // Extract theme slug from filename
        if (!preg_match('/^([a-z0-9-]+)-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.zip$/', $backup_file, $matches)) {
            return new WP_Error('invalid_filename', __('Invalid backup filename.', 'wp-child-theme-pro'));
        }
        
        $theme_slug = $matches[1];
        
        // Get themes directory
        $themes_dir = get_theme_root();
        
        // Remove existing theme if it exists
        $theme_dir = $themes_dir . '/' . $theme_slug;
        
        if (is_dir($theme_dir)) {
            // Backup current theme first if it's active
            if (get_stylesheet() == $theme_slug) {
                // Switch to default theme temporarily
                switch_theme(WP_DEFAULT_THEME);
            }
            
            // Remove directory
            $this->recursive_delete($theme_dir);
        }
        
        // Extract backup
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip', __('PHP ZipArchive extension is not available.', 'wp-child-theme-pro'));
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($file_path) !== TRUE) {
            return new WP_Error('zip_failed', __('Failed to open ZIP archive.', 'wp-child-theme-pro'));
        }
        
        // Extract to themes directory
        $zip->extractTo($themes_dir);
        $zip->close();
        
        // Check if theme directory was created
        if (!is_dir($theme_dir)) {
            return new WP_Error('extract_failed', __('Failed to extract theme from backup.', 'wp-child-theme-pro'));
        }
        
        // Restore theme settings if available
        $this->restore_theme_settings($theme_slug, $backup_file);
        
        return $theme_slug;
    }

    /**
     * Restore theme settings
     *
     * @param string $theme_slug Theme slug
     * @param string $backup_file Backup file name
     */
    private function restore_theme_settings($theme_slug, $backup_file) {
        // Get settings file name
        $settings_file = str_replace('.zip', '.json', str_replace($theme_slug, $theme_slug . '-settings', $backup_file));
        $settings_path = $this->backup_dir . $settings_file;
        
        // Check if settings file exists
        if (file_exists($settings_path)) {
            // Read settings
            $settings_json = file_get_contents($settings_path);
            $theme_mods = json_decode($settings_json, true);
            
            if ($theme_mods) {
                // Update theme mods
                update_option('theme_mods_' . $theme_slug, $theme_mods);
            }
        }
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory path
     */
    private function recursive_delete($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $this->recursive_delete("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        
        rmdir($dir);
    }

    /**
     * Import a child theme from a ZIP file
     *
     * @param string $zip_file ZIP file path
     * @return string|WP_Error Theme slug or error
     */
    public function import_theme($zip_file) {
        // Check if file exists
        if (!file_exists($zip_file)) {
            return new WP_Error('file_not_found', __('ZIP file not found.', 'wp-child-theme-pro'));
        }
        
        // Get themes directory
        $themes_dir = get_theme_root();
        
        // Extract ZIP
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip', __('PHP ZipArchive extension is not available.', 'wp-child-theme-pro'));
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file) !== TRUE) {
            return new WP_Error('zip_failed', __('Failed to open ZIP archive.', 'wp-child-theme-pro'));
        }
        
        // Get theme directory from first entry
        if ($zip->numFiles === 0) {
            $zip->close();
            return new WP_Error('empty_zip', __('ZIP file is empty.', 'wp-child-theme-pro'));
        }
        
        // Extract first directory name
        $first_entry = $zip->getNameIndex(0);
        $theme_dir = explode('/', $first_entry)[0];
        
        // Check if theme directory already exists
        if (is_dir($themes_dir . '/' . $theme_dir)) {
            $zip->close();
            return new WP_Error('theme_exists', __('A theme with this name already exists.', 'wp-child-theme-pro'));
        }
        
        // Extract to themes directory
        $zip->extractTo($themes_dir);
        $zip->close();
        
        // Check if theme was extracted
        if (!is_dir($themes_dir . '/' . $theme_dir)) {
            return new WP_Error('extract_failed', __('Failed to extract theme from ZIP.', 'wp-child-theme-pro'));
        }
        
        // Check if it's a valid theme
        $theme_data = wp_get_theme($theme_dir);
        
        if (!$theme_data->exists()) {
            // Clean up
            $this->recursive_delete($themes_dir . '/' . $theme_dir);
            return new WP_Error('invalid_theme', __('The ZIP file does not contain a valid WordPress theme.', 'wp-child-theme-pro'));
        }
        
        return $theme_dir;
    }

    /**
     * Export a child theme to a ZIP file
     *
     * @param string $theme_slug Theme slug
     * @return string|WP_Error ZIP file path or error
     */
    public function export_theme($theme_slug) {
        // Get theme directory
        $theme_dir = get_theme_root() . '/' . $theme_slug;
        
        // Check if theme exists
        if (!is_dir($theme_dir)) {
            return new WP_Error('invalid_theme', __('Theme does not exist.', 'wp-child-theme-pro'));
        }
        
        // Get theme data
        $theme_data = wp_get_theme($theme_slug);
        
        // Create export file name
        $export_file = $this->backup_dir . $theme_slug . '-export.zip';
        
        // Create ZIP archive
        if (!class_exists('ZipArchive')) {
            return new WP_Error('no_zip', __('PHP ZipArchive extension is not available.', 'wp-child-theme-pro'));
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($export_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return new WP_Error('zip_failed', __('Failed to create ZIP archive.', 'wp-child-theme-pro'));
        }
        
        // Add all theme files to ZIP
        $this->add_dir_to_zip($zip, $theme_dir, $theme_slug);
        
        // Close ZIP
        $zip->close();
        
        // Check if export file was created
        if (!file_exists($export_file)) {
            return new WP_Error('export_failed', __('Failed to create export file.', 'wp-child-theme-pro'));
        }
        
        return $export_file;
    }
}
