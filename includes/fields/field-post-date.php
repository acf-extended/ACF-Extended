<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_post_date')):

class acfe_field_post_date extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_post_date';
        $this->label = __('Post Date', 'acfe');
        $this->category = 'Post';
        $this->defaults = array();
        
        parent::__construct();
        
    }
    
    function load_field($field){
        
        $field['name'] = '';
        $field['required'] = 0;
        $field['value'] = false;
        
        return $field;
        
    }
    
    function prepare_field($field){
        
        $post_id = acf_get_valid_post_id();
        
        if(!$post_id)
            return false;
        
        $data = acf_get_post_id_info($post_id);
        
        // Bail early if not Post
        if($data['type'] !== 'post')
            return false;
        
        return $field;
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_post_date');

endif;