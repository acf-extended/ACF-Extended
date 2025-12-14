<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_array_get
 *
 * Search within array using dot mapping
 *
 * @param $array
 * @param $key
 * @param $default
 *
 * @return mixed|null
 */
function acfe_array_get($array, $key, $default = null){
    
    if(empty($key) && !is_numeric($key)){
        return $array;
    }
    
    if(!is_array($key)){
        $key = explode('.', $key);
    }
    
    $count = count($key);
    $i=-1; foreach($key as $segment){ $i++;
        
        if(isset($array[ $segment ])){
            
            if($i+1 === $count){
                return $array[ $segment ];
            }
            
            unset($key[ $i ]);
            
            return acfe_array_get($array[ $segment ], $key, $default);
            
        }
        
    }
    
    return $default;
    
}

/**
 * acfe_array_set
 *
 * @param $array
 * @param $keys
 * @param $value
 *
 * @return array|mixed
 */
function acfe_array_set(&$array, $keys, $value = null){
    
    if(func_num_args() === 2){
        return $array = $keys;
    }
    
    if(!is_array($keys)){
        $keys = explode('.', $keys);
    }
    
    foreach($keys as $i => $key){
        
        if(count($keys) === 1){
            break;
        }
        
        unset($keys[ $i ]);
        
        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if(!isset($array[ $key ]) || !is_array($array[ $key ])){
            
            if(empty($key) && acf_is_sequential_array($array)){
                $array[] = array();
                $key = key(array_slice($array, -1, 1, true));
                
            }else{
                $array[ $key ] = array();
            }
            
        }
        
        $array = &$array[ $key ];
        
    }
    
    // if segment finish with "."
    $final = array_shift($keys);
    
    if(empty($final) && !is_numeric($final) && acf_is_sequential_array($array)){
        $array[] = $value;
    }else{
        $array[ $final ] = $value;
    }
    
    return $array;
    
}


/**
 * acfe_array_unset
 *
 * @param $array
 * @param $keys
 *
 * @return array|mixed
 */
function acfe_array_unset(&$array, $keys){
    
    $original = &$array;
    
    $keys = (array) $keys;
    
    if(count($keys) === 0){
        return $array;
    }
    
    foreach($keys as $key){
        
        // if the exact key exists in the top-level, remove it
        if(array_key_exists($key, $array)){
            unset($array[ $key ]);
            continue;
        }
        
        $parts = explode('.', $key);
        
        // clean up before each pass
        $array = &$original;
        
        while(count($parts) > 1){
            
            $part = array_shift($parts);
            
            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];
            } else {
                continue 2;
            }
            
        }
        
        unset($array[ array_shift($parts) ]);
        
    }
    
    return $array;
    
}


/**
 * acfe_maybe_get
 *
 * Similar to acf_maybe_get() but also works with OBJECTS
 *
 * @param array $array
 * @param int   $key
 * @param null  $default
 *
 * @return mixed|null
 */
function acfe_maybe_get($array = array(), $key = 0, $default = null){
    
    if(is_object($array)){
        return isset($array->{$key}) ? $array->{$key} : $default;
    }
    
    return acf_maybe_get($array, $key, $default);
    
}


/**
 * acfe_maybe_get_REQUEST
 *
 * Similar to acf_maybe_get_POST() but works with $_REQUEST
 *
 * @param string $key
 * @param null   $default
 *
 * @return mixed|null
 */
function acfe_maybe_get_REQUEST($key = '', $default = null){
    return isset($_REQUEST[ $key ]) ? $_REQUEST[ $key ] : $default;
}


/**
 * acfe_is_json
 *
 * Check if the string is a json input
 * https://stackoverflow.com/a/6041773
 *
 * @param $string
 *
 * @return bool
 */
function acfe_is_json($string){
    
    // in case string = 1 or not string
    if(is_numeric($string) || !is_string($string)){
        return false;
    }
    
    // decode
    json_decode($string);
    
    // check if decode has errors
    return json_last_error() == JSON_ERROR_NONE;
    
}


