<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_deprecated_function
 *
 * @param $function
 * @param $version
 * @param $replacement
 */
function acfe_deprecated_function($function, $version, $replacement = ''){
    acfe_trigger_error('Function', $function, $version, $replacement);
}


/**
 * acfe_deprecated_setting
 *
 * @param $setting
 * @param $version
 * @param $replacement
 */
function acfe_deprecated_setting($setting, $version, $replacement = ''){
    acfe_trigger_error('Setting', $setting, $version, $replacement);
}


/**
 * acfe_deprecated_constant
 *
 * @param $constant
 * @param $version
 * @param $replacement
 */
function acfe_deprecated_constant($constant, $version, $replacement = ''){
    acfe_trigger_error('Constant', $constant, $version, $replacement);
}


/**
 * acfe_deprecated_hook
 *
 * @param $hook
 * @param $version
 * @param $replacement
 */
function acfe_deprecated_hook($hook, $version, $replacement = ''){
    acfe_trigger_error('Hook', $hook, $version, $replacement);
}


/**
 * acfe_apply_filters_deprecated
 *
 * @param $hook
 * @param $args
 * @param $version
 * @param $replacement
 *
 * @return mixed
 */
function acfe_apply_filters_deprecated($hook, $args, $version, $replacement = ''){
    
    if(!has_filter($hook)){
        return $args[0];
    }
    
    acfe_deprecated_hook($hook, $version, $replacement);
    return apply_filters_ref_array($hook, $args);
    
}


/**
 * acfe_do_action_deprecated
 *
 * @param $hook
 * @param $args
 * @param $version
 * @param $replacement
 */
function acfe_do_action_deprecated($hook, $args, $version, $replacement = ''){
    
    if(!has_action($hook)){
        return;
    }
    
    acfe_deprecated_hook($hook, $version, $replacement);
    do_action_ref_array($hook, $args);
    
}


/**
 * acfe_trigger_error
 *
 * @param $label
 * @param $function
 * @param $version
 * @param $replacement
 */
function acfe_trigger_error($label, $function, $version, $replacement = ''){
    
    do_action('deprecated_function_run', $function, $replacement, $version);
    
    if(WP_DEBUG && apply_filters('deprecated_function_trigger_error', true)){
        
        if($replacement){
            $message = 'ACF Extended: ' . $label . ' ' . sprintf(__('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $function, $version, $replacement);
        }else{
            $message = 'ACF Extended: ' . $label . ' ' . sprintf(__('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version);
        }
        
        // trigger error
        trigger_error($message, E_USER_DEPRECATED);
        
    }
    
}