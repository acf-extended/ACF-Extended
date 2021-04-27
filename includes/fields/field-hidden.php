<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_hidden')):

class acfe_field_hidden extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_hidden';
        $this->label = __('Hidden', 'acfe');
        $this->category = 'basic';
        $this->defaults = array(
            'default_value' => ''
        );
        
        parent::__construct();
        
    }
    
    function prepare_field($field){
        
        $field['wrapper']['class'] = 'acf-hidden';
        
        return $field;
        
    }
    
    function render_field($field){
        
        ?>
        <input type="hidden" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" />
        <?php
        
    }
    
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting( $field, array(
            'label'         => __('Value','acf'),
            'instructions'  => __('Default value in the hidden input','acf'),
            'type'          => 'text',
            'name'          => 'default_value',
        ));
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_hidden');

endif;