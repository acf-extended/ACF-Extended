<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_actions_title')):

class acfe_field_flexible_content_actions_title{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acfe/flexible/wrapper_attributes',          array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/load_fields',                 array($this, 'load_fields'), 10, 2);
        add_filter('acfe/flexible/prepare_layout',              array($this, 'prepare_layout'), 10, 5);
        add_filter('acfe/flexible/layouts/div',                 array($this, 'layout_div'), 10, 6);
        
        add_filter('acf/fields/flexible_content/layout_title',  array($this, 'layout_title'), 5, 4);
        add_filter('acf/fields/flexible_content/layout_attrs',  array($this, 'layout_attrs'), 5, 4);
        
    }
    
    /**
     * wrapper_attributes
     *
     * acfe/flexible/wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function wrapper_attributes($wrapper, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $wrapper;
        }
        
        if(in_array('title', $field['acfe_flexible_add_actions'])){
            $wrapper['data-acfe-flexible-title-edition'] = 1;
        }
        
        return $wrapper;
        
    }
    
    /**
     * load_fields
     *
     * acfe/flexible/load_fields
     *
     * @param $fields
     * @param $field
     *
     * @return mixed
     */
    function load_fields($fields, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $fields;
        }
        
        // check setting
        if(!in_array('title', $field['acfe_flexible_add_actions'])){
            return $fields;
        }
        
        // loop layouts
        foreach($field['layouts'] as $i => $layout){
            
            // Vars
            $key = "field_{$layout['key']}_title";
            $name = 'acfe_flexible_layout_title';
            $label = $layout['label'];
            
            // Add local
            acf_add_local_field(array(
                'label'                 => false,
                'key'                   => $key,
                'name'                  => $name,
                'type'                  => 'text',
                'required'              => false,
                'maxlength'             => false,
                'default_value'         => $label,
                'placeholder'           => $label,
                'parent_layout'         => $layout['key'],
                'parent'                => $field['key']
            ));
            
            // Add sub field
            array_unshift($fields, acf_get_field($key));
            
        }
        
        return $fields;
        
    }
    
    
    /**
     * prepare_layout
     *
     * acfe/flexible/prepare_layout
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed
     */
    function prepare_layout($layout, $field, $i, $value, $prefix){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $layout;
        }
        
        if(empty($layout['sub_fields'])){
            return $layout;
        }
        
        // check setting
        if(!in_array('title', $field['acfe_flexible_add_actions'])){
            return $layout;
        }
        
        // extract title sub field
        $sub_field = acfe_extract_sub_field($layout, 'acfe_flexible_layout_title', $value);
        
        if($sub_field){
            
            // update prefix to allow for nested values
            $sub_field['prefix'] = $prefix;
            $sub_field['class'] = 'acfe-flexible-control-title';
            $sub_field['data-acfe-flexible-control-title-input'] = 1;
            
            $sub_field = acf_validate_field($sub_field);
            $sub_field = acf_prepare_field($sub_field);
            
            $input_attrs = array();
            foreach(array('type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern', 'readonly', 'disabled', 'required', 'data-acfe-flexible-control-title-input') as $k){
                
                if(isset($sub_field[$k])){
                    $input_attrs[$k] = $sub_field[$k];
                }
                
            }
            
            // render input
            echo acf_get_text_input(acf_filter_attrs($input_attrs));
            
        }
        
        return $layout;
        
    }
    
    
    /**
     * layout_div
     *
     * @param $div
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layout_div($div, $layout, $field, $i, $value, $prefix){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $div;
        }
        
        // remove data-renamed="0|1"
        acfe_unset($div, 'data-renamed');
        
        // return
        return $div;
        
    }
    
    
    /**
     * layout_title
     *
     * acf/fields/flexible_content/layout_title
     *
     * @param $title
     * @param $field
     * @param $layout
     * @param $i
     *
     * @return mixed
     */
    function layout_title($title, $field, $layout, $i){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $title;
        }
        
        if(!in_array('title', $field['acfe_flexible_add_actions'])){
            return $title;
        }
        
        // get edited title
        $value = get_sub_field('acfe_flexible_layout_title');
        if(!empty($value)){
            $title = wp_unslash($value);
        }
        
        return $title;
        
    }
    
    
    /**
     * layout_attrs
     *
     * acf/fields/flexible_content/layout_attrs
     *
     * @param $attrs
     * @param $field
     * @param $layout
     * @param $i
     *
     * @return mixed
     */
    function layout_attrs($attrs, $field, $layout, $i){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $attrs;
        }
        
        // check setting
        if(!in_array('title', $field['acfe_flexible_add_actions'])){
            return $attrs;
        }
    
        $attrs['class'] .= ' acf-js-tooltip';
        $attrs['title'] = __('Layout', 'acfe') . ': ' . esc_attr(strip_tags($layout['label']));
        
        return $attrs;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_actions_title');

endif;