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
    
    function render_field_settings($field){
        
        $field_name = 'field_name';
        if(acf_maybe_get($field, 'name'))
            $field_name = $field['name'];
        
        ob_start();
        ?>
        Write your own PHP/HTML content using the following hook:<br /><br />
<pre>
add_action('acf/render_field/name=<?php echo $field_name; ?>', 'my_acf_dynamic_message');
function my_acf_dynamic_message(){
    
    echo 'Hello World';
    
}
</pre>
        <?php
        
        $message = ob_get_clean();
        
        // field_type
        acf_render_field_setting($field, array(
            'label'			=> __('Instructions','acf'),
            'instructions'	=> '',
            'type'			=> 'message',
            'name'			=> 'instructions',
            'message'       => $message,
            'new_lines'     => false
        ));
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_dynamic_message');

endif;