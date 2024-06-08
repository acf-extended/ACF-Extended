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
 * acfe_get_form_sent
 *
 * @return array|false
 */
function acfe_get_form_sent(){
    
    // check post data
    if(!empty($_POST['_acf_form'])){
        
        // decode post data
        $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
        
        // validate
        if(!empty($form) && is_array($form)){
            return $form;
        }
        
    }
    
    return false;
    
}


/**
 * acfe_is_form_success
 *
 * Check if the current page is a success form page
 *
 * @param false $args
 *
 * @return bool
 */
function acfe_is_form_success($args = false){
    
    // get success form
    $form = acfe_get_form_sent();
    
    // no form found
    if(empty($form)){
        return false;
    }
    
    // argument
    if($args){
        
        // name
        if(is_string($args)){
            
            // compare
            return acf_maybe_get($form, 'name') === $args;
            
        // array
        }elseif(is_array($args)){
            
            // cleanup keys that are subject to change (via hook or template tag)
            unset($form['settings'], $form['attributes'], $form['validation'], $form['success'], $form['render'], $form['uniqid'], $form['cid'], $form['map']);
            unset($args['settings'], $args['attributes'], $args['validation'], $args['success'], $args['render'], $args['uniqid'], $args['cid'], $args['map']);
            
            // compare
            return $form === $args;
            
        }
        
    }
    
    // return
    return true;
    
}


/**
 * acfe_get_form_actions
 *
 * Retrieve all actions output
 *
 * @return array
 */
function acfe_get_form_actions(){
    
    // get actions
    $actions = acf_get_form_data('acfe/form/actions');
    $actions = acf_get_array($actions);
    
    // return
    return $actions;
    
}

/**
 * acfe_get_form_action
 *
 * Retrieve the latest action output
 *
 * @param      $path
 * @param null $default
 *
 * @return false|mixed|string|null
 */
function acfe_get_form_action($path = null, $default = null){
    
    // get actions
    $actions = acfe_get_form_actions();
    
    // no action
    if(empty($actions)){
        return $default;
    }
    
    // get last action
    $action = end($actions);
    
    // get by action by path
    if(!empty($path)){
        $action = acfe_array_get($actions, $path, $default);
    }
    
    return $action;
    
}


/**
 * acfe_enqueue_form
 *
 * Enqueue ACF scripts and append missing data when form is loaded with ajax
 *
 * @return void
 */
function acfe_enqueue_form(){
    
    // enqueue acf scripts
    acf_enqueue_scripts(array(
        'uploader' => true, // uploader for WYSIWYG field
    ));
    
    // append missing data
    acf_set_form_data('screen', 'acfe_form');
    acf_set_form_data('post_id', acfe_get_post_id());
    acf_set_form_data('validation', true);
    
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


/**
 * acfe_form_unique_action_id
 *
 * Make actions names unique
 *
 * @param $form
 * @param $type
 *
 * @return string
 *
 * @deprecated
 */
function acfe_form_unique_action_id($form, $type){
    
    _deprecated_function('ACF Extended: acfe_form_unique_action_id()', '0.9');
    
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
 * @return array|string[]
 *
 * @deprecated
 */
function acfe_form_get_actions(){
    return acfe_get_form_actions();
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
 *
 * @deprecated
 */
function acfe_form_get_action($name = false, $key = false){
    
    // append key to path
    $name = !$key ? $name : "{$name}.{$key}";
    return acfe_get_form_action($name);
}


/**
 * acfe_form_decrypt_args
 *
 * Wrapper to decrypt ACF & ACFE Forms arguments
 *
 * @return array|false
 *
 * @deprecated
 */
function acfe_form_decrypt_args(){
    
    _deprecated_function('ACF Extended: acfe_form_decrypt_args()', '0.9.0.5', "acfe_get_form_sent()");
    return acfe_get_form_sent();
    
}


/**
 * acfe_form_is_submitted
 *
 * check if the current page is a success form page
 *
 * @param false $name
 *
 * @return bool
 *
 * @deprecated
 */
function acfe_form_is_submitted($name = false){
    
    _deprecated_function('ACF Extended: acfe_form_is_submitted()', '0.8.7.5', "acfe_is_form_success()");
    return acfe_is_form_success($name);
    
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