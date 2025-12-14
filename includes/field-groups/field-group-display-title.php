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
        
        add_filter('acfe/prepare_field_group', array($this, 'prepare_field_group'));
        
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
        if(!empty($field_group['acfe_display_title']) && is_string($field_group['acfe_display_title'])){
            $field_group['title'] = $field_group['acfe_display_title'];
        }
        
        // ACF 6.6+ native "display_title" takes priority
        if(!empty($field_group['display_title']) && is_string($field_group['display_title'])){
            $field_group['title'] = $field_group['display_title'];
        }
        
        return $field_group;
        
    }
    
}

// initialize
new acfe_field_group_display_title();

endif;