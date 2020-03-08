<?php

if(!defined('ABSPATH'))
    exit;

/**
 *  Field Wrapper Attributes
 */
add_filter('acf/field_wrapper_attributes', 'acfe_field_wrapper_attributes', 10, 2);
function acfe_field_wrapper_attributes($wrapper, $field){
    
    $wrapper = apply_filters('acfe/field_wrapper_attributes', $wrapper, $field);
    
    return $wrapper;
    
}

if(function_exists('acf_add_filter_variations')){
    
    acf_add_filter_variations('acfe/field_wrapper_attributes', array('type', 'name', 'key'), 1);
    
}

/**
 *  Load Fields
 */
add_filter('acf/load_fields', 'acfe_field_load_fields', 10, 2);
function acfe_field_load_fields($fields, $parent){
    
    // check if field (fitler is also called on field groups)
    if(!acf_maybe_get($parent, 'type'))
        return $fields;
    
    $fields = apply_filters('acfe/load_fields', $fields, $parent);
    
    return $fields;
    
}

if(function_exists('acf_add_filter_variations')){
    
    acf_add_filter_variations('acfe/load_fields', array('type', 'name', 'key'), 1);
    
}

/**
 *  Load Field
 */
add_filter('acf/load_field', 'acfe_load_field');
function acfe_load_field($field){
    
    if(acfe_is_admin_screen())
        return $field;
    
    // Everywhere
    $field = apply_filters('acfe/load_field', $field);
    
    // Admin
    if(acfe_form_is_admin()){
        
        $field = apply_filters('acfe/load_field_admin', $field);
        
    }
    
    // Front
    elseif(acfe_form_is_front()){
        
        $field = apply_filters('acfe/load_field_front', $field);
        
    }
    
    return $field;
    
}

if(function_exists('acf_add_filter_variations')){
    
    acf_add_filter_variations('acfe/load_field',        array('type', 'name', 'key'), 0);
    acf_add_filter_variations('acfe/load_field_front',  array('type', 'name', 'key'), 0);
    acf_add_filter_variations('acfe/load_field_admin',  array('type', 'name', 'key'), 0);
    
}

add_filter('acf/pre_render_fields', 'acfe_fields_wrapper_instructions', 10, 2);
function acfe_fields_wrapper_instructions($fields, $post_id){
    
    $tooltip = false;
    
    if(!isset($fields[0]))
    	return $fields;
        
    $field_group = acfe_get_field_group_from_field($fields[0]);
    
    if(!$field_group)
        return $fields;
    
    if($field_group['instruction_placement'] !== 'acfe_instructions_tooltip')
    	return $fields;
	
	foreach($fields as &$field){
		
		acfe_add_fields_instructions_tooltip($field);
		
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