<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_group_instruction_placement')):

class acfe_field_group_instruction_placement{
    
    function __construct(){
        
        // Field Group
        add_action('acf/field_group/admin_head',    array($this, 'admin_head'));
        add_filter('acf/validate_field_group',      array($this, 'validate_tooltip_compatibility'), 20);
        
    }
    
    function admin_head(){
        
        add_filter('acf/prepare_field/name=instruction_placement',  array($this, 'prepare_instruction_placement'));
        
    }
    
    /*
     * Instruction Placement: Settings
     */
    function prepare_instruction_placement($field){
    
        $field['choices'] = array_merge($field['choices'], array(
            'above_field' => 'Above fields',
            'tooltip' => 'Tooltip'
        ));
    
        return $field;
        
    }
    
    /*
     * Instruction Placement: Tooltip 0.8.7.5 Compatibility
     */
    function validate_tooltip_compatibility($field_group){
        
        if(acf_maybe_get($field_group, 'instruction_placement') !== 'acfe_instructions_tooltip')
            return $field_group;
        
        $field_group['instruction_placement'] = 'tooltip';
        
        return $field_group;
        
    }
    
}

// initialize
new acfe_field_group_instruction_placement();

endif;