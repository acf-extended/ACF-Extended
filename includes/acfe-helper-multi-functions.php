<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get
 *
 * Search within array/object using dot notation
 *
 * @param $subject array | object
 * @param $path    string | int | array
 * @param $default mixed
 *
 * @return array|mixed|null
 */
function acfe_get($subject, $path, $default = null){
    
    // validate subject (array or object)
    if(!is_array($subject) && !is_object($subject)){
        return $default;
    }
    
    // short-circuit for fast search
    if(is_string($path) && $path !== '' && strpos($path, '.') === false){
        
        if(is_array($subject)){
            return isset($subject[ $path ]) ? $subject[ $path ] : $default;
            
        }elseif(is_object($subject)){
            return isset($subject->$path) ? $subject->$path : $default;
        }
        
    }
    
    // explode path into parts
    $keys = acfe_as_array($path, '.');
    
    // no path, return whole subject
    if(empty($keys)){
        return $subject;
    }
    
    // loop & update the reference to child rows
    foreach($keys as $i => $key){
        
        // unset current key
        unset($keys[ $i ]);
        
        // wildcard
        if($key === '*'){
            
            if(!is_array($subject)){
                return $default;
            }
            
            $result = array();
            foreach($subject as $item){
                $result[] = acfe_get($item, $keys);
            }
            
            return in_array('*', $keys) ? acfe_array_collapse($result) : $result;
            
        // array
        }elseif(is_array($subject) && isset($subject[ $key ])){
            $subject = $subject[ $key ];
            
        // object
        }elseif(is_object($subject) && isset($subject->{$key})){
            $subject = $subject->{$key};
            
        // default
        }else{
            return $default;
        }
        
    }
    
    // return
    return $subject;
    
}


/**
 * acfe_set
 *
 * Set value within array, object or string using dot notation
 * Mixed types (string, boolean, int...) are only fully replaced
 *
 * @param $subject array | object | mixed
 * @param $path    string | int | array
 * @param $value   mixed
 *
 * @return void
 */
function acfe_set(&$subject, $path, $value = null){
    
    // short syntax:
    // acfe_set($subject, 'value')
    if(func_num_args() === 2){
        $value = $path;
        $path = '';
    }
    
    // normalize path as string
    $path = acfe_as_string($path, '.');
    
    // overwrite fully
    if($path === ''){
        $subject = $value;
        return;
    }
    
    // validate array or object
    if(!is_array($subject) && !is_object($subject)){
        $subject = $value;
        return;
    }
    
    // when path is only dots (...), remove the last dot
    // this    set(subject, '.', value)
    // becomes set(subject, '',  value)
    if(strpos($path, '.') !== false && preg_match('/^\.+$/', $path)){
        $path = substr($path, 1);
    }
    
    // explode path into parts
    $keys = acfe_as_array($path, '.');
    $keys = empty($keys) ? array('') : $keys; // add empty last key if we removed all dots above
    
    // loop keys
    foreach($keys as $i => $key){
        
        // unset current key
        unset($keys[ $i ]);
        
        // if the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if(is_array($subject)){
            
            // last key
            if(empty($keys)){
                
                // last key is dot
                if($key === ''){
                    $subject[] = $value;
                    return;
                }
                
                // last key is named
                $subject[ $key ] = $value;
                return;
            }
            
            if(!isset($subject[ $key ]) || !is_array($subject[ $key ])){
                
                // allow multiple dot notation
                // ie: array.key..subkey
                if($key === ''){
                    $subject[] = array();
                    $key = key(array_slice($subject, -1, 1, true)); // get last key
                }
                
                $subject[ $key ] = array();
                
            }
            
            // update reference to child
            $subject = &$subject[ $key ];
            
            
        }elseif(is_object($subject)){
            
            // last key
            if(empty($keys)){
                
                // last key is dot
                if($key === ''){
                    $subject = (array) $subject;
                    $subject[] = $value;
                    return;
                }
                
                // last key is named
                $subject->$key = $value;
                return;
            }
            
            $new_array = false;
            
            if(isset($subject->$key) && is_array($subject->$key)){
                // array inside object
                // do nothing
            }elseif(!isset($subject->$key) || !is_object($subject->$key)){
                
                if($key === ''){
                    $new_array = true;
                    $subject = array();
                    $subject[] = array();
                    $key = key(array_slice($subject, -1, 1, true)); // get last key
                }else{
                    $subject->$key = new stdClass();
                }
                
            }
            
            // update reference to child
            if($new_array){
                $subject = &$subject[ $key ];
                
            }else{
                $subject = &$subject->$key;
            }
            
        }
        
    }
    
}


