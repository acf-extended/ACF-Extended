<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_choices_label')):

class acfe_field_choices_label{
    
    /**
     * construct
     */
    function __construct(){
        
        // instructions
        add_filter('acf/prepare_field/name=choices',                array($this, 'prepare_instructions'), 20);
    
        // filters
        add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_choices'), 20);
        add_filter('acf/prepare_field/type=checkbox',               array($this, 'prepare_choices'), 20);
        add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_choices'), 20);
    
        add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_radio'), 20);
        add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_radio'), 20);
        
    }
    
    
    /**
     * prepare_instructions
     *
     * ACF Admin choices insctructions
     */
    function prepare_instructions($field){
        
        // allowed types
        $field_types = array('radio', 'checkbox', 'select');
    
        // check field type
        if(in_array(acf_maybe_get($field['wrapper'], 'data-setting'), $field_types, true)){
            
            $text = "<br/><br/>" . __('You may use "## Title" to create a group of options.', 'acfe');
            $key = acf_maybe_get($field, 'hint') ? 'hint' : 'instructions'; // handle hint / instructions
            
            // add instructions
            $field[ $key ] .= $text;
            
        }
    
        return $field;
        
    }
    
    
    /**
     * prepare_choices
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_choices($field){
        
        // bail early if no choices
        if(empty($field['choices'])){
            return $field;
        }
        
        // transform choices array
        $field = $this->decode_choices_label($field);
        
        // get labels
        $labels = $this->get_labels($field['choices']);
        
        // assign data attribute
        if(!empty($labels)){
            $field['wrapper']['data-acfe-labels'] = json_encode($labels);
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_radio
     *
     * Radio fields need special treatment as they don't recognize nested arrays choices
     * This function flattens the choices array. Then labels are added in javascript
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_radio($field){
        
        if($field['type'] !== 'radio' && $field['field_type'] !== 'radio'){
            return $field;
        }
    
        if(empty($field['choices'])){
            return $field;
        }
    
        $choices = array();
        
        // loop choices to flatten
        foreach($field['choices'] as $key => $value){
        
            if(is_array($value)){
                $choices = $choices + $value;
            }else{
                $choices = $choices + array($key => $value);
            }
        
        }
        
        // assign
        $field['choices'] = $choices;
        
        return $field;
        
    }
    
    
    /**
     * decode_choices_label
     *
     * Transform choices with "##" into grouped choices
     * Example:
     *
     * 'choices' => array(
     *     '## Fruits',
     *     'apple' => 'Apple',
     *     'banana' => 'Banana',
     * )
     *
     * Becomes
     *
     * 'choices' => array(
     *     'Fruits' => array(
     *         'apple'  => 'Apple',
     *         'banana' => 'Banana',
     *     )
     * )
     *
     * @param $field
     *
     * @return mixed
     */
    function decode_choices_label($field){
        
        // bail early if no choices
        if(empty($field['choices']) || !is_array($field['choices'])){
            return $field;
        }
        
        // vars
        $last_label = false;
        $new_choices = array();
        
        // loop choices
        foreach($field['choices'] as $k => $choice){
            
            // only strings
            if(is_string($choice)){
                
                // sanitize choice
                $choice = trim($choice);
                
                // ## Group Label
                if(acfe_starts_with($choice, '##')){
                    
                    // get label
                    $label = substr($choice, 2);
                    $label = trim($label);
                    
                    // prepare new choices
                    $last_label = $label;
                    $new_choices[ $label ] = array();
                    
                    continue; // new row
                }
                
                // assign sub choice to last label
                if(!empty($last_label)){
                    $new_choices[ $last_label ][ $k ] = $choice;
                }
                
            }
            
        }
        
        // assign new choices
        if(!empty($new_choices)){
            $field['choices'] = $new_choices;
        }
        
        return $field;
        
    }
    
    
    /**
     * strip_choices_label
     *
     * Removes choices starting with "##"
     * This is mainly used on select2 ajax requests which doesn't handle grouped choices
     *
     * @param $field
     *
     * @return mixed
     */
    function strip_choices_label($field){
        
        // bail early if no choices
        if(empty($field['choices']) || !is_array($field['choices'])){
            return $field;
        }
        
        // loop choices
        foreach(array_keys($field['choices']) as $k){
            
            // get choice
            $choice = $field['choices'][ $k ];
            
            // only string
            if(is_string($choice) && acfe_starts_with($choice, '##')){
                unset($field['choices'][ $k ]);
            }
            
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * get_labels
     *
     * @param $choices
     * @param $depth
     * @param $labels
     *
     * @return array|mixed
     */
    function get_labels($choices = array(), $depth = 1, $labels = array()){
        
        // bail early if no choices
        if(empty($choices)){
            return $labels;
        }
        
        // loop choices
        foreach($choices as $key => $value){
            
            // label must be an array
            if(is_array($value)){
                
                // get the first array key
                reset($value);
                $first_key = key($value);
                
                if(!is_numeric($key)){
                    $labels = array_merge($labels, array($key => $first_key));
                }
                
                $labels = $this->get_labels($value, $depth+1, $labels);
                
            }
            
        }
        
        // return
        return $labels;
        
    }
    
}

acf_new_instance('acfe_field_choices_label');

endif;


/**
 * acfe_prepare_choices_label
 *
 * @param $field
 *
 * @return mixed
 */
function acfe_prepare_choices_label($field){
    
    // get instance
    $instance = acf_get_instance('acfe_field_choices_label');
    
    // perform actions
    $field = $instance->prepare_choices($field);
    $field = $instance->prepare_radio($field);
    
    // return
    return $field;
    
}


/**
 * acfe_decode_choices_label
 *
 * @param $field
 *
 * @return mixed
 */
function acfe_decode_choices_label($field){
    $instance = acf_get_instance('acfe_field_choices_label');
    return $instance->decode_choices_label($field);
}


/**
 * acfe_strip_choices_label
 *
 * @param $field
 *
 * @return mixed
 */
function acfe_strip_choices_label($field){
    $instance = acf_get_instance('acfe_field_choices_label');
    return $instance->strip_choices_label($field);
}


/**
 * acfe_prepare_checkbox_labels
 *
 * @param $field
 *
 * @deprecated since 0.9.2.2 use acfe_prepare_choices_label instead
 *
 * @return mixed
 */
function acfe_prepare_checkbox_labels($field){
    acfe_deprecated_function(__FUNCTION__, '0.9.2.2', 'acfe_prepare_choices_label()');
    return acfe_prepare_choices_label($field);
}