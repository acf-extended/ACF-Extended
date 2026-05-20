<?php

if(!defined('ABSPATH')){
    exit;
}

// check version
if(!acfe_is_acf('5.9', '6.1')){
    return;
}

if(!class_exists('acfe_compatibility_acf_59')):

class acfe_compatibility_acf_59{
    
    /**
     * construct
     */
    function __construct(){

        add_filter('acfe/acf_admin_navigation_page', array($this, 'acf_admin_navigation_page'));
        add_filter('acfe/acf_admin_body_class',      array($this, 'acf_admin_body_class'));
        
    }
    
    
    /**
     * acf_admin_navigation_page
     *
     * @param $page
     *
     * @return string
     */
    function acf_admin_navigation_page($page){
        return 'html-admin-navigation';
    }
    
    
    /**
     * acf_admin_body_class
     *
     * @param $new_class
     *
     * @return string
     */
    function acf_admin_body_class($new_class){
        
        // remove class before acf 6.0
        if(!acfe_is_acf('6.0')){
            return '';
        }
        
        // return normally
        return $new_class;
        
    }
    
}

acf_new_instance('acfe_compatibility_acf_59');

endif;