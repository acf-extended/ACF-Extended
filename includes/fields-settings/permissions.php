<?php

if(!defined('ABSPATH'))
    exit;

add_action('acf/render_field_settings', 'acfe_permissions_settings', 999);
function acfe_permissions_settings($field){
    
    // Settings
    acf_render_field_setting($field, array(
        'label'         => __('Permissions'),
        'name'          => 'acfe_permissions',
        'key'           => 'acfe_permissions',
        'instructions'  => __('Select user roles that are allowed to view and edit this field. If nothing is selected, then this field will be available to everyone.'),
        'type'          => 'checkbox',
        'required'      => false,
        'default_value' => false,
        'choices'       => acfe_get_roles(),
        'layout'        => 'horizontal'
    ), true);
    
}

add_filter('acf/prepare_field', 'acfe_roles_prepare_field');
function acfe_roles_prepare_field($field){
    
    if(!isset($field['acfe_permissions']) || empty($field['acfe_permissions']))
        return $field;
    
    $current_user_roles = acfe_get_current_user_roles();
    $render_field = false;
    
    foreach($current_user_roles as $current_user_role){
        
        foreach($field['acfe_permissions'] as $field_role){
            
            if($current_user_role !== $field_role)
                continue;
            
            $render_field = true;
            break;
            
        }
        
        if($render_field)
            break;
        
    }
    
    if(!$render_field)
        return false;
    
    return $field;
    
}