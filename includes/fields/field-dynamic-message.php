<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_dynamic_message extends acf_field{
    
    function __construct(){
        $this->name = 'acfe_dynamic_message';
        $this->label = __('Dynamic Message', 'acfe');
        $this->category = 'layout';
        
        parent::__construct();
    }
    
}

new acfe_field_dynamic_message();