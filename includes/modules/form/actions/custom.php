<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_filter('acf/validate_value/name=acfe_form_custom_action',                   array($this, 'validate'), 10, 4);
        
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_load',       array($this, 'advanced_load'));
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_validation', array($this, 'advanced_validation'));
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_submit',     array($this, 'advanced_submit'));
        
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
    
    function advanced_load($field){
	
	    $form_name = 'my_form';
	
	    if(acf_maybe_get($field, 'value'))
		    $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_filter('acfe/form/load/my-custom-action', 'my_form_custom_action_load', 10, 3);
add_filter('acfe/form/load/my-custom-action/form=<?php echo $form_name; ?>', 'my_form_custom_action_load', 10, 3);</pre>
<br />
<pre>
/**
 * @array   $form       The form settings
 * @int     $post_id    Current post ID
 * @string  $alias      Action alias (Empty for custom actions)
 */
add_filter('acfe/form/load/my-custom-action', 'my_form_custom_action_load', 10, 3);
function my_form_custom_action_load($form, $post_id, $alias){
    
    /**
     * Set a custom query var
     * The value '145' can be retrieved in an another action using the template tag:
     * {query_var:my_tag}
     */
    set_query_var('my_tag', 145);
    
    
    /**
     * Set a custom query var array
     * The values can be retrieved in an another action using the template tags:
     * {query_var:my_tag:target} {query_var:my_tag:load}
     */
    set_query_var('my_tag', array(
        'target' => 145,
        'load' => 12,
    );
    
    /**
     * Change form success message dynamically
     */
    $form['updated_message'] = 'New success message!';
    
    /**
     * Change form redirection URL
     */
    $form['return'] = '/thank-you';
    
    /**
     * Return arguments
     * Note: Return false will hide the form
     */
    return $form;
    
}
</pre><?php
        
    }
    
    function advanced_validation($field){
	
	    $form_name = 'my_form';
	
	    if(acf_maybe_get($field, 'value'))
		    $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_action('acfe/form/validation/my-custom-action', 'my_form_custom_action_validation', 10, 3);
add_filter('acfe/form/validation/my-custom-action/form=<?php echo $form_name; ?>', 'my_form_custom_action_validation', 10, 3);</pre>
<br />
<pre>
/**
 * @array   $form       The form settings
 * @int     $post_id    Current post ID
 * @string  $alias      Action alias (Empty for custom actions)
 */
add_action('acfe/form/validation/my-custom-action', 'my_form_custom_action_validation', 10, 3);
function my_form_custom_action_validation($form, $post_id, $alias){
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    $my_field_unformatted = get_field('my_field', false, false);
    
    if($my_field === 'Hello'){
        
        // Add validation error
        acfe_add_validation_error('my_field', 'Hello is not allowed');
        
    }
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $post_my_field = get_field('my_field', 145);
    $post_my_field_unformatted = get_field('my_field', 145, false);
    
}
</pre><?php
    
    }
    
    function advanced_submit($field){
	
	    $form_name = 'my_form';
	
	    if(acf_maybe_get($field, 'value'))
		    $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_action('acfe/form/submit/my-custom-action', 'my_form_custom_action', 10, 2);
add_action('acfe/form/submit/my-custom-action/form=<?php echo $form_name; ?>', 'my_form_custom_action', 10, 2);</pre>
<br />
<pre>
/**
 * @array   $form       The form settings
 * @int     $post_id    Current post ID
 */
add_action('acfe/form/submit/my-custom-action', 'my_form_custom_action', 10, 2);
function my_form_custom_action($form, $post_id){
    
    /**
     * Get the value from the form input named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $my_post_field = get_field('my_field', 145);
    
    
    /**
     * Set a custom query var
     * The value '145' can be retrieved in an another action using the template tag:
     * {query_var:my_tag}
     */
    set_query_var('my_tag', 145);
    
    
    /**
     * Set a custom query var array
     * The values can be retrieved in an another action using the template tags:
     * {query_var:my_tag:post_id} {query_var:my_tag:user}
     */
    set_query_var('my_tag', array(
        'post_id' => 145,
        'user' => 12,
    );
    
}
</pre><?php
    
    }
    
}

new acfe_form_custom();

endif;