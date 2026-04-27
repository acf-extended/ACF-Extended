<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_post_type_list')):

class acfe_location_post_type_list extends acfe_location{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        $this->name     = 'post_type_list';
        $this->label    = __('Post Type List', 'acfe');
        $this->category = 'post';
        $this->after    = 'post_type_archive';
        
    }


    /**
     * rule_types
     *
     * @param $choices
     *
     * @return mixed
     */
    function rule_types($choices){
        
        $name = __('Post', 'acf');
        $choices[ $name ] = acfe_array_insert_after($choices[ $name ], 'post_type', 'post_type_list', __('Post Type List'));

        return $choices;
        
    }


    /**
     * rule_values
     *
     * @param $choices
     *
     * @return array
     */
    function rule_values($choices){
        
        $post_types = acf_get_post_types(array(
            'show_ui'    => 1,
            'exclude'    => array('attachment')
        ));
        
        $pretty_post_types = array();
        
        if(!empty($post_types)){
            $pretty_post_types = acf_get_pretty_post_types($post_types);
        }
        
        $choices = array('all' => __('All', 'acf'));
        $choices = array_merge($choices, $pretty_post_types);
        
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
        
        if(!acf_maybe_get($screen, 'post_type_list') || !acf_maybe_get($rule, 'value')){
            return $match;
        }
        
        $match = ($screen['post_type_list'] === $rule['value']);
        
        if($rule['value'] === 'all'){
            $match = true;
        }
        
        if($rule['operator'] === '!='){
            $match = !$match;
        }
        
        return $match;

    }
    
}

acf_register_location_rule('acfe_location_post_type_list');

endif;