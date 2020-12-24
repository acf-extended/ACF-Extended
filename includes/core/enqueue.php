<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_enqueue')):

class acfe_enqueue{
    
    function __construct(){
        
        // Hooks
        add_action('init',                              array($this, 'register_assets'));
        add_action('admin_enqueue_scripts',             array($this, 'admin_enqueue'));
        add_action('acf/input/admin_enqueue_scripts',   array($this, 'acf_enqueue'));
        
    }
    
    function register_assets(){
    
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
    
    /**
     * Admin Enqueue
     */
    function admin_enqueue(){
    
        // Admin
        wp_enqueue_style('acf-extended-admin');
    
        // Field Group
        if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
        
            wp_enqueue_style('acf-extended-field-group');
        
        }
        
    }
    
    /**
     * ACF (Front + Back) Enqueue
     */
    function acf_enqueue(){
        
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
            'acfe_version' => ACFE_VERSION,
            'acfe' => array(
                'home_url'          => home_url(),
                'is_admin'          => is_admin(),
                'is_user_logged_in' => is_user_logged_in(),
            )
        ));
        
        $read_more = __('Read more...');
        $read_more = str_replace('…', '', $read_more);
        $read_more = str_replace('...', '', $read_more);
        
        acf_localize_text(array(
            'Close'     => __('Close', 'acf'),
            'Read more' => $read_more,
        ));
        
    }
    
}

new acfe_enqueue();

endif;