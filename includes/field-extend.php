<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_extend')):

class acfe_field_extend{
    
    var $name     = '',
        $replace  = array(),
        $defaults = array(),
        $instance = '';
    
    /**
     * construct
     */
    function __construct(){
        
        // initialize
        $this->initialize();
    
        // field instance
        $this->instance = $this->get_field_type();
        
        // defaults
        if($this->defaults){
            $this->instance->defaults = array_merge($this->instance->defaults, $this->defaults);
        }
        
        // field actions
        $actions = array(
    
            // value
            array('filter', 'acf/load_value',                array($this, 'load_value'),               10, 3),
            array('filter', 'acf/update_value',              array($this, 'update_value'),             10, 3),
            array('filter', 'acf/format_value',              array($this, 'format_value'),             10, 3),
            array('filter', 'acf/validate_value',            array($this, 'validate_value'),           10, 4),
            array('action', 'acf/delete_value',              array($this, 'delete_value'),             10, 3),
    
            // field
            array('filter', 'acf/validate_rest_value',       array($this, 'validate_rest_value'),      10, 3),
            array('filter', 'acf/validate_field',            array($this, 'validate_field'),           10, 1),
            array('filter', 'acf/load_field',                array($this, 'load_field'),               10, 1),
            array('filter', 'acf/update_field',              array($this, 'update_field'),             10, 1),
            array('filter', 'acf/duplicate_field',           array($this, 'duplicate_field'),          10, 1),
            array('action', 'acf/delete_field',              array($this, 'delete_field'),             10, 1),
            array('action', 'acf/render_field',              array($this, 'render_field'),             9, 1),
            array('action', 'acf/render_field_settings',     array($this, 'render_field_settings'),    9, 1),
            array('filter', 'acf/prepare_field',             array($this, 'prepare_field'),            10, 1),
            array('filter', 'acf/translate_field',           array($this, 'translate_field'),          10, 1),
            
            // acfe
            array('filter', 'acfe/form/validate_value',      array($this, 'validate_front_value'),     10, 5),
            array('filter', 'acfe/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2),
            array('filter', 'acfe/load_fields',              array($this, 'load_fields'),              10, 2),
        );
        
        // loop
        foreach($actions as $row){
            
            // vars
            list($type, $hook, $function, $priority, $args) = $row;
            
            // get method
            $method = $type === 'filter' ? 'add_field_filter' : 'add_field_action';
            
            // use replace method
            if(in_array($function[1], $this->replace)){
                $method = $type === 'filter' ? 'replace_field_filter' : 'replace_field_action';
            }
            
            // call method
            $this->{$method}($hook, $function, $priority, $args);
            
        }
        
        // input actions
        $this->add_action('acf/input/admin_enqueue_scripts',         array($this, 'input_admin_enqueue_scripts'),       10, 0);
        $this->add_action('acf/input/admin_head',                    array($this, 'input_admin_head'),                  10, 0);
        $this->add_action('acf/input/form_data',                     array($this, 'input_form_data'),                   10, 1);
        $this->add_filter('acf/input/admin_l10n',                    array($this, 'input_admin_l10n'),                  10, 1);
        $this->add_action('acf/input/admin_footer',                  array($this, 'input_admin_footer'),                10, 1);
        
        // field group actions
        $this->add_action('acf/field_group/admin_enqueue_scripts',   array($this, 'field_group_admin_enqueue_scripts'), 10, 0);
        $this->add_action('acf/field_group/admin_head',              array($this, 'field_group_admin_head'),            10, 0);
        $this->add_action('acf/field_group/admin_footer',            array($this, 'field_group_admin_footer'),          10, 0);
        
    }
    
    
    /**
     * initialize
     */
    function initialize(){
        // ...
    }
    
    
    /**
     * get_field_type
     *
     * @return mixed
     */
    function get_field_type(){
        return acf_get_field_type($this->name);
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
    
    
    /**
     * add_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        // bail early if no callable
        if(!is_callable($function_to_add)){
            return;
        }
        
        // add
        add_filter($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * remove_filter
     *
     * @param $tag
     * @param $function_to_remove
     * @param $priority
     */
    function remove_filter($tag = '', $function_to_remove = '', $priority = 10){
        
        // bail early if no callable
        if(!is_callable($function_to_remove)){
            return;
        }
        
        // remove
        remove_filter($tag, $function_to_remove, $priority);
        
    }
    
    
    /**
     * replace_filter
     *
     * @param $tag
     * @param $function_to_replace
     * @param $priority
     * @param $accepted_args
     */
    function replace_filter($tag = '', $function_to_replace = '', $priority = 10, $accepted_args = 1){
        
        // check instance
        if(!$this->instance){
            $this->instance = $this->get_field_type();
        }
    
        // array
        if(is_array($function_to_replace)){
            $function_to_remove = array($this->instance, $function_to_replace[1]);
            $function_to_add = $function_to_replace;
        
        // string
        }else{
            $function_to_remove = array($this->instance, $function_to_replace);
            $function_to_add = array($this, $function_to_replace);
        
        }
    
        // bail early if no callable
        if(!is_callable($function_to_add)){
            return;
        }
        
        // replace
        $this->remove_filter($tag, $function_to_remove, $priority);
        $this->add_filter($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * add_field_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_field_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // add
        $this->add_filter($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * remove_field_filter
     *
     * @param $tag
     * @param $function_to_remove
     * @param $priority
     */
    function remove_field_filter($tag = '', $function_to_remove = '', $priority = 10){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // remove
        $this->remove_filter($tag, $function_to_remove, $priority);
        
    }
    
    
    /**
     * replace_field_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function replace_field_filter($tag = '', $function_to_replace = '', $priority = 10, $accepted_args = 1){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // replace
        $this->replace_filter($tag, $function_to_replace, $priority, $accepted_args);
        
    }
    
    
    /**
     * add_action
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        // bail early if no callable
        if(!is_callable($function_to_add)){
            return;
        }
        
        // add
        add_action($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * remove_action
     *
     * @param $tag
     * @param $function_to_remove
     * @param $priority
     */
    function remove_action($tag = '', $function_to_remove = '', $priority = 10){
        
        // bail early if no callable
        if(!is_callable($function_to_remove)){
            return;
        }
        
        // remove
        remove_action($tag, $function_to_remove, $priority);
        
    }
    
    
    /**
     * replace_action
     *
     * @param $tag
     * @param $function_to_replace
     * @param $priority
     * @param $accepted_args
     */
    function replace_action($tag = '', $function_to_replace = '', $priority = 10, $accepted_args = 1){
    
        // check instance
        if(!$this->instance){
            $this->instance = $this->get_field_type();
        }
    
        // array
        if(is_array($function_to_replace)){
            $function_to_remove = array($this->instance, $function_to_replace[1]);
            $function_to_add = $function_to_replace;
            
        // string
        }else{
            $function_to_remove = array($this->instance, $function_to_replace);
            $function_to_add = array($this, $function_to_replace);
            
        }
        
        // bail early if no callable
        if(!is_callable($function_to_add)){
            return;
        }
        
        // replace
        $this->remove_action($tag, $function_to_remove, $priority);
        $this->add_action($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    /**
     * add_field_action
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_field_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // add
        $this->add_action($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * remove_field_action
     *
     * @param $tag
     * @param $function_to_remove
     * @param $priority
     */
    function remove_field_action($tag = '', $function_to_remove = '', $priority = 10){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // remove
        $this->remove_action($tag, $function_to_remove, $priority);
        
    }
    
    
    /**
     * replace_field_action
     *
     * @param $tag
     * @param $function_to_replace
     * @param $priority
     * @param $accepted_args
     */
    function replace_field_action($tag = '', $function_to_replace = '', $priority = 10, $accepted_args = 1){
        
        // append
        $tag .= '/type=' . $this->name;
        
        // replace
        $this->replace_action($tag, $function_to_replace, $priority, $accepted_args);
        
    }
    
}

endif;