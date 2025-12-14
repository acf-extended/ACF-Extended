<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_relationship')):

class acfe_field_relationship extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'relationship';
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['acfe_add_post'])){
            return $valid;
        }
        
        // cast array
        $value = acf_get_array($value);
        
        // loop values
        foreach($value as $v){
            
            // get post
            $post = get_post($v);
            
            // check post exists
            if(!$post || is_wp_error($post)){
                return false;
            }
            
            // check query method exists
            if(method_exists($this->instance, 'get_ajax_query')){
                
                // query relationship ajax query
                $query = $this->instance->get_ajax_query(array(
                    'field_key' => $field['key'],
                    'post_id'   => $form['post_id'],
                    'include'   => $v,
                ));
                
                // return false if no results
                if(empty($query)){
                    return false;
                }
                
            }
            
        }
        
        // return
        return $valid;
        
    }
    
}

acf_new_instance('acfe_field_relationship');

endif;