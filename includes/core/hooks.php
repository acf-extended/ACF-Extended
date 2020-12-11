<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_hooks')):

class acfe_hooks{
    
    public $field_group;
    
    function __construct(){
        
        // Field Groups
        add_filter('acf/load_field_groups',                         array($this, 'load_field_groups'), 100);
        add_filter('acf/pre_render_fields',                         array($this, 'pre_render_fields'), 10, 2);
        add_action('acf/render_fields',                             array($this, 'render_fields'), 10, 2);
        
        acf_add_filter_variations('acfe/prepare_field_group',       array('ID', 'key'), 0);
        acf_add_action_variations('acfe/pre_render_field_group',    array('ID', 'key'), 0);
        acf_add_action_variations('acfe/render_field_group',        array('ID', 'key'), 0);
        
        // Fields
        add_filter('acf/field_wrapper_attributes',                  array($this, 'field_wrapper_attributes'), 10, 2);
        add_filter('acf/load_fields',                               array($this, 'load_fields'), 10, 2);
        add_filter('acf/load_field',                                array($this, 'load_field'));
        
        acf_add_filter_variations('acfe/field_wrapper_attributes',  array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/load_fields',               array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/load_field',                array('type', 'name', 'key'), 0);
        acf_add_filter_variations('acfe/load_field_front',          array('type', 'name', 'key'), 0);
        acf_add_filter_variations('acfe/load_field_admin',          array('type', 'name', 'key'), 0);
        
    }
    
    /*
     * Load Field Groups
     */
    function load_field_groups($field_groups){
    
        if(acfe_is_admin_screen())
            return $field_groups;
        
        foreach($field_groups as $i => &$field_group){
    
            $field_group = apply_filters('acfe/prepare_field_group', $field_group);
            
            // Do not render if false
            if($field_group === false){
                
                unset($field_groups[$i]);
                
            }
        
        }
    
        return $field_groups;
        
    }
    
    /*
     * Pre Render Fields
     */
    function pre_render_fields($fields, $post_id){
        
        $this->field_group = array();
        
        if(!isset($fields[0]))
            return $fields;
        
        if(!acf_maybe_get($fields[0], 'parent'))
            return $fields;
        
        $field_group = acf_get_field_group($fields[0]['parent']);
        
        if(!$field_group)
            return $fields;
        
        $this->field_group = $field_group;
        
        do_action('acfe/pre_render_field_group', $field_group, $fields, $post_id);
        
        return $fields;
        
    }
    
    /*
     * Pre Render Fields
     */
    function render_fields($fields, $post_id){
        
        if(empty($this->field_group))
            return;
        
        do_action('acfe/render_field_group', $this->field_group, $fields, $post_id);
        
    }
    
    /*
     *  Field Wrapper Attributes
     */
    function field_wrapper_attributes($wrapper, $field){
        
        $wrapper = apply_filters('acfe/field_wrapper_attributes', $wrapper, $field);
        
        return $wrapper;
        
    }
    
    /*
     *  Load Fields
     */
    function load_fields($fields, $parent){
        
        // check if field (fitler is also called on field groups)
        if(!acf_maybe_get($parent, 'type'))
            return $fields;
        
        $fields = apply_filters('acfe/load_fields', $fields, $parent);
        
        return $fields;
        
    }
    
    /*
     *  Load Field
     */
    function load_field($field){
        
        if(acfe_is_admin_screen())
            return $field;
        
        // Everywhere
        $field = apply_filters('acfe/load_field', $field);
        
        // Admin
        if(acfe_form_is_admin()){
            
            $field = apply_filters('acfe/load_field_admin', $field);
            
        }
        
        // Front
        elseif(acfe_form_is_front()){
            
            $field = apply_filters('acfe/load_field_front', $field);
            
        }
        
        return $field;
        
    }
    
}

new acfe_hooks();

endif;