/**
 * acfe_unset
 *
 * Remove a value from an array/object using dot notation
 * For strings: The $path string is deleted
 *
 * @param $subject array | object | string
 * @param $path
 *
 * @return void
 */
function acfe_unset(&$subject, $path){
    
    // validate
    if(!is_array($subject) && !is_object($subject) && !is_string($subject)){
        return;
    }
    
    // backup original
    $original = &$subject;
    
    // allow multiple keys
    $paths = (array) $path;
    if(count($paths) === 0){
        return;
    }
    
    // loop paths
    foreach($paths as $path){
        
        // if the exact key exists in the top-level, remove it
        if(is_array($subject) && array_key_exists($path, $subject)){
            unset($subject[ $path ]);
            continue;
            
        }elseif(is_object($subject) && isset($subject->$path)){
            unset($subject->$path);
            continue;
            
        }elseif(is_string($subject)){
            $subject = str_replace($path, '', $subject);
            continue;
        }
        
        $keys = explode('.', $path);
        
        // clean up before each pass
        $subject = &$original;
        
        while(count($keys) > 1){
            
            $key = array_shift($keys);
            
            if(is_array($subject) && isset($subject[ $key ]) && (is_array($subject[ $key ]) || is_object($subject[ $key ]))){
                $subject = &$subject[ $key ];
                
            }elseif(is_object($subject) && isset($subject->$key) && (is_array($subject->$key) || is_object($subject->$key))){
                $subject = &$subject->$key;
                
            }else{
                continue 2;
            }
            
        }
        
        // handle last key
        $last_key = array_shift($keys);
        if(is_array($subject)){
            unset($subject[ $last_key ]);
            
        }elseif(is_object($subject)){
            unset($subject->$last_key);
            
        }elseif(is_string($subject)){
            $subject = str_replace($last_key, '', $subject);
        }
        
    }
    
}


/**
 * acfe_has
 *
 * Perform array_key_exists() or property_exists() with dot notation
 *
 * Usage examples:
 *
 * acfe_has($subject, 'path.key');
 * acfe_has($subject, array('path.key', 'path.sub.key'));
 *
 * @param $subject string | array | object
 * @param $path    string | array
 * @param $compare string
 *
 * @return bool
 */
function acfe_has($subject, $path, $compare = 'AND'){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short-circuit for fast search
    if(is_string($path) && $path !== ''){
        
        if(is_string($subject)){
            return strpos($subject, $path) !== false;
            
        }elseif(strpos($path, '.') === false){
            
            if(is_array($subject)){
                return array_key_exists($path, $subject);
                
            }elseif(is_object($subject)){
                return property_exists($subject, $path);
            }
            
        }
        
    }
    
    // normalize compare
    $compare = strtoupper($compare);
    $compare === 'AND' || $compare === 'OR' ?: $compare = 'AND';
    
    // allow multiple keys
    $paths = (array) $path;
    if(count($paths) === 0){
        return false;
    }
    
    // run OR compare
    if($compare === 'OR'){
        return acfe_array_some($paths, function($path) use($subject){
            return acfe_has($subject, $path, 'AND');
        });
    }
    
    // string search
    if(is_string($subject)){
        return acfe_array_some($paths, function($path) use($subject){
            return !is_string($path) || $path === '' || strpos($subject, $path) === false;
        }, false, true);
    }
    
    // validate array or object
    if((!is_array($subject) && !is_object($subject))){
        return false;
    }
    
    // loop keys
    foreach($paths as $path){
        
        // backup subject for next iteration
        $row = $subject;
        $keys = acfe_as_array($path, '.');
        
        // short-circuit if the exact key exists in the top-level
        if(is_array($row) && array_key_exists($path, $row)){
            continue;
        }
        
        // loop keys
        foreach($keys as $key){
            
            if(is_array($row) && array_key_exists($key, $row)){
                $row = $row[ $key ];
                
            }elseif(is_object($row) && property_exists($row, $key)){
                $row = $row->$key;
                
            }else{
                return false; // bail early
            }
            
        }
        
    }
    
    // return true
    return true;
    
}


/**
 * acfe_contains
 *
 * Performs in_array() within array/object using dot notation (operator: AND)
 *
 * Usage examples:
 *
 * acfe_contains($subject, 'foo');
 * acfe_contains($subject, 'path.key', 'foo');
 * acfe_contains($subject, 'path.key', array('foo', 'bar')); // AND
 *
 * @param $subject
 * @param $path
 * @param $value
 * @param $compare
 *
 * @return bool
 */
