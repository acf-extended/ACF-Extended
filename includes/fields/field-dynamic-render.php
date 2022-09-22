<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_dynamic_render')):

class acfe_field_dynamic_render extends acf_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_dynamic_render';
        $this->label = __('Dynamic Render', 'acfe');
        $this->category = 'layout';
        $this->defaults = array(
            'render' => ''
        );
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // check callback
        if(is_callable($field['render'])){
            call_user_func_array($field['render'], array($field));
        }
    
    }
    
}

// initialize
acf_register_field_type('acfe_field_dynamic_render');

endif;