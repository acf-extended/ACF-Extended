<?php 

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/post_types')){
    return;
}

if(!class_exists('acfe_dynamic_post_types_import')):

class acfe_dynamic_post_types_import extends acfe_module_import{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        // vars
        $this->hook = 'post_type';
        $this->name = 'acfe_dynamic_post_types_import';
        $this->title = __('Import Post Types');
        $this->description = __('Import Post Types');
        $this->instance = acf_get_instance('acfe_dynamic_post_types');
        $this->messages = array(
            'success_single'    => '1 post type imported',
            'success_multiple'  => '%s post types imported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_post_types_import');

endif;