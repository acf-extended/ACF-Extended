<?php

if(!defined('ABSPATH'))
    exit;

/**
 * acfe_get_pretty_forms
 *
 * Similar to acf_get_pretty_post_types() but for ACFE Forms
 *
 * @param array $forms
 *
 * @return array
 */
function acfe_get_pretty_forms($forms = array()){
    
    if(empty($forms)){
        
        $forms = get_posts(array(
            'post_type'         => 'acfe-form',
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'orderby'           => 'title',
            'order'             => 'ASC',
        ));
        
    }
    
    $return = array();
    
    // Choices
    if(!empty($forms)){
        
        foreach($forms as $form_id){
            
            $form_name = get_the_title($form_id);
            
            $return[$form_id] = $form_name;
            
        }
        
    }
    
    return $return;
    
}

/**
 * acfe_form_decrypt_args
 *
 * Wrapper to decrypt ACF & ACFE Forms arguments
 *
 * @return false|mixed
 */
function acfe_form_decrypt_args(){
    
    if(!acf_maybe_get_POST('_acf_form'))
        return false;
    
    $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
    
    if(empty($form))
        return false;
    
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
    
    _deprecated_function('ACF Extended - Dynamic Forms: "acfe_form_is_submitted()" function', '0.8.7.5', "acfe_is_form_success()");
    
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
    
    $name = $form['name'] . '-' . $type;
    
    global $acfe_form_uniqid;
    
    $acfe_form_uniqid = acf_get_array($acfe_form_uniqid);
    
    if(!isset($acfe_form_uniqid[$type])){
        
        $acfe_form_uniqid[$type] = 1;
        
    }
    
    if($acfe_form_uniqid[$type] > 1)
        $name = $name . '-' . $acfe_form_uniqid[$type];
    
    $acfe_form_uniqid[$type]++;
    
    return $name;
    
}

/**
 * acfe_form_get_actions
 *
 * Retrieve all actions output
 *
 * @return mixed
 */
function acfe_form_get_actions(){
    
    return get_query_var('acfe_form_actions', array());
    
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
    
    $actions = acfe_form_get_actions();
    
    // No action
    if(empty($actions))
        return false;
    
    // Action name
    if(!empty($name)){
        $return = acf_maybe_get($actions, $name, false);
    }else{
        $return = end($actions);
    }
    
    if($key !== false || is_numeric($key))
        $return = acf_maybe_get($return, $key, false);
    
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