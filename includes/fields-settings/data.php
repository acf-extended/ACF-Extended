<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Setting: Field Data
 */
add_action('acf/render_field_settings', 'acfe_settings_field_data', 992);
function acfe_settings_field_data($field){
    
    if(!isset($field['ID']) || $field['ID'] === 'acfcloneindex')
        return;
    
    $acfe_field_data_id = false;
    if($field['ID'] != 'acfcloneindex')
        $acfe_field_data_id = $field['ID'];
    
    acf_render_field_setting($field, array(
        'label'         => false,
        'instructions'  => '',
        'type'          => 'acfe_dynamic_message',
        'required'      => false,
        'name'          => 'acfe_field_data',
        'key'           => 'acfe_field_data',
        'value'         => $acfe_field_data_id,
    ), true);
    
}

/**
 * Render: Field Data
 */
add_filter('acf/render_field/name=acfe_field_data', 'acfe_render_field_data');
function acfe_render_field_data($field){
    
    $acfe_field_data_id = $field['value'];
    
    $get_field = acf_get_field($acfe_field_data_id);
    $get_field_debug = '<pre style="margin-bottom:15px;">' . print_r($get_field, true) . '</pre>';

    if(!$get_field)
        $get_field_debug = '<pre>Field data unavailable</pre>';

    $get_post = get_post($acfe_field_data_id);
    $get_post_debug = '<pre>' . print_r($get_post, true) . '</pre>';
    
    if(!$get_post || $get_post->post_type !== 'acf-field'){
        $get_post_debug = '<pre>Post object unavailable</pre>';
    }
    
    $button = '<a href="#" class="button acfe_modal_open" style="margin-left:5px;" data-modal-key="' . $acfe_field_data_id . '">' . __('Data') . '</a>';
    if(!$get_field && !$get_post)
        $button = '<a href="#" class="button disabled" disabled>' . __('Data') . '</a>';
    
    echo $button . '<div class="acfe-modal" data-modal-key="' . $acfe_field_data_id . '"><div style="padding:15px;">' . $get_field_debug . $get_post_debug . '</div></div>';
    
}