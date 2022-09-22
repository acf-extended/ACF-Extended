<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_wysiwyg')):

class acfe_field_wysiwyg extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'wysiwyg';
        
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
    
        // auto init
        if(acf_maybe_get($field, 'acfe_wysiwyg_auto_init')){
            $wrapper['data-acfe-wysiwyg-auto-init'] = $field['acfe_wysiwyg_auto_init'];
        }
        
        return $wrapper;
        
    }
    
}

acf_new_instance('acfe_field_wysiwyg');

endif;