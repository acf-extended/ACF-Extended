<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_image')):

class acfe_field_image extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'image';
        $this->defaults = array(
            'uploader'       => '',
            'acfe_thumbnail' => 0,
        );
        
        $this->add_filter('gettext',                         array($this, 'gettext'), 99, 3);
        $this->add_filter('acf/prepare_field/name=library',  array($this, 'prepare_library'));
        $this->add_field_action('acf/render_field_settings', array($this, 'render_field_settings_before'), 0);
        $this->add_field_action('acf/render_field_settings', array($this, 'render_field_settings_after'), 15);
        
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
        
        if($domain === 'acf'){
            if($text === 'No image selected'){
                return '';
            }
        }
        
        return $translated_text;
        
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
        if(acf_maybe_get($field['wrapper'], 'data-setting') === 'image'){
            
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
     * render_field_settings_before
     *
     * acf/render_field_settings:0
     *
     * @param $field
     */
    function render_field_settings_before($field){
        
        // uploader type
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
     * render_field_settings_after
     *
     * acf/render_field_settings:15
     *
     * @param $field
     */
    function render_field_settings_after($field){
    
        // featured thumbnail
        acf_render_field_setting($field, array(
            'label'         => __('Featured Thumbnail', 'acfe'),
            'name'          => 'acfe_thumbnail',
            'key'           => 'acfe_thumbnail',
            'instructions'  => __('Make this image the featured thumbnail', 'acfe'),
            'type'          => 'true_false',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'required'      => false,
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
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function update_value($value, $post_id, $field){
        
        // bail early setting
        if(!$field['acfe_thumbnail']){
            return $value;
        }
    
        // bail early when local meta
        if(acfe_is_local_post_id($post_id)){
            return $value;
        }
        
        // bail early on wp preview
        if(acf_maybe_get_POST('wp-preview') == 'dopreview'){
            return $value;
        }
    
        // bail early if not post
        $data = acf_get_post_id_info($post_id);
        
        if($data['type'] !== 'post'){
            return $value;
        }
        
        // update meta
        update_post_meta($post_id, '_thumbnail_id', $value);
        
        // return
        return $value;
        
    }
    
    
    /**
     * load_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function load_value($value, $post_id, $field){
    
        // bail early setting
        if(!$field['acfe_thumbnail']){
            return $value;
        }
    
        // bail early on wp preview
        if(acf_maybe_get_GET('preview') && filter_var(acf_maybe_get_GET('preview'), FILTER_VALIDATE_BOOLEAN)){
            return $value;
        }
    
        // bail early if not post
        $data = acf_get_post_id_info($post_id);
        
        if($data['type'] !== 'post'){
            return $value;
        }
        
        // return thumbnail
        return get_post_meta($post_id, '_thumbnail_id', true);
        
    }
    
}

acf_new_instance('acfe_field_image');

endif;