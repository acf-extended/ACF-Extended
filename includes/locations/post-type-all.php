<?php

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE Location: Post Type All Choices
 */
add_filter('acf/location/rule_values/post_type', 'acfe_location_post_type_all_choices');
function acfe_location_post_type_all_choices($choices){
    
	$choices = array_merge(array('all' => __('All', 'acf')), $choices);
    
    return $choices;
    
}

/**
 * ACFE Location: Post Type All Matching
 */
add_filter('acf/location/rule_match/post_type', 'acfe_location_post_type_all_match', 10, 3);
function acfe_location_post_type_all_match($match, $rule, $options){
    
    if($rule['value'] !== 'all')
        return $match;
    
    if(!acf_maybe_get($options, 'post_type'))
        return $match;
    
    $post_types = acf_get_post_types();
    
    $match = in_array($options['post_type'], $post_types);
    
    if($rule['operator'] === '!=')
        $match = !$match;

    return $match;
    
}