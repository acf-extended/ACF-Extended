<?php

if(!defined('ABSPATH'))
    exit;

add_action('acf/render_field_settings/type=post_object', 'acfe_field_post_object_settings');
function acfe_field_post_object_settings($field){
    
    // other_choice
    acf_render_field_setting($field, array(
        'label'         => __('Allow Custom','acf'),
        'instructions'  => '',
        'name'          => 'allow_custom',
        'type'          => 'true_false',
        'ui'            => 1,
        'message'       => __("Allow 'custom' values to be added", 'acf'),
    ));
    
    // save other_choice
    acf_render_field_setting($field, array(
        'label'			=> __('Save Custom','acf'),
        'instructions'	=> '',
        'name'			=> 'save_custom',
        'type'			=> 'true_false',
        'ui'			=> 1,
        'message'		=> __("Save 'custom' values as new post", 'acf'),
        'conditions'	=> array(
            'field'		=> 'allow_custom',
            'operator'	=> '==',
            'value'		=> 1
        )
    ));
    
    // save post_type
    acf_render_field_setting($field, array(
        'label'			=> __('New Post Arguments','acf'),
        'instructions'	=> '',
        'name'			=> 'save_post_type',
        'type'			=> 'acfe_post_types',
        'field_type'    => 'select',
        'conditions'	=> array(
            'field'		=> 'save_custom',
            'operator'	=> '==',
            'value'		=> 1
        )
    ));
    
    // save post_status
    acf_render_field_setting($field, array(
        'label'			=> '',
        'instructions'	=> '',
        'name'			=> 'save_post_status',
        'type'			=> 'acfe_post_statuses',
        'field_type'    => 'select',
        'conditions'	=> array(
            'field'		=> 'save_custom',
            'operator'	=> '==',
            'value'		=> 1
        ),
        '_append'       => 'save_post_type'
    ));
    
}

add_filter('acfe/field_wrapper_attributes/type=post_object', 'acfe_field_post_object_wrapper', 10, 2);
function acfe_field_post_object_wrapper($wrapper, $field){
    
    if(acf_maybe_get($field, 'allow_custom')){
        
        $wrapper['data-acfe-allow-custom'] = 1;
        
    }
    
    return $wrapper;
    
}

add_filter('acf/update_value/type=post_object', 'acfe_field_post_object_update_value', 5, 3);
function acfe_field_post_object_update_value($value, $post_id, $field){
    
    // Allow + save custom value
    if(empty(acf_maybe_get($field, 'allow_custom')) || empty(acf_maybe_get($field, 'save_custom')))
        return $value;
    
    // Bail early if value is numeric (normal behavior)
    if(is_numeric($value))
        return $value;
    
    $value_slug = sanitize_title($value);
    $value_post_type = 'post';
    $value_post_status = 'publish';
    
    if(acf_maybe_get($field, 'save_post_type'))
        $value_post_type = $field['save_post_type'];
    
    if(acf_maybe_get($field, 'save_post_status'))
        $value_post_status = $field['save_post_status'];
    
    $get_post = get_page_by_path($value_slug, OBJECT, $value_post_type);
    
    // Post name with value has been found, return ID
    if(!empty($get_post))
        return $get_post->ID;
    
    // Create new post
    $args = array(
        'post_title'    => $value,
        'post_type'     => $value_post_type,
        'post_status'   => $value_post_status,
    );
    
    // Allow filters
    $args = apply_filters('acfe/fields/post_object/custom_save_args',                           $args, $value, $post_id, $field);
    $args = apply_filters('acfe/fields/post_object/custom_save_args/name=' . $field['name'],    $args, $value, $post_id, $field);
    $args = apply_filters('acfe/fields/post_object/custom_save_args/key=' . $field['key'],      $args, $value, $post_id, $field);
    
    // Insert post
    $_post_id = wp_insert_post($args);
    
    if(empty($_post_id) || is_wp_error($_post_id))
        return $value;
    
    // Allow actions after insert
    do_action('acfe/fields/post_object/custom_save',                           $_post_id, $value, $post_id, $field);
    do_action('acfe/fields/post_object/custom_save/name=' . $field['name'],    $_post_id, $value, $post_id, $field);
    do_action('acfe/fields/post_object/custom_save/key=' . $field['key'],      $_post_id, $value, $post_id, $field);
    
    return $_post_id;
    
}