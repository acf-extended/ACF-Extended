<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_radio')):

class acfe_field_radio extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'radio';
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['other_choice'])){
            return $valid;
        }
        
        // vars
        $value = acf_get_array($value);
        $choices = acf_get_array($field['choices']);
        
        // empty choices
        if(empty($choices)){
            return false; // value is always invalid as there no choice is allowed
        }
        
        // check values against choices
        if(!empty(array_diff($value, array_keys($choices)))){
            return false;
        }
        
        // return
        return $valid;
        
    }
    
}

acf_new_instance('acfe_field_radio');

endif;