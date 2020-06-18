<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_required')):

class acfe_field_required{
    
    function __construct(){
    
        add_action('acf/render_field_settings', array($this, 'field_settings'), 1000);
    
        add_filter('acf/validate_value',        array($this, 'validate_value'), 10, 4);
        
    }
    
    function field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'			=> __('Required Message','acf'),
            'instructions'	=> 'Include <code>%s</code> to print the field label',
            'type'			=> 'text',
            'name'			=> 'required_message',
            'placeholder'   => sprintf(__( '%s value is required', 'acf' ), $field['label']),
            'conditional_logic'	=> array(
                'field'		=> 'required',
                'operator'	=> '==',
                'value'		=> 1
            ),
            'wrapper'       => array(
                'data-after' => 'required'
            )
        ));
        
    }
    
    function validate_value($valid, $value, $field, $input){
        
        $required_message = acf_maybe_get($field, 'required_message');
        
        if(!$required_message)
            return $valid;
        
        if($field['required']){
        
            // valid is set to false if the value is empty, but allow 0 as a valid value
            if(empty($value) && !is_numeric($value)){
            
                $valid = sprintf(__($required_message, 'acf'), $field['label']);
            
            }
        
        }
        
        // return
        return $valid;
        
    }
    
}

new acfe_field_required();

endif;