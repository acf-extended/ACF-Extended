<?php

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(acfe_get_setting('modules/field_group_ui')){
    return;
}

if(!class_exists('acfe_field_group_advanced')):

class acfe_field_group_advanced{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acf/field_group/admin_head',        array($this, 'admin_head'), 5);
        add_action('acf/render_field_group_settings',   array($this, 'render_settings'));
        
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
        
        global $field_group;
        
        if(!acf_maybe_get($field_group, 'acfe_form'))
            return;
        
        acf_enable_filter('acfe/field_group/advanced');
        
    }
    
    
    /**
     * render_settings
     *
     * @param $field_group
     */
    function render_settings($field_group){
        
        // Form settings
        acf_render_field_wrap(array(
            'label'         => __('Advanced settings', 'acfe'),
            'name'          => 'acfe_form',
            'prefix'        => 'acf_field_group',
            'type'          => 'true_false',
            'ui'            => 1,
            'instructions'  => __('Enable advanced fields settings & validation', 'acfe'),
            'value'         => (isset($field_group['acfe_form'])) ? $field_group['acfe_form'] : '',
            'required'      => false,
            'wrapper'       => array(
                'data-after' => 'active'
            )
        ), 'div', 'label', true);
        
    }
    
}

// initialize
new acfe_field_group_advanced();

endif;