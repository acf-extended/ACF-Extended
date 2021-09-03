<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_assets')):

class acfe_assets{
    
    /*
     * Construct
     */
    function __construct(){
        
        // Hooks
        add_action('init',                              array($this, 'init'));
        add_action('admin_enqueue_scripts',             array($this, 'wp_admin_enqueue_scripts'));
        add_action('acf/input/admin_enqueue_scripts',   array($this, 'acf_admin_enqueue_scripts'));
        
    }
    
    /*
     * Init
     */
    function init(){
    
        $version = ACFE_VERSION;
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    
        // register scripts
        wp_register_script('acf-extended',              acfe_get_url("assets/js/acfe{$min}.js"),                array('acf-input'),         $version);
        wp_register_script('acf-extended-input',        acfe_get_url("assets/js/acfe-input{$min}.js"),          array('acf-extended'),      $version);
        wp_register_script('acf-extended-admin',        acfe_get_url("assets/js/acfe-admin{$min}.js"),          array('acf-extended'),      $version);
        wp_register_script('acf-extended-field-group',  acfe_get_url("assets/js/acfe-field-group{$min}.js"),    array('acf-field-group'),   $version);
        wp_register_script('acf-extended-ui',           acfe_get_url("assets/js/acfe-ui{$min}.js"),             array('acf-extended'),      $version);
    
        // register styles
        wp_register_style('acf-extended',               acfe_get_url("assets/css/acfe{$min}.css"),              array(),                    $version);
        wp_register_style('acf-extended-input',         acfe_get_url("assets/css/acfe-input{$min}.css"),        array(),                    $version);
        wp_register_style('acf-extended-admin',         acfe_get_url("assets/css/acfe-admin{$min}.css"),        array(),                    $version);
        wp_register_style('acf-extended-field-group',   acfe_get_url("assets/css/acfe-field-group{$min}.css"),  array(),                    $version);
        wp_register_style('acf-extended-ui',            acfe_get_url("assets/css/acfe-ui{$min}.css"),           array(),                    $version);
        
    }
    
    /*
     * WP Admin Enqueue Scripts
     */
    function wp_admin_enqueue_scripts(){
    
        // Admin
        wp_enqueue_style('acf-extended-admin');
    
        // Field Group
        if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
        
            wp_enqueue_style('acf-extended-field-group');
        
        }
        
    }
    
    /*
     * ACF Admin Enqueue Scripts
     */
    function acf_admin_enqueue_scripts(){
        
        // Global
        wp_enqueue_style('acf-extended');
        wp_enqueue_script('acf-extended');
    
        // Input
        wp_enqueue_style('acf-extended-input');
        wp_enqueue_script('acf-extended-input');
    
        // Admin
        if(is_admin()){
    
            wp_enqueue_script('acf-extended-admin');
            
        }
    
        // Field Group
        if(acf_is_screen(array('acf-field-group'))){
            
            wp_enqueue_script('acf-extended-field-group');
        
        }
        
        acf_localize_data(array(
            'acfe' => array(
                'version'           => ACFE_VERSION,
                'home_url'          => home_url(),
                'is_admin'          => is_admin(),
                'is_user_logged_in' => is_user_logged_in(),
            )
        ));
        
        acf_localize_text(array(
            'Close'     => __('Close', 'acfe'),
            'Read more' => __('Read more', 'acfe'),
            'Details'   => __('Details', 'acfe'),
            'Debug'     => __('Debug', 'acfe'),
        ));
        
    }
    
}

new acfe_assets();

endif;