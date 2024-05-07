<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_pretty_forms
 *
 * Similar to acf_get_pretty_post_types() but for ACFE Forms
 * Used in the Forms field type
 *
 * @param array $forms
 *
 * @return array
 */
function acfe_get_pretty_forms($allowed = array()){
    
    $forms = acfe_get_module('form')->get_items();
    $choices = array();
    
    foreach($forms as $item){
        
        // fallback to name if empty title
        $item['title'] = !empty($item['title']) ? $item['title'] : $item['name'];
        
        if(empty($allowed) || in_array($item['name'], $allowed, true)){
            $choices[ $item['name'] ] = $item['title'];
        }
        
    }

    return $choices;

}


/**
 * acfe_form_decrypt_args
 *
 * Wrapper to decrypt ACF & ACFE Forms arguments
 *
 * @return false|mixed
 */
function acfe_form_decrypt_args(){
    
    if(!acf_maybe_get_POST('_acf_form')){
        return false;
    }
    
    $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
    
    if(empty($form)){
        return false;
    }
    
    return $form;
    
}


/**
 * acfe_is_form_success
 *
 * Check if the current page is a success form page
 *
 * @param false $form_name
 *
 * @return bool
 */
function acfe_is_form_success($form_name = false){
    
    if(!acf_maybe_get_POST('_acf_form')){
        return false;
    }
    
    $form = acfe_form_decrypt_args();
    
    if(empty($form)){
        return false;
    }
    
    if(!empty($form_name) && acf_maybe_get($form, 'name') !== $form_name){
        return false;
    }
    
    // avoid multiple submissions
    // this method is already added in js
    // but it must be added here in case developer use this as conditional on acfe_form()
    // and thus, prevent the acf javascript from being enqueued
    if(headers_sent()){
        
        // check filter
        if(!acf_is_filter_enabled('acfe/form/is_success')){
            ?>
            <script>
                if(window.history.replaceState){
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
            <?php
            
            // only once
            acf_enable_filter('acfe/form/is_success');
        }
        
    }
    
    return true;
    
}


/**
 * acfe_form_is_submitted
 *
 * check if the current page is a success form page
 *
 * @param false $form_name
 *
 * @return bool
 *
 * @deprecated
 */
function acfe_form_is_submitted($form_name = false){
    
    _deprecated_function('ACF Extended: acfe_form_is_submitted()', '0.8.7.5', "acfe_is_form_success()");
    
    return acfe_is_form_success($form_name);
    
}


/**
 * acfe_form_unique_action_id
 *
 * Make actions names unique
 *
 * @param $form
 * @param $type
 *
 * @return string
 */
function acfe_form_unique_action_id($form, $type){
    
    // global
    global $acfe_form_uniqid;
    $acfe_form_uniqid = acf_get_array($acfe_form_uniqid);
    
    $name = "{$form['name']}-{$type}";
    
    if(!isset($acfe_form_uniqid[ $type ])){
        $acfe_form_uniqid[ $type ] = 1;
    }
    
    if($acfe_form_uniqid[ $type ] > 1){
        $name = "{$name}-{$acfe_form_uniqid[ $type ]}";
    }
    
    $acfe_form_uniqid[ $type ]++;
    
    return $name;
    
}


/**
 * acfe_form_get_actions
 *
 * Retrieve all actions output
 *
 * @return array|false|string[]
 */
function acfe_form_get_actions(){
    
    // get actions
    $actions = acf_get_form_data('acfe/form/actions');
    $actions = acf_get_array($actions);
    
    // return
    return $actions;
}


/**
 * acfe_form_get_action
 *
 * Retrieve the latest action output
 *
 * @param false $name
 * @param false $key
 *
 * @return false|mixed|null
 */
function acfe_form_get_action($name = false, $key = false){
    
    // get actions
    $actions = acfe_form_get_actions();
    
    // no action
    if(empty($actions)){
        return false;
    }
    
    // get last action
    $return = end($actions);
    
    // get by action name
    if(!empty($name)){
        $return = acf_maybe_get($actions, $name, false);
    }
    
    if($return && !acf_is_empty($key)){
        $return = acfe_array_get($return, $key);
    }
    
    return $return;
    
}


/**
 * acfe_form_is_admin
 *
 * Check if current screen is back-end
 *
 * @return bool
 *
 * @deprecated
 */
function acfe_form_is_admin(){
    
    _deprecated_function('ACF Extended: acfe_form_is_admin()', '0.8.8', "acfe_is_admin()");
    return acfe_is_admin();
    
}


/**
 * acfe_form_is_front
 *
 * Check if current screen is front-end
 *
 * @return bool
 *
 * @deprecated
 */
function acfe_form_is_front(){
    
    _deprecated_function('ACF Extended: acfe_form_is_front()', '0.8.8', "acfe_is_front()");
    return acfe_is_front();
    
}


/**
 * acfe_import_form
 *
 * @param $args
 *
 * @return array|mixed|WP_Error
 */
function acfe_import_form($args){
    
    // json string
    if(is_string($args)){
        $args = json_decode($args, true);
    }
    
    // validate array
    if(!is_array($args) || empty($args)){
        return new WP_Error('acfe_import_form_invalid_input', __("Input is invalid: Must be a json string or an array."));
    }
    
    // module
    $module = acfe_get_module('form');
    
    /**
     * single item
     *
     * array(
     *     'title' => 'My Form',
     *     'acfe_form_name' => 'my-form',
     *     'acfe_form_actions' => array(...)
     * )
     */
    if(isset($args['title'])){
        
        $args = array(
            $args
        );
        
    }
    
    // vars
    $result = array();
    
    // loop
    foreach($args as $key => $item){
        
        // prior 0.9
        // old import had name as key
        if(!is_numeric($key) && !isset($item['name'])){
            $item['name'] = $key;
        }
        
        // name still missing
        // retrieve from old key acfe_form_name
        if(!isset($item['name'])){
            $item['name'] = acf_maybe_get($item, 'acfe_form_name');
        }
        
        // search database for existing item
        $post = $module->get_item_post($item['name']);
        if($post){
            $item['ID'] = $post->ID;
        }
        
        // import item
        $item = $module->import_item($item);
        
        $return = array(
            'success' => true,
            'post_id' => $item['ID'],
            'message' => 'Form "' . get_the_title($item['ID']) . '" successfully imported.',
        );
        
        $result[] = $return;
        
    }
    
    if(count($result) === 1){
        $result = $result[0];
    }
    
    return $result;
    
}