<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_has_acf
 *
 * Checks ACF version
 *
 * @return bool
 */
function acfe_has_acf(){
    return class_exists('ACF') && defined('ACF_PRO') && defined('ACF_VERSION') && version_compare(ACF_VERSION, '5.8', '>=');
}

/**
 * acfe_is_acf_59
 *
 * @return bool
 */
function acfe_is_acf_59(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '5.9');
}

/**
 * acfe_is_acf_6
 *
 * @return bool
 */
function acfe_is_acf_6(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.0');
}

/**
 * acfe_is_acf_61
 *
 * @return bool
 */
function acfe_is_acf_61(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.1');
}

/**
 * acfe_is_acf_622
 *
 * @return bool
 */
function acfe_is_acf_622(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.2.2');
}

/**
 * acfe_is_acf_64
 *
 * @return bool
 */
function acfe_is_acf_64(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.4');
}

/**
 * acfe_is_acf_65
 *
 * @return bool
 */
function acfe_is_acf_65(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.5');
}

/**
 * acfe_is_acf_66
 *
 * @return bool
 */
function acfe_is_acf_66(){
    return acf_version_compare(acf_get_setting('version'),  '>=', '6.6');
}

/**
 * acfe_include
 *
 * Includes a file within the plugin
 *
 * @param $filename
 * @param $once
 *
 * @return false|mixed
 */
function acfe_include($filename = '', $once = true){
    
    $file_path = acfe_get_path($filename);
    
    if(file_exists($file_path)){
        if($once){
            return include_once($file_path);
        }else{
            return include($file_path);
        }
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

/**
 * acfe_after_plugin_row
 *
 * after_plugin_row
 *
 * @param $plugin_file
 * @param $plugin_data
 * @param $status
 */
add_action('after_plugin_row_' . ACFE_BASENAME, 'acfe_after_plugin_row', 5, 3);
function acfe_after_plugin_row($plugin_file, $plugin_data, $status){
    
    // bail early
    if(acfe_has_acf()){
        return;
    }
    
    // vars
    $colspan = version_compare($GLOBALS['wp_version'], '5.5', '<') ? 3 : 4;
    
    // class
    $class = 'acfe-plugin-tr';
    if(isset($plugin_data['update']) && !empty($plugin_data['update'])){
        $class .= ' acfe-plugin-tr-update';
    }
    
    ?>
    <style>
        .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] th,
        .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] td{
            box-shadow:none;
        }
    </style>
    <tr class="plugin-update-tr active <?php echo $class; ?>">
        <td colspan="<?php echo $colspan; ?>" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e('ACF Extended requires <a href="https://www.advancedcustomfields.com/pro/" target="_blank">Advanced Custom Fields PRO</a> (minimum: 5.8).', 'acfe'); ?></p>
            </div>
        </td>
    </tr>
    <?php
    
}