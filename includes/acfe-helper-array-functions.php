<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_as_array
 *
 * Convert a variable to an array if possible, with some basic type checks
 *
 * @param $var
 * @param $delimiter
 *
 * @return array
 */
function acfe_as_array($var = false, $delimiter = null){
    
    // array
    if(is_array($var)){
        return $var;
        
    // basic type check
    }elseif(!$var && !is_numeric($var)){
        return array();
        
    // string
    }elseif(is_string($var) && $delimiter !== null){
        return explode($delimiter, $var);
    }
    
    // force array
    return (array) $var;
    
}

/**
 * acfe_array_map
 *
 * Similar to array_map() but with keys and row metadata in the callback.
 *
 * @param $array     array
 * @param $callback  callable
 * @param $recursive bool
 * @param $row       array Internal parameter for row context (depth, index, path)
 *
 * @return array
 */
function acfe_array_map($array, $callback, $recursive = false, $row = array()){
    
    // validate
    if(!is_array($array) || empty($array)){
        return array();
    }
    
    // vars
    $result = array();
    
    // prepare row
    $row = wp_parse_args($row, array(
        'depth' => 0,
        'index' => 0,
        'path'  => '',
    ));

    // loop array
    foreach($array as $key => $value){
        
        // update row
        $current_row = array(
            'depth' => $row['depth'],
            'index' => $row['index'],
            'path'  => ($row['path'] !== '' ? "{$row['path']}.{$key}" : (string) $key),
        );

        // callback
        $map = $callback($value, $key, $current_row);

        // handle recursive mapping
        if(is_array($map) && $recursive){
            
            // call itself with updated row context
            $map = acfe_array_map($map, $callback, true, array(
                'depth' => $row['depth']+1,
                'path'  => $current_row['path'],
            ));
            
        }
        
        // update result
        $result[ $key ] = $map;
        
        // increment index for next iteration
        $row['index']++;
        
    }
    
    // return
    return $result;
    
}



/**
 * acfe_array_rewrite
 *
 * Run a map over each of the items in the array.
 *
 * Return false = remove the row from the result
 * Return true = keep the original row in the result, do not proceed children, even if recursive is enabled
 * Return array(key => value) = rewrite the item with the new key and value, if recursive is enabled, children will be processed as well
 *
 * @param  callable  $callback
 * @param  array  $array
 *
 * @return array
 */
function acfe_array_rewrite($array, $callback, $recursive = false){
    
    // validate
    if(!is_array($array) || empty($array)){
        return array();
    }

    $result = array();

    // stack frames: input array, keys, current index, output reference, parent dot-path, depth
    $stack = array(array(
        'input'  => $array,
        'keys'   => array_keys($array),
        'index'  => 0,
        'result' => &$result,
        'path'   => '',
        'depth'  => 0,
    ));
    
    while(!empty($stack)){

        $i = count($stack) - 1;

        // frame completed
        if($stack[$i]['index'] >= count($stack[$i]['keys'])){
            array_pop($stack);
            continue;
        }
        
        $key         = $stack[$i]['keys'][ $stack[$i]['index'] ];
        $value       = $stack[$i]['input'][ $key ];
        $parent_path = $stack[$i]['path'];
        $depth       = $stack[$i]['depth'];
        
        // prepare row data for callback
        $row = array(
            'depth' => $depth,
            'index' => $stack[$i]['index'],
            'path'  => ($parent_path !== '' ? $parent_path . '.' . $key : (string) $key),
        );
        
        $stack[$i]['index']++;
        
        // callback
        $map = $callback($value, $key, $row);
        
        // string | bool | null etc...
        if(!is_array($map)){
            
            // true returns original row
            // do not proceed children, even if recursive is enabled
            if($map === true){
                $stack[$i]['result'][ $key ] = $value;
            }
            
            // false, null, string remove the row
            continue;
            
        }
        
        // rewrite rows
        foreach($map as $map_key => $map_value){
            
            if(is_array($map_value) && $recursive){
                
                $stack[$i]['result'][ $map_key ] = array();
                $child_result = &$stack[$i]['result'][ $map_key ];
                $child_path   = $parent_path !== '' ? $parent_path . '.' . $map_key : (string) $map_key;
                
                $stack[] = array(
                    'input'  => $map_value,
                    'keys'   => array_keys($map_value),
                    'index'  => 0,
                    'result' => &$child_result,
                    'path'   => $child_path,
                    'depth'  => $depth + 1,
                );
                
                unset($child_result);
                
            }else{
                $stack[$i]['result'][ $map_key ] = $map_value;
            }
            
        }

    }

    return $result;
}


