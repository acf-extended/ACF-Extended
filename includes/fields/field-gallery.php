<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_gallery')):

class acfe_field_gallery extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'gallery';
        
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
     * @return string
     */
    function format_front_value($formatted, $unformatted, $post_id, $field, $form){
        
        // vars
        $value = acf_get_array($unformatted);
        $array = array();
        
        // loop values
        foreach($value as $p_id){
            
            // get post
            $post = get_post($p_id);
            
            // validate
            if($post && !is_wp_error($post)){
                $array[] = get_the_title($post->ID);
            }
            
        }
        
        // merge
        return implode(', ', $array);
        
    }
    
}

acf_new_instance('acfe_field_gallery');

endif;