<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_checkbox')):

class acfe_field_checkbox{
    
    function __construct(){
    
        add_action('current_screen',                                array($this, 'current_screen'));
        
    }
    
    function current_screen(){
        
        // Field Group UI
        if(acfe_is_admin_screen()){
    
            add_filter('acf/prepare_field/name=choices', array($this, 'prepare_field_choices'), 5);
            
        }else{
    
            // Filters
            add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_checkbox'), 20);
            add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_checkbox'), 20);
            add_filter('acf/prepare_field/type=checkbox',               array($this, 'prepare_checkbox'), 20);
    
            add_filter('acf/prepare_field/type=radio',                  array($this, 'prepare_radio'), 20);
            add_filter('acf/prepare_field/type=acfe_taxonomy_terms',    array($this, 'prepare_radio'), 20);
    
            add_filter('acfe/field_wrapper_attributes/type=radio',      array($this, 'field_wrapper'), 10, 2);
            add_filter('acfe/field_wrapper_attributes/type=checkbox',   array($this, 'field_wrapper'), 10, 2);
            
        }
        
        
        
    }
    
    function prepare_field_choices($field){
        
        $wrapper = $field['wrapper'];
        
        if(acf_maybe_get($wrapper, 'data-setting') !== 'radio' && acf_maybe_get($wrapper, 'data-setting') !== 'checkbox')
            return $field;
        
        $field['instructions'] .= '<br/><br/>You may use "## Title" to create a group of options.';
        
        return $field;
        
    }
    
    function prepare_checkbox($field){
    
        if(empty($field['choices']))
            return $field;
        
        // Map '## Group'
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
        $labels = $this->walk($field['choices']);
    
        if(!empty($labels)){
        
            $field['acfe_labels'] = $labels;
        
        }
        
        return $field;
        
    }
    
    function walk($choices = array(), $depth = 1, $labels = array()){
        
        // bail early if no choices
        if(empty($choices))
            return $labels;
        
        foreach($choices as $value => $label){
            
            // optgroup
            if(is_array($label)){
    
                reset($label);
                $key = key($label);
                
                if(!is_numeric($value))
                    $labels = array_merge($labels, array($value => $key));
                
                $labels = $this->walk($label, $depth+1, $labels);
                
            }
            
        }
        
        return $labels;
        
    }
    
    function prepare_radio($field){
    
        if($field['type'] !== 'radio' && $field['field_type'] !== 'radio')
            return $field;
    
        if(empty($field['choices']))
            return $field;
    
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
    
    function field_wrapper($wrapper, $field){
        
        $labels = acf_maybe_get($field, 'acfe_labels');
        
        if(empty($labels))
            return $wrapper;
        
        $wrapper['data-acfe-labels'] = $labels;
        
        return $wrapper;
        
    }
    
}

new acfe_field_checkbox();

endif;