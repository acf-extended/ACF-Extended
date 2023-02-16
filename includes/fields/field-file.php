<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_file')):

class acfe_field_file extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'file';
        $this->defaults = array(
            'uploader' => '',
        );
    
        $this->add_filter('gettext',                         array($this, 'gettext'), 99, 3);
        $this->add_filter('acf/prepare_field/name=min_size', array($this, 'prepare_size'));
        $this->add_filter('acf/prepare_field/name=max_size', array($this, 'prepare_size'));
        $this->add_filter('acf/prepare_field/name=library',  array($this, 'prepare_library'));
    
        $this->add_field_action('acf/render_field_settings', array($this, '_render_field_settings'), 0);
        
    }
    
    
    /**
     * gettext
     *
     * @param $translated_text
     * @param $text
     * @param $domain
     *
     * @return string
     */
    function gettext($translated_text, $text, $domain){
        
        if($domain === 'acf' && $text === 'No file selected'){
            return '';
        }
        
        return $translated_text;
        
    }
    
    
    /**
     * prepare_size
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_size($field){
        
        if(acf_maybe_get($field['wrapper'], 'data-setting') === 'file'){
            
            switch($field['_name']){
                
                case 'min_size': {
    
                    $field['label'] = __('File size', 'acf');
                    $field['prepend'] = __('Min size', 'acfe');
                    break;
                    
                }
    
                case 'max_size': {
    
                    $field['prepend'] = __('Max size', 'acfe');
                    $field['wrapper']['data-append'] = 'min_size';
                    break;
        
                }
                
            }
        
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_library
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_library($field){
    
        // check if field group ui setting
        if(acf_maybe_get($field['wrapper'], 'data-setting') === 'file'){
        
            // add conditional logic
            $field['conditional_logic'] = array(
                array(
                    array(
                        'field'     => 'uploader',
                        'operator'  => '==',
                        'value'     => 'wp',
                    )
                )
            );
        
        }
        
        return $field;
        
    }
    
    
    /**
     * _render_field_settings
     *
     * acf/render_field_settings:0
     *
     * @param $field
     */
    function _render_field_settings($field){
        
        // uploader
        acf_render_field_setting($field, array(
            'label'         => __('Uploader Type', 'acfe'),
            'name'          => 'uploader',
            'key'           => 'uploader',
            'instructions'  => __('Choose the uploader type', 'acfe'),
            'type'          => 'radio',
            'choices'       => array(
                ''      => __('Default', 'acf'),
                'wp'    => 'WordPress',
                'basic' => __('Browser', 'acfe'),
            ),
            'default_value' => '',
            'layout'        => 'horizontal',
            'return_format' => 'value',
        ));
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
        
        // let acfe form force specific uploader
        if(acf_is_filter_enabled('acfe/form/uploader')){
            $field['uploader'] = '';
        }
    
        // default uploader in settings
        // use global acf uploader
        if(empty($field['uploader'])){
            $field['uploader'] = acf_get_setting('uploader');
        }
    
        // current user can't upload files
        // force basic
        if(!current_user_can('upload_files')){
            $field['uploader'] = 'basic';
        }
    
        // update global uploader
        acf_update_setting('uploader', $field['uploader']);
    
        // return
        return $field;
        
    }

}

acf_new_instance('acfe_field_file');

endif;