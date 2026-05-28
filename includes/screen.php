<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_screen')):

class acfe_screen{
    
    var $data = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // form data
        add_filter('acf/location/screen',    array($this, 'acf_location_screen'), 99);
        add_action('acf/input/form_data',    array($this, 'acf_form_data'));
        add_action('acf/validate_save_post', array($this, 'acf_validate_save_post'), 0);
        add_action('acf/save_post',          array($this, 'acf_save_post'), 0);
        
    }
    
    
    /**
     * acf_location_screen
     *
     * acf/location/screen:99
     *
     * @param $screen
     *
     * @return mixed
     */
    function acf_location_screen($screen){
        
        // clone var
        $location = $screen;
        
        // remove vars
        acf_extract_vars($location, array('lang', 'ajax'));
        
        // set form data for later use
        acf_set_form_data('location', $location);
        
        // return
        return $screen;
        
    }
    
    
    /**
     * acf_form_data
     *
     * acf/input/form_data
     */
    function acf_form_data($data){
        
        // retrieve location from screen filter
        $location = acf_get_form_data('location');
        
        // prepare data to encrypt
        $encrypt = array(
            'screen'   => acfe_get($data, 'screen', 'post'),
            'post_id'  => acfe_get($data, 'post_id', 0),
            'location' => $location ?: false,
        );
        
        // render input
        acf_hidden_input(array(
            'id'	=> "_acfe_data",
            'name'	=> "_acfe_data",
            'value'	=> acf_encrypt(json_encode($encrypt))
        ));
    
    }
    
    
    /**
     * acf_save_post_form_data
     *
     * acf/validate_save_post:0
     */
    function acf_validate_save_post(){
        
        // get data
        $data = acf_maybe_get_POST('_acfe_data');
        if(empty($data)){
            return;
        }
        
        // decrypt data
        $decrypt = json_decode(acf_decrypt($data), true);
        if(empty($decrypt) || !is_array($decrypt)){
            return;
        }
        
        // default
        $decrypt = wp_parse_args($decrypt, array(
            'screen'   => 'post',
            'post_id'  => 0,
            'location' => false,
        ));
        
        // store data for later use in acf/save_post
        $this->data = $decrypt;
        
        // loop allowed keys
        foreach($decrypt as $key => $value){
            acf_set_form_data($key, $value);
        }
        
    }
    
    
    /**
     * acf_save_post
     *
     * Update the post_id form data again because ACF manually set it in acf_save_post()
     * This is problematic for front-end submission where acfe_form() make a dry-save with acf_save_post(false) to upload files
     *
     * acf/save_post:0
     *
     * @param $post_id
     *
     * @return void
     */
    function acf_save_post($post_id){
        
        // bail early if no data
        if(!empty($this->data)){
            acf_set_form_data('post_id', acfe_get($this->data, 'post_id', 0));
        }
        
    }
    
}

acf_new_instance('acfe_screen');

endif;