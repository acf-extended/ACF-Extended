<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/options_pages'))
    return;

if(!class_exists('acfe_dynamic_options_pages_export')):

class acfe_dynamic_options_pages_export extends acfe_module_export{
    
    function initialize(){
        
        // vars
        $this->name = 'acfe_dynamic_options_pages_export';
        $this->title = __('Export Options Pages');
        $this->description = __('Export Options Pages');
        $this->select = __('Select Options Pages');
        $this->default_action = 'json';
        $this->allowed_actions = array('json', 'php');
        $this->instance = acf_get_instance('acfe_dynamic_options_pages');
        $this->file = 'options-page';
        $this->files = 'options-pages';
        $this->messages = array(
            'not_found'         => __('No options page available.'),
            'not_selected'      => __('No options pages selected'),
            'success_single'    => '1 options page exported',
            'success_multiple'  => '%s options pages exported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_options_pages_export');

endif;