/**
 * acfe_is_html
 *
 * Check if string is html
 * https://subinsb.com/php-check-if-string-is-html/
 *
 * @param $string
 *
 * @return bool
 */
function acfe_is_html($string){
    return $string !== strip_tags($string);
}


/**
 * acfe_array_keys_r
 *
 * Array Keys Recursive
 *
 * @param $array
 *
 * @return int[]|string[]
 */
function acfe_array_keys_r($array){

    $keys = array_keys($array);

    foreach($array as $i){
        if(is_array($i)){
            $keys = array_merge($keys, acfe_array_keys_r($i));
        }
    }

    return $keys;
    
}

/**
 * acfe_starts_with
 *
 * Check if a strings starts with something
 *
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function acfe_starts_with($haystack, $needle){
    
    // make sure $haystack is a string
    // substr deprecated non-string since php 8.0
    if(!is_string($haystack)){
        return false;
    }
        
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;

}

/**
 * acfe_ends_with
 *
 * Check if a strings ends with something
 *
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function acfe_ends_with($haystack, $needle){
        
    $length = strlen($needle);
    
    if($length === 0){
        return true;
    }
    
    // make sure $haystack is a string
    // substr deprecated non-string since php 8.0
    if(!is_string($haystack)){
        return false;
    }

    return substr($haystack, -$length) === $needle;
    
}

/**
 * acfe_prefix_array_keys
 *
 * Prefix array keys recursively ignoring numeric keys
 *
 * @param $array
 * @param $prefix
 * @param $ignore
 *
 * @return array
 */
function acfe_prefix_array_keys($array, $prefix, $ignore = array(), $recursive = true){
    
    // vars
    $array2 = array();
    
    // loop
    foreach($array as $k => $v){
        
        if(is_numeric($k)){
            
            $k2 = $k;
            $array2[ $k2 ] = $v;
            
        }else{
            
            $k2 = $prefix . $k;
            
            // ignore
            if($ignore && in_array($k, $ignore)){
                $k2 = $k;
            }
            
            $array2[ $k2 ] = $v;
            
        }
        
        // recursive sub array
        if($recursive){
            if(is_array($array2[ $k2 ])){
                $array2[ $k2 ] = acfe_prefix_array_keys($array2[ $k2 ], $prefix, $ignore, $recursive);
            }
        }
        
    }
    
    // return
    return $array2;
    
}

/**
 * acfe_unprefix_array_keys
 *
 * Prefix array keys recursively ignoring numeric keys
 *
 * @param $array
 * @param $prefix
 * @param $ignore
 *
 * @return array
 */
function acfe_unprefix_array_keys($array, $prefix, $ignore = array(), $recursive = true){
    
    // vars
    $array2 = array();
    
    // loop
    foreach($array as $k => $v){
        
        if(is_numeric($k)){
            
            $k2 = $k;
            $array2[ $k2 ] = $v;
            
        }else{
            
            $k2 = acfe_starts_with($k, $prefix) ? substr($k, strlen($prefix)) : $k;
    
            if($ignore && in_array($k, $ignore)){
                $k2 = $k;
            }
            
            $array2[ $k2 ] = $v;
            
        }
    
        // recursive sub array
        if($recursive){
            if(is_array($array2[ $k2 ])){
                $array2[ $k2 ] = acfe_unprefix_array_keys($array2[ $k2 ], $prefix, $ignore, $recursive);
            }
        }
        
    }
    
    // return
    return $array2;
    
}


/**
 * acfe_map_array_keys
 *
 * Map array keys recursively, allowing to update a row key.
 * Return false in callback to remove the row.
 *
 * @param $array
 * @param $func
 *
 * @return array|false
 */
