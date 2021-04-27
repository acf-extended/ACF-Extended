<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_group_permissions')):

class acfe_field_group_permissions{
 
    function __construct(){
        
        add_filter('acfe/prepare_field_group', array($this, 'prepare_field_group'));
        
    }
    
    /*
     * Prepare Field Group
     */
    function prepare_field_group($field_group){
        
        if(!acf_maybe_get($field_group, 'acfe_permissions'))
            return $field_group;
        
        $current_user_roles = acfe_get_current_user_roles();
        $render_field_group = false;
        
        foreach($current_user_roles as $current_user_role){
            
            foreach($field_group['acfe_permissions'] as $field_group_role){
                
                if($current_user_role !== $field_group_role)
                    continue;
                
                $render_field_group = true;
                break;
                
            }
            
            if($render_field_group)
                break;
            
        }
        
        if(!$render_field_group)
            $field_group = false;
        
        return $field_group;
        
    }
    
}

// initialize
new acfe_field_group_permissions();

endif;