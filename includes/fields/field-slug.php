<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_slug')):

class acfe_field_slug extends acf_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_slug';
        $this->label = __('Slug', 'acfe');
        $this->category = 'basic';
        $this->defaults = array(
            'default_value' => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
            'maxlength'     => '',
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting($field, array(
            'label'         => __('Default Value','acf'),
            'instructions'  => __('Appears when creating a new post','acf'),
            'type'          => 'text',
            'name'          => 'default_value',
        ));
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'         => __('Placeholder Text','acf'),
            'instructions'  => __('Appears within the input','acf'),
            'type'          => 'text',
            'name'          => 'placeholder',
        ));
        
        // prepend
        acf_render_field_setting($field, array(
            'label'         => __('Prepend','acf'),
            'instructions'  => __('Appears before the input','acf'),
            'type'          => 'text',
            'name'          => 'prepend',
        ));
        
        // append
        acf_render_field_setting($field, array(
            'label'         => __('Append','acf'),
            'instructions'  => __('Appears after the input','acf'),
            'type'          => 'text',
            'name'          => 'append',
        ));
        
        // maxlength
        acf_render_field_setting($field, array(
            'label'         => __('Character Limit','acf'),
            'instructions'  => __('Leave blank for no limit','acf'),
            'type'          => 'number',
            'name'          => 'maxlength',
        ));
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // field type
        $field['type'] = 'text';
        
        // render as text field
        acf_get_field_type('text')->render_field($field);
        
    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return string
     */
    function validate_value($valid, $value, $field, $input){
        
        // sanitize
        $value = sanitize_title($value);
        
        // check max length
        if($field['maxlength'] && mb_strlen(wp_unslash($value)) > $field['maxlength']){
            return sprintf(__('Value must not exceed %d characters', 'acf'), $field['maxlength']);
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function update_value($value, $post_id, $field){
        return sanitize_title($value);
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function translate_field($field){
        
        $field['placeholder'] = acf_translate($field['placeholder']);
        $field['prepend'] = acf_translate($field['prepend']);
        $field['append'] = acf_translate($field['append']);
        
        return $field;
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_slug');

endif;