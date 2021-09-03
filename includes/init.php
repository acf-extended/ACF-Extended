<?php

if(!defined('ABSPATH'))
    exit;

/**
 * acfe_include
 *
 * Includes a file within the plugin
 *
 * @param string $filename
 */
function acfe_include($filename = ''){
    
    $file_path = ACFE_PATH . ltrim($filename, '/');
    
    if(file_exists($file_path)){
        return include_once($file_path);
    }
    
    return false;
    
}

/**
 * acfe_get_path
 *
 * Returns the plugin path
 *
 * @param string $filename
 *
 * @return string
 */
function acfe_get_path($filename = ''){
    return ACFE_PATH . ltrim($filename, '/');
}

/**
 * acfe_get_url
 *
 * Returns the plugin url
 *
 * @param string $filename
 *
 * @return string
 */
function acfe_get_url($filename = ''){
    
    if(!defined('ACFE_URL')){
        define('ACFE_URL', acf_get_setting('acfe/url'));
    }
    
    return ACFE_URL . ltrim($filename, '/');
}

/**
 * acfe_get_view
 *
 * Load in a file from the 'admin/views' folder and allow variables to be passed through
 * Based on acf_get_view()
 *
 * @param string $path
 * @param array  $args
 */
function acfe_get_view($path = '', $args = array()){
    
    // allow view file name shortcut
    if(substr($path, -4) !== '.php'){
        $path = acfe_get_path("includes/admin/views/{$path}.php");
    }
    
    // include
    if(file_exists($path)){
        
        extract($args);
        include($path);
        
    }
    
}

/**
 * acfe_load_textdomain
 *
 * Load textdomain files based on acf_load_textdomain()
 *
 * @param string $domain
 *
 * @return bool
 */
function acfe_load_textdomain($domain = 'acfe'){
    
    $locale = apply_filters('plugin_locale', acf_get_locale(), $domain);
    $mofile = $domain . '-' . $locale . '.mo';
    
    // Try to load from the languages directory first.
    if(load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $mofile)){
        return true;
    }
    
    // Load from plugin lang folder.
    return load_textdomain($domain, acfe_get_path('lang/' . $mofile));
    
}