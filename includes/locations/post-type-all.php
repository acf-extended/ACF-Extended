<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_post_type_all')):

class acfe_location_post_type_all{
    
    /**
     * construct
     */
    function __construct(){
    
        add_filter('acf/location/rule_values/post_type',    array($this, 'rule_values'));
        add_filter('acf/location/rule_match/post_type',     array($this, 'rule_match'), 10, 3);
        
    }
    
    
    /**
     * rule_values
     *
     * @param $choices
     *
     * @return string[]|void[]
     */
    function rule_values($choices){
        
        $choices = array_merge(array('all' => __('All', 'acf')), $choices);
        
        return $choices;
        
    }
    
    
    /**
     * rule_match
     *
     * @param $match
     * @param $rule
     * @param $options
     *
     * @return bool|mixed
     */
    function rule_match($match, $rule, $options){
        
        // rule value might be empty
        // in case a Field Group use a custom location type from third party plugin
        // if the third party plugin is disabled, acf will fallback to "Post Type == ''"
        // and pass thru this rule because it's the first one
        if(empty($rule['value'])){
            return $match;
        }
        
        if($rule['value'] !== 'all'){
            return $match;
        }
        
        if(!acf_maybe_get($options, 'post_type')){
            return $match;
        }
        
        $post_types = acf_get_post_types();
        
        $match = in_array($options['post_type'], $post_types);
        
        if($rule['operator'] === '!='){
            $match = !$match;
        }
        
        return $match;
        
    }
    
}

new acfe_location_post_type_all();

endif;