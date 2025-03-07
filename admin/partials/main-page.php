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
    <h1><?php esc_html_e('WP Child Theme Pro', 'wp-child-theme-pro'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-child-theme-pro" class="nav-tab nav-tab-active"><?php esc_html_e('Generate Child Theme', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-customize" class="nav-tab"><?php esc_html_e('Customize', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-backup" class="nav-tab"><?php esc_html_e('Backup & Restore', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-settings" class="nav-tab"><?php esc_html_e('Settings', 'wp-child-theme-pro'); ?></a>
    </h2>
    
    <div class="wpchild-page-content">
        <div class="wpchild-row">
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Generate Child Theme', 'wp-child-theme-pro'); ?></h2>
                    <p><?php esc_html_e('Create a child theme from any installed parent theme with just a few clicks.', 'wp-child-theme-pro'); ?></p>
                    
                    <form id="wpchild-generate-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="parent_theme"><?php esc_html_e('Parent Theme', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <select name="parent_theme" id="parent_theme" class="regular-text" required>
                                        <option value=""><?php esc_html_e('-- Select Parent Theme --', 'wp-child-theme-pro'); ?></option>
                                        <?php
                                        // Get current theme
                                        $current_theme = wp_get_theme();
                                        $current_theme_slug = $current_theme->get_stylesheet();
                                        
                                        // Get available themes
                                        $themes = wp_get_themes();
                                        
                                        foreach ($themes as $theme_slug => $theme) {
                                            // Skip child themes
                                            if ($theme->parent()) {
                                                continue;
                                            }
                                            
                                            // Check if this is the current active theme
                                            $selected = ($theme_slug === $current_theme_slug) ? 'selected="selected"' : '';
                                            
                                            echo '<option value="' . esc_attr($theme_slug) . '" ' . esc_attr($selected) . '>' . esc_html($theme->get('Name')) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child_name"><?php esc_html_e('Child Theme Name', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child_name" name="child_name" class="regular-text" required 
                                           value="<?php echo esc_attr($current_theme->get('Name') . ' Child'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child_desc"><?php esc_html_e('Description', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <textarea id="child_desc" name="child_desc" class="large-text" rows="3"><?php 
                                        // translators: %s - describe the placeholder (e.g. the widget title)
                                        echo sprintf(esc_textarea(__('A child theme of the %s theme', 'wp-child-theme-pro')), 
                                                 esc_html($current_theme->get('Name'))); 
                                    ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child_author"><?php esc_html_e('Author', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child_author" name="child_author" class="regular-text" value="<?php echo esc_attr(isset($options['default_author']) ? $options['default_author'] : get_bloginfo('name')); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="child_version"><?php esc_html_e('Version', 'wp-child-theme-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="child_version" name="child_version" class="regular-text" value="1.0.0">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Options', 'wp-child-theme-pro'); ?>
                                </th>
                                <td>
                                    <fieldset>
                                        <label for="copy_settings">
                                            <input type="checkbox" id="copy_settings" name="copy_settings" value="1" <?php checked(1, isset($options['copy_parent_settings']) ? $options['copy_parent_settings'] : true); ?>>
                                            <?php esc_html_e('Copy parent theme settings', 'wp-child-theme-pro'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php esc_html_e('Select Files to Include', 'wp-child-theme-pro'); ?></h3>
                        <p><?php esc_html_e('Select specific files from the parent theme to include in your child theme.', 'wp-child-theme-pro'); ?></p>
                        
                        <div id="parent-files-container" class="wpchild-file-list">
                            <div class="wpchild-loading">
                                <?php esc_html_e('Please select a parent theme first...', 'wp-child-theme-pro'); ?>
                            </div>
                        </div>
                        
                        <?php wp_nonce_field('wpchild-nonce', 'wpchild_nonce'); ?>
                        <p class="submit">
                            <input type="submit" name="generate" id="generate-button" class="button button-primary" value="<?php esc_html_e('Generate Child Theme', 'wp-child-theme-pro'); ?>">
                            <span class="spinner"></span>
                        </p>
                    </form>
                </div>
            </div>
            
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php esc_html_e('Quick Guide', 'wp-child-theme-pro'); ?></h2>
                    <ol>
                        <li><?php esc_html_e('Select the parent theme from the dropdown.', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Enter a name for your child theme.', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Optionally add a description, author name, and version.', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Choose whether to copy parent theme settings.', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Select which parent theme files to include.', 'wp-child-theme-pro'); ?></li>
                        <li><?php esc_html_e('Click "Generate Child Theme" to create your child theme.', 'wp-child-theme-pro'); ?></li>
                    </ol>
                    
                    <div class="wpchild-info-box">
                        <h3><?php esc_html_e('Why Use a Child Theme?', 'wp-child-theme-pro'); ?></h3>
                        <p><?php esc_html_e('Using a child theme is the recommended way to modify an existing WordPress theme. A child theme allows you to update the parent theme without losing your customizations.', 'wp-child-theme-pro'); ?></p>
                        
                        <h3><?php esc_html_e('Key Benefits', 'wp-child-theme-pro'); ?></h3>
                        <ul>
                            <li><?php esc_html_e('Safely update the parent theme without losing customizations', 'wp-child-theme-pro'); ?></li>
                            <li><?php esc_html_e('Customize theme functionality with your own code', 'wp-child-theme-pro'); ?></li>
                            <li><?php esc_html_e('Modify the theme appearance with custom CSS', 'wp-child-theme-pro'); ?></li>
                            <li><?php esc_html_e('Learn theme development by starting with a working theme', 'wp-child-theme-pro'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <div class="wpchild-card wpchild-result" style="display: none;">
                    <h2><?php esc_html_e('Success!', 'wp-child-theme-pro'); ?></h2>
                    <div id="wpchild-result-message"></div>
                    
                    <div class="wpchild-result-actions">
                        <a href="#" id="activate-theme" class="button button-primary"><?php esc_html_e('Activate Child Theme', 'wp-child-theme-pro'); ?></a>
                        <a href="?page=wp-child-theme-pro-customize" class="button"><?php esc_html_e('Customize Child Theme', 'wp-child-theme-pro'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
