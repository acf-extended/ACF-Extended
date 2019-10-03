<?php

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE Location: Post Type All Choices
 */
add_filter('acf/location/rule_values/post_type', 'acfe_location_post_type_all_choices');
function acfe_location_post_type_all_choices($choices){
    
	$choices = array_merge(array('all' => __('All')), $choices);
    
    return $choices;
    
}

/**
 * ACFE Location: Post Type All Matching
 */
add_filter('acf/location/rule_match', 'acfe_location_post_type_all_match', 10, 3);
function acfe_location_post_type_all_match($match, $rule, $options){
    
    if($rule['param'] != 'post_type' || $rule['value'] != 'all')
        return $match;

    if($rule['operator'] == "=="){
        
        $match = false;
        
        $post_types = acf_get_post_types();
        if(isset($options['post_type']) && !empty($options['post_type']) && in_array($options['post_type'], $post_types))
            $match = true;
        
    }
    
    elseif($rule['operator'] == "!="){
        
        $match = !isset($options['post_type']) || empty($options['post_type']);
        
    }

    return $match;
    
}