function acfe_map_array_keys($array, $func){
    
    // validate array
    if(!is_array($array)){
        return $array;
    }
    
    // vars
    $array2 = false;
    
    // loop
    foreach($array as $k => $v){
        
        // callback
        $k2 = call_user_func($func, $k, $v);
        
        // remove the row
        if($k2 === false){
            continue;
            
        // allow row and don't process sub array
        }elseif($k2 === true){
            
            $array2 = acf_get_array($array2);
            $array2[ $k ] = $v;
            
        // allow row and process sub array
        }else{
            
            $array2 = acf_get_array($array2);
            $array2[ $k2 ] = $v;
            
            // recursive sub array
            if(is_array($array2[ $k2 ])){
                
                $array2[ $k2 ] = acfe_map_array_keys($array2[ $k2 ], $func);
                
                if($array2[ $k2 ] === false){
                    unset($array2[ $k2 ]);
                }
                
                if(is_array($array2) && empty($array2)){
                    $array2 = false;
                }
                
            }
            
        }
        
    }
    
    // return
    return $array2;
    
}


/**
 * acfe_filter_acf_values_by_keys
 *
 * @param $acf
 * @param $field_keys
 *
 * @return array|false
 */
function acfe_filter_acf_values_by_keys($acf, $field_keys){
    
    $acf = acf_get_array($acf);
    $field_keys = acf_get_array($field_keys);
    $filtered_keys = $field_keys;
    
    foreach($field_keys as $field_key){
        
        $field = acf_get_field($field_key);
        
        if($field && $field['type'] === 'clone' && $field['display'] === 'seamless' && $field['sub_fields']){
            
            $sub_fields = $field['sub_fields'];
            $sub_fields = wp_list_pluck($sub_fields, 'key');
            
            if($sub_fields){
                $filtered_keys = array_merge($filtered_keys, $sub_fields);
            }
            
        }
        
    }
    
    $acf = acfe_map_array_keys($acf, function($key, $value) use($filtered_keys){
        
        // top level key found
        // allow row and ignore sub array
        if(in_array($key, $filtered_keys, true)){
            return true;
        }
        
        // sub array key not found and value is not array (so no sub array available)
        // do not allow row and ignore sub array
        if(!is_array($value) && !in_array($key, $filtered_keys, true)){
            return false;
        }
        
        // process sub array
        return $key;
        
    });
    
    return $acf;
    
}


/**
 * acfe_get_value_from_acf_values_by_key
 *
 * @param $acf
 * @param $field_key
 *
 * @return mixed|null
 */
function acfe_get_value_from_acf_values_by_key($acf, $field_key){
    
    // vars
    $value = null;
    $acf = acf_get_array($acf);
    
    // seamless clone rule
    $field = acf_get_field($field_key);
    $is_seamless = false;
    
    if($field && $field['type'] === 'clone' && $field['display'] === 'seamless'){
        $value = array();
        $is_seamless = true;
    }
    
    // loop values
    acfe_map_array_keys($acf, function($k, $v) use($field_key, $is_seamless, &$value){
        
        if($is_seamless){
            
            if(acfe_starts_with($k, "{$field_key}_")){
                $value[ $k ] = $v;
            }
            
        }else{
            
            // found key
            if($k === $field_key){
                $value = $v;
            }
            
        }
        
        // continue loop
        return $k;
        
    });
    
    if($is_seamless && empty($value)){
        $value = null;
    }
    
    // return
    return $value;
    
}


/**
 * acfe_array_insert_before
 *
 * Insert data before a specific array key
 *
 * @param       $key
 * @param array $array
 * @param       $new_key
 * @param       $new_value
 *
 * @return array
 */
