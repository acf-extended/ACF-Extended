<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_filter('acf/validate_value/name=acfe_form_custom_action', array($this, 'validate'), 10, 4);
        
    }
    
    function validate($valid, $value, $field, $input){
        
        if(!$valid)
            return $valid;
        
        $reserved = array(
            'custom',
            'email',
            'post',
            'term',
        );
        
        if(in_array($value, $reserved))
            $valid = 'This action name is not authorized';
        
        return $valid;
    }
    
}

new acfe_form_custom();

endif;