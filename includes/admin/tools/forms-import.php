<?php

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/forms')){
    return;
}

if(!class_exists('acfe_dynamic_forms_import')):

class acfe_dynamic_forms_import extends acfe_module_import{
    
    /**
     * initialize
     *
     * @return void
     */
    function initialize(){
        
        // vars
        $this->hook = 'form';
        $this->name = 'acfe_dynamic_forms_import';
        $this->title = __('Import Forms');
        $this->description = __('Import Forms');
        $this->instance = acf_get_instance('acfe_dynamic_forms');
        $this->messages = array(
            'success_single'    => '1 form imported',
            'success_multiple'  => '%s forms imported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_forms_import');

endif;