<?php

if(!defined('ABSPATH'))
    exit;

// Settings
add_action('acf/render_field_settings/type=textarea', 'acfe_field_textarea_settings');
function acfe_field_textarea_settings($field){
    
    acf_render_field_setting($field, array(
        'label'         => __('Code mode'),
        'name'          => 'acfe_textarea_code',
        'key'           => 'acfe_textarea_code',
        'instructions'  => __('Switch font family to monospace and allow tab indent'),
        'type'			=> 'true_false',
        'ui'			=> 1,
    ));
    
}

// Field wrapper
add_filter('acf/field_wrapper_attributes', 'acfe_field_textarea_wrapper', 10, 2);
function acfe_field_textarea_wrapper($wrapper, $field){
    
    if($field['type'] != 'textarea')
        return $wrapper;
    
    if(isset($field['acfe_textarea_code']) && !empty($field['acfe_textarea_code'])){
        
        $wrapper['data-acfe-textarea-code'] = 1;
        
    }
    
    return $wrapper;
    
}