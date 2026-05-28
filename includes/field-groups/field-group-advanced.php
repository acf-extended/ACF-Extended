<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_advanced')):

class acfe_field_group_advanced{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acf/field_group/admin_head',      array($this, 'admin_head'), 5);
        add_action('acf/render_field_group_settings', array($this, 'render_field_group_settings'));
        
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
        
        global $field_group;
        
        // enable field group advanced filter
        if(acfe_get($field_group, 'acfe.advanced')){
            acf_enable_filter('acfe/field_group/advanced');
        }
        
    }
    
    
    /**
     * render_field_group_settings
     *
     * @param $field_group
     */
    function render_field_group_settings($field_group){
        
        if(!acfe_get_setting('modules/field_group_ui')){
            
            // advanced settings
            acfe_render_group_setting($field_group, array(
                'label'         => __('Advanced settings', 'acfe'),
                'name'          => 'acfe.advanced',
                'type'          => 'true_false',
                'ui'            => 1,
                'instructions'  => __('Enable advanced fields settings & validation', 'acfe'),
                'required'      => false,
                'wrapper'       => array(
                    'data-after' => 'active'
                )
            ), 'div', 'label', true);
            
        }
        
    }
    
}

// initialize
new acfe_field_group_advanced();

endif;