function acfe_array_insert_before($array, $key, $new_key, $new_value = null){
    
    if(!is_array($array) || !isset($array[ $key ])){
        return $array;
    }
    
    $is_sequential = acf_is_sequential_array($array);
    $new_array = array();
    
    foreach($array as $k => $value){
        
        if($k === $key){
            
            if($is_sequential){
                
                $new_value = $new_value === null ? $new_key : $new_value;
                $new_array[] = $new_value;
                
            }else{
                
                if($new_value === null && is_array($new_key)){
                    reset($new_key);
                    $new_value = current($new_key);
                    $new_key = key($new_key);
                }
                
                $new_array[ $new_key ] = $new_value;
                
            }
            
        }
    
        if($is_sequential){
            $new_array[] = $value;
        
        }else{
            $new_array[ $k ] = $value;
        }
        
    }
    
    return $new_array;
    
}

/**
 * acfe_array_insert_after
 *
 * Insert data after a specific array key
 *
 * @param       $key
 * @param array $array
 * @param       $new_key
 * @param       $new_value
 *
 * @return array
 */
function acfe_array_insert_after($array, $key, $new_key, $new_value = null){
    
    if(!is_array($array) || !isset($array[ $key ])){
        return $array;
    }
    
    $is_sequential = acf_is_sequential_array($array);
    $new_array = array();
    
    foreach($array as $k => $value){
    
        if($is_sequential){
            $new_array[] = $value;
        
        }else{
            $new_array[ $k ] = $value;
        }
        
        if($k === $key){
            
            if($is_sequential){
                
                $new_value = $new_value === null ? $new_key : $new_value;
                $new_array[] = $new_value;
                
            }else{
                
                if($new_value === null && is_array($new_key)){
                    reset($new_key);
                    $new_value = current($new_key);
                    $new_key = key($new_key);
                }
                
                $new_array[ $new_key ] = $new_value;
                
            }
            
        }
        
    }
    
    return $new_array;
    
}

/**
 * acfe_array_move
 *
 * Move the array key from position $a to $b
 *
 * @param $array
 * @param $a
 * @param $b
 */
function acfe_array_move(&$array, $a, $b){
    
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
    
}


/**
 * acfe_parse_args_r
 *
 * parse arguments recursively
 *
 * @param $a
 * @param $b
 *
 * @return array
 */
function acfe_parse_args_r(&$a, $b){
    
    $a = (array) $a;
    $b = (array) $b;
    $r = $b;
    
    foreach($a as $k => &$v){
        
        if(is_array($v) && isset($r[ $k ]) && is_array($r[ $k ]) && acf_is_associative_array($r[ $k ])){
            $r[ $k ] = acfe_parse_args_r($v, $r[ $k ]);
        }else{
            $r[ $k ] = $v;
        }
        
    }
    
    return $r;
    
}

/**
 * acfe_add_validation_error
 *
 * Similar to acf_add_validation_error() but allows to use field name or field key
 *
 * @param string $selector
 * @param string $message
 *
 * @return mixed
 */
function acfe_add_validation_error($selector = '', $message = ''){
    
    // general error
    if(empty($selector)){
        return acf_add_validation_error('', $message);
    }
    
    // selector is a field key
    if(acf_is_field_key($selector)){
    
        return add_filter("acf/validate_value/key={$selector}", function() use($message){
            return $message;
        });
        
    }
    
    // get field by name
    $field = acf_get_field($selector);
    
    // check form data
    // todo: make it more clean
    if($form = acf_get_form_data('acfe/form')){
        
        // vars
        $fields = array();
        $field_groups = acf_get_array($form['field_groups']);
    
        // loop field groups
        foreach($field_groups as $key){
            $fields = array_merge($fields, acf_get_fields($key));
        }
    
        foreach($fields as $_field){
            
            // field name is different
            if($_field['name'] !== $selector){
                continue;
            }
            
            // assign field
            $field = $_field;
            break;
        
        }
        
    }
    
    // check active loop
    $row = acf_get_loop();
    
    // exclude acfe form actions
    if($row && acf_maybe_get($row, 'selector') !== 'acfe_form_actions'){
        
        // get sub field
        $field = acf_get_sub_field($selector, $row['field']);
        
    }
    
    // field not found: add general error
    if(!$field){
        return acf_add_validation_error('', $message);
    }
    
    // add validation error
    add_filter("acf/validate_value/key={$field['key']}", function() use($message){
        return $message;
    });
    
    return false;
    
}