function acfe_contains($subject, $path, $value = '', $compare = 'AND'){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short syntax:
    // acfe_contains(subject, value)
    if(func_num_args() === 2){
        $value = $path;
        $path = '';
        
    // acfe_contains(subject, value, OR)
    }elseif(func_num_args() === 3 && (is_string($value) && (strtoupper($value) === 'AND' || strtoupper($value) === 'OR'))){
        $compare = $value;
        $value = $path;
        $path = '';
    }
    
    // shortcircuit for fast search
    if($path === ''){
        
        if(is_array($subject)){
            return in_array($value, $subject, true);
            
        }elseif(is_string($subject) && is_string($value) && $value !== ''){
            return strpos($subject, $value) !== false;
        }
        
    }
    
    // normalize compare
    $compare = strtoupper($compare);
    $compare === 'AND' || $compare === 'OR' ?: $compare = 'AND';
    
    // string (acfe_has)
    if(is_string($subject)){
        return acfe_has($subject, $value, $compare);
    }
    
    // allow multiple values
    $values = (array) $value;
    if(count($values) === 0){
        return false;
    }
    
    // run OR compare
    if($compare === 'OR'){
        return acfe_array_some($values, function($v) use ($subject, $path){
            return acfe_contains($subject, $path, $v, 'AND');
        });
    }
    
    // validate
    if(!is_array($subject) && !is_object($subject)){
        return false;
    }
    
    // get row value
    $row = acfe_get($subject, $path);
    
    // loop values
    foreach($values as $v){
        
        // check if value is found in row (array or string)
        $found = (is_array($row) && in_array($v, $row, true)) || (is_string($row) && is_string($v) && strpos($row, $v) !== false);
        
        // bail early
        if(!$found){
            return false;
        }
        
    }
    
    // return true
    return true;
    
}


/**
 * acfe_extract
 *
 * Extract a value from an array/object and remove it
 *
 * acfe_extract($subject, 'foo');
 * acfe_extract($subject, array('foo', 'bar'));
 * acfe_extract($subject, array('my_foo' => 'foo', 'my_bar' => 'bar')); // named multiple return
 * acfe_extract($subject, array('foo', 'bar'), array('foo' => 'default foo', 'bar' => 'default bar'));
 * acfe_extract($subject, array('my_foo' => 'foo', 'my_bar' => 'bar'), array('my_foo' => 'default foo', 'my_bar' => 'default bar'));
 *
 * @param $subject array | object | string
 * @param $keys
 * @param $default
 *
 * @return array|mixed|null
 */
function acfe_extract(&$subject, $keys, $default = null){
    
    // validate
    if(!is_array($subject) && !is_object($subject) && !is_string($subject)){
        return $default;
    }
    
    // multiple extract
    if(is_array($keys)){
        
        $value = [];
        foreach($keys as $k => $v){
            
            // determine name if associative array is used
            $name = is_string($k) ? $k : $v;
            
            // default row
            $row_default = $default;
            
            // default is an array, try to get default
            if(is_array($default)){
                $row_default = array_key_exists($name, $default) ? $default[ $name ] : null;
            }
            
            // extract value
            $value[ $name ] = acfe_extract($subject, $v, $row_default);
            
        }
        
        // return
        return $value;
        
    }
    
    // single extract
    $key = $keys;
    
    // case: string
    if(is_string($subject)){
        
        if(!acfe_has($subject, $key)){
            return $default;
        }
        
        acfe_unset($subject, $key);
        return $key;
        
    }
    
    // case: array/object
    $value = acfe_get($subject, $key, $default);
    acfe_unset($subject, $key);
    
    // return
    return $value;
}


/**
 * acfe_starts_with
 *
 * Check if array/object/string starts with something
 *
 * @param $subject
 * @param $path
 * @param $search
 *
 * @return bool
 */
function acfe_starts_with($subject, $path, $search = ''){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short syntax:
    // acfe_starts_with(subject, value)
    if(func_num_args() === 2){
        $search = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return acfe_str_starts_with($subject, $search);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return false;
    }
    
    // get value (must be string)
    $value = acfe_get($subject, $path);
    if(!is_string($value)){
        return false;
    }
    
    // return
    return acfe_str_starts_with($value, $search);
    
}


/**
 * acfe_ends_with
 *
 * Check if array/object/string starts with something
 *
 * @param $subject
 * @param $path
 * @param $search
 *
 * @return bool
 */
