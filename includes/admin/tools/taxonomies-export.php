<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/taxonomies'))
    return;

if(!class_exists('acfe_dynamic_taxonomies_export')):

class acfe_dynamic_taxonomies_export extends acfe_module_export{
    
    function initialize(){
        
        // vars
        $this->name = 'acfe_dynamic_taxonomies_export';
        $this->title = __('Export Taxonomies');
        $this->description = __('Export Taxonomies');
        $this->select = __('Select Taxonomies');
        $this->default_action = 'json';
        $this->allowed_actions = array('json', 'php');
        $this->instance = acf_get_instance('acfe_dynamic_taxonomies');
        $this->file = 'taxonomy';
        $this->files = 'taxonomies';
        $this->messages = array(
            'not_found'         => __('No taxonomy available.'),
            'not_selected'      => __('No taxonomies selected'),
            'success_single'    => '1 taxonomy exported',
            'success_multiple'  => '%s taxonomies exported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_taxonomies_export');

endif;