<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_taxonomy')):

class acfe_field_taxonomy extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'taxonomy';
        $this->replace = array(
            'load_value',
            'update_value',
        );
        
    }
    
    
    /**
     * load_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function load_value($value, $post_id, $field){
        
        // disable load terms for local meta & acfe_form
        if(acfe_is_local_post_id($post_id) || acfe_starts_with($post_id, 'acfe_form')){
            $field['load_terms'] = false;
        }
        
        // return
        return $this->instance->load_value($value, $post_id, $field);
        
    }
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|mixed
     */
    function update_value($value, $post_id, $field){
        
        // disable save terms for local meta & acfe_form
        if(acfe_is_local_post_id($post_id) || acfe_starts_with($post_id, 'acfe_form')){
            $field['save_terms'] = false;
        }
        
        // return
        return $this->instance->update_value($value, $post_id, $field);
        
    }
    
}

acf_new_instance('acfe_field_taxonomy');

endif;