<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_button extends acf_field{
    
    function __construct(){

        $this->name = 'button';
        $this->label = __('Button', 'acfe');
        $this->category = 'basic';

        parent::__construct();

    }
      
    function render_field_settings($field){
        
        acf_render_field_setting($field, array(
            'label'         => __('Button value', 'acfe'),
            'instructions'  => __('Set a default value for the field', 'acfe'),
            'type'          => 'text',
            'name'          => 'button_value',
            'default_value' => __('Submit', 'acfe')
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Button attributes', 'acfe'),
            'instructions'  => __('Set button attributes', 'acfe'),
            'type'          => 'group',
            'name'          => 'button_attributes',
            'sub_fields'    => array(
                array(
                    'label'         => '',
                    'instructions'  => '',
                    'type'          => 'select',
                    'name'          => 'button_type',
                    '_name'         => 'button_type',
                    'key'           => 'button_type',
                    'required'      => false,
                    'default_value' => 'button',
                    'choices'       => array(
                        'button' => __('Button', 'acfe'),
                        'submit' => __('Submit', 'acfe'),
                    ),
                    'wrapper'       => array(
                        'width' => 33
                    )
                ),
                
                array(
                    'label'         => '',
                    'instructions'  => '',
                    'type'          => 'text',
                    'name'          => 'button_class',
                    '_name'         => 'button_class',
                    'key'           => 'button_class',
                    'required'      => false,
                    'prepend'       => 'class',
                    'wrapper'       => array(
                        'width' => 33
                    )
                ),
                
                array(
                    'label'         => '',
                    'instructions'  => '',
                    'type'          => 'text',
                    'name'          => 'button_id',
                    '_name'         => 'button_id',
                    'key'           => 'button_id',
                    'required'      => false,
                    'prepend'       => 'id',
                    'wrapper'       => array(
                        'width' => 33
                    )
                ),
                
                
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Button wrapper', 'acfe'),
            'instructions'  => __('Set button wrapper', 'acfe'),
            'type'          => 'group',
            'name'          => 'button_wrapper',
            'sub_fields'    => array(
                
                array(
                    'label'         => '',
                    'instructions'  => '',
                    'type'          => 'text',
                    'name'          => 'button_before',
                    '_name'         => 'button_before',
                    'key'           => 'button_before',
                    'required'      => false,
                    'prepend'       => __('Before'),
                    'wrapper'       => array(
                        'width' => 50
                    )
                ),
                
                array(
                    'label'         => '',
                    'instructions'  => '',
                    'type'          => 'text',
                    'name'          => 'button_after',
                    '_name'         => 'button_after',
                    'key'           => 'button_after',
                    'required'      => false,
                    'prepend'       => __('After'),
                    'wrapper'       => array(
                        'width' => 50
                    )
                ),
                
                
            )
        ));
        
    }
    
    function render_field($field){
        
        if(!empty($field['button_wrapper']['button_before'])){
            echo $field['button_wrapper']['button_before'];
        }
        
        echo '<input 
            type="' . esc_attr($field['button_attributes']['button_type']) . '" 
            name="' . esc_attr($field['name']) . '" 
            id="' . esc_attr($field['button_attributes']['button_id']) . '" 
            class="' . esc_attr($field['button_attributes']['button_class']) . '" 
            value="' . esc_attr($field['button_value']) . '"
            />';
            
        if(!empty($field['button_wrapper']['button_after'])){
            echo $field['button_wrapper']['button_after'];
        }
            
    }
    
}

new acfe_field_button();