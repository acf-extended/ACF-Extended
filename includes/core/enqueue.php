<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Admin: Enqueue everywhere + conditional
 */
add_action('admin_enqueue_scripts', 'acfe_enqueue_admin_scripts');
function acfe_enqueue_admin_scripts(){
    
    // Enqueue ACF input
    wp_enqueue_style('acf-input');
    wp_enqueue_script('acf-input');
    
    // ACF Extended
    wp_enqueue_script('acf-extended', plugins_url('assets/acf-extended.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    wp_enqueue_style('acf-extended', plugins_url('assets/acf-extended.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_style('acf-extended-admin', plugins_url('assets/acf-extended-admin.css', ACFE_FILE), false, ACFE_VERSION);
    
    // Better Taxonomies
    if(acf_get_setting('acfe/modules/taxonomies')){
        
        wp_enqueue_style('acf-extended-taxonomies', plugins_url('assets/acf-extended-taxonomies.css', ACFE_FILE), false, ACFE_VERSION);
        
    }

    // ACF Extended: Field Groups only
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
        
        wp_enqueue_script('acf-extended-fg', plugins_url('assets/acf-extended-fg.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
        wp_enqueue_style('acf-extended-fg', plugins_url('assets/acf-extended-fg.css', ACFE_FILE), false, ACFE_VERSION);
    
    }
    
}

/**
 * Admin + WP: Enqueue where ACF is loaded
 */
add_action('acf/enqueue_scripts', 'acfe_enqueue_scripts');
function acfe_enqueue_scripts(){
    
    // ACF Extended
    wp_enqueue_script('acf-extended', plugins_url('assets/acf-extended.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    wp_enqueue_style('acf-extended', plugins_url('assets/acf-extended.css', ACFE_FILE), false, ACFE_VERSION);
    
    // ACF Extended: Fields
    wp_enqueue_script('acf-extended-fields', plugins_url('assets/acf-extended-fields.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    // Front only
    if(!is_admin()){
        
        wp_enqueue_script('acf-extended-form', plugins_url('assets/acf-extended-form.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
        
    }
    
}

/**
 * Admin: Enqueue where ACF is loaded
 */
add_action('acf/input/admin_enqueue_scripts', 'acfe_enqueue_admin_input_scripts');
function acfe_enqueue_admin_input_scripts(){
    
    // ACF Extended: Modal
    wp_enqueue_style('acf-extended-modal', plugins_url('assets/acf-extended-modal.css', ACFE_FILE), false, ACFE_VERSION);
    
    // Do not enqueue on ACF Field Groups views
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group')))
        return;
    
    // ACF Extended: Repeater
    wp_enqueue_style('acf-extended-repeater', plugins_url('assets/acf-extended-repeater.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-repeater', plugins_url('assets/acf-extended-repeater.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content
    wp_enqueue_style('acf-extended-fc', plugins_url('assets/acf-extended-fc.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc', plugins_url('assets/acf-extended-fc.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Control
    wp_enqueue_style('acf-extended-fc-control', plugins_url('assets/acf-extended-fc-control.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-control', plugins_url('assets/acf-extended-fc-control.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Modal Select
    wp_enqueue_style('acf-extended-fc-modal-select', plugins_url('assets/acf-extended-fc-modal-select.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-modal-select', plugins_url('assets/acf-extended-fc-modal-select.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Modal Edit
    wp_enqueue_style('acf-extended-fc-modal-edit', plugins_url('assets/acf-extended-fc-modal-edit.css', ACFE_FILE), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-modal-edit', plugins_url('assets/acf-extended-fc-modal-edit.js', ACFE_FILE), array('jquery'), ACFE_VERSION);
    
    acf_localize_data(array(
        'close'	=> __('Close', 'acfe')
    ));
    
}