<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_permissions')):

class acfe_permissions{
    
    function __construct(){
    
        add_action('acf/render_field_settings',     array($this, 'render_field_settings'));
        add_filter('acf/prepare_field',             array($this, 'prepare_field'));
        
    }
    
    function render_field_settings($field){
        
        if(acf_is_filter_enabled('acfe/field_group/advanced') || acf_maybe_get($field, 'acfe_permissions')){
            
            acf_render_field_setting($field, array(
                'label'         => __('Permissions'),
                'name'          => 'acfe_permissions',
                'key'           => 'acfe_permissions',
                'instructions'  => __('Restrict user roles that are allowed to view and edit this field'),
                'type'          => 'checkbox',
                'required'      => false,
                'default_value' => false,
                'choices'       => acfe_get_roles(),
                'layout'        => 'horizontal',
                'wrapper'       => array(
                    'data-after' => 'instructions'
                )
            ), true);
            
        }
        
    }
    
    function prepare_field($field){
        
        if(!acf_maybe_get($field, 'acfe_permissions'))
            return $field;
        
        $user_roles = acfe_get_current_user_roles();
        $render = false;
        
        foreach($user_roles as $user_role){
            
            foreach($field['acfe_permissions'] as $field_role){
                
                if($user_role !== $field_role)
                    continue;
    
                $render = true;
                break 2;
                
            }
            
        }
        
        if(!$render)
            return false;
        
        return $field;
        
    }
    
}

new acfe_permissions();

endif;