/**
 * acfe_array_where
 *
 * Similar to array_filter(), but with preserved keys and an additional offset parameter in the callback function.
 *
 * Iterates over each value in the array passing them to the callback function.
 * If the callback function returns true, the current value from array is returned into the result array.
 *
 * @param  array  $array
 * @param  callable  $callback
 *
 * @return array
 */
function acfe_array_where($array, $callback){
    
    // validate
    if(!is_array($array)){
        return array();
    }
    
    // result
    $result = array();
    $offset = 0;
    
    // loop
    foreach($array as $key => $value){
        
        if($callback($value, $key, $offset)){
            $result[ $key ] = $value;
        }
        
        $offset++;
    }
    
    // return
    return $result;
}


/**
 * acfe_array_first
 *
 * Return the first element in an array passing a given truth test
 *
 * @param $array
 * @param $callback
 * @param $default
 *
 * @return mixed|null
 */
function acfe_array_first($array, $callback = null, $default = null){
    
    // validate
    if(!is_array($array) || empty($array)){
        return $default;
    }
    
    // no callback, return first item
    if($callback === null){
        foreach($array as $item){
            return $item;
        }
    }
    
    // loop
    $offset = 0;
    foreach($array as $key => $value){
        
        if($callback($value, $key, $offset)){
            return $value;
        }
        
        $offset++;
    }
    
    // default
    return $default;
}


/**
 * acfe_array_last
 *
 * Return the last element in an array passing a given truth test
 *
 * @param $array
 * @param $callback
 * @param $default
 *
 * @return false|mixed|null
 */
function acfe_array_last($array, $callback = null, $default = null){
    
    // validate
    if(!is_array($array) ||empty($array)){
        return $default;
    }
    
    return acfe_array_first(array_reverse($array, true), $callback, $default);
}


/**
 * acfe_array_key_first
 *
 * Returns the first key of an array using a callback function to test each element
 * Returns null if no element passed the truth test or if the array is empty.
 *
 * @param $array
 * @param $callback
 *
 * @return int|string|null
 */
function acfe_array_key_first($array, $callback = null){
    
    // validate
    if(!is_array($array) || empty($array)){
        return null;
    }
    
    // no callback, return first key
    if($callback === null){
        foreach($array as $key => $value){
            return $key;
        }
    }
    
    // loop
    $offset = 0;
    foreach($array as $key => $value){
        
        if($callback($value, $key, $offset)){
            return $key;
        }
        
        $offset++;
    }
    
    // default
    return null;
}


/**
 * acfe_array_key_last
 *
 * Returns the last key of an array using dot notation
 *
 * @param $array
 * @param $callback
 *
 * @return int|string|null
 */
function acfe_array_key_last($array, $callback = null){
    
    // validate
    if(!is_array($array) || empty($array)){
        return null;
    }
    
    return acfe_array_key_first(array_reverse($array, true), $callback);
    
}


/**
 * acfe_array_some
 *
 * Return true if any item matches the truth test, else false.
 *
 * @param array    $array
 * @param callable $callback
 * @param mixed    $true
 * @param mixed    $false
 *
 * @return mixed
 */
function acfe_array_some($array, $callback, $true = true, $false = false){
    
    // validate
    if(!is_array($array) || empty($array)){
        return $false;
    }
    
    // loop
    foreach($array as $key => $value){
        if($callback($value, $key)){
            return $true;
        }
    }
    
    // return
    return $false;
}


