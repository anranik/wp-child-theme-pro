<?php
/**
 * Child Theme Customizer Class
 * 
 * @package WP_Child_Theme_Pro
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Child_Theme_Customizer {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('customize_register', array($this, 'customize_register'));
    }

    /**
     * Register customizer settings
     *
     * @param WP_Customize_Manager $wp_customize Customizer manager
     */
    public function customize_register($wp_customize) {
        // Get current theme
        $theme = wp_get_theme();
        
        // Only add settings for child themes
        if (!$theme->parent()) {
            return;
        }
        
        // Add Child Theme Pro section
        $wp_customize->add_section('wpchild_customizer_section', array(
            'title' => __('Child Theme Pro', 'wp-child-theme-pro'),
            'priority' => 200,
        ));
        
        // Add custom CSS setting
        $wp_customize->add_setting('wpchild_custom_css', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'wp_filter_nohtml_kses',
        ));
        
        // Add custom CSS control
        $wp_customize->add_control('wpchild_custom_css', array(
            'label' => __('Additional CSS', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_custom_css',
            'type' => 'textarea',
        ));
        
        // Add custom font settings
        $wp_customize->add_setting('wpchild_heading_font', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        $wp_customize->add_control('wpchild_heading_font', array(
            'label' => __('Heading Font', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_heading_font',
            'type' => 'select',
            'choices' => $this->get_google_fonts(),
        ));
        
        $wp_customize->add_setting('wpchild_body_font', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        $wp_customize->add_control('wpchild_body_font', array(
            'label' => __('Body Font', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_body_font',
            'type' => 'select',
            'choices' => $this->get_google_fonts(),
        ));
        
        // Add color settings
        $wp_customize->add_setting('wpchild_primary_color', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wpchild_primary_color', array(
            'label' => __('Primary Color', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_primary_color',
        )));
        
        $wp_customize->add_setting('wpchild_secondary_color', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ));
        
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wpchild_secondary_color', array(
            'label' => __('Secondary Color', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_secondary_color',
        )));
        
        // Add layout settings
        $wp_customize->add_setting('wpchild_layout', array(
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        $wp_customize->add_control('wpchild_layout', array(
            'label' => __('Layout', 'wp-child-theme-pro'),
            'section' => 'wpchild_customizer_section',
            'settings' => 'wpchild_layout',
            'type' => 'radio',
            'choices' => array(
                '' => __('Default', 'wp-child-theme-pro'),
                'wide' => __('Wide', 'wp-child-theme-pro'),
                'boxed' => __('Boxed', 'wp-child-theme-pro'),
                'fluid' => __('Fluid', 'wp-child-theme-pro'),
            ),
        ));
    }

    /**
     * Get list of Google Fonts
     *
     * @return array Font list
     */
    private function get_google_fonts() {
        $fonts = array(
            '' => __('Default', 'wp-child-theme-pro'),
            'Open Sans' => 'Open Sans',
            'Roboto' => 'Roboto',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Oswald' => 'Oswald',
            'Raleway' => 'Raleway',
            'Poppins' => 'Poppins',
            'Ubuntu' => 'Ubuntu',
            'Playfair Display' => 'Playfair Display',
            'Merriweather' => 'Merriweather',
        );
        
        return $fonts;
    }

    /**
     * Save custom CSS to child theme
     *
     * @param string $theme Child theme slug
     * @param string $css CSS content
     * @return bool|WP_Error Success or error
     */
    public function save_custom_css($theme, $css) {
        $theme_dir = get_theme_root() . '/' . $theme;
        
        // Check if theme exists
        if (!is_dir($theme_dir)) {
            return new WP_Error('invalid_theme', __('Invalid theme.', 'wp-child-theme-pro'));
        }
        
        // Check if theme is a child theme
        $theme_data = wp_get_theme($theme);
        if (!$theme_data->parent()) {
            return new WP_Error('not_child_theme', __('The selected theme is not a child theme.', 'wp-child-theme-pro'));
        }
        
        // Append CSS to style.css
        $style_css = trailingslashit($theme_dir) . 'style.css';
        
        if (!file_exists($style_css)) {
            return new WP_Error('no_style_css', __('style.css not found in child theme.', 'wp-child-theme-pro'));
        }
        
        // Get current content
        $current_css = file_get_contents($style_css);
        
        // Extract theme header
        preg_match('/\/\*.*?\*\//s', $current_css, $header_match);
        $header = isset($header_match[0]) ? $header_match[0] : '';
        
        // Create new content with header and custom CSS
        $new_css = $header . "\n\n/* Custom CSS */\n" . $css;
        
        // Write to file
        if (file_put_contents($style_css, $new_css)) {
            return true;
        } else {
            return new WP_Error('write_error', __('Failed to write CSS to file.', 'wp-child-theme-pro'));
        }
    }

    /**
     * Save custom JS to child theme
     *
     * @param string $theme Child theme slug
     * @param string $js JS content
     * @return bool|WP_Error Success or error
     */
    public function save_custom_js($theme, $js) {
        $theme_dir = get_theme_root() . '/' . $theme;
        
        // Check if theme exists
        if (!is_dir($theme_dir)) {
            return new WP_Error('invalid_theme', __('Invalid theme.', 'wp-child-theme-pro'));
        }
        
        // Check if theme is a child theme
        $theme_data = wp_get_theme($theme);
        if (!$theme_data->parent()) {
            return new WP_Error('not_child_theme', __('The selected theme is not a child theme.', 'wp-child-theme-pro'));
        }
        
        // Create js directory if it doesn't exist
        $js_dir = trailingslashit($theme_dir) . 'js';
        if (!is_dir($js_dir) && !wp_mkdir_p($js_dir)) {
            return new WP_Error('mkdir_failed', __('Failed to create js directory.', 'wp-child-theme-pro'));
        }
        
        // Create custom.js file
        $custom_js = trailingslashit($js_dir) . 'custom.js';
        
        // Add header comment
        $js_content = "/**\n";
        $js_content .= " * Custom JS for " . $theme_data->get('Name') . "\n";
        $js_content .= " * Generated by WP Child Theme Pro\n";
        $js_content .= " */\n\n";
        $js_content .= $js;
        
        // Write to file
        if (file_put_contents($custom_js, $js_content)) {
            return true;
        } else {
            return new WP_Error('write_error', __('Failed to write JS to file.', 'wp-child-theme-pro'));
        }
    }

    /**
     * Apply customizer settings to child theme
     *
     * @param string $theme Child theme slug
     * @return bool Success or failure
     */
    public function apply_customizer_settings($theme) {
        $theme_data = wp_get_theme($theme);
        
        // Check if theme is a child theme
        if (!$theme_data->parent()) {
            return false;
        }
        
        // Get customizer settings
        $settings = get_theme_mods();
        
        // Generate custom CSS
        $custom_css = '';
        
        // Add font styles if set
        if (!empty($settings['wpchild_heading_font'])) {
            $custom_css .= "h1, h2, h3, h4, h5, h6 { font-family: '{$settings['wpchild_heading_font']}', sans-serif; }\n";
        }
        
        if (!empty($settings['wpchild_body_font'])) {
            $custom_css .= "body, p, li, div { font-family: '{$settings['wpchild_body_font']}', sans-serif; }\n";
        }
        
        // Add color styles if set
        if (!empty($settings['wpchild_primary_color'])) {
            $custom_css .= ":root { --primary-color: {$settings['wpchild_primary_color']}; }\n";
            $custom_css .= "a, .primary-color, .btn-primary { color: {$settings['wpchild_primary_color']}; }\n";
            $custom_css .= ".bg-primary, .btn-primary { background-color: {$settings['wpchild_primary_color']}; }\n";
        }
        
        if (!empty($settings['wpchild_secondary_color'])) {
            $custom_css .= ":root { --secondary-color: {$settings['wpchild_secondary_color']}; }\n";
            $custom_css .= ".secondary-color, .btn-secondary { color: {$settings['wpchild_secondary_color']}; }\n";
            $custom_css .= ".bg-secondary, .btn-secondary { background-color: {$settings['wpchild_secondary_color']}; }\n";
        }
        
        // Add layout styles if set
        if (!empty($settings['wpchild_layout'])) {
            switch ($settings['wpchild_layout']) {
                case 'wide':
                    $custom_css .= ".container { max-width: 1400px; width: 95%; }\n";
                    break;
                case 'boxed':
                    $custom_css .= "body { max-width: 1200px; margin-left: auto; margin-right: auto; background-color: #f0f0f0; }\n";
                    $custom_css .= ".site, .website, #page { background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }\n";
                    break;
                case 'fluid':
                    $custom_css .= ".container { max-width: 100%; width: 95%; }\n";
                    break;
            }
        }
        
        // Add custom CSS
        if (!empty($settings['wpchild_custom_css'])) {
            $custom_css .= $settings['wpchild_custom_css'];
        }
        
        // Save custom CSS to child theme if not empty
        if (!empty($custom_css)) {
            return $this->save_custom_css($theme, $custom_css);
        }
        
        return true;
    }
}
