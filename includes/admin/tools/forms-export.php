<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/forms'))
    return;

if(!class_exists('acfe_dynamic_forms_export')):

class acfe_dynamic_forms_export extends acfe_module_export{
    
    function initialize(){
        
        // vars
        $this->name = 'acfe_dynamic_forms_export';
        $this->title = __('Export Forms');
        $this->description = __('Export Forms');
        $this->select = __('Select Forms');
        $this->default_action = 'json';
        $this->allowed_actions = array('json');
        $this->instance = acf_get_instance('acfe_dynamic_forms');
        $this->file = 'form';
        $this->files = 'forms';
        $this->messages = array(
            'not_found'         => __('No form available.'),
            'not_selected'      => __('No forms selected'),
            'success_single'    => '1 form exported',
            'success_multiple'  => '%s forms exported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_forms_export');

endif;