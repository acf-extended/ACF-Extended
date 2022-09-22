<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_repeater')):

class acfe_field_repeater extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'repeater';
        $this->defaults = array(
            'acfe_repeater_stylised_button' => 0,
        );
        
        $this->add_field_action('acf/render_field_settings', array($this, '_render_field_settings'), 0);
        
    }
    
    
    /**
     * _render_field_settings
     *
     * acf/render_field_settings:0
     *
     * @param $field
     */
    function _render_field_settings($field){
    
        // stylised button
        acf_render_field_setting($field, array(
            'label'         => __('Stylised Button', 'acfe'),
            'name'          => 'acfe_repeater_stylised_button',
            'key'           => 'acfe_repeater_stylised_button',
            'instructions'  => __('Better row button integration'),
            'type'          => 'true_false',
            'message'       => '',
            'default_value' => false,
            'ui'            => true,
        ));
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function field_wrapper_attributes($wrapper, $field){
    
        // stylised button
        if($field['acfe_repeater_stylised_button']){
            $wrapper['data-acfe-repeater-stylised-button'] = 1;
        }
    
        // lock sortable
        $lock = false;
        $lock = apply_filters("acfe/repeater/lock",                        $lock, $field);
        $lock = apply_filters("acfe/repeater/lock/name={$field['_name']}", $lock, $field);
        $lock = apply_filters("acfe/repeater/lock/key={$field['key']}",    $lock, $field);
    
        if($lock){
            $wrapper['data-acfe-repeater-lock'] = 1;
        }
    
        // remove actions
        $remove = false;
        $remove = apply_filters("acfe/repeater/remove_actions",                        $remove, $field);
        $remove = apply_filters("acfe/repeater/remove_actions/name={$field['_name']}", $remove, $field);
        $remove = apply_filters("acfe/repeater/remove_actions/key={$field['key']}",    $remove, $field);
    
        if($remove){
            $wrapper['data-acfe-repeater-remove-actions'] = 1;
        }
        
        // return
        return $wrapper;
        
    }
    
}

acf_new_instance('acfe_field_repeater');

endif;