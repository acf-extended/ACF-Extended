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

add_filter('acfe/field_wrapper_attributes/type=repeater', 'acfe_repeater_wrapper', 10, 2);
function acfe_repeater_wrapper($wrapper, $field){
    
    // Stylised button
    if(isset($field['acfe_repeater_stylised_button']) && !empty($field['acfe_repeater_stylised_button'])){
        
        $wrapper['data-acfe-repeater-stylised-button'] = 1;
        
    }
    
    // Lock sortable
    $acfe_repeater_lock_sortable = false;
    $acfe_repeater_lock_sortable = apply_filters('acfe/repeater/lock', $acfe_repeater_lock_sortable, $field);
    $acfe_repeater_lock_sortable = apply_filters('acfe/repeater/lock/name=' . $field['_name'], $acfe_repeater_lock_sortable, $field);
    $acfe_repeater_lock_sortable = apply_filters('acfe/repeater/lock/key=' . $field['key'], $acfe_repeater_lock_sortable, $field);
    
    if($acfe_repeater_lock_sortable){
        
        $wrapper['data-acfe-repeater-lock'] = 1;
        
    }
    
    // Remove actions
    $acfe_repeater_remove_actions = false;
    $acfe_repeater_remove_actions = apply_filters('acfe/repeater/remove_actions', $acfe_repeater_remove_actions, $field);
    $acfe_repeater_remove_actions = apply_filters('acfe/repeater/remove_actions/name=' . $field['_name'], $acfe_repeater_remove_actions, $field);
    $acfe_repeater_remove_actions = apply_filters('acfe/repeater/remove_actions/key=' . $field['key'], $acfe_repeater_remove_actions, $field);
    
    if($acfe_repeater_remove_actions){
        
        $wrapper['data-acfe-repeater-remove-actions'] = 1;
        
    }
    
    return $wrapper;
    
}