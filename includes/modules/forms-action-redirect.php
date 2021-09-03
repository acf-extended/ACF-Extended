<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_redirect')):

class acfe_form_redirect{
    
    function __construct(){
    
        /*
         * Action
         */
        add_filter('acfe/form/actions',         array($this, 'add_action'));
        add_action('acfe/form/make/redirect',   array($this, 'make'), 10, 3);
        
    }
    
    function make($form, $current_post_id, $action){
    
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/redirect',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/redirect/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/redirect/action=' . $action,    $prepare, $form, $current_post_id, $action);
        
        if($prepare === false)
            return;
    
        // Fields
        $url = get_sub_field('acfe_form_redirect_url');
        $url = acfe_form_map_field_value($url, $current_post_id, $form);
    
        // Args
        $url = apply_filters('acfe/form/submit/redirect_url',                     $url, $form, $action);
        $url = apply_filters('acfe/form/submit/redirect_url/form=' . $form_name,  $url, $form, $action);
    
        if(!empty($action))
            $url = apply_filters('acfe/form/submit/redirect_url/action=' . $action, $url, $form, $action);
        
        // Sanitize
        $url = trim($url);
        
        // Bail early if empty
        if(empty($url))
            return;
        
        // Redirect
        wp_redirect($url);
        exit;
        
    }
    
    function add_action($layouts){
        
        $layouts['layout_redirect'] = array(
            'key' => 'layout_redirect',
            'name' => 'redirect',
            'label' => 'Redirect action',
            'display' => 'row',
            'sub_fields' => array(
    
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_docs',
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
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/redirect-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Redirect Action
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_tab_action',
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
                    'key' => 'field_acfe_form_redirect_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => __('(Optional) Target this action using hooks.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Redirect',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_redirect_url',
                    'label' => 'Action URL',
                    'name' => 'acfe_form_redirect_url',
                    'type' => 'text',
                    'instructions' => 'The URL to redirect to. See "Cheatsheet" tab for all available template tags.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
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

new acfe_form_redirect();

endif;