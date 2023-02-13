<?php

if(!defined('ABSPATH')){
    exit;
}

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
        
        if($row['name'] === $name){
            $sub_field = acf_extract_var($layout['sub_fields'], $k);
            break;
        }
        
    }
    
    if(!$sub_field){
        return false;
    }
    
    // reset keys
    $layout['sub_fields'] = array_values($layout['sub_fields']);
    
    // add value
    if(isset($value[$sub_field['key']])){
        $sub_field['value'] = $value[$sub_field['key']];
        
    }elseif(isset($sub_field['default_value'])){
        $sub_field['value'] = $sub_field['default_value'];
    }
    
    return $sub_field;
    
}

/**
 * acfe_map_fields
 *
 * Map custom callback to all fields and subfields passed
 *
 * @param $field
 * @param $callback
 *
 * @return mixed
 */
function acfe_map_fields($field, $callback){
    
    // bail early
    if(empty($field)){
        return $field;
    }
    
    /**
     * multiple fields array
     *
     * array(
     *     array(
     *         'key'   => 'field_5c9a1b0b9c2a1',
     *         'label' => 'Field 1',
     *         'name'  => 'field_1',
     *         'type'  => 'text',
     *     ),
     *     array(
     *         'key'   => 'field_5c9a1b0b9c2a2',
     *         'label' => 'Field 2',
     *         'name'  => 'field_2',
     *         'type'  => 'text',
     *     ),
     * )
     */
    if(acf_is_sequential_array($field)){
    
        $fields = $field;
        
        foreach(array_keys($fields) as $k){
            $fields[ $k ] = acfe_map_fields($fields[ $k ], $callback);
        }
        
        return $fields;
        
    }
    
    /**
     * single field array
     *
     * array(
     *     'key'   => 'field_5c9a1b0b9c2a1',
     *     'label' => 'Field',
     *     'name'  => 'field',
     *     'type'  => 'text',
     * )
     */
    $field = call_user_func($callback, $field);
    
    // subfields
    // repeater, group...
    if(acf_maybe_get($field, 'sub_fields')){
        $field['sub_fields'] = acfe_map_fields($field['sub_fields'], $callback);
    }
    
    // layouts
    // flexible content
    if(acf_maybe_get($field, 'layouts')){
        
        foreach(array_keys($field['layouts']) as $l){
            
            if(acf_maybe_get($field['layouts'][ $l ], 'sub_fields')){
                $field['layouts'][ $l ]['sub_fields'] = acfe_map_fields($field['layouts'][ $l ]['sub_fields'], $callback);
            }
        }
        
    }
    
    return $field;
    
}


/**
 * acfe_query_field
 *
 * @param $args
 *
 * @return false|mixed
 */
function acfe_query_field($args = array()){
    
    // default limit
    $args = wp_parse_args($args, array(
        'limit' => 1
    ));
    
    // query
    $fields = acfe_query_fields($args);
    
    // return
    return current($fields);
    
}


/**
 * acfe_query_fields
 *
 * @param $args
 *
 * @return array
 */
