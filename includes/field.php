<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field')):

class acfe_field extends acf_field{
    
    /**
     * construct
     */
    function __construct(){
    
        // parent construct
        parent::__construct();
        
        // custom filters
        $this->add_field_filter('acfe/form/validate_value',      array($this, 'validate_front_value'),     10, 5);
        $this->add_field_filter('acfe/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2);
        $this->add_field_filter('acfe/load_fields',              array($this, 'load_fields'),              10, 2);

    }
    
    
    /**
     * pre_validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $form
     *
     * @return mixed|null
     */
    function pre_validate_front_value($valid, $value, $field, $form){
        
        // already invalid
        if(!$valid || (is_string($valid) && !empty($valid))){
            return false;
        }
        
        // empty value
        if(empty($value)){
            return false;
        }
        
        // default validation
        $validate = true;
        
        // variations
        $validate = apply_filters("acfe/form/pre_validate_value/form={$form['name']}",   $validate, $field, $form);
        $validate = apply_filters("acfe/form/pre_validate_value/type={$field['type']}",  $validate, $field, $form);
        $validate = apply_filters("acfe/form/pre_validate_value/name={$field['_name']}", $validate, $field, $form);
        $validate = apply_filters("acfe/form/pre_validate_value/key={$field['key']}",    $validate, $field, $form);
        
        // return
        return $validate;
        
    }
    
}

endif;