<?php

if(!defined('ABSPATH')){
    exit;
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
 * acfe_filter_acf_values_by_keys
 *
 * This is used when value is saved as post meta, during a Post Action creation for example
 *
 * @param $acf
 * @param $field_keys
 *
 * @return array
 */
function acfe_filter_acf_values_by_keys($acf, $field_keys){
    
    $acf = acfe_as_array($acf);
    $field_keys = acfe_as_array($field_keys);
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
    
    // cleanup acf array, only allowing field keys and their sub fields if available
    $acf = acfe_array_rewrite($acf, function($value, $key) use($filtered_keys){
        
        // top level key found
        // allow row and ignore child array
        if(acfe_contains($filtered_keys, $key)){
            return true;
        }
        
        // child array key not found and value is not array (so no child array available)
        // do not allow row and ignore child array
        if(!acfe_contains($filtered_keys, $key) && !is_array($value)){
            return false;
        }
        
        // continue loop, and process child array if available
        return array($key => $value);
        
    }, true);
    
    // return
    return $acf;
    
}


/**
 * acfe_get_value_from_acf_values_by_key
 *
 * This is used to load value from template tags.
 * For example in the Mail action, when using {fields} template tag
 *
 * @param $acf
 * @param $field_key
 *
 * @return mixed|null
 */
function acfe_get_value_from_acf_values_by_key($acf, $field_key){
    
    // vars
    $value = null;
    $acf = acfe_as_array($acf);
    
    // seamless clone rule
    $field = acf_get_field($field_key);
    $is_seamless = false;
    
    if($field && $field['type'] === 'clone' && $field['display'] === 'seamless'){
        $value = array();
        $is_seamless = true;
    }
    
    // loop values
    acfe_array_rewrite($acf, function($val, $key) use($field_key, $is_seamless, &$value){
        
        if($is_seamless){
            
            if(acfe_starts_with($key, "{$field_key}_")){
                $value[ $key ] = $val;
            }
            
        }else{
            
            // found key
            if($key === $field_key){
                $value = $val;
            }
            
        }
        
        // continue loop
        return array($key => $val);
    
    }, true);
    
    // reset value back to null
    if($is_seamless && empty($value)){
        $value = null;
    }
    
    // return
    return $value;
    
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
        $field_groups = acfe_as_array($form['field_groups']);
    
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
    if($row && acfe_get($row, 'selector') !== 'acfe_form_actions'){
        
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
 * @return mixed
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
 * @return mixed
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
 * @return mixed
 */
function acfe_get_setting($name, $value = null){
    return acf_get_setting("acfe/{$name}", $value);
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
    
    // default
    $ip = false;
    
    // try cloudflare first
    if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = filter_var(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']), FILTER_VALIDATE_IP);
        
    // http client
    }elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
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
    
    // return first ip
    $ip = $ip_array[0];
    $ip = apply_filters('acfe/load_ip', $ip);
    
    // return
    return $ip;
    
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
    $filters = acfe_as_array($filters);
    
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
    $filters = acfe_as_array($filters);
    
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
 * acfe_log
 *
 * Similar to acf_log(), but better handle true/false/null inside arrays
 *
 * @return void
 */
function acfe_log(){
    
    // vars
    $args = func_get_args();
    
    // loop arguments
    foreach($args as $i => $arg){
        
        // parse log
        $arg = acfe_parse_log($arg);
        
        // array | object
        if(is_array($arg) || is_object($arg)){
            $arg = print_r($arg, true);
        }
        
        // update
        $args[ $i ] = $arg;
        
    }
    
    // log
    error_log(implode( ' ', $args));
    
}


/**
 * acfe_parse_log
 *
 * @param $arg
 *
 * @return mixed|string
 */
function acfe_parse_log($arg){
    
    // array
    if(is_array($arg)){
        
        foreach($arg as &$value){
            $value = acfe_parse_log($value);
        }
        
    // boolean
    }elseif($arg === true || $arg === false){
        $arg = $arg ? '(true)' : '(false)';
        
    // null
    }elseif($arg === null){
        $arg = '(null)';
        
    // empty string
    }elseif($arg === ''){
        $arg = '""';
    }
    
    // return
    return $arg;
    
}