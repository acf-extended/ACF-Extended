<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_front')):

class acfe_module_form_front{
    
    function __construct(){
        
        add_action('acf/validate_save_post',  array($this, 'validate_save_post'), 1);
        add_action('wp',                      array($this, 'save_post'));
        
        add_action('acfe/form/validate_form', array($this, 'validate_form'), 9);
        add_action('acfe/form/submit_form',   array($this, 'submit_form'), 9);
        add_filter('acfe/form/load_form',     array($this, 'load_form'), 19);
        
    }
    
    
    /**
     * get_form_validation
     * @return false|mixed
     */
    function get_form_validation(){
    
        $valid_screen = acfe_is_front() && acf_maybe_get_POST('_acf_screen') === 'acfe_form';
        $form = $this->get_form_data(); // get $_POST data
        
        if($valid_screen && $form){
            return $form;
        }
        
        return false;
    }
    
    
    /**
     * get_form_submission
     * @return false|mixed
     */
    function get_form_submission(){
        
        $valid_screen = acf_verify_nonce('acfe_form');
        $form = $this->get_form_data(); // get $_POST data
    
        if($valid_screen && $form){
            return $form;
        }
    
        return false;
        
    }
    
    
    /**
     * get_form_data
     * @return false|mixed
     */
    function get_form_data(){
        
        if(isset($_POST['_acf_form'])){
            if($form = json_decode(acf_decrypt($_POST['_acf_form']), true)){
                return $form;
            }
        }
        
        return false;
        
    }
    
    
    /**
     * validate_save_post
     *
     * acf/validate_save_post:1
     */
    function validate_save_post(){
    
        // get form
        $form = $this->get_form_validation();
    
        // bail early
        if(!$form){
            return;
        }
        
        // set form data (used in validation with acfe_add_validation_error())
        acf_set_form_data('acfe/form', $form);
        
        // tags context
        acfe_add_context('form', $form);
        acfe_add_context('method', 'validate');
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/form/validation', true);
    
        // validate form
        do_action("acfe/form/validate_form", $form);
        
        // reset
        acfe_reset_meta();
        
    }
    
    
    /**
     * validate_form
     *
     * @param $form
     *
     * @return void
     */
    function validate_form($form){
        
        add_action("acfe/form/validate_form/form={$form['name']}", array($this, 'validate_actions'), 9);
        do_action("acfe/form/validate_form/form={$form['name']}", $form);
        
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
     * save_post
     *
     * wp
     */
    function save_post(){
        
        // get form
        $form = $this->get_form_submission();
    
        // bail early
        if(!$form){
            return;
        }
        
        // default acf
        if(empty($_POST['acf'])){
            $_POST['acf'] = array();
        }
        
        // default acf
        // this pass thru acf_sanitize_request_args() which use wp_kses
        // and break block editor image metadata
        // $_POST['acf'] = acf_maybe_get_POST('acf', array());
        
        // run kses on all $_POST data
        if($form['settings']['kses']){
            
            // wp_kses_post_deep() expects unslashed data
            $_POST['acf'] = wp_kses_post_deep(wp_unslash($_POST['acf']));
            $_POST['acf'] = wp_slash($_POST['acf']); // slash data back
            
        }
        
        // should we show errors and die
        $show_errors = true;
        $show_errors = apply_filters("acfe/form/submit_show_errors",                      $show_errors, $form);
        $show_errors = apply_filters("acfe/form/submit_show_errors/form={$form['name']}", $show_errors, $form);
        
        // validate save post
        // pass thru $this->validate_save_post()
        $valid = acf_validate_save_post($show_errors);
        
        // invalid form
        if(!$valid){
            return;
        }
        
        // set form data
        acf_set_form_data('acfe/form', $form);
        
        // tags context
        acfe_add_context('form', $form);
        acfe_add_context('method', 'submit');
        
        // remove save post action
        add_filter('acf/pre_update_value', '__return_false', 99);
    
        // upload files but do not save post
        acf_save_post(false);
        
        // restore save post action
        remove_filter('acf/pre_update_value', '__return_false', 99);
        
        // unset files to avoid duplicate upload
        unset($_FILES);
        
        // remove shortcode (temp)
        // https://github.com/elementor/elementor/issues/10998
        // https://github.com/Yoast/wordpress-seo/issues/14643
        remove_shortcode('acfe_form');
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/form/submit', true);
    
        // submit form
        do_action("acfe/form/submit_form", $form);
        
        // submit_success
        do_action("acfe/form/submit_success",                      $form);
        do_action("acfe/form/submit_success/form={$form['name']}", $form);
        
        // reset
        acfe_reset_meta();
        
        // re-add shortcode
        add_shortcode('acfe_form', array(acf_get_instance('acfe_module_form_shortcode'), 'render_shortcode'));
    
        // return (deprecated)
        if($return = acf_maybe_get($form, 'return')){
            acfe_redirect($return);
        }
        
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
     * get_form
     *
     * @param $form
     *
     * @return array|mixed
     */
    function get_form($form){
        
        // allow non array argument
        if(!is_array($form)){
            
            $name_or_id = $form;
            $form = array();
            
            if(is_numeric($name_or_id)){
                $form['ID'] = $name_or_id;
            }else{
                $form['name'] = $name_or_id;
            }
            
        }
    
        // check lowercase id
        if(isset($form['id'])){
            $form['ID'] = $form['id'];
            unset($form['id']);
        }
    
        // get module
        $module = acfe_get_module('form');
    
        // get by name or ID
        $name = acf_maybe_get($form, 'name', acf_maybe_get($form, 'ID'));
        
        if($name){
        
            // get item
            $item = $module->get_item($name);
        
            // merge arrays
            if($item){
            
                $form['ID'] = $item['ID'];
                $form['name'] = $item['name'];
                $form = acfe_parse_args_r($form, $item);
    
                acf_extract_vars($form, array('_valid'));
            
            }
        
        }
    
        // validate form (set alias)
        $form = $module->validate_item($form);
        
        // cleanup keys
        acf_extract_vars($form, array('label', 'modified', 'local', 'local_file', '_valid'));
        
        // add post id
        if(!isset($form['post_id'])){
            
            // acf_get_valid_post_id() will return the post_id within the loop
            // acfe_get_post_id() will return the post_id outside of the loop (queried_object)
            $form['post_id'] = acf_get_valid_post_id();
            
        }
        
        // add uniqid
        if(!isset($form['uniqid'])){
            $form['uniqid'] = acf_uniqid("acfe_form_{$form['name']}");
        }
        
        // add map
        if(!isset($form['map'])){
            $form['map'] = array();
        }
        
        return $form;
    
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
        
        // update context
        acfe_add_context('form', $form);
        
        // apply tags
        acfe_apply_tags($form['attributes']['form']['class']);
        acfe_apply_tags($form['attributes']['form']['id']);
        acfe_apply_tags($form['attributes']['fields']['wrapper_class']);
        acfe_apply_tags($form['attributes']['fields']['class']);
        acfe_apply_tags($form['attributes']['submit']['value']);
        acfe_apply_tags($form['attributes']['submit']['button']);
        acfe_apply_tags($form['attributes']['submit']['spinner']);
        acfe_apply_tags($form['validation']['errors_class']);
        
        // deprecated
        if(isset($form['return'])){
            acfe_apply_tags($form['return']);
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
    
    
    /**
     * render_form
     *
     * @param $form
     */
    function render_form($form){
    
        // get form
        $form = $this->get_form($form);
        
        // tags context
        acfe_add_context('form', $form);
        acfe_add_context('method', 'load');
        
        // load form
        $form = apply_filters('acfe/form/load_form', $form);
        
        // validate
        if(!$form || empty($form['active'])){
            return;
        }
        
        // update context
        // used in prepare_field_attributes() & prepare_field_values()
        acfe_add_context('form', $form);
        
        // enqueue acf
        acf_enqueue_scripts();
        
        // render success
        if(acfe_is_form_success($form['name'])){
            $this->render_success($form);
        }
        
        // prepare form
        $form = apply_filters("acfe/form/prepare_form", $form);
        
        // validate
        if(!$form){
            return;
        }
        
        // hooks
        do_action("acfe/form/render_before_form",   $form);
        do_action("acfe/form/render_before_fields", $form);
        do_action("acfe/form/render_fields",        $form);
        do_action("acfe/form/render_after_fields",  $form);
        
        if($form['attributes']['submit']){
            do_action("acfe/form/render_submit",    $form);
        }
        
        do_action("acfe/form/render_after_form",    $form);
        
    }
    
    
    /**
     * render_success
     *
     * @param $form
     *
     * @return void
     */
    function render_success($form){
        
        $setup_meta = !acfe_is_local_meta() && !empty(acf_maybe_get_POST('acf'));
        
        if($setup_meta){
            acfe_setup_meta($_POST['acf'], 'acfe/form/success', true);
        }
        
        // apply tags
        acfe_apply_tags($form['success']['message']);
        acfe_apply_tags($form['success']['wrapper']);
        
        // default args
        $args = array(
            'name' => $form['name'],
            'id'   => $form['ID'],
        );
        
        // scroll to message
        if($form['success']['scroll']){
            
            // enable
            $args['scroll'] = true;
            
            // message exists
            if(!empty($form['success']['message']) && !empty($form['success']['wrapper'])){
                
                // get css selector
                $selector = $this->get_css_selector_from_string($form['success']['wrapper']);
                
                if(!empty($selector)){
                    $args['selector'] = $selector;
                }
                
            }
            
        }
        
        // append data for javascript hooks
        acfe_append_localize_data('acfe_form_success', $args);
        
        // hook
        do_action('acfe/form/render_success', $form);
        
        if($setup_meta){
            acfe_reset_meta();
        }
        
    }
    
    
    /**
     * get_css_selector_from_string
     *
     * @param $str
     *
     * @return string
     */
    function get_css_selector_from_string($str){
        
        // extract id and class using regex
        preg_match('/id="([^"]*)"/', $str, $idMatch);
        preg_match('/class="([^"]*)"/', $str, $classMatch);
        
        // construct jQuery selector
        $selector = '';
        if(!empty($idMatch)){
            $selector .= '#' . $idMatch[1];
        }
        if(!empty($classMatch)){
            $selector .= '.' . str_replace(' ', '.', $classMatch[1]);
        }
        
        return $selector;
        
    }
    
    
}

acf_new_instance('acfe_module_form_front');

endif;

/**
 * acfe_form
 *
 * @param $form
 */
function acfe_form($form = array()){
    acf_get_instance('acfe_module_form_front')->render_form($form);
}