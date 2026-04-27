<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_taxonomy_list')):

class acfe_location_taxonomy_list extends acfe_location{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        $this->name     = 'taxonomy_list';
        $this->label    = __('Taxonomy List', 'acfe');
        $this->category = 'forms';
        $this->after    = 'taxonomy';
        
    }


    /**
     * rule_values
     *
     * @param $choices
     *
     * @return array
     */
    function rule_values($choices){
        
        $choices = array('all' => __('All', 'acf'));
        $choices = array_merge($choices, acf_get_taxonomy_labels());
        
        return $choices;
        
    }


    /**
     * rule_match
     *
     * @param $match
     * @param $rule
     * @param $screen
     *
     * @return bool
     */
    function rule_match($match, $rule, $screen){
        
        if(!acf_maybe_get($screen, 'taxonomy_list') || !acf_maybe_get($rule, 'value')){
            return $match;
        }
        
        $match = ($screen['taxonomy_list'] === $rule['value']);
        
        if($rule['value'] === 'all'){
            $match = true;
        }
        
        if($rule['operator'] === '!='){
            $match = !$match;
        }
        
        return $match;

    }
    
}

acf_register_location_rule('acfe_location_taxonomy_list');

endif;