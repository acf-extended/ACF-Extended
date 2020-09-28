<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Admin Enqueue
 */
add_action('admin_enqueue_scripts', 'acfe_enqueue_admin_scripts');
function acfe_enqueue_admin_scripts(){
    
    // Vars
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    $version = ACFE_VERSION;
    
    wp_enqueue_script('acf-extended-admin', acfe_get_url('assets/acf-extended-admin' . $suffix . '.js'), array('jquery'), $version);
    wp_enqueue_style('acf-extended-admin', acfe_get_url('assets/acf-extended-admin' . $suffix . '.css'), false, $version);
    
    // ACF Extended: UI
    if(acf_get_setting('acfe/modules/ui')){
        
        wp_enqueue_style('acf-extended-ui', acfe_get_url('assets/acf-extended-ui' . $suffix . '.css'), false, $version);
        
    }
    
}

/**
 * ACF Loaded (Front + Back) Enqueue
 */
add_action('acf/input/admin_enqueue_scripts', 'acfe_enqueue_admin_input_scripts');
function acfe_enqueue_admin_input_scripts(){
    
    // Vars
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    $version = ACFE_VERSION;
    
    // ACF Extended
    wp_enqueue_script('acf-extended', acfe_get_url('assets/acf-extended' . $suffix . '.js'), array('jquery'), $version);
    wp_enqueue_style('acf-extended', acfe_get_url('assets/acf-extended' . $suffix . '.css'), false, $version);
    
    acf_localize_data(array(
        'is_admin' => is_admin()
    ));
    
    acf_localize_text(array(
        'Close'	=> __('Close', 'acf'),
    ));
    
}