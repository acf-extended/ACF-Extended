<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_button_group')):

class acfe_field_button_group extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'button_group';
        
    }
    
    
    /**
     * format_front_value
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     * @param $form
     *
     * @return string
     */
    function format_front_value($formatted, $unformatted, $post_id, $field, $form){
        
        // vars
        $value = acfe_as_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $v){
            $array[] = acfe_get($field['choices'], $v, $v);
        }
        
        // merge
        return implode(', ', $array);
        
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
        
        // vars
        $value = acfe_as_array($value);
        $choices = acfe_as_array($field['choices']);
        
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

acf_new_instance('acfe_field_button_group');

endif;