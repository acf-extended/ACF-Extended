<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_fields
 *
 * This function will return an array containing all the custom field values for a specific post_id.
 * Similar to get_fields(), but but allow to output dataset identical to a $_POST dataset
 *
 * Unformatted:
 * array(
 *     'field_60ed19d658ce1' => array(
 *         'field_60eaeee6e6fa4' => 'Value',
 *     )
 * )
 *
 * Formatted:
 * array(
 *     'my_field' => array(
 *         'my_field_subfield' => 'Value',
 *     )
 * )
 *
 * @param false $post_id
 * @param false $format_value
 *
 * @return array|false
 */
function acfe_get_fields($post_id = false, $format_value = false){
    
    // vars
    $fields = get_field_objects($post_id, $format_value);
    $meta = array();
    
    // bail early
    if(!$fields){
        return false;
    }
    
    // populate
    foreach($fields as $k => $field){
        
        // use key or name
        $key = $format_value ? $k : $field['key'];
        
        // append
        $meta[ $key ] = $field['value'];
        
    }
    
    // return
    return $meta;
    
}

/**
 * acfe_get_meta
 *
 * Retrieve all meta details of a given object in the following format
 *
 * array(
 *     'key'   => 'field_abcd1234',
 *     'name'  => 'repeater_0_my_field',
 *     'value' => 'Text value',
 *     'field' => array(
 *         'key'  => 'field_abcd1234',
 *         'name' => 'My Field',
 *         'type' => 'text',
 *     ),
 *     'field_group' => array(
 *         'key'   => 'group_abcd1234',
 *         'title' => 'Field Group',
 *     )
 * )
 *
 * @param false $post_id
 *
 * @return array
 */
function acfe_get_meta($post_id = false){
    
    // validate post_id
    $post_id = acf_get_valid_post_id($post_id);
    
    // get meta
    $meta = acf_get_meta($post_id);
    
    // sort array
    // fix an issue where virtual layouts sub fields (layout title edit, toggle, grid...) aren't correctly loaded
    // because field values appear before the parent flexible content
    ksort($meta);
    
    // Vars
    $data = array();
    
    // Loop
    foreach($meta as $key => $value){
        
        // Bail early
        if(!isset($meta["_$key"])){
            continue;
        }
        
        $field_key = $meta["_$key"];
        
        // Bail early if field key isn't valid
        // we need to check if is_string because performance mode use
        // an array as '_acf = array(_color_picker => field_123456abcdef)'
        if(!is_string($field_key) || !acf_is_field_key($field_key)){
            continue;
        }
        
        // Get field
        $field = acf_get_field($field_key);
        
        // Check clone in sub field: field_123456abcdef_field_123456abcfed
        if(!$field && substr_count($field_key, 'field_') > 1){
            
            // get field key (last key)
            $_field_key = substr($field_key, strrpos($field_key, 'field_'));
            
            // get field
            $field = acf_get_field($_field_key);
            
        }
        
        // Get field group
        $field_group = $field ? acfe_get_field_group_from_field($field) : false;
        
        // construct
        $data[] = array(
            'key'   => $field_key,
            'name'  => $key,
            'value' => $value,
            'field' => $field,
            'field_group' => $field_group,
        );
        
    }
    
    // Return
    return $data;
    
}

/**
 * acfe_delete_orphan_meta
 *
 * Delete orphan meta from a post id
 *
 * @param int $post_id
 */
function acfe_delete_orphan_meta($post_id = 0, $confirm = true){
    
    // get orphan
    $meta = acfe_get_orphan_meta($post_id);
    $deleted = array();
    
    // loop
    foreach($meta as $row){
    
        // vars
        $key = $row['key'];
        $name = $row['name'];
        $value = $row['value'];
        
        // delete meta
        if($confirm){
            
            acf_delete_metadata($post_id, $name, true);  // prefix
            acf_delete_metadata($post_id, $name);        // normal
            
        }
        
        // store
        $deleted[ "_{$name}" ] = $key;
        $deleted[ $name ] = $value;
        
    }
    
    $return = array(
        'normal' => $deleted,
    );
    
    // filters
    $return = apply_filters('acfe/delete_orphan_meta', $return, $post_id, $confirm);
    
    // return
    return $return;
    
}

/**
 * acfe_get_orphan_meta
 *
 * Retrieve a list of orphan meta from a post id
 *
 * array(
 *     array(
 *         'key' => 'field_63f700899a4fb',
 *         'name' => 'languages',
 *         'value' => array(
 *             'fr_FR',
 *             'en_US',
 *         ),
 *         'field' => false,
 *         'field_group' => false,
 *     ),
 * )
 *
 *
 * @param int $post_id
 *
 * @return array
 */
