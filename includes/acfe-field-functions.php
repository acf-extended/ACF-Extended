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
    
    // allow field key or name
    if(!is_array($field)){
        $field = acf_get_field($field);
    }
    
    // check parent exists
    if(!acf_maybe_get($field, 'parent')){
        return false;
    }
    
    // get parent fields and reverse order (top field first)
    $ancestors = acf_get_field_ancestors($field);
    $ancestors = array_reverse($ancestors);
    
    // no ancestors, return field group
    if(!$ancestors){
        return acf_get_field_group($field['parent']);
    }
    
    // retrieve top field
    $top_field = acf_get_field($ancestors[0]);
    
    // return
    return acf_get_field_group($top_field['parent']);
    
}

/**
 * acfe_get_field_descendants
 *
 * Similar to acf_get_field_ancestors but retrieve all descendants instead
 *
 * @param $field
 *
 * @return array
 */
function acfe_get_field_descendants($field){
    
    // allow field key or name
    if(!is_array($field)){
        $field = acf_get_field($field);
    }
    
    // var
    $descendants = array();
    $sub_fields = acf_get_fields($field);
    
    // no sub fields
    if(!$sub_fields){
        return $descendants;
    }
    
    // loop sub fields
    foreach($sub_fields as $sub_field){
        
        $descendants[] = $sub_field['ID'] ? $sub_field['ID'] : $sub_field['key'];
        
        if(isset($sub_field['sub_fields'])){
            $descendants = array_merge($descendants, acfe_get_field_descendants($sub_field));
        }
        
    }
    
    // return
    return $descendants;
    
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
        
        if($row['name'] !== $name) continue;
        
        $sub_field = acf_extract_var($layout['sub_fields'], $k);
        break;
        
    }
    
    if(!$sub_field){
        return false;
    }
    
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

/**
 * acfe_map_any_field
 *
 * @param $fields
 * @param $type
 * @param $callback
 *
 * @return mixed
 */
function acfe_map_any_field($fields, $type, $callback){
    
    foreach($fields as &$field){
        
        if($field['type'] === $type){
            $field = call_user_func($callback, $field);
        }
        
        if(acf_maybe_get($field, 'sub_fields')){
            $field['sub_fields'] = acfe_map_any_field($field['sub_fields'], $type, $callback);
        }
        
    }
    
    return $fields;
    
}
