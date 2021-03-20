<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/block_types'))
    return;

if(!class_exists('acfe_dynamic_block_types_export')):

class acfe_dynamic_block_types_export extends acfe_module_export{
    
    function initialize(){
        
        // vars
        $this->name = 'acfe_dynamic_block_types_export';
        $this->title = __('Export Block Types');
        $this->description = __('Export Block Types');
        $this->select = __('Select Block Types');
        $this->default_action = 'json';
        $this->allowed_actions = array('json', 'php');
        $this->instance = acf_get_instance('acfe_dynamic_block_types');
        $this->file = 'block-type';
        $this->files = 'block-types';
        $this->messages = array(
            'not_found'         => __('No block type available.'),
            'not_selected'      => __('No block types selected'),
            'success_single'    => '1 block type exported',
            'success_multiple'  => '%s block types exported',
        );
        
    }
    
}

acf_register_admin_tool('acfe_dynamic_block_types_export');

endif;