/**
 * acfe_number_suffix
 *
 * Adds 1"st", 2"nd", 3"rd" to number
 *
 * @param $num
 *
 * @return string
 */
function acfe_number_suffix($num){
    
    if(!in_array(($num % 100), array(11, 12, 13))){
        
        switch($num % 10){
            case 1:  return $num . 'st';
            case 2:  return $num . 'nd';
            case 3:  return $num . 'rd';
        }
        
    }
    
    return $num . 'th';
    
}

/**
 * acfe_array_to_string
 *
 * Convert an array to string
 *
 * @param array $array
 *
 * @return array|false|mixed|string
 */
function acfe_array_to_string($array = array()){
    
    // check type
    if(is_array($array)){
        
        // loop
        foreach($array as $val){
            if(is_string($val) || is_numeric($val) || is_bool($val)){
                return $val;
            }
        }
        
        // no valid value
        return false;
        
    }
    
    // default
    return $array;
    
}

/**
 * acfe_is_dev
 *
 * Check if the developer mode is enabled
 *
 * @return bool
 */
function acfe_is_dev(){
    
    // deprecated
    if(defined('ACFE_dev')){
        
        acfe_deprecated_constant('ACFE_dev', '0.8.8.7', 'ACFE_DEV');
        return ACFE_dev;
        
    }
    
    return acf_get_setting('acfe/dev', false) || (defined('ACFE_DEV') && ACFE_DEV);
    
}

/**
 * acfe_is_super_dev
 *
 * Only for awesome developers!
 *
 * @return bool
 */
function acfe_is_super_dev(){
    
    // deprecated
    if(defined('ACFE_super_dev')){
    
        acfe_deprecated_constant('ACFE_super_dev', '0.8.8.7', 'ACFE_SUPER_DEV');
        return ACFE_super_dev;
        
    }
    
    return acf_get_setting('acfe/super_dev', false) || (defined('ACFE_SUPER_DEV') && ACFE_SUPER_DEV);
    
}

/**
 * acfe_is_post_type_reserved
 *
 * Check if the post type is reserved
 *
 * @param $post_type
 *
 * @return bool
 */
function acfe_is_post_type_reserved($post_type){
    
    // restricted post types
    $reserved = acfe_get_setting('reserved_post_types', array());
    
    return in_array($post_type, $reserved);
    
}

/**
 * acfe_is_post_type_reserved_dev
 *
 * Check if the post type is reserved in dev mode
 *
 * @param $post_type
 *
 * @return bool
 */
function acfe_is_post_type_reserved_dev($post_type){
    
    // restricted post types
    $reserved = acfe_get_setting('reserved_post_types', array());
    
    return !acfe_is_super_dev() && in_array($post_type, $reserved);
    
}

/**
 * acfe_is_taxonomy_reserved
 *
 * Check if the taxonomy is reserved
 *
 * @param $taxonomy
 *
 * @return bool
 */
function acfe_is_taxonomy_reserved($taxonomy){
    
    // restricted post types
    $reserved = acfe_get_setting('reserved_taxonomies', array());
    
    return in_array($taxonomy, $reserved);
    
}

/**
 * acfe_is_taxonomy_reserved_dev
 *
 * Check if the taxonomy is reserved in dev mode
 *
 * @param $taxonomy
 *
 * @return bool
 */
function acfe_is_taxonomy_reserved_dev($taxonomy){
    
    // restricted post types
    $reserved = acfe_get_setting('reserved_taxonomies', array());
    
    return !acfe_is_super_dev() && in_array($taxonomy, $reserved);
    
}

/**
 * acfe_update_setting
 *
 * Similar to acf_update_setting() but with the 'acfe' prefix
 *
 * @param $name
 * @param $value
 *
 * @return bool|true
 */
