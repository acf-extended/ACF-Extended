<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_front')):

class acfe_module_form_front{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acf/validate_save_post',                    array($this, 'validate_save_post'), 1);
        add_action('wp',                                        array($this, 'save_post'));
        
        add_action('acfe/form/validate_form',                   array($this, 'validate_form'), 9);
        add_action('acfe/form/submit_form',                     array($this, 'submit_form'), 9);
        add_filter('acfe/form/load_form',                       array($this, 'load_form'), 19);
        add_filter('acfe/form/set_form_data',                   array($this, 'set_form_data'), 10, 2);
        
        add_action(       'wp_ajax_acfe/form/render_form_ajax', array($this, 'render_form_ajax'));
        add_action('wp_ajax_nopriv_acfe/form/render_form_ajax', array($this, 'render_form_ajax'));
        
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
        
        // set form data
        // used in validation with acfe_add_validation_error()
        acf_set_form_data('acfe/form', $form);
        
        // tags context
        acfe_add_context('form', $form);
        acfe_add_context('method', 'validate');
        
        // setup meta
        acfe_setup_meta(wp_unslash($_POST['acf']), 'acfe/form/validation', true);
    
        // validate form
        do_action("acfe/form/validate_form", $form);
        
        // reset
        acfe_reset_meta();
        
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
        
        // remove shortcode temporarly
        // https://github.com/elementor/elementor/issues/10998
        // https://github.com/Yoast/wordpress-seo/issues/14643
        remove_shortcode('acfe_form');
        
        // setup meta
        acfe_setup_meta(wp_unslash($_POST['acf']), 'acfe/form/submit', true);
    
        // submit form
        do_action("acfe/form/submit_form", $form);
        
        // submit_success
        do_action("acfe/form/submit_success",                      $form);
        do_action("acfe/form/submit_success/form={$form['name']}", $form);
        
        // reset
        acfe_reset_meta();
        
        // re-add shortcode
        add_shortcode('acfe_form', array(acf_get_instance('acfe_module_form_shortcode'), 'render_shortcode'));
        
        // prevent refresh
        add_action('wp_print_footer_scripts', array($this, 'prevent_refresh'));
        
        // return (deprecated)
        if($return = acf_maybe_get($form, 'return')){
            acfe_redirect($return);
        }
        
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
            
