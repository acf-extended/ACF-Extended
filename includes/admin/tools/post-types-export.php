<?php 

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/post_types')){
    return;
}

if(!class_exists('acfe_dynamic_post_types_export')):

class acfe_dynamic_post_types_export extends acfe_module_export{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
    
        // vars
        $this->name = 'acfe_dynamic_post_types_export';
        $this->title = __('Export Post Types');
        $this->description = __('Export Post Types');
        $this->select = __('Select Post Types');
        $this->default_action = 'json';
        $this->allowed_actions = array('json', 'php');
        $this->instance = acf_get_instance('acfe_dynamic_post_types');
        $this->file = 'post-type';
        $this->files = 'post-types';
        $this->messages = array(
            'not_found'         => __('No post type available.'),
            'not_selected'      => __('No post types selected'),
            'success_single'    => '1 post type exported',
            'success_multiple'  => '%s post types exported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_post_types_export');

endif;