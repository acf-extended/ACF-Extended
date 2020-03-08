<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_filter('acf/validate_value/name=acfe_form_custom_action',               array($this, 'validate'), 10, 4);
        
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_submit', array($this, 'advanced_submit'));
        
    }
    
    function validate($valid, $value, $field, $input){
        
        if(!$valid)
            return $valid;
        
        $reserved = array(
            'custom',
            'email',
            'post',
            'term',
        );
        
        if(in_array($value, $reserved))
            $valid = 'This action name is not authorized';
        
        return $valid;
    }
    
    function advanced_submit($field){
        
        ?>You may use the following hooks:<br /><br />
<pre>
/**
 * @array   $form       The form settings
 * @int     $post_id    Current post ID
 */
add_action('acfe/form/submit/my-custom-action', 'my_form_custom_action', 10, 2);
function my_form_custom_action($form, $post_id){
    
    // do something ...
    
}
</pre><?php
        
    }
    
}

new acfe_form_custom();

endif;