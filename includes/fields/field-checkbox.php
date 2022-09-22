<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_checkbox')):

class acfe_field_checkbox{
    
    /**
     * construct
     */
    function __construct(){
        
        // instructions
        add_filter('acf/prepare_field/name=choices',                array($this, 'prepare_instructions'), 20);
    
        // Filters
        add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_choices'), 20);
        add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_choices'), 20);
        add_filter('acf/prepare_field/type=checkbox',               array($this, 'prepare_choices'), 20);
    
        add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_radio'), 20);
        add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_radio'), 20);
        
    }
    
    
    /**
     * prepare_instructions
     */
    function prepare_instructions($field){
    
        // check setting
        if(acf_maybe_get($field['wrapper'], 'data-setting') === 'radio' || acf_maybe_get($field['wrapper'], 'data-setting') === 'checkbox' || acf_maybe_get($field['wrapper'], 'data-setting') === 'select'){
            
            $text = "<br/><br/>" . __('You may use "## Title" to create a group of options.', 'acfe');
            
            if(acf_maybe_get($field, 'hint')){
                $field['hint'] .= $text;
            }else{
                $field['instructions'] .= $text;
            }
            
            
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
        
        // map '## group'
        if(is_array($field['choices'])){
        
            $found = false;
            $found_array = array();
        
            foreach($field['choices'] as $k => $choice){
            
                if(is_string($choice)){
                
                    $choice = trim($choice);
                    
                    if(strpos($choice, '##') === 0){
                    
                        $choice = substr($choice, 2);
                        $choice = trim($choice);
                    
                        $found = $choice;
                        $found_array[$choice] = array();
                    
                    }elseif(!empty($found)){
                    
                        $found_array[$found][$k] = $choice;
                    
                    }
                
                }
            
            }
        
            if(!empty($found_array)){
                $field['choices'] = $found_array;
            }
        
        }
        
        // Labels
        $labels = $this->walk_choices($field['choices']);
    
        if(!empty($labels)){
            $field['wrapper']['data-acfe-labels'] = json_encode($labels);
        }
        
        return $field;
        
    }
    
    
    /**
     * walk_choices
     *
     * @param $choices
     * @param $depth
     * @param $labels
     *
     * @return array|mixed
     */
    function walk_choices($choices = array(), $depth = 1, $labels = array()){
        
        // bail early if no choices
        if(empty($choices)){
            return $labels;
        }
        
        foreach($choices as $value => $label){
            
            // bail early if not array
            if(!is_array($label)) continue;
    
            reset($label);
            $key = key($label);
            
            if(!is_numeric($value)){
                $labels = array_merge($labels, array($value => $key));
            }
            
            $labels = $this->walk_choices($label, $depth+1, $labels);
            
        }
        
        return $labels;
        
    }
    
    
    /**
     * prepare_radio
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
    
        foreach($field['choices'] as $value => $label){
        
            if(is_array($label)){
                $choices = $choices + $label;
            }else{
                $choices = $choices + array($value => $label);
            }
        
        }
    
        $field['choices'] = $choices;
        
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_checkbox');

endif;


/**
 * acfe_prepare_checkbox_labels
 *
 * @param $field
 *
 * @return mixed
 */
function acfe_prepare_checkbox_labels($field){
    
    $instance = acf_get_instance('acfe_field_checkbox');
    
    $field = $instance->prepare_choices($field);
    $field = $instance->prepare_radio($field);
    
    return $field;
    
}