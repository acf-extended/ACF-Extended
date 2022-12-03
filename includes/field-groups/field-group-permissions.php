<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_permissions')):

class acfe_field_group_permissions{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acfe/prepare_field_group', array($this, 'prepare_field_group'));
        
    }
    
    /**
     * prepare_field_group
     *
     * @param $field_group
     *
     * @return false|mixed
     */
    function prepare_field_group($field_group){
        
        // no permissions
        // display normally
        if(!acf_maybe_get($field_group, 'acfe_permissions')){
            return $field_group;
        }
        
        // get current user roles
        $current_user_roles = acfe_get_current_user_roles();
        
        // loop roles
        foreach($current_user_roles as $current_user_role){
            
            foreach($field_group['acfe_permissions'] as $field_group_role){
                
                // current user has the selected role
                // display normally
                if($field_group_role === $current_user_role){
                    return $field_group;
                }
                
            }
            
        }
        
        // hide field group
        return false;
        
    }
    
}

// initialize
new acfe_field_group_permissions();

endif;