function acfe_ends_with($subject, $path, $search = ''){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short syntax:
    // acfe_ends_with(subject, value)
    if(func_num_args() === 2){
        $search = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return acfe_str_ends_with($subject, $search);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return false;
    }
    
    // get value (must be string)
    $value = acfe_get($subject, $path);
    if(!is_string($value)){
        return false;
    }
    
    // return
    return acfe_str_ends_with($value, $search);
    
}


/**
 * acfe_ltrim
 *
 * Remove a prefix from a path value within an array/object using dot notation
 * If subject is a string, it will be trimmed directly
 *
 * @param $subject
 * @param $path
 * @param $remove
 *
 * @return array|object|string
 */
function acfe_ltrim($subject, $path, $remove = ''){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short syntax:
    // acfe_ltrim(subject, value)
    if(func_num_args() === 2){
        $remove = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return acfe_str_ltrim($subject, $remove);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // get value (must be string)
    $value = acfe_get($subject, $path);
    if(!is_string($value)){
        return $subject;
    }
    
    // trim value
    $value = acfe_str_ltrim($value, $remove);
    
    // update
    acfe_set($subject, $path, $value);
    
    // return
    return $subject;
    
}


/**
 * acfe_rtrim
 *
 * Remove a suffix from a path value within an array/object using dot notation
 * If subject is a string, it will be trimmed directly
 *
 * @param $subject
 * @param $path
 * @param $remove
 *
 * @return array|object|string
 */
function acfe_rtrim($subject, $path, $remove = ''){
    
    // validate subject
    if(empty($subject) && !is_numeric($subject)){
        return false;
    }
    
    // short syntax:
    // acfe_rtrim(subject, value)
    if(func_num_args() === 2){
        $remove = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return acfe_str_rtrim($subject, $remove);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // get value (must be string)
    $value = acfe_get($subject, $path);
    if(!is_string($value)){
        return $subject;
    }
    
    // trim value
    $value = acfe_str_rtrim($value, $remove);
    
    // update
    acfe_set($subject, $path, $value);
    
    // return
    return $subject;
    
}


/**
 * acfe_prepend
 *
 * @param $subject array | object | string
 * @param $path
 * @param $prepend
 * @param $unpack bool Shall we unpack the appended array instead of adding it as a single element
 *
 * @return array|object|string
 */
function acfe_prepend($subject, $path, $prepend = null, $unpack = true){
    
    // short syntax:
    // acfe_prepend(subject, value)
    if(func_num_args() === 2){
        $prepend = $path;
        $path = '';
        
    // acfe_prepend(subject, value, true)
    }elseif(func_num_args() === 3 && is_bool($prepend)){
        $unpack = $prepend;
        $prepend = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return acfe_as_string($prepend) . $subject;
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // get value
    $value = acfe_get($subject, $path);
    
    // case: string
    if(is_string($value)){
        
        // prepend
        $value = acfe_as_string($prepend) . $value;
        
        // update
        acfe_set($subject, $path, $value);
        
    // case: array
    }elseif(is_array($value)){
        
        if(is_array($prepend) && $unpack){
            $value = array_merge($prepend, $value);
        }else{
            array_unshift($value, $prepend);
        }
        
        // update
        acfe_set($subject, $path, $value);
        
    }
    
    // return
    return $subject;
    
}


/**
 * acfe_append
 *
 * @param $subject array | object | string
 * @param $path    mixed  The path
 * @param $append  mixed  The value to append
 * @param $unpack  bool   Shall we unpack the appended array instead of adding it as a single element
 *
 * @return array|object|string
 */
function acfe_append($subject, $path, $append = null, $unpack = true){
    
    // short syntax:
    // acfe_append(subject, value)
    if(func_num_args() === 2){
        $append = $path;
        $path = '';
        
    // acfe_append(subject, value, true)
    }elseif(func_num_args() === 3 && is_bool($append)){
        $unpack = $append;
        $append = $path;
        $path = '';
    }
    
    // string
    if(is_string($subject)){
        return $subject . acfe_as_string($append);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // get value
    $value = acfe_get($subject, $path);
    
    // case: string
    if(is_string($value)){
        
        // append
        $value = $value . acfe_as_string($append);
        
        // update
        acfe_set($subject, $path, $value);
        
    // case: array
    }elseif(is_array($value)){
        
        if(is_array($append) && $unpack){
            $value = array_merge($value, $append);
        }else{
            $value[] = $append;
        }
        
        // update
        acfe_set($subject, $path, $value);
        
    }
    
    // return
    return $subject;
    
}


/**
 * acfe_before
 *
 * @param $subject array | object | string
 * @param $path
 * @param $before
 * @param $unpack bool Shall we unpack the appended array instead of adding it as a single element
 *
 * @return array|int|mixed|object|string|string[]
 */
function acfe_before($subject, $path, $before = null, $unpack = true){
    
    // short syntax:
    // acfe_before($subject, 'value')
    if(func_num_args() === 2){
        return acfe_prepend($subject, $path);
        
    // acfe_before($subject, 'value', true)
    }elseif(func_num_args() === 3 && is_bool($before)){
        return acfe_prepend($subject, $path, $before);
    }
    
    // use path as string
    $path = acfe_as_string($path, '.');
    
    // string
    if(is_string($subject)){
        
        // convert prepend to string
        $before_str = acfe_as_string($before);
        
        // if path is empty: prepend. Otherwise, replace the first occurence
        return $path === '' ? "{$before_str}{$subject}" : acfe_str_replace($subject, $path, "{$before_str}{$path}", 0, 1);
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // explode into parts
    // convert numeric parts to int (so last key can be found within the loop)
    $parts = explode('.', $path);
    $parts = acfe_array_map($parts, function($part){
        return is_numeric($part) ? (int) $part : $part;
    });
    
    // extract last part
    $last_key = array_pop($parts);
    
    // get row value
    $value = acfe_get($subject, $parts);
    
    // validate value as array
    if(!is_array($value)){
        return $subject;
    }
    
    // vars
    $rows = array();
    $is_sequential = acf_is_sequential_array($value);
    
    // loop value
    foreach($value as $k => $v){
        
        // last key found
        if($last_key === $k){
            
            if(is_array($before) && $unpack){
                $rows = array_merge($before, $rows);
            }else{
                $rows[] = $before;
            }
            
        }
        
        // add current element
        $is_sequential ? $rows[] = $v : $rows[ $k ] = $v;
        
    }
    
    // update value
    $value = $rows;
    
    // replace that specific part
    acfe_set($subject, $parts, $value);
    
    // return
    return $subject;
    
}


/**
 * acfe_after
 *
 * @param $subject array | object | string
 * @param $path
 * @param $after
 * @param $unpack bool Shall we unpack the appended array instead of adding it as a single element
 *
 * @return array|int|mixed|object|string|string[]
 */
function acfe_after($subject, $path, $after = null, $unpack = true){
    
    // short syntax:
    // acfe_after($subject, 'value')
    if(func_num_args() === 2){
        return acfe_append($subject, $path);
        
    // acfe_after($subject, 'value', true)
    }elseif(func_num_args() === 3 && is_bool($after)){
        return acfe_append($subject, $path, $after);
    }
    
    // use path as string
    $path = acfe_as_string($path, '.');
    
    // string
    if(is_string($subject)){
        
        // convert append to string
        $after_str = acfe_as_string($after);
        
        // if path is empty: append. Otherwise, replace the first occurence
        return $path === '' ? "{$subject}{$after_str}" : acfe_str_replace($subject, $path, "{$path}{$after_str}", 0, 1);
        
    }
    
    // validate array/object
    if(!is_array($subject) && !is_object($subject)){
        return $subject;
    }
    
    // explode into parts
    // convert numeric parts to int (so last key can be found within the loop)
    $parts = explode('.', $path);
    $parts = acfe_array_map($parts, function($part){
        return is_numeric($part) ? (int) $part : $part;
    });
    
    // extract last part
    $last_key = array_pop($parts);
    
    // get row value
    $value = acfe_get($subject, $parts);
    
    // validate value as array
    if(!is_array($value)){
        return $subject;
    }
    
    // vars
    $rows = array();
    $is_sequential = acf_is_sequential_array($value);
    
    // loop value
    foreach($value as $k => $v){
        
        // add current element
        $is_sequential ? $rows[] = $v : $rows[ $k ] = $v;
        
        // last key found
        if($last_key === $k){
            
            if(is_array($after) && $unpack){
                $rows = array_merge($rows, $after);
            }else{
                $rows[] = $after;
            }
            
        }
    }
    
    // update value
    $value = $rows;
    
    // replace that specific part
    acfe_set($subject, $parts, $value);
    
    // return
    return $subject;
    
}


/**
 * acfe_count
 *
 * Count the number of items in a variable
 *
 * @param $var
 * @param $default
 *
 * @return int
 */
function acfe_count($var, $default = 0){
    
    // object case
    if(is_object($var)){
        $var = get_object_vars($var);
    }
    
    // countable (php 5.6 version of is_countable())
    if(is_array($var) || $var instanceof Countable){
        return count($var);
        
    // numeric
    }elseif(is_numeric($var)){
        return (int) $var;
    }
    
    // default
    return $default;
    
}