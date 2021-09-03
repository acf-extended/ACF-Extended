<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_image')):

class acfe_field_image{
    
    function __construct(){
        
        add_filter('gettext',                               array($this, 'gettext'), 99, 3);
        add_filter('acf/validate_field/type=image',         array($this, 'validate_field'), 20);
        add_action('acf/render_field_settings/type=image',  array($this, 'render_field_settings'), 0);
        add_filter('acf/prepare_field/type=image',          array($this, 'prepare_field'));
        add_filter('acf/prepare_field/name=library',        array($this, 'prepare_library'));
    
        add_filter('acf/update_value/type=image',           array($this, 'update_value'), 10, 3);
        add_filter('acf/load_value/type=image',             array($this, 'load_value'), 10, 3);
        
    }
    
    function prepare_library($field){
        
        if(acf_maybe_get($field['wrapper'], 'data-setting') !== 'image') return $field;
        
        $field['conditional_logic'] = array(
            array(
                array(
                    'field'     => 'uploader',
                    'operator'  => '==',
                    'value'     => 'wp',
                )
            )
        );
        
        return $field;
        
    }
    
    function gettext($translated_text, $text, $domain){
        
        if($domain !== 'acf') return $translated_text;
    
        if($text === 'No image selected') return '';
        
        return $translated_text;
        
    }
    
    function validate_field($field){
        
        if(!acf_maybe_get($field, 'acfe_uploader')) return $field;
        
        $field['uploader'] = $field['acfe_uploader'];
        unset($field['acfe_uploader']);
        
        return $field;
        
    }
    
    function render_field_settings($field){
        
        acf_render_field_setting($field, array(
            'label'         => __('Uploader type'),
            'name'          => 'uploader',
            'key'           => 'uploader',
            'instructions'  => __('Choose the uploader type'),
            'type'          => 'radio',
            'choices'       => array(
                ''      => 'Default',
                'wp'    => 'WordPress',
                'basic' => 'Browser',
            ),
            'default_value' => '',
            'layout'        => 'horizontal',
            'return_format' => 'value',
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
    
    function prepare_field($field){
            
        // ACFE Form force uploader type
        if(acf_is_filter_enabled('acfe/form/uploader')){
            unset($field['uploader']);
        }
        
        if(!acf_maybe_get($field, 'uploader')){
            $field['uploader'] = acf_get_setting('uploader');
        }
        
        if(!current_user_can('upload_files')){
            $field['uploader'] = 'basic';
        }
    
        acf_update_setting('uploader', $field['uploader']);
        
        return $field;
        
    }
    
    function update_value($value, $post_id, $field){
        
        // Bail early if no thumbnail setting
        if(!acf_maybe_get($field, 'acfe_thumbnail')){
            return $value;
        }
    
        // Bail early if local meta
        if(acfe_is_local_post_id($post_id)){
            return $value;
        }
        
        // Bail early if wp preview
        if(acf_maybe_get_POST('wp-preview') == 'dopreview'){
            return $value;
        }
    
        // Bail early if not post
        $data = acf_get_post_id_info($post_id);
        
        if($data['type'] !== 'post'){
            return $value;
        }
        
        update_post_meta($post_id, '_thumbnail_id', $value);
        
        return $value;
        
    }
    
    function load_value($value, $post_id, $field){
        
        if(!acf_maybe_get($field, 'acfe_thumbnail')){
            return $value;
        }
    
        if(acf_maybe_get_GET('preview') && filter_var(acf_maybe_get_GET('preview'), FILTER_VALIDATE_BOOLEAN)){
            return $value;
        }
        
        $data = acf_get_post_id_info($post_id);
        
        if($data['type'] !== 'post'){
            return $value;
        }
        
        $value = get_post_meta($post_id, '_thumbnail_id', true);
        
        return $value;
        
    }
    
}

new acfe_field_image();

endif;