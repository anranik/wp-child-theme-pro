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

// Get current theme
$current_theme = wp_get_theme();
$is_child_theme = $current_theme->parent() ? true : false;

// Get all child themes for fallback
$all_themes = wp_get_themes();
$child_themes = array();

foreach ($all_themes as $theme_slug => $theme) {
    if ($theme->parent()) {
        $child_themes[$theme_slug] = $theme;
    }
}

// Check if we have an active child theme or any child themes
$has_child_theme = $is_child_theme || !empty($child_themes);
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
        <?php if (!$is_child_theme) : ?>
            <div class="wpchild-notice wpchild-warning">
                <p><strong><?php _e('You are not using a child theme.', 'wp-child-theme-pro'); ?></strong></p>
                <p><?php _e('It is highly recommended to use a child theme for customizations to avoid losing changes during theme updates.', 'wp-child-theme-pro'); ?></p>
                <p><a href="?page=wp-child-theme-pro" class="button button-primary"><?php _e('Generate Child Theme', 'wp-child-theme-pro'); ?></a></p>
            </div>
        <?php endif; ?>
        
        <div class="wpchild-row">
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php _e('Customize Your Theme', 'wp-child-theme-pro'); ?></h2>
                    
                    <?php if ($is_child_theme) : ?>
                    <div class="wpchild-active-theme">
                        <h3><?php printf(__('Active Child Theme: %s', 'wp-child-theme-pro'), $current_theme->get('Name')); ?></h3>
                        <p><?php printf(__('Parent Theme: %s', 'wp-child-theme-pro'), $current_theme->parent()->get('Name')); ?></p>
                    </div>
                    <?php else : ?>
                    <div class="wpchild-active-theme">
                        <h3><?php printf(__('Active Theme: %s', 'wp-child-theme-pro'), $current_theme->get('Name')); ?></h3>
                        <p class="description"><?php _e('Note: This is not a child theme. Your customizations may be lost during theme updates.', 'wp-child-theme-pro'); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="wpchild-tabs">
                        <a href="#" class="wpchild-tab active" data-tab="css"><?php _e('CSS', 'wp-child-theme-pro'); ?></a>
                        <a href="#" class="wpchild-tab" data-tab="js"><?php _e('JavaScript', 'wp-child-theme-pro'); ?></a>
                        <a href="#" class="wpchild-tab" data-tab="php"><?php _e('PHP (Pro)', 'wp-child-theme-pro'); ?></a>
                    </div>
                    
                    <div id="css-tab" class="wpchild-tab-content active">
                        <h3><?php _e('Custom CSS', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('Add custom CSS for your site. This code will be applied globally across your site.', 'wp-child-theme-pro'); ?></p>
                        
                        <div class="wpchild-editor-tips">
                            <p><strong><?php _e('Tips for CSS customization:', 'wp-child-theme-pro'); ?></strong></p>
                            <ul>
                                <li><?php _e('Use browser inspector to identify elements you want to target', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Be specific with your selectors to avoid conflicts', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Use !important sparingly and only when necessary', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Group related styles together with comments', 'wp-child-theme-pro'); ?></li>
                            </ul>
                        </div>
                        
                        <textarea id="custom-css-editor" name="wpchild_custom_css" rows="15" style="width: 100%;"><?php echo esc_textarea(get_theme_mod('wpchild_custom_css', '')); ?></textarea>
                        
                        <p>
                            <button id="save-custom-css" class="button button-primary"><?php _e('Save CSS', 'wp-child-theme-pro'); ?></button>
                            <span class="wpchild-saving"><?php _e('Saving...', 'wp-child-theme-pro'); ?></span>
                            <span class="wpchild-success"><?php _e('Saved!', 'wp-child-theme-pro'); ?></span>
                        </p>
                    </div>
                    
                    <div id="js-tab" class="wpchild-tab-content">
                        <h3><?php _e('Custom JavaScript', 'wp-child-theme-pro'); ?></h3>
                        <p><?php _e('Add custom JavaScript for your site. This code will be applied globally across your site.', 'wp-child-theme-pro'); ?></p>
                        
                        <div class="wpchild-editor-tips">
                            <p><strong><?php _e('Tips for JavaScript customization:', 'wp-child-theme-pro'); ?></strong></p>
                            <ul>
                                <li><?php _e('Wrap your code in a document ready function', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Use jQuery with $ safely by wrapping code in (function($) { ... })(jQuery);', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Avoid conflicts by using unique function and variable names', 'wp-child-theme-pro'); ?></li>
                                <li><?php _e('Consider performance impact with DOM manipulations', 'wp-child-theme-pro'); ?></li>
                            </ul>
                        </div>
                        
                        <textarea id="custom-js-editor" name="wpchild_custom_js" rows="15" style="width: 100%;"><?php echo esc_textarea(get_theme_mod('wpchild_custom_js', '')); ?></textarea>
                        
                        <p>
                            <button id="save-custom-js" class="button button-primary"><?php _e('Save JavaScript', 'wp-child-theme-pro'); ?></button>
                            <span class="wpchild-saving"><?php _e('Saving...', 'wp-child-theme-pro'); ?></span>
                            <span class="wpchild-success"><?php _e('Saved!', 'wp-child-theme-pro'); ?></span>
                        </p>
                    </div>
                    
                    <div id="php-tab" class="wpchild-tab-content">
                        <div class="wpchild-pro-feature">
                            <h3><?php _e('PHP Editing (Pro Feature)', 'wp-child-theme-pro'); ?></h3>
                            <p><?php _e('The ability to edit PHP files is available in the Pro version of this plugin.', 'wp-child-theme-pro'); ?></p>
                            <p><a href="#" class="button button-secondary"><?php _e('Upgrade to Pro', 'wp-child-theme-pro'); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wpchild-col-6">
                <div class="wpchild-card">
                    <h2><?php _e('Theme Preview', 'wp-child-theme-pro'); ?></h2>
                    <div class="theme-preview">
                        <?php if ($current_theme->get_screenshot()) : ?>
                            <img src="<?php echo esc_url($current_theme->get_screenshot()); ?>" alt="<?php echo esc_attr($current_theme->get('Name')); ?>" />
                        <?php else : ?>
                            <div class="no-screenshot"><?php _e('No screenshot available', 'wp-child-theme-pro'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="wpchild-card">
                    <h2><?php _e('Quick Help', 'wp-child-theme-pro'); ?></h2>
                    
                    <div class="wpchild-help">
                        <h3><?php _e('CSS Examples', 'wp-child-theme-pro'); ?></h3>
                        <pre>
/* Change the site title color */
.site-title a {
    color: #ff6b6b;
}

/* Adjust the content width */
.content-area {
    max-width: 1200px;
    margin: 0 auto;
}

/* Customize buttons */
.button, .btn, .wp-block-button__link {
    background-color: #3498db;
    color: #ffffff;
    border-radius: 4px;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.button:hover, .btn:hover, .wp-block-button__link:hover {
    background-color: #2980b9;
}</pre>

                        <h3><?php _e('JavaScript Examples', 'wp-child-theme-pro'); ?></h3>
                        <pre>
// Wrap code in jQuery ready function
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Smooth scroll to anchors
        $('a[href*="#"]:not([href="#"])').click(function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && 
                location.hostname == this.hostname) {
                
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 50
                    }, 1000);
                    return false;
                }
            }
        });
        
        // Add class to menu items on scroll
        $(window).scroll(function() {
            var scrollDistance = $(window).scrollTop();
            
            // Highlight menu item based on scroll position
            $('section').each(function(i) {
                if ($(this).position().top <= scrollDistance + 100) {
                    $('.nav-menu a.active').removeClass('active');
                    $('.nav-menu a').eq(i).addClass('active');
                }
            });
        });
    });
})(jQuery);</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.wpchild-tab').on('click', function(e) {
        e.preventDefault();
        
        var tab = $(this).data('tab');
        
        $('.wpchild-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.wpchild-tab-content').removeClass('active');
        $('#' + tab + '-tab').addClass('active');
    });
});
</script>
