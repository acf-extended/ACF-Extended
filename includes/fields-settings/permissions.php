<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_permissions')):

class acfe_permissions{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acf/render_field_settings', array($this, 'render_field_settings'));
        add_filter('acf/prepare_field',         array($this, 'prepare_field'));
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     *
     * @return void
     */
    function render_field_settings($field){
        
        if(acf_is_filter_enabled('acfe/field_group/advanced') || acf_maybe_get($field, 'acfe_permissions')){
            
            // default "data-after"
            $placement_after = 'instructions';
            
            // exception: tab field
            if($field['type'] === 'tab'){
                $placement_after = 'label';
            }
            
            // render permissions setting
            acf_render_field_setting($field, array(
                'label'         => __('Permissions', 'acfe'),
                'name'          => 'acfe_permissions',
                'key'           => 'acfe_permissions',
                'instructions'  => __('Restrict user roles that are allowed to view and edit this field', 'acfe'),
                'type'          => 'checkbox',
                'required'      => false,
                'default_value' => false,
                'choices'       => acfe_get_roles(),
                'layout'        => 'horizontal',
                'wrapper'       => array(
                    'data-after' => $placement_after
                )
            ), true);
            
        }
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return false|mixed
     */
    function prepare_field($field){
        
        // default var
        $target_field = $field;
        
        // exception: clone field
        if(!empty($field['_clone']) && acf_is_field_key($field['_clone'])){
            
            // get clone field
            $clone_field = acf_get_field($field['_clone']);
            
            // validate
            if(!empty($clone_field)){
                $target_field = $clone_field;
            }
            
        }
        
        // check permissions setting
        if(empty($target_field['acfe_permissions'])){
            return $field;
        }
        
        // vars
        $should_render = false;
        $field_roles = $target_field['acfe_permissions'];
        $user_roles = acfe_get_current_user_roles();
        
        // cast as array
        $field_roles = acf_get_array($field_roles);
        $user_roles = acf_get_array($user_roles);
        
        // loop current user roles
        foreach($user_roles as $user_role){
            
            // current user role is in field roles, render field
            if(in_array($user_role, $field_roles, true)){
                $should_render = true;
                break;
            }
            
        }
        
        // bail early
        if(!$should_render){
            return false;
        }
        
        // return field
        return $field;
        
    }
    
}

new acfe_permissions();

endif;