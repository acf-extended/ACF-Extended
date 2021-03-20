<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/block_types'))
    return;

if(!class_exists('acfe_dynamic_block_types_import')):

class acfe_dynamic_block_types_import extends acfe_module_import{
    
    function initialize(){
        
        // vars
        $this->hook = 'block_type';
        $this->name = 'acfe_dynamic_block_types_import';
        $this->title = __('Import Block Types');
        $this->description = __('Import Block Types');
        $this->instance = acf_get_instance('acfe_dynamic_block_types');
        $this->messages = array(
            'success_single'    => '1 block type imported',
            'success_multiple'  => '%s block types imported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_block_types_import');

endif;