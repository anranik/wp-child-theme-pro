<?php
/**
 * Customize admin page
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
        <a href="?page=wp-child-theme-pro" class="nav-tab"><?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-customize" class="nav-tab nav-tab-active"><?php _e('Customize', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-backup" class="nav-tab"><?php _e('Backup & Restore', 'wp-child-theme-pro'); ?></a>
        <a href="?page=wp-child-theme-pro-settings" class="nav-tab"><?php _e('Settings', 'wp-child-theme-pro'); ?></a>
    </h2>
    
    <div class="wpchild-page-content">
        <?php if (empty($child_themes)) : ?>
            <div class="wpchild-card">
                <h2><?php _e('No Child Themes Found', 'wp-child-theme-pro'); ?></h2>
                <p><?php _e('You need to create a child theme before you can customize it.', 'wp-child-theme-pro'); ?></p>
                <a href="?page=wp-child-theme-pro" class="button button-primary"><?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?></a>
            </div>
        <?php else : ?>
            <div class="wpchild-row">
                <div class="wpchild-col-6">
                    <div class="wpchild-card">
                        <h2><?php _e('Customize Child Theme', 'wp-child-theme-pro'); ?></h2>
                        
                        <p><?php _e('Select a child theme to customize:', 'wp-child-theme-pro'); ?></p>
                        
                        <select id="child-theme-select">
                            <option value=""><?php _e('-- Select Child Theme --', 'wp-child-theme-pro'); ?></option>
                            <?php foreach ($child_themes as $theme_slug => $theme) : ?>
                                <option value="<?php echo esc_attr($theme_slug); ?>"><?php echo esc_html($theme->get('Name')); ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div class="wpchild-tabs">
                            <a href="#css-tab" class="wpchild-tab active"><?php _e('CSS Editor', 'wp-child-theme-pro'); ?></a>
                            <a href="#js-tab" class="wpchild-tab"><?php _e('JS Editor', 'wp-child-theme-pro'); ?></a>
                            <a href="#php-tab" class="wpchild-tab"><?php _e('PHP Editor', 'wp-child-theme-pro'); ?></a>
                        </div>
                        
                        <div id="css-tab" class="wpchild-tab-content active">
                            <h3><?php _e('Custom CSS', 'wp-child-theme-pro'); ?></h3>
                            <p><?php _e('Add custom CSS to your child theme. This will be added to the style.css file.', 'wp-child-theme-pro'); ?></p>
                            
                            <textarea id="custom-css-editor" rows="15" class="large-text code"></textarea>
                            
                            <p>
                                <button id="save-custom-css" class="button button-primary"><?php _e('Save CSS', 'wp-child-theme-pro'); ?></button>
                                <span class="wpchild-saving"><?php _e('Saving...', 'wp-child-theme-pro'); ?></span>
                                <span class="wpchild-success"><?php _e('Saved successfully!', 'wp-child-theme-pro'); ?></span>
                            </p>
                        </div>
                        
                        <div id="js-tab" class="wpchild-tab-content">
                            <h3><?php _e('Custom JavaScript', 'wp-child-theme-pro'); ?></h3>
                            <p><?php _e('Add custom JavaScript to your child theme. This will be added to the js/custom.js file.', 'wp-child-theme-pro'); ?></p>
                            
                            <textarea id="custom-js-editor" rows="15" class="large-text code"></textarea>
                            
                            <p>
                                <button id="save-custom-js" class="button button-primary"><?php _e('Save JS', 'wp-child-theme-pro'); ?></button>
                                <span class="wpchild-saving"><?php _e('Saving...', 'wp-child-theme-pro'); ?></span>
                                <span class="wpchild-success"><?php _e('Saved successfully!', 'wp-child-theme-pro'); ?></span>
                            </p>
                        </div>
                        
                        <div id="php-tab" class="wpchild-tab-content">
                            <h3><?php _e('PHP Editor', 'wp-child-theme-pro'); ?></h3>
                            <p><?php _e('Edit functions.php and other PHP files in your child theme.', 'wp-child-theme-pro'); ?></p>
                            
                            <div class="wpchild-pro-feature">
                                <h4><?php _e('Pro Feature', 'wp-child-theme-pro'); ?></h4>
                                <p><?php _e('The PHP editor is a premium feature of WP Child Theme Pro. Upgrade to Pro to edit PHP files directly in the dashboard.', 'wp-child-theme-pro'); ?></p>
                                <a href="#" class="button button-primary"><?php _e('Upgrade to Pro', 'wp-child-theme-pro'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="wpchild-col-6">
                    <div class="wpchild-card">
                        <h2><?php _e('Advanced Customization Options', 'wp-child-theme-pro'); ?></h2>
                        
                        <div class="wpchild-theme-preview">
                            <img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="<?php _e('Theme Preview', 'wp-child-theme-pro'); ?>" id="theme-preview-image">
                            <div class="wpchild-theme-info" id="theme-info"></div>
                        </div>
                        
                        <h3><?php _e('Theme Customizer', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('Use the WordPress Theme Customizer for more advanced customization options.', 'wp-child-theme-pro'); ?></p>
                        <a href="#" id="customizer-link" class="button button-secondary"><?php _e('Open Theme Customizer', 'wp-child-theme-pro'); ?></a>
                        
                        <h3><?php _e('Child Theme Files', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('View and edit child theme files directly from the WordPress file editor.', 'wp-child-theme-pro'); ?></p>
                        <a href="#" id="editor-link" class="button button-secondary"><?php _e('Open Theme Editor', 'wp-child-theme-pro'); ?></a>
                        
                        <h3><?php _e('Additional Resources', 'wp-child-theme-pro'); ?></h3>
                        <ul>
                            <li><a href="https://developer.wordpress.org/themes/advanced-topics/child-themes/" target="_blank"><?php _e('WordPress Child Theme Documentation', 'wp-child-theme-pro'); ?></a></li>
                            <li><a href="https://developer.mozilla.org/en-US/docs/Web/CSS" target="_blank"><?php _e('CSS Reference', 'wp-child-theme-pro'); ?></a></li>
                            <li><a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank"><?php _e('JavaScript Reference', 'wp-child-theme-pro'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <script>
                jQuery(document).ready(function($) {
                    // Tab switching
                    $('.wpchild-tab').on('click', function(e) {
                        e.preventDefault();
                        
                        // Hide all tabs
                        $('.wpchild-tab').removeClass('active');
                        $('.wpchild-tab-content').removeClass('active');
                        
                        // Show selected tab
                        $(this).addClass('active');
                        $($(this).attr('href')).addClass('active');
                    });
                    
                    // Child theme selection
                    $('#child-theme-select').on('change', function() {
                        var theme = $(this).val();
                        
                        if (!theme) {
                            return;
                        }
                        
                        // Update theme preview
                        $('#theme-preview-image').attr('src', '<?php echo admin_url('themes.php?theme='); ?>' + theme);
                        $('#theme-info').text($('#child-theme-select option:selected').text());
                        
                        // Update customizer and editor links
                        $('#customizer-link').attr('href', '<?php echo admin_url('customize.php?theme='); ?>' + theme);
                        $('#editor-link').attr('href', '<?php echo admin_url('theme-editor.php?theme='); ?>' + theme);
                        
                        // Load CSS and JS
                        loadThemeFiles(theme);
                    });
                    
                    // Load theme files
                    function loadThemeFiles(theme) {
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'wpchild_get_theme_files',
                                nonce: '<?php echo wp_create_nonce('wpchild-nonce'); ?>',
                                theme: theme
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update editors
                                    if (response.data.css) {
                                        window.cssEditor.setValue(response.data.css);
                                    } else {
                                        window.cssEditor.setValue('');
                                    }
                                    
                                    if (response.data.js) {
                                        window.jsEditor.setValue(response.data.js);
                                    } else {
                                        window.jsEditor.setValue('');
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>
