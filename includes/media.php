<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_media')):

class acfe_media{
    
    public $upload_field = false;
    
    /**
     * construct
     */
    function __construct(){
        
        // hooks
        add_filter('acf/upload_prefilter', array($this, 'attachment_upload'), 10, 3);
        
        // variations
        acf_add_filter_variations('acfe/upload_dir',  array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/upload_file', array('type', 'name', 'key'), 1);
        
    }
    
    
    /**
     * attachment_upload
     *
     * acf/upload_prefilter
     *
     * @param $errors
     * @param $file
     * @param $field
     *
     * @return mixed
     */
    function attachment_upload($errors, $file, $field){
        
        // vars
        $this->upload_field = $field;
        
        // filters
        add_filter('upload_dir',                 array($this, 'handle_upload_dir'), 20);
        add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_file'), 20);
        
        // return
        return $errors;
        
    }
    
    
    /**
     * handle_upload_dir
     *
     * upload_dir:20
     *
     * @param $uploads
     *
     * @return mixed|void
     */
    function handle_upload_dir($uploads){
        
        // vars
        $field = $this->upload_field;
        
        // return
        return apply_filters('acfe/upload_dir', $uploads, $field);
        
    }
    
    
    /**
     * handle_upload_file
     *
     * wp_handle_upload_prefilter:20
     *
     * @param $file
     *
     * @return mixed|void
     */
    function handle_upload_file($file){
        
        // vars
        $field = $this->upload_field;
        
        // return
        return apply_filters('acfe/upload_file', $file, $field);
        
    }
    
}

acf_new_instance('acfe_media');

endif;