/**
 * acfe_array_every
 *
 * Return true if all items match the truth test, else false.
 *
 * @param array    $array
 * @param callable $callback
 * @param mixed    $true
 * @param mixed    $false
 *
 * @return bool|mixed
 */
function acfe_array_every($array, $callback, $true = true, $false = false){
    
    // validate
    if(!is_array($array) || empty($array)){
        return $false;
    }
    
    // loop
    foreach($array as $key => $value){
        if(!$callback($value, $key)){
            return $false;
        }
    }
    
    // return
    return $true;
}


/**
 * acfe_array_dot
 *
 * Flatten a multidimensional associative array with dots
 *
 * Example:
 *
 * $array = array(
 *     'key1' => 'value',
 *     'key2' => array(
 *         'subkey1' => true,
 *         'subkey2' => true,
 *      ),
 * );
 *
 * will result to:
 *
 * $array = array(
 *     'key1' => 'value',
 *     'key2.subkey1' => true,
 *     'key2.subkey2' => true,
 * );
 *
 * @param $array
 *
 * @return array
 */
function acfe_array_dot($array){
    
    // basic validation
    if(!is_array($array) || empty($array)){
        return array();
    }
    
    // results
    $results = array();
    
    // stack entries: [current_array, prepend_prefix]
    $stack = array(array($array, ''));
    
    // loop stack
    while(!empty($stack)){
        
        // extract array & prefix
        list($current, $prepend) = array_shift($stack);
        
        foreach($current as $key => $row){
            
            if(is_array($row) && !empty($row)){
                $stack[] = array($row, "{$prepend}{$key}.");
            }else{
                $results[ $prepend.$key ] = $row;
            }
            
        }
        
    }
    
    return $results;
}


/**
 * acfe_array_keys_dot
 *
 * Flatten a multidimensional array keys with dots
 *
 * Example:
 *
 * $array = array(
 *     'key1' => 'value',
 *     'key2' => array(
 *         'subkey1' => true,
 *         'subkey2' => true,
 *      ),
 * );
 *
 * will result to:
 *
 * $array = array(
 *     'key1',
 *     'key2',
 *     'key2.subkey1',
 *     'key2.subkey2',
 * );
 *
 * @param array $array      The input array to flatten keys from
 * @param bool  $only_assoc Only allow associative arrays, ignore sequential arrays (numeric keys)
 *
 * @return array
 */
function acfe_array_keys_dot($array, $only_assoc = false){
    
    // basic validation
    if(!is_array($array) || empty($array)){
        return array();
    }
    
    // results
    $results = array();
    
    // stack entries: [current_array, prepend_prefix]
    $stack = array(
        array($array, '')
    );
    
    // loop stack
    while(!empty($stack)){
        
        // extract array & prefix
        list($current, $prepend) = array_shift($stack);
        
        foreach($current as $key => $row){
            
            $results[] = $prepend . $key;
            
            if(is_array($row) && !empty($row) && (!acf_is_sequential_array($row) || !$only_assoc)){
                $stack[] = array($row, "{$prepend}{$key}.");
            }
            
        }
        
    }
    
    return $results;
    
}


/**
 * acfe_array_undot
 *
 * Allows to use dot notation in array keys and assign the correct keys
 *
 * Example:
 *
 * $array = array(
 *     'my.key' => true
 * )
 *
 * will result to:
 *
 * $array = array(
 *     'my' => array(
 *         'key' => true
 *     )
 * )
 *
 * @param $array
 *
 * @return array|mixed
 */