            $arg = $form;
            $form = array(
                'ID'   => is_numeric($arg) ? $arg : 0,
                'name' => !is_numeric($arg) ? $arg : '',
            );
            
        }
    
        // check lowercase id
        if(isset($form['id'])){
            $form['ID'] = acf_extract_var($form, 'id');
        }
    
        // get module
        $module = acfe_get_module('form');
        
        // get by name or ID
        $selector = !empty($form['name']) ? $form['name'] : acf_maybe_get($form, 'ID');
        
        if($selector){
        
            // get item
            $item = $module->get_item($selector);
        
            // merge arrays
            if($item){
                
                // assign item vars
                $form['ID'] = $item['ID'];
                $form['name'] = $item['name'];
                $form = acfe_parse_args_r($form, $item);
                
                // allow validate_item again
                acf_extract_vars($form, array('_valid'));
            
            }
        
        }
    
        // validate form (set alias)
        // also add settings in case there is no form found
        $form = $module->validate_item($form);
        
        // cleanup keys
        acf_extract_vars($form, array('label', 'modified', 'local', 'local_file', '_valid'));
        
        // add post id
        if(!isset($form['post_id'])){
            $form['post_id'] = acfe_get_post_id();
        }
        
        // add uniqid
        if(!isset($form['uniqid'])){
            $form['uniqid'] = acf_uniqid("acfe_form_{$form['name']}");
        }
        
        // add cid (visible in the DOM)
        if(!isset($form['cid'])){
            $form['cid'] = uniqid();
        }
        
        // add map
        if(!isset($form['map'])){
            $form['map'] = array();
        }
        
        return $form;
    
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
        
        $data = array();
        $data = apply_filters("acfe/form/set_form_data",                      $data, $form);
        $data = apply_filters("acfe/form/set_form_data/form={$form['name']}", $data, $form);
        
        if($data){
            acfe_set_form_data($form, $data);
        }
        
        // override acf.data.post_id
        add_action('wp_print_footer_scripts', array($this, 'override_acf_post_id'), 9);
        
        // render success only once
        // this allows to render the same form within the success message with [acfe_form]
        // do not execute in ajax so form displayed in success message using ajax submission doesn't display success twice
        if(acfe_is_form_success($form) && !acf_did('acfe/form/success') && !acf_is_ajax()){
            
            // render success
            $this->render_success($form);
            
            // add success class
            $form['attributes']['form']['class'] .= !empty($form['attributes']['form']['class']) ? ' ' : '';
            $form['attributes']['form']['class'] .= '-success';
            
            // hide form
            if($form['success']['hide_form']){
                return;
            }
            
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
        
        $setup_meta = !acfe_is_local_meta() && !empty($_POST['acf']);
        
        if($setup_meta){
            acfe_setup_meta(wp_unslash($_POST['acf']), 'acfe/form/success', true);
        }
        
        // apply tags
        // wysiwyg is formatted later with acf_the_content filter in render_success()
        acfe_apply_tags($form['success']['wrapper'], array('context' => 'display'));
        acfe_apply_tags($form['success']['message'], array('context' => 'display', 'unformat' => 'wysiwyg'));
        
        // hook
        do_action('acfe/form/render_success', $form);
        
        if($setup_meta){
            acfe_reset_meta();
        }
        
    }
    
    
    /**
     * set_form_data
     *
     * @param $data
     * @param $form
     *
     * @return mixed
     */
    function set_form_data($data, $form){
        
        $data['cid'] =               $form['cid'];
        $data['name'] =              $form['name'];
        $data['id'] =                $form['ID'];
        $data['field_class'] =       $form['attributes']['fields']['class'];
        $data['hide_error'] =        $form['validation']['hide_error'];
        $data['hide_unload'] =       $form['validation']['hide_unload'];
        $data['hide_revalidation'] = $form['validation']['hide_revalidation'];
        $data['error_position'] =    $form['validation']['errors_position'];
        $data['error_class'] =       $form['validation']['errors_class'];
        $data['messages'] =          $form['validation']['messages'];
        $data['scroll'] =            $form['success']['scroll'];
        $data['hide_form'] =         $form['success']['hide_form'];
        $data['success'] =           false;
        
        return $data;
        
    }
    
    
    /**
     * get_form_validation
     *
     * @return array|false
     */
    function get_form_validation(){
        
        $valid_screen = acfe_is_front() && acf_maybe_get_POST('_acf_screen') === 'acfe_form';
        $form = acfe_get_form_sent();
        
        if($valid_screen && $form){
            return $form;
        }
        
        return false;
    }
    
    
    /**
     * get_form_submission
     *
     * @return array|false
     */
    function get_form_submission(){
        
        $valid_screen = acf_verify_nonce('acfe_form');
        $form = acfe_get_form_sent();
        
        if($valid_screen && $form){
            return $form;
        }
        
        return false;
        
    }
    
    
    /**
     * set_data
     *
     * @param $form
     * @param $path
     * @param $value
     *
     * @return void
     */
    function set_data($form, $path = null, $value = null){
        
        if($value === null){
            $value = $path;
            $path = null;
        }
        
        // append data for acf.data
        if($form['cid']){
            
            // determine path
            $data_path = $path ? "forms.{$form['cid']}.{$path}" : "forms.{$form['cid']}";
            
            // localize
            acfe_set_localize_data($data_path, $value);
            
        }
        
    }
    
    
    /**
     * override_acf_post_id
     *
     * wp_print_footer_scripts
     *
     * Override acf.data.post_id to use page id and not form post_id
     * Multiple acfe_form() in a WP_Query loop will assign acf.data.post_id to the last post
     *
     * @return void
     */
    function override_acf_post_id(){
        
        // only once
        if(!acf_did('acfe/form/override_acf_post_id')){
            acf_set_form_data('post_id', acfe_get_post_id());
        }
        
    }
    
    
    /**
     * prevent_refresh
     *
     * wp_print_footer_scripts
     *
     * Avoid multiple submissions on page refresh
     *
     * @return void
     */
    function prevent_refresh(){
        ?>
        <script>
        if(window.history.replaceState){
            window.history.replaceState(null, null, window.location.href);
        }
        </script>
        <?php
    }
    
    
    /**
     * render_form_ajax
     *
     * @return void
     */
    function render_form_ajax(){
        
        // validate ajax
        if(!acf_verify_ajax()){
            die;
        }
        
        // parse options
        $options = wp_parse_args($_POST, array(
            'form' => false,
        ));
        
        // render form
        if(!empty($options['form'])){
            acfe_form($options['form']);
        }
        
        die;
        
    }
    
    
    /**
     * get_form_data
     *
     * @return array|false
     */
    function get_form_data(){
        return acfe_get_form_sent();
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


/**
 * acfe_get_form
 *
 * @param array $form
 *
 * @return mixed
 */
function acfe_get_form(array $form = array()){
    return acf_get_instance('acfe_module_form_front')->get_form($form);
}


/**
 * acfe_set_form_data
 *
 * @param $form
 * @param $value
 *
 * @return void
 */
function acfe_set_form_data($form, $path = null, $value = null){
    acf_get_instance('acfe_module_form_front')->set_data($form, $path, $value);
}