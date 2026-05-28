<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_as_string
 *
 * @param $var
 * @param $delimiter
 *
 * @return string
 */
function acfe_as_string($var, $delimiter = ''){
    
    // already a string
    if(is_string($var)){
        return $var;
    }
    
    // basic type return empty string
    if($var === true || $var === false || $var === null){
        return '';
    }
    
    // array
    if(is_array($var) && $delimiter !== null){
        
        // flatten array and remove empty values
        $flatten = acfe_array_flatten($var);
        $flatten = acfe_array_where($flatten, function($value){
            return is_numeric($value) || (is_string($value) && !empty($value));
        });
        
        return implode($delimiter, $flatten);
        
    }
    
    // object
    if(is_object($var)){
        return method_exists($var, '__toString') ? (string) $var : '';
    }
    
    // return
    return strval($var);
    
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
 * acfe_str_rtrim
 *
 * Remove a suffix from a string
 *
 * @param $subject
 * @param $search
 *
 * @return false|mixed|string
 */
function acfe_str_rtrim($subject, $search){
    
    // validate
    if(!is_string($subject)){
        return $subject;
    }
    
    $length = strlen($search);
    if(substr($subject, -$length) === $search){
        return substr($subject, 0, -$length);
    }
    
    return $subject;
}


/**
 * acfe_str_ltrim
 *
 * Remove a prefix from a string
 *
 * @param $subject
 * @param $search
 *
 * @return string
 */
function acfe_str_ltrim($subject, $search){
    
    // validate
    if(!is_string($subject)){
        return $subject;
    }
    
    $length = strlen($search);
    if(substr($subject, 0, $length) === $search){
        return substr($subject, $length);
    }
    
    return $subject;
}


/**
 * acfe_str_starts_with
 *
 * Check if a strings starts with something
 *
 * @param $subject
 * @param $search
 *
 * @return bool
 */
function acfe_str_starts_with($subject, $search){
    
    // validate
    if(!is_string($subject)){
        return false;
    }
    
    $length = strlen($search);
    return substr($subject, 0, $length) === $search;

}

/**
 * acfe_str_ends_with
 *
 * Check if a strings ends with something
 *
 * @param $subject
 * @param $search
 *
 * @return bool
 */
function acfe_str_ends_with($subject, $search){
    
    $length = strlen($search);
    if($length === 0){
        return true;
    }
    
    // validate
    if(!is_string($subject)){
        return false;
    }

    return substr($subject, -$length) === $search;
    
}


/**
 * acfe_str_replace
 *
 * Replace occurrences of a search string with a replacement string, with support for offset and limit.
 *
 * If offset is 0, it will replace the first occurrence of the search string.
 * If offset is 1, it will replace the second occurrence of the search string, and so on.
 * If offset is -1 it will replace the last occurrence of the search string
 * If offset is -2 it will replace the second to last occurrence of the search string, and so on.
 *
 * Limit is the maximum number of replacements to perform.
 * If limit is 0, it will replace all occurrences of the search string after the offset.
 *
 * If delete_others is true, it will delete the other occurrences of the search string after the all occurences have been replaced following the offset + limit rule.
 *
 * @param $subject
 * @param $search
 * @param $replace
 * @param $offset
 * @param $limit
 * @param $delete_others
 *
 * @return array|mixed|string|string[]
 */
function acfe_str_replace($subject, $search, $replace, $offset = 0, $limit = 0, $delete_others = false){
    
    // validate
    if(!is_string($subject) || $search === ''){
        return $subject;
    }
    
    // limit must be always at minimum 0
    $limit = max(0, $limit);
    
    // shortcircuit: if limit is 0 and offset is 0, replace all occurrences
    if($limit === 0 && $offset === 0){
        return str_replace($search, $replace, $subject);
    }
    
    // count total occurrences in original subject
    $occurrences = substr_count($subject, $search);
    if($occurrences === 0){
        return $subject;
    }
    
    // negative offset, start from the end
    if($offset < 0){
        $offset = $occurrences + $offset;
    }
    
    // out of range offset
    if($offset < 0 || $offset >= $occurrences){
        return $subject;
    }
    
    // replacement boundaries based on occurrence index
    $replace_from = $offset;
    $replace_to = $limit === 0 ? $occurrences : min($occurrences, $offset + $limit); // exclusive
    
    // iterate on original subject and rebuild output safely
    $result = '';
    $search_len = strlen($search);
    $scan_offset = 0;
    $chunk_start = 0;
    $occurrence_index = 0;
    
    // loop through occurrences of search string
    while(($pos = strpos($subject, $search, $scan_offset)) !== false){
        
        // append everything before the current occurrence
        $result .= substr($subject, $chunk_start, $pos - $chunk_start);
        
        // before replacement window: keep search
        if($occurrence_index < $replace_from){
            $result .= $search;
            
        // inside replacement window: replace
        }elseif($occurrence_index < $replace_to){
            $result .= $replace;
            
        // after replacement window
        }else{
            if(!$delete_others){
                $result .= $search;
            }
        }
        
        $occurrence_index++;
        $scan_offset = $pos + $search_len;
        $chunk_start = $scan_offset;
    }
    
    // append remaining tail
    $result .= substr($subject, $chunk_start);
    
    // return
    return $result;
    
}


/**
 * acfe_str_replace_first
 *
 * @param $search
 * @param $replace
 * @param $subject
 * @param $delete_others  bool Should delete the other occurrences of the search string
 *
 * @deprecated since 0.9.2.6
 *
 * @return array|mixed|string|string[]
 */
function acfe_str_replace_first($search, $replace, $subject, $delete_others = false){
    acfe_deprecated_function('acfe_str_replace_first()', '0.9.2.6', 'Use acfe_str_replace() with offset = 0 & limit = 1');
    return acfe_str_replace($subject, $search, $replace, 0, 1, $delete_others);
}