function acfe_array_undot($array){
    
    // validate array
    if(!is_array($array)){
        return $array;
    }
    
    // vars
    $array2 = array();
    
    // loop
    foreach($array as $k => $v){
        if(is_string($k) && acfe_has($k, '.')){
            
            $array2[ $k ] = $v;
            unset($array[ $k ]);
            
        }
    }
    
    // loop dotted array
    foreach($array2 as $k => $v){
        acfe_set($array, $k, $v);
    }
    
    foreach($array as $k => $v){
        if(is_array($v)){
            $array[ $k ] = acfe_array_undot($v);
        }
    }
    
    // return
    return $array;
    
}


/**
 * acfe_array_flatten
 *
 * Flatten a multi-dimensional array into a single level.
 * Use $depth parameter to specify the maximum depth to flatten. Default is -1 (flatten all levels).
 *
 * $array = array(
 *     'America' => array(
 *         'us' => 'USA',
 *         'ca' => 'Canada'
 *     ),
 *     'Europe' => array(
 *         'fr' => 'France',
 *         'it' => 'Italy'
 *     ),
 * );
 *
 * will result to:
 * $array = array(
 *     'USA',
 *     'Canada',
 *     'France',
 *     'Italy'
 * )
 *
 * @param  array  $array     The multi-dimensional array to flatten
 * @param  bool   $with_keys Whether to preserve keys in the flattened result (default: false)
 * @param  int    $depth     Maximum depth to flatten (-1 for unlimited)
 *
 * @return array
 */
