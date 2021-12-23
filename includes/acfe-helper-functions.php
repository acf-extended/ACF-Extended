<?php

if(!defined('ABSPATH'))
    exit;

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
    
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    
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
    
    json_decode($string);
    
    return json_last_error() == JSON_ERROR_NONE;
    
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
        
        if(!is_array($i)) continue;
        
        $keys = array_merge($keys, acfe_array_keys_r($i));
        
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
        
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);

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
    
    if($length == 0) return true;

    return (substr($haystack, -$length) === $needle);
    
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
function acfe_array_insert_before($key, array &$array, $new_key, $new_value){
    
    if(!array_key_exists($key, $array)){
        return $array;
    }
    
    $new = array();
    
    foreach($array as $k => $value){
        
        if($k === $key){
            $new[$new_key] = $new_value;
        }
        
        $new[$k] = $value;
        
    }
    
    return $new;
    
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
function acfe_array_insert_after($key, array &$array, $new_key, $new_value){
    
    if(!array_key_exists($key, $array)){
        return $array;
    }
    
    $new = array();
    
    foreach($array as $k => $value){
        
        $new[$k] = $value;
        
        if($k === $key){
            $new[$new_key] = $new_value;
        }
        
    }
    
    return $new;
    
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
            if($_field['name'] !== $selector) continue;
            
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
    
    if(!in_array(($num % 100), array(11,12,13))){
        
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
    
    if(!is_array($array)){
        return $array;
    }
    
    if(empty($array)){
        return false;
    }
    
    if(acf_is_sequential_array($array)){
        
        foreach($array as $k => $v){
            
            if(!is_string($v)) continue;
            
            return $v;
            
        }
        
    }elseif(acf_is_associative_array($array)){
        
        foreach($array as $k => $v){
            
            if(!is_string($v)) continue;
            
            return $v;
            
        }
        
    }
    
    return false;
    
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
    
        _deprecated_function('ACF Extended: "ACFE_dev" constant', '0.8.8.7', 'the constant "ACFE_DEV"');
        
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
        
        _deprecated_function('ACF Extended: "ACFE_super_dev" constant', '0.8.8.7', 'the constant "ACFE_SUPER_DEV"');
        
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

    if(isset($array[$key])){
        unset($array[$key]);
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
 * @return mixed
 */
function acfe_get_ip(){
    
    $ip = false;
    
    // http client
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        
        $ip = filter_var(wp_unslash($_SERVER['HTTP_CLIENT_IP']), FILTER_VALIDATE_IP);
        
    // proxy pass
    }elseif(!empty( $_SERVER['HTTP_X_FORWARDED_FOR'])){
        
        // can include more than 1 ip, first is the public one.
        $ips = explode(',', wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        
        if (is_array($ips)){
            $ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
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