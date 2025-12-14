<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_front_render_hooks')):

class acfe_module_form_front_render_hooks{
    
    /**
     * __construct
     */
    function __construct(){
        
        add_filter('acfe/form/prepare_form',         array($this, 'prepare_form'), 9);
        add_action('acfe/form/render_success',       array($this, 'render_success'), 9);
        
        add_action('acfe/form/render_before_form',   array($this, 'render_before_form'), 9);
        add_action('acfe/form/render_before_fields', array($this, 'render_before_fields'), 9);
        add_action('acfe/form/render_fields',        array($this, 'render_fields'), 9);
        add_action('acfe/form/render_after_fields',  array($this, 'render_after_fields'), 9);
        add_action('acfe/form/render_submit',        array($this, 'render_submit'), 9);
        add_action('acfe/form/render_after_form',    array($this, 'render_after_form'), 9);
        
        add_action('acfe/form/validate_form',        array($this, 'validate_form'), 9);
        add_action('acfe/form/submit_form',          array($this, 'submit_form'), 9);
        add_filter('acfe/form/load_form',            array($this, 'load_form'), 19);
        
    }
    
    
    /**
     * prepare_form
     *
     * @param $form
     */
    function prepare_form($form){
        
        if(!$form){
            return false;
        }
        
        add_filter("acfe/form/prepare_form/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'prepare_form'), 9);
        return apply_filters("acfe/form/prepare_form/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_success
     *
     * @param $form
     */
    function render_success($form){
        
        add_action("acfe/form/render_success/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_success'), 9);
        do_action("acfe/form/render_success/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_before_form
     *
     * @param $form
     *
     * @return void
     */
    function render_before_form($form){
        
        add_action("acfe/form/render_before_form/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_before_form'), 9);
        do_action("acfe/form/render_before_form/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_before_fields
     *
     * @param $form
     *
     * @return void
     */
    function render_before_fields($form){
        
        add_action("acfe/form/render_before_fields/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_before_fields'), 9);
        do_action("acfe/form/render_before_fields/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_fields
     *
     * @param $form
     *
     * @return void
     */
    function render_fields($form){
        
        add_action("acfe/form/render_fields/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_fields'), 9);
        do_action("acfe/form/render_fields/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_after_fields
     *
     * @param $form
     *
     * @return void
     */
    function render_after_fields($form){
        
        add_action("acfe/form/render_after_fields/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_after_fields'), 9);
        do_action("acfe/form/render_after_fields/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_submit
     *
     * @param $form
     *
     * @return void
     */
    function render_submit($form){
        
        add_action("acfe/form/render_submit/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_submit'), 9);
        do_action("acfe/form/render_submit/form={$form['name']}", $form);
        
    }
    
    
    /**
     * render_after_form
     *
     * @param $form
     *
     * @return void
     */
    function render_after_form($form){
        
        add_action("acfe/form/render_after_form/form={$form['name']}", array(acf_get_instance('acfe_module_form_front_render'), 'render_after_form'), 9);
        do_action("acfe/form/render_after_form/form={$form['name']}", $form);
        
    }
    
    
    /**
     * validate_form
     *
     * @param $form
     *
     * @return void
     */
    function validate_form($form){
        
        // validate form
        add_action("acfe/form/validate_form/form={$form['name']}", array($this, 'validate_actions'), 9);
        do_action("acfe/form/validate_form/form={$form['name']}", $form);
        
        // validate value
        add_filter('acf/validate_value', array($this, 'validate_value'), 10, 4);
        
    }
    
    
    /**
     * validate_actions
     *
     * @param $form
     *
     * @return void
     */
    function validate_actions($form){
        
        // force array
        $form['actions'] = acf_get_array($form['actions']);
        
        // validate actions
        foreach($form['actions'] as $action){
            
            // tags context
            acfe_add_context('action', $action);
            
            // validate action
            do_action("acfe/form/validate_{$action['action']}",                          $form, $action);
            do_action("acfe/form/validate_{$action['action']}/form={$form['name']}",     $form, $action);
            do_action("acfe/form/validate_{$action['action']}/action={$action['name']}", $form, $action);
            
            // tags context
            acfe_delete_context('action');
            
        }
        
    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return void
     */
    function validate_value($valid, $value, $field, $input){
        
        // get form
        $form = acf_get_form_data('acfe/form');
        
        // filter
        add_filter('acfe/form/validate_value', array($this, 'validate_field_value'), 9, 5);
        add_filter('acfe/form/validate_value', array($this, 'validate_field_value_after'), 999, 5);
        $valid = apply_filters('acfe/form/validate_value', $valid, $value, $field, $input, $form);
        
        return $valid;
        
    }
    
    
    /**
     * validate_field_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return mixed|null
     */
    function validate_field_value($valid, $value, $field, $input, $form){
        
        // variations
        $valid = apply_filters("acfe/form/validate_value/form={$form['name']}",   $valid, $value, $field, $input, $form);
        $valid = apply_filters("acfe/form/validate_value/type={$field['type']}",  $valid, $value, $field, $input, $form);
        $valid = apply_filters("acfe/form/validate_value/name={$field['_name']}", $valid, $value, $field, $input, $form);
        $valid = apply_filters("acfe/form/validate_value/key={$field['key']}",    $valid, $value, $field, $input, $form);
        
        return $valid;
    }
    
    
    /**
     * validate_field_value_after
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return mixed|null
     */
    function validate_field_value_after($valid, $value, $field, $input, $form){
        
        // field is invalid and not required
        if(!$valid && empty($field['required'])){
            $valid = __('Validation failed', 'acf'); // add generic message
        }
        
        return $valid;
    }
    
    
    /**
     * submit_form
     *
     * @param $form
     *
     * @return void
     */
    function submit_form($form){
        
        add_action("acfe/form/submit_form/form={$form['name']}", array($this, 'submit_actions'), 9);
        do_action("acfe/form/submit_form/form={$form['name']}", $form);
        
    }
    
    
    /**
     * submit_actions
     *
     * @param $form
     *
     * @return void
     */
    function submit_actions($form){
        
        // force array
        $form['actions'] = acf_get_array($form['actions']);
        
        // submit actions
        foreach($form['actions'] as $action){
            
            // tags context
            acfe_add_context('action', $action);
            
            // prepare action
                        $action = apply_filters("acfe/form/prepare_{$action['action']}",                          $action, $form);
            if($action){$action = apply_filters("acfe/form/prepare_{$action['action']}/form={$form['name']}",     $action, $form);}
            if($action){$action = apply_filters("acfe/form/prepare_{$action['action']}/action={$action['name']}", $action, $form);}
            
            if($action === false){
                continue;
            }
            
            // make action
            do_action("acfe/form/make_{$action['action']}", $form, $action);
            
            // tags context
            acfe_delete_context('action');
            
        }
        
    }
    
    
    /**
     * load_form
     *
     * @param $form
     *
     * @return false|mixed|null
     */
    function load_form($form){
        
        if(!$form){
            return false;
        }
        
        // load actions
        add_filter("acfe/form/load_form/form={$form['name']}", array($this, 'load_actions'), 19);
        return apply_filters("acfe/form/load_form/form={$form['name']}", $form);
        
    }
    
    
    /**
     * load_actions
     *
     * @param $form
     *
     * @return mixed|null
     */
    function load_actions($form){
        
        if(!$form){
            return false;
        }
        
        // backup map without actions injecting values
        $form['map_default'] = $form['map'];
        
        // update context
        acfe_add_context('form', $form);
        
        // tags context
        $opt = array('context' => 'display');
        
        // apply tags
        acfe_apply_tags($form['attributes']['form']['class'],           $opt);
        acfe_apply_tags($form['attributes']['form']['id'],              $opt);
        acfe_apply_tags($form['attributes']['fields']['wrapper_class'], $opt);
        acfe_apply_tags($form['attributes']['fields']['class'],         $opt);
        acfe_apply_tags($form['attributes']['submit']['value'],         $opt);
        acfe_apply_tags($form['attributes']['submit']['button'],        $opt);
        acfe_apply_tags($form['attributes']['submit']['spinner'],       $opt);
        acfe_apply_tags($form['validation']['errors_class'],            $opt);
        
        // deprecated
        if(isset($form['return'])){
            acfe_apply_tags($form['return'], $opt);
        }
        
        // load form per action
        foreach($form['actions'] as $action){
            
            // tags context
            acfe_add_context('action', $action);
            
            // load action
            $form = apply_filters("acfe/form/load_{$action['action']}",                          $form, $action);
            $form = apply_filters("acfe/form/load_{$action['action']}/form={$form['name']}",     $form, $action);
            $form = apply_filters("acfe/form/load_{$action['action']}/action={$action['name']}", $form, $action);
            
            // tags context
            acfe_delete_context('action');
            
        }
        
        // update context
        acfe_add_context('form', $form);
        
        return $form;
        
    }
    
}

acf_new_instance('acfe_module_form_front_render_hooks');

endif;