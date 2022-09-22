<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field')):

class acfe_field extends acf_field{
    
    /**
     * construct
     */
    function __construct(){
    
        // parent construct
        parent::__construct();
        
        // field
        $this->add_field_filter('acfe/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2);
        $this->add_field_filter('acfe/load_fields',              array($this, 'load_fields'),              10, 2);

    }
    
}

endif;