<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_hidden')):

class acfe_field_hidden extends acf_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_hidden';
        $this->label = __('Hidden', 'acfe');
        $this->category = 'basic';
        $this->defaults = array(
            'default_value' => ''
        );
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return array
     */
    function prepare_field($field){
        
        $field['wrapper']['class'] = 'acf-hidden';
        
        return $field;
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
    
        acf_hidden_input(array(
            'name'  => $field['name'],
            'value' => $field['value'],
        ));
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting($field, array(
            'label'         => __('Value', 'acf'),
            'instructions'  => __('Default value in the hidden input', 'acf'),
            'type'          => 'text',
            'name'          => 'default_value',
        ));
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_hidden');

endif;