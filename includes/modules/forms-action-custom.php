<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_custom')):

class acfe_form_custom{
    
    function __construct(){
        
        add_filter('acfe/form/actions',                                 array($this, 'add_action'));
        
        add_action('acfe/form/make/custom',                             array($this, 'make'), 10, 3);
        add_filter('acf/validate_value/name=acfe_form_custom_action',   array($this, 'validate_action'), 10, 4);
        
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
    
    function add_action($layouts){

        $layouts['layout_custom'] = array(
            'key' => 'layout_custom',
            'name' => 'custom',
            'label' => 'Custom action',
            'display' => 'row',
            'sub_fields' => array(
    
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_custom_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/custom-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
            
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
                    'instructions' => __('Set a unique action slug.', 'acfe'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'my-custom-action',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
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