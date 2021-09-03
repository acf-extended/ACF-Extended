<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(acfe_get_setting('modules/field_group_ui'))
    return;

if(!class_exists('acfe_field_group_advanced')):

class acfe_field_group_advanced{
    
    function __construct(){
    
        add_action('acf/field_group/admin_head',        array($this, 'admin_head'), 5);
        add_action('acf/render_field_group_settings',   array($this, 'render_settings'));
        
    }
    
    /*
     * Admin Head
     */
    function admin_head(){
        
        global $field_group;
        
        if(!acf_maybe_get($field_group, 'acfe_form'))
            return;
        
        acf_enable_filter('acfe/field_group/advanced');
        
    }
    
    /*
     * Advanced Settings
     */
    function render_settings($field_group){
        
        // Form settings
        acf_render_field_wrap(array(
            'label'         => __('Advanced settings', 'acfe'),
            'name'          => 'acfe_form',
            'prefix'        => 'acf_field_group',
            'type'          => 'true_false',
            'ui'            => 1,
            'instructions'  => __('Enable advanced fields settings & validation'),
            'value'         => (isset($field_group['acfe_form'])) ? $field_group['acfe_form'] : '',
            'required'      => false,
            'wrapper'       => array(
                'data-after' => 'active'
            )
        ));
        
    }
    
}

// initialize
new acfe_field_group_advanced();

endif;