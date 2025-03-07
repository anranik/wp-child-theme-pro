<?php
/**
 * Backup admin page
 * 
 * @package WP_Child_Theme_Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpchild-admin">
    <h1><?php esc_html_e('WP Child Theme Pro', 'wp-child-theme-pro'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-child-theme-pro" class="nav-tab"><?php esc_html_e('Generate Child Theme', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-customize" class="nav-tab"><?php esc_html_e('Customize', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-backup" class="nav-tab nav-tab-active"><?php esc_html_e('Backup & Restore', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-settings" class="nav-tab"><?php esc_html_e('Settings', 'wp-child-theme-pro'); ?></a>
    </h2>
    
    <div class="wpchild-page-content">
        <div class="wpchild-row">
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Backup Themes', 'wp-child-theme-pro'); ?></h2>
                    <p><?php esc_html_e('Create backups of your themes to keep them safe. You can restore them later if needed.', 'wp-child-theme-pro'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Select Theme to Backup', 'wp-child-theme-pro'); ?></th>
                            <td>
                                <select id="theme-to-backup">
                                    <option value=""><?php esc_html_e('-- Select Theme --', 'wp-child-theme-pro'); ?></option>
                                    <?php foreach ($themes as $theme_slug => $theme) : ?>
                                        <option value="<?php echo esc_attr($theme_slug); ?>"><?php echo esc_html($theme->get('Name')); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button backup-theme" data-theme=""><?php esc_html_e('Backup', 'wp-child-theme-pro'); ?></button>
                            </td>
                        </tr>
                    </table>
                    
                    <script>
                        jQuery(document).ready(function($) {
                            $('#theme-to-backup').on('change', function() {
                                $('.backup-theme').attr('data-theme', $(this).val());
                            });
                        });
                    </script>
                </div>
                
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Import/Export', 'wp-child-theme-pro'); ?></h2>
                    
                    <h3><?php esc_html_e('Export Theme', 'wp-child-theme-pro'); ?></h3>
                    <p><?php esc_html_e('Export a theme to use on another WordPress site.', 'wp-child-theme-pro'); ?></p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('wpchild-export-nonce', 'wpchild_export_nonce'); ?>
                        <select name="theme_to_export" class="wpchild-select-theme">
                            <option value=""><?php esc_html_e('-- Select Theme --', 'wp-child-theme-pro'); ?></option>
                            <?php 
                            // Add all themes to dropdown
                            $all_themes = wp_get_themes();
                            foreach ($all_themes as $theme_slug => $theme) : ?>
                                <option value="<?php echo esc_attr($theme_slug); ?>"><?php 
                                    echo esc_html($theme->get('Name')); 
                                    if ($theme->parent()) {
                                        echo ' (' . esc_html__('Child of', 'wp-child-theme-pro') . ' ' . esc_html($theme->parent()->get('Name')) . ')';
                                    }
                                ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" name="wpchild_export" class="button" value="<?php esc_html_e('Export', 'wp-child-theme-pro'); ?>" />
                    </form>
                    
                    <h3><?php esc_html_e('Import Child Theme', 'wp-child-theme-pro'); ?></h3>
                    <p><?php esc_html_e('Import a child theme from a ZIP file.', 'wp-child-theme-pro'); ?></p>
                    
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field('wpchild-import-nonce', 'wpchild_import_nonce'); ?>
                        <input type="file" name="theme_zip" accept=".zip" />
                        <input type="submit" name="wpchild_import" class="button" value="<?php esc_html_e('Import', 'wp-child-theme-pro'); ?>" />
                    </form>
                </div>
            </div>
            
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Backup History', 'wp-child-theme-pro'); ?></h2>
                    
                    <?php if (empty($backups)) : ?>
                        <p><?php esc_html_e('No backups found.', 'wp-child-theme-pro'); ?></p>
                    <?php else : ?>
                        <table class="wpchild-backup-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Theme', 'wp-child-theme-pro'); ?></th>
                                    <th><?php esc_html_e('Date', 'wp-child-theme-pro'); ?></th>
                                    <th><?php esc_html_e('Size', 'wp-child-theme-pro'); ?></th>
                                    <th><?php esc_html_e('Actions', 'wp-child-theme-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->plugin->backup->get_backups() as $backup) : ?>
                                    <tr>
                                        <td><?php echo esc_html($backup['theme']); ?></td>
                                        <td><?php echo esc_html($backup['date']); ?></td>
                                        <td><?php echo esc_html($backup['size']); ?></td>
                                        <td>
                                            <a href="#" class="button wpchild-restore-backup" data-backup="<?php echo esc_attr($backup['file']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('wpchild-nonce')); ?>"><?php esc_html_e('Restore', 'wp-child-theme-pro'); ?></a>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-child-theme-pro-backup&action=download&file=' . urlencode($backup['file'])), 'wpchild-download-' . $backup['file'], 'nonce')); ?>" class="button button-secondary"><?php esc_html_e('Download', 'wp-child-theme-pro'); ?></a>
                                            <a href="#" class="button button-secondary wpchild-delete-backup" data-backup="<?php echo esc_attr($backup['file']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('wpchild-nonce')); ?>"><?php esc_html_e('Delete', 'wp-child-theme-pro'); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
