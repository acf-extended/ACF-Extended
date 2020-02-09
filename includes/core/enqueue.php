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
    wp_enqueue_script('acf-extended', acfe_get_url('assets/acf-extended.js'), array('jquery'), ACFE_VERSION);
    wp_enqueue_style('acf-extended', acfe_get_url('assets/acf-extended.css'), false, ACFE_VERSION);
    wp_enqueue_style('acf-extended-admin', acfe_get_url('assets/acf-extended-admin.css'), false, ACFE_VERSION);
    
    // Better Taxonomies
    if(acf_get_setting('acfe/modules/taxonomies')){
        
        wp_enqueue_style('acf-extended-taxonomies', acfe_get_url('assets/acf-extended-taxonomies.css'), false, ACFE_VERSION);
        
    }

    // ACF Extended: Field Groups only
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
        
        wp_enqueue_script('acf-extended-fg', acfe_get_url('assets/acf-extended-fg.js'), array('jquery'), ACFE_VERSION);
        wp_enqueue_style('acf-extended-fg', acfe_get_url('assets/acf-extended-fg.css'), false, ACFE_VERSION);
    
    }
    
}

/**
 * Admin + WP: Enqueue where ACF is loaded
 */
add_action('acf/enqueue_scripts', 'acfe_enqueue_scripts');
function acfe_enqueue_scripts(){
    
    // ACF Extended
    wp_enqueue_script('acf-extended', acfe_get_url('assets/acf-extended.js'), array('jquery'), ACFE_VERSION);
    wp_enqueue_style('acf-extended', acfe_get_url('assets/acf-extended.css'), false, ACFE_VERSION);
    
    // ACF Extended: Fields
    wp_enqueue_script('acf-extended-fields', acfe_get_url('assets/acf-extended-fields.js'), array('jquery'), ACFE_VERSION);
    
    // Front only
    if(!is_admin()){
        
        wp_enqueue_script('acf-extended-form', acfe_get_url('assets/acf-extended-form.js'), array('jquery'), ACFE_VERSION);
        
    }
    
}

/**
 * Admin: Enqueue where ACF is loaded
 */
add_action('acf/input/admin_enqueue_scripts', 'acfe_enqueue_admin_input_scripts');
function acfe_enqueue_admin_input_scripts(){
    
    // ACF Extended: Modal
    wp_enqueue_style('acf-extended-modal', acfe_get_url('assets/acf-extended-modal.css'), false, ACFE_VERSION);
    
    // Do not enqueue on ACF Field Groups views
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group')))
        return;
    
    // ACF Extended: Repeater
    wp_enqueue_style('acf-extended-repeater', acfe_get_url('assets/acf-extended-repeater.css'), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-repeater', acfe_get_url('assets/acf-extended-repeater.js'), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content
    wp_enqueue_style('acf-extended-fc', acfe_get_url('assets/acf-extended-fc.css'), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc', acfe_get_url('assets/acf-extended-fc.js'), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Control
    wp_enqueue_style('acf-extended-fc-control', acfe_get_url('assets/acf-extended-fc-control.css'), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-control', acfe_get_url('assets/acf-extended-fc-control.js'), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Modal Select
    wp_enqueue_style('acf-extended-fc-modal-select', acfe_get_url('assets/acf-extended-fc-modal-select.css'), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-modal-select', acfe_get_url('assets/acf-extended-fc-modal-select.js'), array('jquery'), ACFE_VERSION);
    
    // ACF Extended: Flexible Content Modal Edit
    wp_enqueue_style('acf-extended-fc-modal-edit', acfe_get_url('assets/acf-extended-fc-modal-edit.css'), false, ACFE_VERSION);
    wp_enqueue_script('acf-extended-fc-modal-edit', acfe_get_url('assets/acf-extended-fc-modal-edit.js'), array('jquery'), ACFE_VERSION);
    
    acf_localize_text(array(
        'Close'	=> __('Close', 'acf'),
    ));
    
}