function acfe_update_setting($name, $value){
    return acf_update_setting("acfe/{$name}", $value);
}

/**
 * acfe_append_setting
 *
 * Similar to acf_append_setting() but with the 'acfe' prefix
 *
 * @param $name
 * @param $value
 *
 * @return bool|true
 */
function acfe_append_setting($name, $value){
    return acf_append_setting("acfe/{$name}", $value);
}

/**
 * acfe_get_setting
 *
 * Similar to acf_get_setting() but with the 'acfe' prefix
 *
 * @param      $name
 * @param null $value
 *
 * @return mixed|void
 */
function acfe_get_setting($name, $value = null){
    return acf_get_setting("acfe/{$name}", $value);
}

/**
 * acfe_unset
 *
 * Safely remove an array key
 *
 * @param $array
 * @param $key
 */
function acfe_unset(&$array, $key){

    if(isset($array[ $key ])){
        unset($array[ $key ]);
    }

}

/**
 * acfe_unarray
 *
 * Retrieve and return only the first value of an array
 *
 * @param $val
 *
 * @return false|mixed
 */
function acfe_unarray($val){
    
    if(is_array($val)){
        return reset($val);
    }
    
    return $val;
}

/**
 * acfe_get_ip
 *
 * @return mixed
 */
function acfe_get_ip(){
    
    $ip = false;
    
    // http client
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        
        $ip = filter_var(wp_unslash($_SERVER['HTTP_CLIENT_IP']), FILTER_VALIDATE_IP);
        
    // proxy pass
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        
        // can include more than 1 ip, first is the public one.
        $ips = explode(',', wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        
        if(is_array($ips)){
            $ip = filter_var($ips[0], FILTER_VALIDATE_IP);
        }
        
    // remote addr
    }elseif(!empty($_SERVER['REMOTE_ADDR'])){
        
        $ip = filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP);
        
    }
    
    // default
    $ip = $ip !== false ? $ip : '127.0.0.1';
    
    // fix potential csv return
    $ip_array = explode(',', $ip);
    $ip_array = array_map('trim', $ip_array);
    
    // return
    return $ip_array[0];
    
}

/**
 * acfe_var_export
 *
 * export php code
 *
 * @param $code
 * @param $esc
 *
 * @return array|string|string[]|null
 */
function acfe_var_export($code, $esc = true){
    
    $str_replace = array(
        "  "            => "    ",
        "'!!__(!!\'"    => "__('",
        "!!\', !!\'"    => "', '",
        "!!\')!!'"      => "')",
        "array ("       => "array(",
        " NULL,"        => " null,",
    );
    
    $preg_replace = array(
        '/([ \r\n]+?)array/'    => ' array',
        '/array\(\n\)/'         => 'array()',
        '/array\(\n([ ]+)\)/'   => 'array()',
        '/[0-9]+ => /'          => '',
        //'/[0-9]+ => array/'   => 'array',
    );
    
    // code
    $code = var_export($code, true);
    
    // change double spaces to tabs
    $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
    
    // correctly formats "=> array("
    $code = preg_replace(array_keys($preg_replace), array_values($preg_replace), $code);
    
    // esc_textarea
    if($esc){
        $code = esc_textarea($code);
    }
    
    // return
    return $code;
    
}

/**
 * acfe_parse_types
 *
 * cousin of acf_parse_type() but also handle 'false' | 'true' | 'null' values
 *
 * @param $v
 * @param $filters
 *
 * @return array|bool|int|mixed|string|null
 */
