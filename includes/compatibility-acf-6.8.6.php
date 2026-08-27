<?php

if(!defined('ABSPATH')){
    exit;
}

// check version
if(!acfe_is_acf('6.8.6')){
    return;
}

if(!class_exists('acfe_compatibility_acf_686')):

class acfe_compatibility_acf_686{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('admin_body_class', array($this, 'admin_body_class'));
        
    }
    
    
    /**
     * admin_body_class
     *
     * @param $classes
     *
     * @return string
     */
    function admin_body_class($classes){
        
        // wp 7.0+
        if(acfe_is_wp('7.0') && acfe_get_setting('modules/wp7_ui')){
            $classes = str_replace('acf-admin-7-0', '', $classes);
        }
        
        // return normally
        return $classes;
        
    }
    
}

acf_new_instance('acfe_compatibility_acf_686');

endif;