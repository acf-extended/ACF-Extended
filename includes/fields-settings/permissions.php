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
        
        // hook name
        $render_field_settings = acfe_is_acf('6.0') ? 'render_field_validation_settings' : 'render_field_settings';
        
        // loop field types
        foreach(acf_get_field_types() as $field_type){
            if(!empty($field_type->name)){
                add_action("acf/{$render_field_settings}/type={$field_type->name}", array($this, 'render_field_settings'));
            }
        }
        
        add_filter('acf/prepare_field', array($this, 'prepare_field'));
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     *
     * @return void
     */
    function render_field_settings($field){
        
        $field_type = acf_get_field_type($field['type']);
        
        if(
            acf_is_filter_enabled('acfe/field_group/advanced')
            || acfe_get($field, 'acfe_permissions')
            || ($field_type && !empty($field_type->defaults['acfe_permissions']))
        ){
            
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
            ));
            
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
        
        // empty permissions: always render field
        if(empty($target_field['acfe_permissions'])){
            return $field;
        }
        
        // check current user permissions
        $has_role = acfe_has_current_user_role($target_field['acfe_permissions']);
        
        // bail early
        if(!$has_role){
            return false;
        }
        
        // return field
        return $field;
        
    }
    
}

new acfe_permissions();

endif;