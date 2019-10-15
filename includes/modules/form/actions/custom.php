<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_action('acfe/form/submit/action/custom', array($this, 'submit'), 1, 3);
        
    }
    
    function submit($form, $post_id, $acf){
        
        $action_name = get_sub_field('acfe_form_custom_action');
        
        do_action($action_name, $form, $post_id);
        
    }
    
}

new acfe_form_custom();

endif;