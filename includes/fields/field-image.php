<?php

if(!defined('ABSPATH'))
    exit;

add_filter('gettext', 'acfe_field_image_text', 99, 3);
function acfe_field_image_text($translated_text, $text, $domain){
    
    if($domain !== 'acf')
        return $translated_text;
    
    if($text === 'No image selected')
        return '';

    return $translated_text;
    
}

add_action('acf/render_field_settings/type=image', 'acfe_field_image_settings', 0);
function acfe_field_image_settings($field){
    
    acf_render_field_setting($field, array(
        'label'         => __('Uploader type'),
        'name'          => 'acfe_uploader',
        'key'           => 'acfe_uploader',
        'instructions'  => __('Choose the uploader type'),
        'type'          => 'radio',
        'choices'       => array(
            'wp'    => 'Media',
            'basic' => 'Basic',
        ),
        'allow_null'    => 0,
        'other_choice'  => 0,
        'default_value' => '',
        'layout'        => 'horizontal',
        'return_format' => 'value',
        'save_other_choice' => 0,
    ));
    
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

add_filter('acf/prepare_field/type=image', 'acfe_field_image_uploader_type');
function acfe_field_image_uploader_type($field){
	
	if(!acf_maybe_get($field, 'acfe_uploader'))
        return $field;
	
	// ACFE Form force uploader type
	if(acf_is_filter_enabled('acfe/form/uploader'))
		return $field;
	
	acf_update_setting('uploader', $field['acfe_uploader']);

    return $field;

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