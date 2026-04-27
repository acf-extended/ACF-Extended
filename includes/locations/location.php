<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_location
 */
if(!class_exists('acfe_location')):

class acfe_location extends acf_location{

    // vars
    public $after = '';
    
}

endif;


/**
 * acfe_location_rules
 */
if(!class_exists('acfe_location_rules')):

class acfe_location_rules{

    /**
     * construct
     */
    function __construct(){

        add_filter('acf/location/rule_types', array($this, 'location_rules_types'));

    }


    /**
     * location_rules_types
     *
     * @param $groups
     *
     * @return mixed
     */
    function location_rules_types($groups){

        // loop groups
        foreach($groups as $group => $locations){

            // loop locations
            foreach($locations as $location_name => $location_label){

                // get location type
                $location_type = acf_get_location_type($location_name);

                // validate location type and move using "after" prop
                if(!empty($location_type) && !empty($location_type->after) && isset($groups[ $group ][ $location_type->after ])){
                    $groups[ $group ] = acfe_array_insert_after($groups[ $group ], $location_type->after, $location_name, $location_label);
                }

            }


        }

        // return
        return $groups;

    }

}

new acfe_location_rules();

endif;


/**
 * acfe_compare_location_rule
 *
 * @param $value
 * @param $rule
 * @param $all_as_wildcard
 *
 * @return bool|int
 */
function acfe_compare_location_rule($value, $rule, $all_as_wildcard = true){
    
    if($rule['operator'] === '=='){
        
        if($rule['value'] === 'all' && $all_as_wildcard){
            return true;
        }
        
        return $value == $rule['value'];
        
    }elseif($rule['operator'] === '!='){
        
        if($rule['value'] === 'all' && $all_as_wildcard){
            return false;
        }
        
        return $value != $rule['value'];
        
    }elseif($rule['operator'] === '<'){
        return $value < $rule['value'];
        
    }elseif($rule['operator'] === '<='){
        return $value <= $rule['value'];
        
    }elseif($rule['operator'] === '>'){
        return $value > $rule['value'];
        
    }elseif($rule['operator'] === '>='){
        return $value >= $rule['value'];
        
    }elseif($rule['operator'] === 'contains'){
        return stripos($value, $rule['value']) !== false;
        
    }elseif($rule['operator'] === '!contains'){
        return stripos($value, $rule['value']) === false;
        
    }elseif($rule['operator'] === 'starts'){
        return stripos($value, $rule['value']) === 0;
        
    }elseif($rule['operator'] === '!starts'){
        return stripos($value, $rule['value']) !== 0;
        
    }elseif($rule['operator'] === 'ends'){
        return acfe_ends_with($value, $rule['value']);
        
    }elseif($rule['operator'] === '!ends'){
        return !acfe_ends_with($value, $rule['value']);
        
    }elseif($rule['operator'] === 'regex'){
        return preg_match('/' . $rule['value'] . '/', $value);
        
    }elseif($rule['operator'] === '!regex'){
        return !preg_match('/' . $rule['value'] . '/', $value);
        
    }elseif($rule['operator'] === '=count'){
        return count($value) == $rule['value'];
        
    }elseif($rule['operator'] === '!=count'){
        return count($value) != $rule['value'];
        
    }elseif($rule['operator'] === '>count'){
        return count($value) > $rule['value'];
        
    }elseif($rule['operator'] === '>=count'){
        return count($value) >= $rule['value'];
        
    }elseif($rule['operator'] === '<count'){
        return count($value) < $rule['value'];
        
    }elseif($rule['operator'] === '<=count'){
        return count($value) <= $rule['value'];
    }
    
    return false;
    
}