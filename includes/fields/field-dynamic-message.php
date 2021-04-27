<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_dynamic_message')):

class acfe_field_dynamic_message extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_dynamic_message';
        $this->label = __('Dynamic Message', 'acfe');
        $this->category = 'layout';
        
        parent::__construct();
        
    }
    
    function render_field($field){
    
        if(!isset($field['render']) || !is_callable($field['render']))
            return;
    
        call_user_func_array($field['render'], array($field));
    
    }
    
}

// initialize
acf_register_field_type('acfe_field_dynamic_message');

endif;