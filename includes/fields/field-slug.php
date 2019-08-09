<?php

if(!defined('ABSPATH'))
    exit;

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
    
    function render_field( $field ) {
        
        // vars
        $atts = array();
        $keys = array('type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern');
        $keys2 = array('readonly', 'disabled', 'required');
        $html = '';
        
        // prepend
        if($field['prepend'] !== ''){
            $field['class'] .= ' acf-is-prepended';
            $html .= '<div class="acf-input-prepend">' . acf_esc_html($field['prepend']) . '</div>';
        }
        
        // append
        if($field['append'] !== ''){
            $field['class'] .= ' acf-is-appended';
            $html .= '<div class="acf-input-append">' . acf_esc_html($field['append']) . '</div>';
        }
        
        // atts (value="123")
        foreach($keys as $k){
            if(isset($field[ $k ])) 
                $atts[ $k ] = $field[ $k ];
        }
        
        // atts2 (disabled="disabled")
        foreach($keys2 as $k ){
            if(!empty($field[ $k ]))
                $atts[ $k ] = $k;
        }
        
        // remove empty atts
        $atts = acf_clean_atts($atts);
        
        // override type
        $atts['type'] = 'text';
        
        // render
        $html .= '<div class="acf-input-wrap">' . acf_get_text_input($atts) . '</div>';
        
        
        // return
        echo $html;
        
    }
    
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting( $field, array(
            'label'			=> __('Default Value','acf'),
            'instructions'	=> __('Appears when creating a new post','acf'),
            'type'			=> 'text',
            'name'			=> 'default_value',
        ));
        
        
        // placeholder
        acf_render_field_setting( $field, array(
            'label'			=> __('Placeholder Text','acf'),
            'instructions'	=> __('Appears within the input','acf'),
            'type'			=> 'text',
            'name'			=> 'placeholder',
        ));
        
        
        // prepend
        acf_render_field_setting( $field, array(
            'label'			=> __('Prepend','acf'),
            'instructions'	=> __('Appears before the input','acf'),
            'type'			=> 'text',
            'name'			=> 'prepend',
        ));
        
        
        // append
        acf_render_field_setting( $field, array(
            'label'			=> __('Append','acf'),
            'instructions'	=> __('Appears after the input','acf'),
            'type'			=> 'text',
            'name'			=> 'append',
        ));
        
        
        // maxlength
        acf_render_field_setting( $field, array(
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

new acfe_field_slug();