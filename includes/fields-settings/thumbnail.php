<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Field Setting
 */
add_action('acf/render_field_settings/type=image', 'acfe_thumbnail_settings');
function acfe_thumbnail_settings($field){
    
    acf_render_field_setting($field, array(
        'label'         => __('Featured thumbnail'),
        'name'          => 'acfe_thumbnail',
        'key'           => 'acfe_thumbnail',
        'instructions'  => __('Make this image the featured thumbnail'),
        'type'          => 'true_false',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
        'required'      => false,
    ));
    
}

/**
 * Field Value Update
 */
add_filter('acf/update_value/type=image', 'acfe_thumbnail_update', 10, 3);
function acfe_thumbnail_update($value, $post_id, $field){
    
    if(!isset($field['acfe_thumbnail']) || empty($field['acfe_thumbnail']) || empty($value) || empty(get_post_type($post_id)))
        return $value;
    
    update_post_meta($post_id, '_thumbnail_id', $value);
    
    return $value;
    
}