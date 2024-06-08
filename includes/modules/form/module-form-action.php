<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action')):

class acfe_module_form_action{
    
    // vars
    public $name     = '',
           $title    = '',
           $item     = array(),
           $validate = array(),
           $fields   = array(),
           $prefix   = '';
    
    /**
     * __construct
     */
    function __construct(){
        
        $this->initialize();
        
        $this->prefix = "{$this->name}_action";
        
        $this->add_filter("acfe/form/load_{$this->name}",            array($this, 'load_action'),     9, 2);
        $this->add_action("acfe/form/validate_{$this->name}",        array($this, 'validate_action'), 9, 2);
        $this->add_filter("acfe/form/prepare_{$this->name}",         array($this, 'prepare_action'),  9, 2);
        $this->add_action("acfe/form/make_{$this->name}",            array($this, 'make_action'),     9, 2);
        $this->add_filter("acfe/form/register_layout_{$this->name}", array($this, 'register_layout'), 9);
        
    }
    
    
    /**
     * initialize
     */
    function initialize(){
        // ...
    }
    
    
    /**
     * get_layout
     *
     * @return mixed
     */
    function get_layout(){
        
        $layout = array(
            'key'        => "layout_{$this->name}",
            'name'       => $this->name,
            'label'      => $this->title,
            'display'    => 'row',
            'sub_fields' => apply_filters("acfe/form/register_layout_{$this->name}", array()),
            'min'        => '',
            'max'        => '',
        );
        
        $layout = $this->prefix_fields_keys($layout, $this->prefix);
        
        return $layout;
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_{name}:9
     *
     * @param $action
     * @param $form
     *
     * @return mixed
     */
    function prepare_action($action, $form){
        
        // return
        return $action;
        
    }
    
    
    /**
     * prepare_load_action
     *
     * @param $action
     *
     * @return mixed
     */
    function prepare_load_action($action){
        return $action;
    }
    
    
    /**
     * prepare_save_action
     *
     * @param $action
     *
     * @return mixed
     */
    function prepare_save_action($action){
    
        $item = $this->item;
    
        // save loop
        foreach(array_keys($item) as $k){
        
            // post_type => save_post_type
            if(acf_maybe_get($action, $k)){
                $item[ $k ] = $action[ $k ];
            }
        
        }
    
        return $item;
        
    }
    
    
    /**
     * prepare_action_for_export
     *
     * @param $action
     *
     * @return mixed
     */
    function prepare_action_for_export($action){
        
        return $action;
        
    }
    
    
    /**
     * validate_item
     *
     * @param $action
     */
    function validate_item($action){
    
        // default item
        $defaults = wp_parse_args($this->item, array(
            'action' => '',
            'name'   => '',
        ));
    
        // parse defaults
        $action = acfe_parse_args_r($action, $defaults);
        
        return $action;
        
    }
    
    
    /**
     * set_action_output
     *
     * @param $data
     * @param $action
     */
    function set_action_output($data, $action){
        
        // get actions
        $actions = acf_get_form_data('acfe/form/actions');
        $actions = acf_get_array($actions);
        
        // add action type (post)
        $actions[ $action['action'] ] = $data;
        
        // add action name (my-post)
        if(!empty($action['name'])){
            $actions[ $action['name'] ] = $data;
        }
        
        // set query var
        acf_set_form_data('acfe/form/actions', $actions);
        
    }
    
    
    /**
     * load_acf_values
     *
     * @param $form
     * @param $post_id
     * @param $acf_fields
     * @param $acf_fields_exclude
     *
     * @return array
     */
    function load_acf_values($form, $post_id, $acf_fields, $acf_fields_exclude){
        
        // get fields
        $acf = acfe_get_fields($post_id);
    
        // load acf fields
        foreach($acf_fields as $field_key){
        
            // field key already loaded
            if(in_array($field_key, $acf_fields_exclude)){
                continue;
            }
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) === false || isset($form['map'][ $field_key ]['value'])){
                continue;
            }
        
            // get field & value
            $field = acf_get_field($field_key);
            $value = acfe_get_value_from_acf_values_by_key($acf, $field_key);
            
            // value is null (doesn't exist in database for $post_id)
            // might be a "taxonomy field" with "load values" enabled
            if($field && $value === null){
                
                // we need to retrieve the taxonomy value via acf_get_value()
                // so the load_value() method kicks in and "load values" can inject data
                $value = acf_get_value($post_id, $field);
                
            }
            
            // map value
            $form['map'][ $field_key ]['value'] = $value;
            
            if($field && $field['type'] === 'clone' && $field['display'] === 'seamless'){
                
                foreach(acf_get_array($value) as $sub_field_key => $sub_field_value){
                    $form['map'][ $sub_field_key ]['value'] = $sub_field_value;
                }
                
            }
            
        
        }
        
