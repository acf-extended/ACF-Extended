<?php

if(!defined('ABSPATH'))
    exit;

add_filter('acf/field_wrapper_attributes', 'acfe_field_wrapper_attributes', 10, 2);
function acfe_field_wrapper_attributes($wrapper, $field){
    
    $wrapper = apply_filters('acfe/field_wrapper_attributes/type=' . $field['type'],    $wrapper, $field);
    $wrapper = apply_filters('acfe/field_wrapper_attributes/name=' . $field['name'],    $wrapper, $field);
    $wrapper = apply_filters('acfe/field_wrapper_attributes/key=' . $field['key'],      $wrapper, $field);
    
    return $wrapper;
    
}

add_filter('acf/pre_render_fields', 'acfe_fields_wrapper_instructions', 10, 2);
function acfe_fields_wrapper_instructions($fields, $post_id){
    
    $tooltip = false;
    
    if(isset($fields[0])){
        
        $field_group = acfe_get_field_group_from_field($fields[0]);
        
        if(!$field_group)
            return $fields;
        
        if($field_group['instruction_placement'] === 'acfe_instructions_tooltip'){
            
            foreach($fields as &$field){
                
                acfe_field_add_key_recursive($field, 'acfe_instructions_tooltip', ($field['instructions'] ? acf_esc_html($field['instructions']) : ''));
                
            }
            
        }
        
    }
    
    return $fields;
    
}

add_filter('acf/field_wrapper_attributes', 'acfe_fields_wrapper', 10, 2);
function acfe_fields_wrapper($wrapper, $field){
    
    if(!acf_maybe_get($field, 'label')){
    
        $wrapper['class'] .= ' acfe-no-label';
    
    }
    
    if(acf_maybe_get($field, 'acfe_instructions_tooltip')){
        
        $wrapper['data-acfe-instructions-tooltip'] = acf_esc_html($field['acfe_instructions_tooltip']);
        
    }
    
    return $wrapper;
    
}