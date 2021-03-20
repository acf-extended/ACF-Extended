<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_file')):

class acfe_field_file{
    
    function __construct(){
        
        add_filter('gettext',                               array($this, 'gettext'), 99, 3);
        add_action('acf/include_admin_tools',               array($this, 'acf_admin_tools'));
        add_filter('acf/validate_field/type=file',          array($this, 'validate_field'), 20);
        add_action('acf/render_field_settings/type=file',   array($this, 'render_field_settings'), 0);
        add_filter('acf/prepare_field/type=file',           array($this, 'prepare_field'));
    
        add_filter('acf/prepare_field/name=min_size',       array($this, 'prepare_min_max_size'));
        add_filter('acf/prepare_field/name=max_size',       array($this, 'prepare_min_max_size'));
        add_filter('acf/prepare_field/name=library',        array($this, 'prepare_library'));
        
    }
    
    function prepare_min_max_size($field){
        
        if(acf_maybe_get($field['wrapper'], 'data-setting') !== 'file')
            return $field;
        
        if($field['_name'] === 'min_size'){
    
            $field['label'] = __('File size', 'acf');
            $field['prepend'] = 'Min size';
            
        }elseif($field['_name'] === 'max_size'){
    
            $field['prepend'] = 'Max size';
            $field['wrapper']['data-append'] = 'min_size';
            
        }
        
        return $field;
        
    }
    
    function prepare_library($field){
        
        if(acf_maybe_get($field['wrapper'], 'data-setting') !== 'file')
            return $field;
        
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
    
    function acf_admin_tools(){
        
        // Do not remove "No file selected" in the ACF Admin Tool
        remove_filter('gettext', array($this, 'gettext'), 99);
        
    }
    
    function gettext($translated_text, $text, $domain){
        
        if($domain !== 'acf')
            return $translated_text;
        
        if($text === 'No file selected')
            return '';
        
        return $translated_text;
        
    }
    
    function validate_field($field){
        
        if(!acf_maybe_get($field, 'acfe_uploader'))
            return $field;
        
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
        
    }
    
    function prepare_field($field){
        
        // ACFE Form force uploader type
        if(acf_is_filter_enabled('acfe/form/uploader'))
            acfe_unset($field, 'uploader');
        
        if(!acf_maybe_get($field, 'uploader'))
            $field['uploader'] = acf_get_setting('uploader');
    
        if(!current_user_can('upload_files'))
            $field['uploader'] = 'basic';
        
        acf_update_setting('uploader', $field['uploader']);
        
        return $field;
        
    }

}

new acfe_field_file();

endif;