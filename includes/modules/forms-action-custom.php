<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_filter('acfe/form/actions',                                                 array($this, 'add_action'));
        
        add_action('acfe/form/make/custom',                                             array($this, 'make'), 10, 3);
        add_filter('acf/validate_value/name=acfe_form_custom_action',                   array($this, 'validate_action'), 10, 4);
        
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_validation', array($this, 'advanced_validation'));
        add_action('acf/render_field/name=acfe_form_custom_action_advanced_submit',     array($this, 'advanced_submit'));
        
    }
    
    function make($form, $current_post_id, $action){
    
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Custom Action Name
        $action = get_sub_field('acfe_form_custom_action');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/' . $action,                            $prepare, $form, $current_post_id, '');
        $prepare = apply_filters('acfe/form/prepare/' . $action . '/form=' . $form_name,    $prepare, $form, $current_post_id, '');
        
        if($prepare === false)
            return;
        
        // Submit
        do_action('acfe/form/submit/' . $action,                            $form, $current_post_id, '');
        do_action('acfe/form/submit/' . $action . '/form=' . $form_name,    $form, $current_post_id, '');
        
    }
    
    function validate_action($valid, $value, $field, $input){
        
        if(!$valid)
            return $valid;
        
        $reserved = array('custom', 'email', 'post', 'option', 'redirect', 'term', 'user');
        
        if(in_array($value, $reserved))
            $valid = 'This action name is not authorized';
        
        return $valid;
        
    }
    
    function advanced_validation($field){
    
        $form_name = get_field('acfe_form_name', acfe_get_post_id());
        if(empty($form_name))
            $form_name = 'my_form';
        
        ?>You may use the following hooks:<br /><br />
        <?php acfe_highlight(); ?>
add_action('acfe/form/validation/my-custom-action', 'my_form_custom_action_validation', 10, 3);
add_action('acfe/form/validation/my-custom-action/form=<?php echo $form_name; ?>', 'my_form_custom_action_validation', 10, 3);<?php acfe_highlight(); ?>
<br />
        <?php acfe_highlight(); ?>
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
    
}<?php acfe_highlight(); ?>
        
        <?php
    
    }
    
    function advanced_submit($field){
    
        $form_name = get_field('acfe_form_name', acfe_get_post_id());
        if(empty($form_name))
            $form_name = 'my_form';
        
        ?>You may use the following hooks:<br /><br />
        <?php acfe_highlight(); ?>
add_action('acfe/form/submit/my-custom-action', 'my_form_custom_action', 10, 2);
add_action('acfe/form/submit/my-custom-action/form=<?php echo $form_name; ?>', 'my_form_custom_action', 10, 2);<?php acfe_highlight(); ?>
<br />
<?php acfe_highlight(); ?>
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
    
}<?php acfe_highlight(); ?>
        
        <?php
    
    }
    
    function add_action($layouts){

        $layouts['layout_custom'] = array(
            'key' => 'layout_custom',
            'name' => 'custom',
            'label' => 'Custom action',
            'display' => 'row',
            'sub_fields' => array(
            
                /*
                 * Layout: Custom Action
                 */
                array(
                    'key' => 'field_acfe_form_custom_action_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_custom_action',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_action',
                    'type' => 'acfe_slug',
                    'instructions' => 'Set a unique action slug',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'my-custom-action',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            
                /*
                 * Layout: Custom Advanced
                 */
                array(
                    'key' => 'field_acfe_form_custom_action_tab_advanced',
                    'label' => 'Code',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_custom_action_advanced_validation',
                    'label' => 'Add custom validation on submission',
                    'name' => 'acfe_form_custom_action_advanced_validation',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_custom_action_advanced_submit',
                    'label' => 'Add custom action on submission',
                    'name' => 'acfe_form_custom_action_advanced_submit',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
            ),
            'min' => '',
            'max' => '',
        );
        
        return $layouts;
        
    }
    
}

new acfe_form_custom();

endif;