function acfe_get_orphan_meta($post_id = 0){
    
    // validate post_id
    $post_id = acf_get_valid_post_id($post_id);
    
    // get meta
    $meta = acfe_get_meta($post_id);
    
    // allowed fields
    $allowed_fields = array();
    
    // allowed field groups
    $allowed_field_groups = acfe_get_post_id_field_groups($post_id);
    $allowed_field_groups = wp_list_pluck($allowed_field_groups, 'key');
    
    // collection
    $clones = array();
    
    // check clones
    foreach($meta as $row){
        
        // vars
        $field = $row['field'];
        $field_key = $row['key'];
        
        // get clone
        if(acf_maybe_get($field, 'type') === 'clone'){
            
            // add to collection
            $clones[] = $field;
            
        // get clone seamless sub field: field_123456abcdef_field_123456abcfed
        }elseif(substr_count($field_key, 'field_') > 1){
            
            // explode field_xxxxxxxxxxx
            $_clones = explode('_field_', $field_key);
    
            foreach($_clones as $_clone_key){
                
                // prepend 'field_'
                if(strpos($_clone_key, 'field_') !== 0){
                    $_clone_key = "field_{$_clone_key}";
                }
        
                // get clone field
                $clone = acf_get_field($_clone_key);
        
                // check type & add to collection
                if(acf_maybe_get($clone, 'type') === 'clone'){
                    $clones[] = $clone;
                }
        
            }
            
        }
        
    }
    
    // process clones
    $run = true;
    
    while($run){
        
        $run = false;
        
        foreach(array_keys($clones) as $i){
            
            // get field
            $field = $clones[$i];
            
            // get field group
            $field_group = acfe_get_field_group_from_field($field);
            
            // conditions
            $is_allowed_field = $field && in_array($field['key'], $allowed_fields);
            $is_allowed_field_group = $field_group && in_array($field_group['key'], $allowed_field_groups);
            
            // field or field group found in allowed list
            if($is_allowed_field || $is_allowed_field_group){
                
                // get cloned fields/field groups and sub fields to allowed list
                foreach(acf_get_array($field['clone']) as $cloned_key){
                    
                    // group_60e52a459bea4
                    if(acf_is_field_group_key($cloned_key)){
                        
                        $allowed_field_groups[] = $cloned_key;
                        
                    // field_60e242edd72e7
                    }elseif(acf_is_field_key($cloned_key)){
                        
                        $allowed_fields[] = $cloned_key;
                        
                        // get field group from cloned field
                        $clone_field_group = acfe_get_field_group_from_field($cloned_key);
    
                        // add field group in the allowed list
                        if($clone_field_group){
                            $allowed_field_groups[] = $clone_field_group['key'];
                        }
                        
                        // todo: enhance logic to only allow sub field of the targeted field
                        
                        // $is_enabled = acf_is_filter_enabled('clone');
                        //
                        // if($is_enabled){
                        //     acf_disable_filter('clone');
                        // }
                        //
                        // // also allow descendants in case of repeater, flexible content or group
                        // $descendants = acfe_get_field_descendants($cloned_key);
                        // $allowed_fields = array_merge($allowed_fields, $descendants);
                        //
                        // if($is_enabled){
                        //     acf_enable_filter('clone');
                        // }
                        
                        
                    }
                    
                }
                
                // remove from collection
                unset($clones[$i]);
                
                // run again
                $run = true;
                
            }
            
        }
        
    }
    
    // remove duplicated entries
    $allowed_fields = array_unique($allowed_fields);
    $allowed_field_groups = array_unique($allowed_field_groups);
    
    // orphan collection
    $orphan = array();
    
    foreach($meta as $row){
        
        // vars
        $field = $row['field'];
        $field_group = $row['field_group'];
        
        // field doesn't exist
        if(!$field){
    
            $orphan[] = $row;
            continue;
            
        }
        
        // field group doesn't exist
        if(!$field_group){
    
            $orphan[] = $row;
            continue;
            
        }
        
        // conditions
        $is_allowed_field = in_array($field['key'], $allowed_fields);
        $is_allowed_field_group = in_array($field_group['key'], $allowed_field_groups);
        
        // field is not allowed
        if(!$is_allowed_field && !$is_allowed_field_group){
    
            $orphan[] = $row;
            continue;
            
        }
        
    }
    
    // return
    return $orphan;
    
}