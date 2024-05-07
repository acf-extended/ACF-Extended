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
    
}

acf_new_instance('acfe_module_form_front_render_hooks');

endif;