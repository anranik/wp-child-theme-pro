<?php
/**
 * Settings admin page
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
        <a href="?page=wp-child-theme-pro-backup" class="nav-tab"><?php esc_html_e('Backup & Restore', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-settings" class="nav-tab nav-tab-active"><?php esc_html_e('Settings', 'wp-child-theme-pro'); ?></a>
    </h2>
    
    <div class="wpchild-page-content">
        <form method="post" action="options.php">
            <?php settings_fields('wp_child_theme_pro_options'); ?>
            <?php do_settings_sections('wp_child_theme_pro_settings'); ?>
            <?php submit_button(); ?>
        </form>
        
        <div class="wpchild-row">
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('About WP Child Theme Pro', 'wp-child-theme-pro'); ?></h2>
                    <p><?php esc_html_e('WP Child Theme Pro is a powerful WordPress plugin for creating and managing child themes.', 'wp-child-theme-pro'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Version', 'wp-child-theme-pro'); ?></th>
                            <td><?php echo esc_html(WPCHILD_VERSION); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Support', 'wp-child-theme-pro'); ?></th>
                            <td><a href="https://example.com/support" target="_blank"><?php esc_html_e('Get Support', 'wp-child-theme-pro'); ?></a></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Documentation', 'wp-child-theme-pro'); ?></th>
                            <td><a href="https://example.com/docs" target="_blank"><?php esc_html_e('View Documentation', 'wp-child-theme-pro'); ?></a></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Upgrade to Pro', 'wp-child-theme-pro'); ?></h2>
                    <p><?php esc_html_e('Upgrade to WP Child Theme Pro for advanced features:', 'wp-child-theme-pro'); ?></p>
                    
                    <ul>
                        <li><?php esc_html_e('Advanced PHP Editor with syntax highlighting', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Pre-designed child theme templates', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Auto-update system for child themes', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('AI-powered custom CSS generator', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('GitHub integration for version control', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Role-based access control', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Priority support', 'wp-child-theme-pro'); ?></li>
                    </ul>
                    
                    <p><a href="https://example.com/upgrade" class="button button-primary" target="_blank"><?php esc_html_e('Upgrade Now', 'wp-child-theme-pro'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>
