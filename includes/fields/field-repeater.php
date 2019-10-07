<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Add Settings
 */
add_action('acf/render_field_settings/type=repeater', 'acfe_repeater_settings', 0);
function acfe_repeater_settings($field){
    
    // Stylised button
    acf_render_field_setting($field, array(
        'label'         => __('Stylised Button'),
        'name'          => 'acfe_repeater_stylised_button',
        'key'           => 'acfe_repeater_stylised_button',
        'instructions'  => __('Better row button integration'),
        'type'          => 'true_false',
        'message'       => '',
        'default_value' => false,
        'ui'            => true,
    ));
    
}

add_filter('acf/field_wrapper_attributes', 'acfe_repeater_wrapper', 10, 2);
function acfe_repeater_wrapper($wrapper, $field){
    
    if($field['type'] !== 'repeater')
        return $wrapper;
    
    // Stylised button
    if(isset($field['acfe_repeater_stylised_button']) && !empty($field['acfe_repeater_stylised_button'])){
        
        $wrapper['data-acfe-repeater-stylised-button'] = 1;
        
    }
    
    return $wrapper;
    
}