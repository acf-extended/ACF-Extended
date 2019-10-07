<?php

if(!defined('ABSPATH'))
    exit;

add_filter('gettext', 'acfe_field_file_text', 99, 3);
function acfe_field_file_text($translated_text, $text, $domain){
    
    if($domain !== 'acf')
        return $translated_text;
    
    if($text === 'No file selected')
        return '';

    return $translated_text;
    
}

add_action('acf/render_field_settings/type=file', 'acfe_field_file_settings', 0);
function acfe_field_file_settings($field){
    
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
    
}

add_filter('acf/prepare_field/type=file', 'acfe_field_file_uploader_type');
function acfe_field_file_uploader_type($field){

    if(!isset($field['acfe_uploader']) || empty($field['acfe_uploader']))
        return $field;

    acf_update_setting('uploader', $field['acfe_uploader']);

    return $field;

}