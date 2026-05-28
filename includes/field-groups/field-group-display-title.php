<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_display_title')):

class acfe_field_group_display_title{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/field_group/render_sidebar_metabox', array($this, 'render_sidebar_metabox'), 5);
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
        
        // display title
        if(!acfe_get_setting('modules/field_group_ui')){
            
            // pre-ACF 6.6
            if(!acfe_is_acf('6.6')){
                
                acf_render_field_wrap(array(
                    'label'         => __('Display title', 'acfe'),
                    'instructions'  => __('Render this title on edit post screen', 'acfe'),
                    'type'          => 'text',
                    'name'          => 'acfe.display_title',
                    'value'         => acfe_get($field_group, 'acfe_display_title'),
                    'placeholder'   => '',
                    'prepend'       => '',
                    'append'        => ''
                ), 'div', 'label', true);
                
            }
            
        }
        
    }
    
    
    /**
     * prepare_field_group
     *
     * @param $field_group
     *
     * @return mixed
     */
    function prepare_field_group($field_group){
        
        // legacy ACFE "acfe_display_title"
        $acfe_display_title = acfe_get($field_group, 'acfe.display_title');
        if(!empty($acfe_display_title) && is_string($acfe_display_title)){
            $field_group['title'] = $acfe_display_title;
        }
        
        // ACF 6.6+ native "display_title" takes priority
        if(!empty($field_group['display_title']) && is_string($field_group['display_title'])){
            $field_group['title'] = $field_group['display_title'];
        }
        
        // return
        return $field_group;
        
    }
    
}

// initialize
new acfe_field_group_display_title();

endif;