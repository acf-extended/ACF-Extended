<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_assets')):

class acfe_assets{
    
    public $data = array();
    
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
        
        // text
        $text = apply_filters('acfe/localize_text', array(
            'Close'                                             => __('Close', 'acfe'),
            'Update'                                            => __('Update', 'acfe'),
            'Read more'                                         => __('Read more', 'acfe'),
            'Details'                                           => __('Details', 'acfe'),
            'Debug'                                             => __('Debug', 'acfe'),
            'Data has been copied to your clipboard.'           => __('Data has been copied to your clipboard.', 'acfe'),
            'Please copy the following data to your clipboard.' => __('Please copy the following data to your clipboard.', 'acfe'),
        ));
        
        acf_localize_text($text);
        
        // data
        $data = apply_filters('acfe/localize_data', array(
            'version'           => ACFE_VERSION,
            'home_url'          => home_url(),
            'is_admin'          => is_admin(),
            'is_user_logged_in' => is_user_logged_in(),
        ));
        
        // set data
        $this->set_data($data);
        
        // localize
        acfe_localize_data();
        
    }
    
    
    /**
     * get_data
     *
     * @param $path
     * @param $default
     *
     * @return array|mixed|null
     */
    function get_data($path = null, $default = null){
        return !$path ? $this->data : acfe_array_get($this->data, $path, $default);
    }
    
    
    /**
     * set_data
     *
     * @param $path
     * @param $value
     *
     * @return void
     */
    function set_data($path = null, $value = null){
        
        if($value === null){
            $value = $path;
            $path = null;
        }
        
        if(!$path){
            $this->data = array_merge($this->data, $value);
        }else{
            acfe_array_set($this->data, $path, $value);
        }
        
    }
    
    
    /**
     * unset_data
     *
     * @param $path
     *
     * @return void
     */
    function unset_data($path = null){
        
        if(!$path){
            $this->data = array();
        }else{
            acfe_array_unset($this->data, $path);
        }
        
    }
    
}

acf_new_instance('acfe_assets');

endif;


/**
 * acfe_localize_data
 *
 * @return void
 */
function acfe_localize_data(){
    acf_localize_data(array('acfe' => acfe_get_localize_data()));
}


/**
 * acfe_get_localize_data
 *
 * @return array|false|string[]
 */
function acfe_get_localize_data($path = null, $default = null){
    return acf_get_instance('acfe_assets')->get_data($path, $default);
}


/**
 * acfe_set_localize_data
 *
 * @param null $path
 * @param null $value
 */
function acfe_set_localize_data($path = null, $value = null){
    acf_get_instance('acfe_assets')->set_data($path, $value);
    acfe_localize_data();
}


/**
 * acfe_append_localize_data
 *
 * @param $path
 * @param $value
 *
 * @return void
 *
 * @deprecated
 */
function acfe_append_localize_data($path = null, $value = null){
    acfe_deprecated_function('acfe_append_localize_data()', '0.9.0.5', 'acfe_set_localize_data()');
    acfe_set_localize_data($path, $value);
}