<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_slug')):

class acfe_field_slug extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_slug';
        $this->label = __('Slug', 'acfe');
        $this->category = 'basic';
        $this->defaults = array(
            'default_value'	=> '',
            'maxlength'		=> '',
            'placeholder'	=> '',
            'prepend'		=> '',
            'append'		=> ''
        );
        
        parent::__construct();
        
    }
    
    function render_field($field){
        
        $field['type'] = 'text';
        
        acf_get_field_type('text')->render_field($field);
        
    }
    
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting($field, array(
            'label'			=> __('Default Value','acf'),
            'instructions'	=> __('Appears when creating a new post','acf'),
            'type'			=> 'text',
            'name'			=> 'default_value',
        ));
        
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'			=> __('Placeholder Text','acf'),
            'instructions'	=> __('Appears within the input','acf'),
            'type'			=> 'text',
            'name'			=> 'placeholder',
        ));
        
        
        // prepend
        acf_render_field_setting($field, array(
            'label'			=> __('Prepend','acf'),
            'instructions'	=> __('Appears before the input','acf'),
            'type'			=> 'text',
            'name'			=> 'prepend',
        ));
        
        
        // append
        acf_render_field_setting($field, array(
            'label'			=> __('Append','acf'),
            'instructions'	=> __('Appears after the input','acf'),
            'type'			=> 'text',
            'name'			=> 'append',
        ));
        
        
        // maxlength
        acf_render_field_setting($field, array(
            'label'			=> __('Character Limit','acf'),
            'instructions'	=> __('Leave blank for no limit','acf'),
            'type'			=> 'number',
            'name'			=> 'maxlength',
        ));
        
    }
    
    function validate_value($valid, $value, $field, $input){
        
        $value = sanitize_title($value);
        
        if($field['maxlength'] && mb_strlen(wp_unslash($value)) > $field['maxlength'])
            return sprintf(__('Value must not exceed %d characters', 'acf'), $field['maxlength']);
        
        return $valid;
        
    }
    
    function update_value($value, $post_id, $field){
        
        return sanitize_title($value);
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_slug');

endif;