function acfe_parse_types($v, $filters = array('trim', 'int', 'bool', 'null')){
    
    // validate filters
    $filters = acf_get_array($filters);
    
    // check array
    if(is_array($v) && !empty($v)){
        
        $v = array_map(function($v) use($filters){
            return acfe_parse_types($v, $filters);
        }, $v);
    
    // check if string
    }elseif(is_string($v)){
        
        // trim ('word ' = 'word')
        if(in_array('trim', $filters)){
            $v = trim($v);
        }
        
        // convert int strings to int ('123' = 123)
        if(in_array('int', $filters) && is_numeric($v) && strval(intval($v)) === $v){
            $v = intval($v);
            
        // convert ('false' = false)
        }elseif(in_array('bool', $filters) && strtolower($v) === 'false'){
            $v = false;
    
        // convert ('true' = true)
        }elseif(in_array('bool', $filters) && strtolower($v) === 'true'){
            $v = true;
    
        // convert ('null' = null)
        }elseif(in_array('null', $filters) && strtolower($v) === 'null'){
            $v = null;
            
        }
        
    }
    
    // return
    return $v;
    
}

/**
 * acfe_unparse_types
 *
 * reverse of acfe_parse_types
 *
 * @param $v
 * @param $filters
 *
 * @return array|mixed|string
 */
function acfe_unparse_types($v, $filters = array('int', 'bool', 'null')){
    
    // validate filters
    $filters = acf_get_array($filters);
    
    // check array
    if(is_array($v) && !empty($v)){
        
        $v = array_map(function($v) use($filters){
            return acfe_unparse_types($v, $filters);
        }, $v);
        
    // others
    }else{
    
        // convert int strings to int (123 = '123')
        if(in_array('int', $filters) && is_int($v)){
            $v = strval($v);
        
        // convert (false = 'false')
        }elseif(in_array('bool', $filters) && $v === false){
            $v = 'false';
        
        // convert (true = 'true')
        }elseif(in_array('bool', $filters) && $v === true){
            $v = 'true';
        
        // convert (null = 'null')
        }elseif(in_array('null', $filters) && $v === null){
            $v = 'null';
        
        }
        
    }
    
    // return
    return $v;
    
}


/**
 * acfe_redirect
 *
 * @param $location
 * @param $status
 *
 * @return void
 */
function acfe_redirect($location, $status = 302){
    
    // filter
    $location = apply_filters('acfe/redirect', $location, $status);
    
    // do not redirect
    if($location === false){
        return;
    }
    
    // sanitize
    $location = trim($location);
    
    // empty location
    // redirect to 'current page'
    if(empty($location)){
        
        global $wp;
        if(!isset($wp->request)){
            return;
        }
        
        $location = home_url($wp->request);
        
    }
    
    wp_redirect($location);
    exit;
    
}


/**
 * acfe_doing_action
 *
 * Returns the current priority of a running action.
 * From acf_doing_action(), but also works with ACF 5.8
 *
 * @param $action
 *
 * @return false|int
 */
function acfe_doing_action($action){
    global $wp_filter;
    if(isset($wp_filter[ $action ])){
        return $wp_filter[ $action ]->current_priority();
    }
    return false;
}


/**
 * acfe_str_replace_first
 *
 * @param $search
 * @param $replace
 * @param $subject
 * @param $delete  bool Should delete the other occurrences of the search string
 *
 * @return array|mixed|string|string[]
 */
function acfe_str_replace_first($search, $replace, $subject, $delete = false){
    
    $pos = strpos($subject, $search);
    
    if($pos !== false){
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
        
        if($delete){
            $subject = str_replace($search, '', $subject);
        }
    }
    
    return $subject;
    
}


/**
 * acfe_get_array_flatten
 *
 * @param $array
 * @param $flattened
 *
 * @return array|mixed
 */
function acfe_get_array_flatten($array = array(), $flattened = array()){
    
    // bail early if no array
    if(empty($array) || !is_array($array)){
        return $flattened;
    }
    
    // loop choices
    foreach($array as $key => $value){
        
        // value must be an array
        if(is_array($value)){
            $flattened = acfe_get_array_flatten($value, $flattened);
        }else{
            $flattened[ $key ] = $value;
        }
        
    }
    
    // return
    return $flattened;
    
}