<?php
/**
 * Main admin page
 * 
 * @package WP_Child_Theme_Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpchild-admin">
    <h1><?php _e('WP Child Theme Pro', 'wp-child-theme-pro'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-child-theme-pro" class="nav-tab nav-tab-active"><?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-customize" class="nav-tab"><?php _e('Customize', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-backup" class="nav-tab"><?php _e('Backup & Restore', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-settings" class="nav-tab"><?php _e('Settings', 'wp-child-theme-pro'); ?></a>
    </h2>
    
    <div class="wpchild-page-content">
        <div class="wpchild-row">
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?></h2>
                    <p><?php _e('Create a child theme from any installed parent theme with just a few clicks.', 'wp-child-theme-pro'); ?></p>
                    
                    <form id="wpchild-generate-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="parent-theme"><?php _e('Parent Theme', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <select id="parent-theme" name="parent_theme" required>
                                        <option value=""><?php _e('-- Select Parent Theme --', 'wp-child-theme-pro'); ?></option>
                                        <?php foreach ($parent_themes as $theme_slug => $theme) : ?>
                                            <option value="<?php echo esc_attr($theme_slug); ?>"><?php echo esc_html($theme->get('Name')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child-name"><?php _e('Child Theme Name', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child-name" name="child_name" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child-desc"><?php _e('Description', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <textarea id="child-desc" name="child_desc" class="large-text" rows="3"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child-author"><?php _e('Author', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child-author" name="child_author" class="regular-text" value="<?php echo esc_attr(isset($options['default_author']) ? $options['default_author'] : get_bloginfo('name')); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child-version"><?php _e('Version', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child-version" name="child_version" class="regular-text" value="1.0.0">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php _e('Options', 'wp-child-theme-pro'); ?>
                                </th>
                                <td>
                                    <fieldset>
                                        <label for="copy-settings">
                                            <input type="checkbox" id="copy-settings" name="copy_settings" value="1" <?php checked(1, isset($options['copy_parent_settings']) ? $options['copy_parent_settings'] : true); ?>>
                                            <?php _e('Copy parent theme settings', 'wp-child-theme-pro'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Select Files to Include', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('Select specific files from the parent theme to include in your child theme.', 'wp-child-theme-pro'); ?></p>
                        
                        <div id="parent-files-container" class="wpchild-file-list">
                            <div class="wpchild-loading">
                                <?php _e('Please select a parent theme first...', 'wp-child-theme-pro'); ?>
                            </div>
                        </div>
                        
                        <?php wp_nonce_field('wpchild-nonce', 'wpchild_nonce'); ?>
                        <p class="submit">
                            <input type="submit" name="generate" id="generate-button" class="button button-primary" value="<?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?>">
                            <span class="spinner"></span>
                        </p>
                    </form>
                </div>
            </div>
            
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php _e('Quick Guide', 'wp-child-theme-pro'); ?></h2>
                    <ol>
                        <li><?php _e('Select the parent theme from the dropdown.', 'wp-child-theme-pro'); ?></li>
                        <li><?php _e('Enter a name for your child theme.', 'wp-child-theme-pro'); ?></li>
                        <li><?php _e('Optionally add a description, author name, and version.', 'wp-child-theme-pro'); ?></li>
                        <li><?php _e('Choose whether to copy parent theme settings.', 'wp-child-theme-pro'); ?></li>
                        <li><?php _e('Select which parent theme files to include.', 'wp-child-theme-pro'); ?></li>
                        <li><?php _e('Click "Generate Child Theme" to create your child theme.', 'wp-child-theme-pro'); ?></li>
                    </ol>
                    
                    <div class="wpchild-info-box">
                        <h3><?php _e('Why Use a Child Theme?', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('Using a child theme is the recommended way to modify an existing WordPress theme. A child theme allows you to update the parent theme without losing your customizations.', 'wp-child-theme-pro'); ?></p>
                        
                        <h3><?php _e('Key Benefits', 'wp-child-theme-pro'); ?></h3>
                        <ul>
                            <li><?php _e('Safely update the parent theme without losing customizations', 'wp-child-theme-pro'); ?></li>
                            <li><?php _e('Customize theme functionality with your own code', 'wp-child-theme-pro'); ?></li>
                            <li><?php _e('Modify the theme appearance with custom CSS', 'wp-child-theme-pro'); ?></li>
                            <li><?php _e('Learn theme development by starting with a working theme', 'wp-child-theme-pro'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <div class="wpchild-card wpchild-result" style="display: none;">
                    <h2><?php _e('Success!', 'wp-child-theme-pro'); ?></h2>
                    <div id="wpchild-result-message"></div>
                    
                    <div class="wpchild-result-actions">
                        <a href="#" id="activate-theme" class="button button-primary"><?php _e('Activate Child Theme', 'wp-child-theme-pro'); ?></a>
                        <a href="?page=wp-child-theme-pro-customize" class="button"><?php _e('Customize Child Theme', 'wp-child-theme-pro'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