function acfe_array_flatten($array, $with_keys = false, $depth = -1){
    
    // validate
    if(!is_array($array)){
        return array();
    }
    
    // prepare
    $depth = max(-1, (int) $depth);
    $result = array();
    
    // loop
    foreach($array as $k => $item){
        
        // non-array item, add to result
        if(!is_array($item)){
            $with_keys ? $result[ $k ] = $item : $result[] = $item;
            
        // unlimited depth
        }elseif($depth === -1){
            $result = array_merge($result, acfe_array_flatten($item, $with_keys, -1));
            
        // handle custom depth
        }elseif($depth > 0){
            $result = array_merge($result, acfe_array_flatten($item, $with_keys, $depth - 1));
        }
        
    }
    
    // return
    return $result;
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
 * acfe_array_collapse
 *
 * Collapse an array of arrays into a single array.
 *
 * @param $array
 *
 * @return array
 */
function acfe_array_collapse($array){
    
    // vars
    $results = array();
    
    // validate
    if(!is_array($array)){
        return $results;
    }
    
    // loop
    foreach($array as $values){
        
        // append
        if(is_array($values)){
            $results[] = $values;
        }
        
    }
    
    // return
    return array_merge(array(), ...$results);
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
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_get($array, $key, $default = null){
    
    acfe_deprecated_function('acfe_array_get()', '0.9.2.6', 'acfe_get()');
    return acfe_get($array, $key, $default);
    
}

/**
 * acfe_array_set
 *
 * @param $array
 * @param $keys
 * @param $value
 *
 * @return void
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_set(&$array, $keys, $value = null){
    
    acfe_deprecated_function('acfe_array_set()', '0.9.2.6', 'acfe_set()');
    if(func_num_args() === 2){
        acfe_set($array, $keys);
    }else{
        acfe_set($array, $keys, $value);
    }
    
}


/**
 * acfe_array_unset
 *
 * @param $array
 * @param $keys
 *
 * @return void
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_unset(&$array, $keys){
    
    acfe_deprecated_function('acfe_array_unset()', '0.9.2.6', 'acfe_unset()');
    acfe_unset($array, $keys);
    
}


/**
 * acfe_array_has
 *
 * Perform array_key_exists() with dot notation (operator: AND)
 *
 * Usage examples:
 *
 * acfe_array_has($array, 'path.key');
 * acfe_array_has($array, array('path.key', 'path.sub.key'));
 *
 * @param $array
 * @param $paths
 *
 * @return bool
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_has($array, $paths){
    
    acfe_deprecated_function('acfe_array_has()', '0.9.2.6', 'acfe_has()');
    return acfe_has($array, $paths);
    
}


/**
 * acfe_maybe_get
 *
 * Similar to acfe_get() but also works with OBJECTS
 *
 * @param array $array
 * @param int   $key
 * @param null  $default
 *
 * @return mixed|null
 *
 * @deprecated since 0.9.2.6
 */
function acfe_maybe_get($array = array(), $key = 0, $default = null){
    
    acfe_deprecated_function('acfe_maybe_get()', '0.9.2.6', 'acfe_get()');
    return acfe_get($array, $key, $default);
    
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
 *
 * @deprecated since 0.9.2.6
 */
function acfe_map_array_keys($array, $func){
    
    acfe_deprecated_function('acfe_map_array_keys()', '0.9.2.6', 'acfe_array_rewrite()');
    
    // validate array
    if(!is_array($array)){
        return $array;
    }
    
    return acfe_array_rewrite($array, function($value, $key) use ($func){
        
        // callback
        $return = $func($key, $value);
        
        // remove row
        if($return === false || $return === true){
            return $return;
        }
        
        // rewrite key
        return array($return => $value);
        
    }, true);
    
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
 *
 * @deprecated since 0.9.2.6
 */
function acfe_prefix_array_keys($array, $prefix, $ignore = array(), $recursive = true){
    
    acfe_deprecated_function('acfe_prefix_array_keys()', '0.9.2.6', 'acfe_array_rewrite()');
    
    return acfe_array_rewrite($array, function($value, $key) use ($prefix, $ignore){
        
        // ignore numeric keys and ignore list
        if(is_numeric($key) || ($ignore && acfe_contains($ignore, $key))){
            return array($key => $value);
        }
        
        // prefix key
        return array("{$prefix}{$key}" => $value);
        
    }, $recursive);
    
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
 *
 * @deprecated since 0.9.2.6
 */
function acfe_unprefix_array_keys($array, $prefix, $ignore = array(), $recursive = true){
    
    acfe_deprecated_function('acfe_unprefix_array_keys()', '0.9.2.6', 'acfe_array_rewrite()');
    
    return acfe_array_rewrite($array, function($value, $key) use ($prefix, $ignore){
        
        // ignore numeric keys and ignore list
        if(is_numeric($key) || ($ignore && acfe_contains($ignore, $key))){
            return array($key => $value);
        }
        
        // ltrim prefix
        $key = acfe_ltrim($key, $prefix);
        return array($key => $value);
        
    }, $recursive);
    
}


/**
 * acfe_get_array_flatten
 *
 * @param $array
 * @param $flattened
 *
 * @return array
 *
 * @deprecated since 0.9.2.6
 */
function acfe_get_array_flatten($array = array(), $flattened = array()){
    acfe_deprecated_function('acfe_get_array_flatten()', '0.9.2.6', 'acfe_array_flatten($array, true)');
    return acfe_array_flatten($array, true);
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
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_insert_before($array, $key, $new_key, $new_value = null){
    acfe_deprecated_function('acfe_array_insert_before()', '0.9.2.6', 'acfe_before()');
    return acfe_before($array, $key, array($new_key => $new_value));
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
 *
 * @deprecatedsince 0.9.2.6
 */
function acfe_array_insert_after($array, $key, $new_key, $new_value = null){
    acfe_deprecated_function('acfe_array_insert_after()', '0.9.2.6', 'acfe_after()');
    return acfe_after($array, $key, array($new_key => $new_value));
}


/**
 * acfe_array_to_string
 *
 * Convert an array to string
 *
 * @param array $array
 *
 * @return mixed
 *
 * @deprecated since 0.9.2.6
 */
function acfe_array_to_string($array = array()){
    
    // depreacted
    acfe_deprecated_function('acfe_array_to_string()', '0.9.2.6', 'acfe_array_first()');
    
    // validate array
    if(!is_array($array)){
        return $array;
    }
    
    // retrieve the first value of the array that is a string, number or boolean
    return acfe_array_first($array, function($val){
        return is_string($val) || is_numeric($val) || is_bool($val);
    }, false);
    
}