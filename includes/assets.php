<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_assets')):

class acfe_assets{
    
    /**
     * construct
     */
    function __construct(){
        
        // Hooks
        add_action('init',                              array($this, 'init'));
        add_action('admin_enqueue_scripts',             array($this, 'admin_enqueue_scripts'));
        add_action('acf/admin_enqueue_scripts',         array($this, 'acf_admin_enqueue_scripts'));
        add_action('acf/input/admin_enqueue_scripts',   array($this, 'acf_input_admin_enqueue_scripts'));
        add_action('acf/enqueue_scripts',               array($this, 'acf_enqueue_scripts'), 99);
        
    }
    
    
    /**
     * init
     */
    function init(){
        
        // vars
        $version = ACFE_VERSION;
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    
        // register scripts
        wp_register_script('acf-extended',              acfe_get_url("assets/js/acfe{$min}.js"),                array('acf'),                               $version);
        wp_register_script('acf-extended-input',        acfe_get_url("assets/js/acfe-input{$min}.js"),          array('acf-extended', 'acf-input'),         $version);
        wp_register_script('acf-extended-admin',        acfe_get_url("assets/js/acfe-admin{$min}.js"),          array('acf-extended'),                      $version);
        wp_register_script('acf-extended-field-group',  acfe_get_url("assets/js/acfe-field-group{$min}.js"),    array('acf-extended', 'acf-field-group'),   $version);
        wp_register_script('acf-extended-ui',           acfe_get_url("assets/js/acfe-ui{$min}.js"),             array('acf-extended'),                      $version);
    
        // register styles
        wp_register_style('acf-extended',               acfe_get_url("assets/css/acfe{$min}.css"),              array(),                                    $version);
        wp_register_style('acf-extended-input',         acfe_get_url("assets/css/acfe-input{$min}.css"),        array(),                                    $version);
        wp_register_style('acf-extended-admin',         acfe_get_url("assets/css/acfe-admin{$min}.css"),        array(),                                    $version);
        wp_register_style('acf-extended-field-group',   acfe_get_url("assets/css/acfe-field-group{$min}.css"),  array(),                                    $version);
        wp_register_style('acf-extended-ui',            acfe_get_url("assets/css/acfe-ui{$min}.css"),           array(),                                    $version);
        
    }
    
    
    /**
     * admin_enqueue_scripts
     *
     * All admin pages
     */
    function admin_enqueue_scripts(){
    
        // admin
        wp_enqueue_style('acf-extended-admin');
    
        // field groups
        if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
            wp_enqueue_style('acf-extended-field-group');
        }
        
    }
    
    
    /**
     * acf_admin_enqueue_scripts
     *
     * acf/admin_enqueue_scripts
     *
     * When acf_enqueue_script('acf') is used
     */
    function acf_admin_enqueue_scripts(){
        
        // global
        wp_enqueue_style('acf-extended');
        wp_enqueue_script('acf-extended');
        
    }
    
    
    /**
     * acf_input_admin_enqueue_scripts
     *
     * acf/input/admin_enqueue_scripts
     *
     * When acf_enqueue_scripts() is used (including acf-input.js)
     */
    function acf_input_admin_enqueue_scripts(){
    
        // input
        wp_enqueue_style('acf-extended-input');
        wp_enqueue_script('acf-extended-input');
    
        // admin
        if(is_admin()){
            wp_enqueue_script('acf-extended-admin');
        }
    
        // field group
        if(acf_is_screen(array('acf-field-group'))){
            wp_enqueue_script('acf-extended-field-group');
        }
        
    }
    
    /**
     * acf_enqueue_scripts
     *
     * acf/enqueue_scripts:99
     *
     * When acf_enqueue_script('acf') is used (late)
     */
    function acf_enqueue_scripts(){
        
        // data
        $data = array(
            'version'           => ACFE_VERSION,
            'home_url'          => home_url(),
            'is_admin'          => is_admin(),
            'is_user_logged_in' => is_user_logged_in(),
        );
        
        // text
        $text = array(
            'Close'     => __('Close', 'acfe'),
            'Update'    => __('Update', 'acfe'),
            'Read more' => __('Read more', 'acfe'),
            'Details'   => __('Details', 'acfe'),
            'Debug'     => __('Debug', 'acfe'),
        );
        
        // filters
        $data = apply_filters('acfe/localize_data', $data);
        $text = apply_filters('acfe/localize_text', $text);
        
        // localize
        acfe_localize_data($data);
        acf_localize_text($text);
        
    }
    
}

new acfe_assets();

endif;

/**
 * acfe_localize_data
 *
 * @param $data
 */
function acfe_localize_data($data){
    
    $acfe_data = acfe_get_localize_data();
    $acfe_data = array_merge($acfe_data, $data);
    
    acf_localize_data(array('acfe' => $acfe_data));
    
}


/**
 * acfe_get_localize_data
 * @return array|false|string[]
 */
function acfe_get_localize_data(){
    
    return acf_get_array(acf_maybe_get(acf_get_instance('ACF_Assets')->data, 'acfe', array()));
    
}


/**
 * acfe_localize_append_data
 *
 * @param $name
 * @param $data
 */
function acfe_append_localize_data($name, $data){
    
    $acfe_data = acfe_get_localize_data();
    
    if(!isset($acfe_data[ $name ])){
        $acfe_data[ $name ] = array();
    }
    
    $acfe_data[ $name ] = acf_get_array($acfe_data[ $name ]);
    $acfe_data[ $name ][] = $data;
    
    acfe_localize_data($acfe_data);
    
}