function acfe_query_fields($args = array()){
    
    // vars
    $storage = array();
    $fields = array();
    
    // validate query
    $args = wp_parse_args($args, array(
        'query'    => array(),                  // main query, should be compatible with wp_list_filter()
        'context'  => acf_get_field_groups(),   // can be field/field group array, or field/field group key, array of fields or field groups etc...
        'orderby'  => false,                    // orderby list
        'order'    => 'ASC',                    // order list
        'limit'    => 0,                        // limit list
        'offset'   => 0,                        // offset list
        'level'    => -1,                       // maximum allowed field level (-1 = any, 0 = only top level, 1 = max 1 sub level etc...)
        'field'    => false,                    // list pluck field
        'filters'  => true,                     // enable/disable acf_filters (such as clone, local)
        
        // internal args
        '_query'   => false,
        '_depth'   => 0,
        '_level'   => 0,
        '_filters' => false,
    ));
    
    // validate context
    $args['context'] = acf_get_array($args['context']);
    
    // validate query
    $args['query'] = acf_get_array($args['query']);
    
    // top-level call
    if(!$args['_depth']){
        
        // disable acf filters
        if(!$args['filters']){
            $args['_filters'] = acf_disable_filters();
        }
        
    }
    
    // process query
    if($args['_query'] === false){
        
        $_query = array();

        /**
         * $_query = array('type' => 'text');
         
         * $_query = array(
         *     array('type' => 'text'),
         *     array('type' => 'image'),
         * );
         
         * $_query = array(
         *     'relation' => 'AND',
         *     array(
         *         'type' => 'text',
         *         'name' => 'my_text'
         *     )
         * );
         
         * $_query = array(
         *     array(
         *         'relation' => 'AND',
         *         array(
         *             'type' => 'text',
         *             'name' => 'my_text'
         *         )
         *     ),
         *     array(
         *         'relation' => 'AND',
         *         array(
         *             'type' => 'image',
         *             'name' => 'my_image'
         *         )
         *     ),
         * );
         */
        
        /**
         * $_query = array(
         *     array('type' => 'text'),
         *     array('type' => 'image'),
         * )
         */
        if(acf_is_associative_array($args['query'])){
        
            if(!isset($args['query']['relation'])){
            
                $_query[] = array(
                    'relation' => 'AND',
                    $args['query']
                );
            
            }else{
            
                if(isset($args['query'][0]) && is_array($args['query'][0])){
                    
                    $_query[] = array(
                        'relation' => $args['query']['relation'],
                        $args['query'][0]
                    );
                    
                }
            
            }
        
        }else{
        
            foreach($args['query'] as $q){
            
                if(!isset($q['relation'])){
                
                    if(isset($q[0]) && is_array($q[0])){
                    
                        if(!empty($q[0])){
                            $_query[] = array(
                                'relation' => 'AND',
                                current($q)
                            );
                        }
                    
                    }elseif(!empty($q)){
                    
                        $_query[] = array(
                            'relation' => 'AND',
                            $q
                        );
                    
                    }
                
                }else{
                
                    if(isset($q[0]) && is_array($q[0]) && !empty($q[0])){
                    
                        $_query[] = array(
                            'relation' => $q['relation'],
                            $q[0]
                        );
                    
                    }
                
                }
            
            }
        
        }
        
        // empty query = all
        if(empty($_query)){
            
            $_query[] = array(
                'relation' => 'AND',
                array()
            );
            
        }
        
        // assign
        $args['_query'] = $_query;
        
    }
    
    // $field
    // $field_group
    if(acf_is_associative_array($args['context'])){
        
        // field group
        if(acf_is_field_group($args['context'])){
            $fields = acf_get_fields($args['context']);
            
        // field
        }else{
            
            foreach($args['_query'] as $q){
                $storage = array_merge($storage, wp_list_filter(array($args['context']), $q[0], $q['relation']));
            }
            
            // query sub fields
            if(isset($args['context']['sub_fields'])){
                $args['_level']++;
                $fields = acf_get_fields($args['context']);
            }
            
        }
    
        if($fields){
            foreach($fields as $field){
    
                foreach($args['_query'] as $q){
                    $storage = array_merge($storage, wp_list_filter(array($field), $q[0], $q['relation']));
                }
            
                // query sub fields
                if(isset($field['sub_fields'])){
                    
                    if($args['level'] === -1 || ($args['level'] > 0 && $args['_level'] < $args['level'])){
    
                        // sub query
                        $_args = $args;
                        $_args['context'] = $field;
                        $_args['_depth']++;
    
                        $storage = array_merge($storage, acfe_query_fields($_args));
                        
                    }
                
                }
            
            }
        }
        
    // array(field_abcdef123456, field_abcdef123456)
    // array(group_abcdef123456, group_abcdef123456)
    // array($field, $field)
    // array($field_group, $field_group)
    }else{
        
        foreach($args['context'] as $context){
            
            // set new sub context
            $_args = $args;
            $_args['_depth']++;
            
            // array
            if(is_array($context)){
    
                $_args['context'] = $context;
                
            // numeric
            }elseif(is_numeric($context)){
                
                $post_type = get_post_type($context);
                
                if($post_type === 'acf-field-group'){
                    $_args['context'] = acf_get_field_group($context);
                    
                }elseif($post_type === 'acf-field'){
                    $_args['context'] = acf_get_field($context);
                }
                
            // string
            }else{
                
                // group_abcdef123456
                if(acf_is_field_group_key($context)){
                    $_args['context'] = acf_get_fields($context);
                    
                // field_abcdef123456
                }else{
                    $_args['context'] = acf_get_field($context);
                }
                
            }
            
            // loop query
            $storage = array_merge($storage, acfe_query_fields($_args));
            
        }
        
    }
    
    // unique array
    // make sure returned fields are unique (based on field key)
    $temp = array();
    $storage = array_filter($storage, function($field) use(&$temp){
        if(in_array($field['key'], $temp)){
            return false;
        }else{
            $temp[] = $field['key'];
            return true;
        }
    });
    
    // reorder
    $storage = array_values($storage);
    
    // top-level call
    if(!$args['_depth']){
        
        // order
        if($args['orderby']){
            $args['order'] = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
            $storage = wp_list_sort($storage, $args['orderby'], $args['order']);
        }
        
        // field
        if($args['field']){
            $storage = wp_list_pluck($storage, $args['field']);
        }
        
        // offset
        if($args['offset'] > 0){
            $storage = array_slice($storage, $args['offset']);
        }
        
        // limit
        if($args['limit'] > 0){
            $storage = array_slice($storage, 0, $args['limit']);
        }
        
        // re-enable acf filters
        if(!$args['filters']){
            acf_enable_filters($args['_filters']);
        }
        
    }
    
    // return
    return $storage;
    
}

/**
 * acfe_get_fields_details_recursive
 *
 * @param $fields
 *
 * @return array|mixed
 */
function acfe_get_fields_details_recursive($fields){
    
    $fields = acf_get_array($fields);
    $return = array();
    
    foreach($fields as $field){
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
    
        $label = '';
        $label = str_repeat('- ', $ancestors) . $label;
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';
    
        $field_type = acf_get_field_type($field['type']);
        $type = isset($field_type->label) ? $field_type->label : '-';
        
        $return[] = array(
            'label' => $label,
            'name'  => $field['name'],
            'key'   => $field['key'],
            'type'  => $type,
        );
    
        if(acf_maybe_get($field, 'sub_fields')){
            $return = array_merge($return, acfe_get_fields_details_recursive($field['sub_fields']));
        }
        
    }
    
    return $return;
    
}


/**
 * acfe_get_pretty_field_label
 *
 * @param $field
 * @param $with_key
 *
 * @return mixed|string|null
 */
function acfe_get_pretty_field_label($field, $with_key = false){
    
    // vars
    $name = isset($field['_name']) ? $field['_name'] : $field['name'];
    $label = acf_maybe_get($field, 'label', $name);
    
    if($with_key){
        $label .= " ({$field['key']})";
    }
    
    return $label;

}