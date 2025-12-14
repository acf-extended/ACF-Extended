<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_user')):

class acfe_field_user extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'user';
        
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
        
        // vars
        $value = acf_get_array($value);
        
        // loop value
        foreach($value as $user_id){
            
            // check value
            if(!$this->is_value_valid($user_id, $field, $form['post_id'])){
                return false;
            }
            
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * is_value_valid
     *
     * @param $user_id
     * @param $field
     * @param $post_id
     *
     * @return bool
     */
    function is_value_valid($user_id, $field, $post_id){
        
        // bail early
        if(empty($user_id)){
            return false;
        }
        
        // check user exists
        $user_data = get_userdata($user_id);
        if(!$user_data){
            return false;
        }
        
        // vars
        $args = array();
        
        // role
        if(!empty($field['role'])){
            $args['role__in'] = acf_get_array($field['role']);
        }
        
        // filter
        // this filter might be used by developers to set specific users as choices
        $args = apply_filters('acf/fields/user/query', $args, $field, $post_id);
        
        // override search args
        $args['search'] = $user_id;
        $args['search_columns'] = array('ID');
        
        // query users
        $query = new WP_User_Query($args);
        $total_users = $query->get_total();
        
        // check total found
        return ($total_users > 0);
        
    }
    
}

acf_new_instance('acfe_field_user');

endif;