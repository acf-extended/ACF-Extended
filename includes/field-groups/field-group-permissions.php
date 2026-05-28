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
        
        add_action('acfe/field_group/render_sidebar_metabox', array($this, 'render_sidebar_metabox'), 15);
        add_filter('acfe/prepare_field_group',                array($this, 'prepare_field_group'));
        
    }
    
    
    /**
     * render_sidebar_metabox
     *
     * @param $field_group
     *
     * @return void
     */
    function render_sidebar_metabox($field_group){
        
        // permissions
        if(!acfe_get_setting('modules/field_group_ui')){
            
            if(acfe_get($field_group, 'acfe.permissions') || acf_is_filter_enabled('acfe/field_group/advanced')){
                
                acfe_render_group_setting($field_group, array(
                    'label'         => __('Permissions', 'acf'),
                    'name'          => 'acfe.permissions',
                    'type'          => 'checkbox',
                    'instructions'  => __('Select user roles that are allowed to view and edit this field group in post edition', 'acfe'),
                    'required'      => false,
                    'default_value' => false,
                    'choices'       => acfe_get_roles(),
                    'layout'        => 'vertical'
                ), 'div', 'label', true);
                
            }
            
        }
        
    }
    
    
    /**
     * prepare_field_group
     *
     * @param $field_group
     *
     * @return false|mixed
     */
    function prepare_field_group($field_group){
        
        // get permissions
        $permissions = acfe_get($field_group, 'acfe.permissions');
        
        // no permissions
        // display normally
        if(empty($permissions)){
            return $field_group;
        }
        
        // check current user role
        if(acfe_has_current_user_role($permissions)){
            return $field_group;
        }
        
        // hide field group
        return false;
        
    }
    
}

// initialize
new acfe_field_group_permissions();

endif;