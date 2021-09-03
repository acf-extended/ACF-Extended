<?php

if(!defined('ABSPATH'))
    exit;

/**
 * acfe_get_field_group_from_field
 *
 * Retrieve the Field Group, starting from any field or sub field
 *
 * @param $field
 *
 * @return array|false|mixed|void|null
 */
function acfe_get_field_group_from_field($field){
    
    if(!acf_maybe_get($field, 'parent'))
        return false;
    
    $field_parent = $field['parent'];
    
    if(!$field_ancestors = acf_get_field_ancestors($field))
        return acf_get_field_group($field_parent);
    
    // Reverse for DESC order (Top field first)
    $field_ancestors = array_reverse($field_ancestors);
    
    $field_top_ancestor = $field_ancestors[0];
    $field_top_ancestor = acf_get_field($field_top_ancestor);
    
    return acf_get_field_group($field_top_ancestor['parent']);
    
}

/**
 * acfe_extract_sub_field
 *
 * Extract Sub Field form Layout & Set Value
 *
 * @param $layout
 * @param $name
 * @param $value
 *
 * @return false|mixed
 */
function acfe_extract_sub_field(&$layout, $name, $value){
    
    $sub_field = false;
    
    // loop
    foreach($layout['sub_fields'] as $k => $row){
        
        if($row['name'] !== $name)
            continue;
        
        $sub_field = acf_extract_var($layout['sub_fields'], $k);
        break;
        
    }
    
    if(!$sub_field)
        return false;
    
    // Reset keys
    $layout['sub_fields'] = array_values($layout['sub_fields']);
    
    // Add value
    if(isset($value[$sub_field['key']])){
        
        $sub_field['value'] = $value[$sub_field['key']];
        
    }elseif(isset($sub_field['default_value'])){
        
        $sub_field['value'] = $sub_field['default_value'];
        
    }
    
    return $sub_field;
    
}