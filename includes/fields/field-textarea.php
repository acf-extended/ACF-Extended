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
        'instructions'  => __('Switch font family to monospace and allow tab indent. For a more advanced code editor, please use the <code>Code Editor</code> field type'),
        'type'          => 'true_false',
        'ui'            => 1,
    ));
    
}

// Field wrapper
add_filter('acfe/field_wrapper_attributes/type=textarea', 'acfe_field_textarea_wrapper', 10, 2);
function acfe_field_textarea_wrapper($wrapper, $field){
    
    if(acf_maybe_get($field, 'acfe_textarea_code')){
        
        $wrapper['data-acfe-textarea-code'] = 1;
        
    }
    
    return $wrapper;
    
}

add_filter('acf/prepare_field/name=new_lines', 'acfe_field_textarea_new_lines');
function acfe_field_textarea_new_lines($field){
    
    $field['conditional_logic'] = array(
        array(
            array(
                'field'     => 'acfe_textarea_code',
                'operator'  => '!=',
                'value'     => '1'
            )
        )
    );
    
    return $field;
    
}