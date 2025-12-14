<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_post_object')):

class acfe_field_post_object extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'post_object';
        $this->defaults = array(
            'save_custom'      => 0,
            'save_post_type'   => '',
            'save_post_status' => '',
        );
        
        // hooks
        $this->add_field_filter('acf/update_value', array($this, '_update_value'), 5, 3);
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        // save custom value
        acf_render_field_setting($field, array(
            'label'         => __('Allow & Save Custom value', 'acf'),
            'instructions'  => '',
            'name'          => 'save_custom',
            'type'          => 'true_false',
            'ui'            => 1,
            'message'       => __("Save 'custom' values as new post", 'acf'),
        ));
    
        // save post_type
        acf_render_field_setting($field, array(
            'label'             => __('New Post Arguments', 'acf'),
            'instructions'      => __('See available hooks in the <a href="https://www.acf-extended.com/features/fields/post-object#custom-value-hooks" target="_blank">documentation</a>.', 'acfe'),
            'name'              => 'save_post_type',
            'type'              => 'acfe_post_types',
            'field_type'        => 'select',
            'conditional_logic' => array(
                'field'     => 'save_custom',
                'operator'  => '==',
                'value'     => 1
            )
        ));
    
        // save post_status
        acf_render_field_setting($field, array(
            'label'             => '',
            'instructions'      => '',
            'name'              => 'save_post_status',
            'type'              => 'acfe_post_statuses',
            'field_type'        => 'select',
            'conditional_logic' => array(
                'field'     => 'save_custom',
                'operator'  => '==',
                'value'     => 1
            ),
            '_append'           => 'save_post_type'
        ));
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function field_wrapper_attributes($wrapper, $field){
    
        if($field['save_custom']){
            $wrapper['data-acfe-allow-custom'] = 1;
        }
    
        return $wrapper;
        
    }
    
    
    /**
     * _update_value
     *
     * acf/update_value:5
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|false|mixed|string[]
     */
    function _update_value($value, $post_id, $field){
    
        // bail early if empty
        if(empty($value)){
            return $value;
        }
        
        // bail early if no save custom setting
        if(!$field['save_custom']){
            return $value;
        }
    
        // bail early when local meta
        if(acfe_is_local_post_id($post_id)){
            return $value;
        }
    
        // new post args
        $post_type = acf_maybe_get($field, 'save_post_type', 'post');
        $post_status = acf_maybe_get($field, 'save_post_status', 'publish');
        
        // vars
        $is_array = is_array($value);
        $value = acf_get_array($value);
        
        // loop
        foreach($value as $k => $v){
            
            // has to be words
            // (post id are selected posts)
            if(is_numeric($v)){
                continue;
            }
            
            // vars
            $title = $v;
        
            // args
            $args = array(
                'post_title'  => $title,
                'post_type'   => $post_type,
                'post_status' => $post_status,
            );
        
            // filters
            $args = apply_filters("acfe/fields/post_object/custom_save_args",                       $args, $title, $post_id, $field);
            $args = apply_filters("acfe/fields/post_object/custom_save_args/name={$field['name']}", $args, $title, $post_id, $field);
            $args = apply_filters("acfe/fields/post_object/custom_save_args/key={$field['key']}",   $args, $title, $post_id, $field);
            
            // do not create post
            if($args === false){
            
                unset($value[ $k ]);
                continue;
            
            }
        
            // insert post
            $_post_id = wp_insert_post($args);
            
            // error during creation
            if(empty($_post_id) || is_wp_error($_post_id)){
            
                unset($value[ $k ]);
                continue;
            
            }
        
            // actions after create
            do_action("acfe/fields/post_object/custom_save",                       $_post_id, $title, $post_id, $field);
            do_action("acfe/fields/post_object/custom_save/name={$field['name']}", $_post_id, $title, $post_id, $field);
            do_action("acfe/fields/post_object/custom_save/key={$field['key']}",   $_post_id, $title, $post_id, $field);
            
            // assign new post id as selected
            $value[ $k ] = $_post_id;
        
        }
        
        // check array
        if(!$is_array){
            $value = acfe_unarray($value);
        }
        
        // return
        return $value;
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['save_custom'])){
            return $valid;
        }
        
        // vars
        $value = acf_get_array($value);
        
        // loop values
        foreach($value as $v){
            
            // get post
            $post = get_post($v);
            
            // check post exists
            if(!$post || is_wp_error($post)){
                return false;
            }
            
            // check query method exists
            if(method_exists($this->instance, 'get_ajax_query')){
                
                // query post object ajax query
                $query = $this->instance->get_ajax_query(array(
                    'field_key' => $field['key'],
                    'post_id'   => $form['post_id'],
                    'include'   => $v,
                ));
                
                // return false if no results
                if(empty($query)){
                    return false;
                }
                
            }
            
        }
        
        // return
        return $valid;
        
    }
    
}

acf_new_instance('acfe_field_post_object');

endif;