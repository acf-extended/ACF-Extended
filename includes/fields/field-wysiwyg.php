<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_free_field_wysiwyg')):

class acfe_free_field_wysiwyg{
    
    function __construct(){
        
        add_filter('acfe/field_wrapper_attributes/type=wysiwyg', array($this, 'field_wrapper'), 10, 2);
        
    }
    
    /*
     * Field Wrapper
     */
    function field_wrapper($wrapper, $field){
    
        // auto init
        if(acf_maybe_get($field, 'acfe_wysiwyg_auto_init')){
        
            $wrapper['data-acfe-wysiwyg-auto-init'] = $field['acfe_wysiwyg_auto_init'];
        
        }
        
        return $wrapper;
        
    }
    
}

new acfe_free_field_wysiwyg();

endif;