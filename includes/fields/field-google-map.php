<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_google_map')):

class acfe_field_google_map extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'google_map';
        
    }
    
    
    /**
     * format_front_value
     *
     * @param $formatted
     * @param $unformatted
     * @param $post_id
     * @param $field
     * @param $form
     *
     * @return mixed|null
     */
    function format_front_value($formatted, $unformatted, $post_id, $field, $form){
        
        if(is_string($formatted)){
            $formatted = json_decode(wp_unslash($formatted), true);
        }
        
        $formatted = acf_get_array($formatted);
        
        return acf_maybe_get($formatted, 'address');
        
    }
    
    
}

acf_new_instance('acfe_field_google_map');

endif;