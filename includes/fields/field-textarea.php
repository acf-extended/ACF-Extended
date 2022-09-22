<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_textarea')):

class acfe_field_textarea extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'textarea';
        $this->defaults = array(
            'acfe_textarea_code' => 0,
        );
        
    }
    
    
    /**
     * field_group_admin_head
     */
    function field_group_admin_head(){
        
        add_filter('acf/prepare_field/name=new_lines', function($field){
            
            // check setting
            if(acf_maybe_get($field['wrapper'], 'data-setting') === 'textarea'){
                
                $field['conditional_logic'] = array(
                    array(
                        array(
                            'field'     => 'acfe_textarea_code',
                            'operator'  => '!=',
                            'value'     => '1'
                        )
                    )
                );
                
            }
            
            return $field;
            
        });
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // code mode
        acf_render_field_setting($field, array(
            'label'         => __('Code mode'),
            'name'          => 'acfe_textarea_code',
            'key'           => 'acfe_textarea_code',
            'instructions'  => __('Switch font family to monospace and allow tab indent. For a more advanced code editor, please use the <code>Code Editor</code> field type', 'acfe'),
            'type'          => 'true_false',
            'ui'            => 1,
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
        
        // code mode
        if($field['acfe_textarea_code']){
            $wrapper['data-acfe-textarea-code'] = 1;
        }
    
        return $wrapper;
        
    }
    
}

acf_new_instance('acfe_field_textarea');

endif;