        return $form;
        
    }
    
    
    /**
     * save_acf_fields
     *
     * @param $post_id
     * @param $action
     */
    function save_acf_fields($post_id, $action){
        
        // acf fields
        $acf_fields = acf_extract_var($action['save'], 'acf_fields');
        $acf_fields = acf_get_array($acf_fields);
        
        // see: /includes/modules/form/module-form-front.php:152
        // $acf = acf_maybe_get_POST('acf');
        
        // backup original acf
        $acf = $_POST['acf'];
        $acf = acf_get_array($acf);
        
        // check if fields to save are in the $_POST['acf'] dataset
        $values = acfe_filter_acf_values_by_keys($acf, $acf_fields);
        
        // acf fields
        if($values){
            
            // save meta fields
            acf_save_post($post_id, $values);
            
            // restore original acf
            $_POST['acf'] = $acf;
            
        }
        
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
        if(is_callable($function_to_add)){
            add_action($tag, $function_to_add, $priority, $accepted_args);
        }
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
        if(is_callable($function_to_add)){
            add_filter($tag, $function_to_add, $priority, $accepted_args);
        }
    }
    
    
    /**
     * prefix_fields_keys
     *
     * @param $layout
     * @param $prefix
     *
     * @return mixed
     */
    function prefix_fields_keys($layout, $prefix){
        
        // vars
        $prefix_ = $prefix ? "{$prefix}_" : '';
    
        if(acf_maybe_get($layout, 'sub_fields')){
            
            // loop
            foreach($layout['sub_fields'] as &$field){
                
                // key doesn't exists
                if(!isset($field['key'])){
                    $field['key'] = "field_{$prefix_}{$field['name']}";
                
                // key exists
                }else{
                    $field['key'] = $this->do_prefix($field['key'], $prefix_);
                }
    
                // check conditional logic
                if(acf_maybe_get($field, 'conditional_logic')){
                    foreach($field['conditional_logic'] as &$group){
                        foreach($group as &$rule){
                            $rule['field'] = $this->do_prefix($rule['field'], $prefix_);
                        }
                    }
                }
                
                // check related fields
                if(isset($field['wrapper']['data-related-field'])){
                    $field['wrapper']['data-related-field'] = $this->do_prefix($field['wrapper']['data-related-field'], $prefix_);
                }
                
                // check sub fields
                if(acf_maybe_get($field, 'sub_fields')){
                    $field = $this->prefix_fields_keys($field, $prefix);
                }
                
            }
            
        }
        
        // return
        return $layout;
        
    }
    
    
    /**
     * do_prefix
     *
     * @param $string
     * @param $prefix
     *
     * @return array|mixed|string|string[]
     */
    function do_prefix($string, $prefix){
    
        if(acf_is_field_key($string)){
        
            // check it doesn't already starts with field_prefix
            if(!acfe_starts_with($string, "field_{$prefix}")){
                $string = substr_replace($string, "field_{$prefix}", 0, 6);
            }
        
        }else{
        
            // check it doesn't already starts with prefix
            if(!empty($prefix) && !acfe_starts_with($string, $prefix)){
                $string = "field_{$prefix}{$string}";
            }
        
        }
        
        return $string;
        
    }
    
}

endif;

// register store
acf_register_store('acfe-module-form-actions');


/**
 * acfe_register_form_action_type
 *
 * @param $class
 *
 * @return bool
 */
function acfe_register_form_action_type($class){
    
    // instantiate
    $action = new $class();
    
    // add to store
    acf_get_store('acfe-module-form-actions')->set($action->name, $action);
    
    // return
    return true;
    
}


/**
 * acfe_get_form_action_types
 * @return array|mixed|null
 */
function acfe_get_form_action_types(){
    return acf_get_store('acfe-module-form-actions')->get();
}


/**
 * acfe_get_form_action
 *
 * @param $name
 *
 * @return array|mixed|null
 */
function acfe_get_form_action_type($name){
    return acf_get_store('acfe-module-form-actions')->get($name);
}


/**
 * acfe_query_form_action_type
 *
 * @param $query
 *
 * @return false|mixed
 */
function acfe_query_form_action_type($query = array()){
    
    $module = acf_get_store('acfe-module-form-actions')->query($query);
    
    if(empty($module)){
        return false;
    }
    
    return current($module);
    
}