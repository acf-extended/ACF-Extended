<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_post_type_archive')):

class acfe_location_post_type_archive extends acfe_location{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        $this->name     = 'post_type_archive';
        $this->label    = __('Post Type Archive', 'acfe');
        $this->category = 'post';
        $this->after    = 'post_type';
        
    }
    
    
    /**
     * rule_values
     *
     * @param $choices
     *
     * @return array
     */
    function rule_values($choices){
        
        // get post types archives
        $post_types = acf_get_post_types(array(
            'acfe_admin_archive' => true
        ));
        
        // append pretty post types
        $pretty_post_types = array();
        
        if(!empty($post_types)){
            $pretty_post_types = acf_get_pretty_post_types($post_types);
        }
        
        // merge choices
        $choices = array('all' => __('All', 'acf'));
        $choices = array_merge($choices, $pretty_post_types);
        
        // return choices
        return $choices;
        
    }
    
    
    /**
     * rule_match
     *
     * @param $result
     * @param $rule
     * @param $screen
     *
     * $rule = array(
     *     'param'    => 'post_type_archive',
     *     'operator' => '==',
     *     'value'    => 'my-post-type',
     * )
     *
     * $screen = array(
     *     'lang'                   => false,
     *     'ajax'                   => false,
     *     'options_page'           => 'my-post-type-archive',
     *     'acfe_post_type_archive' => true,
     * )
     *
     * @return bool
     */
    function rule_match($result, $rule, $screen){
        
        // validate screen & rule
        if(!acfe_get($screen, 'options_page') || !acfe_get($screen, 'acfe_post_type_archive') || !acfe_get($rule, 'value')){
            return $result;
        }
        
        // prepare compare
        $value = $screen['options_page'];
        $rule['value'] = "{$rule['value']}-archive";
        
        // compare $value with $rule['value']
        return $this->compare($value, $rule);

    }
    
}

acf_register_location_rule('acfe_location_post_type_archive');

endif;