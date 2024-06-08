<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_redirect')):

class acfe_module_form_action_redirect extends acfe_module_form_action{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'redirect';
        $this->title = __('Redirect action', 'acfe');
        
        $this->item = array(
            'action' => 'redirect',
            'name'   => '',
            'url'    => '',
        );
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_user:9
     *
     * @param $action
     * @param $form
     *
     * @return mixed
     */
    function prepare_action($action, $form){
        
        return $action;
        
    }
    
    
    /**
     * make_action
     *
     * acfe/form/make_redirect:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
        
        // apply tags
        acfe_apply_tags($action['url'], array('context' => 'display'));
    
        // url
        $url = $action['url'];
        
        // filters
        $url = apply_filters("acfe/form/submit_redirect_url",                          $url, $form, $action);
        $url = apply_filters("acfe/form/submit_redirect_url/form={$form['name']}",     $url, $form, $action);
        $url = apply_filters("acfe/form/submit_redirect_url/action={$action['name']}", $url, $form, $action);
    
        // sanitize
        $url = trim($url);
    
        // redirect
        if(!empty($url)){
            acfe_redirect($url);
        }
    
    }
    
    
    /**
     * register_layout
     *
     * @param $layout
     *
     * @return array
     */
    function register_layout($layout){
    
        return array(
    
            /**
             * documentation
             */
            array(
                'key' => 'field_doc',
                'label' => '',
                'name' => '',
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
    
            /**
             * action
             */
            array(
                'key' => 'field_tab_action',
                'label' => __('Action', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_name',
                'label' => __('Action name', 'acfe'),
                'name' => 'name',
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
                'default_value' => '',
                'placeholder' => __('Redirect', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_url',
                'label' => __('Action URL', 'acfe'),
                'name' => 'url',
                'type' => 'text',
                'instructions' => __('The redirection URL.', 'acfe'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-instruction-placement' => 'field'
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),

        );
        
    }
    
}

acfe_register_form_action_type('acfe_module_form_action_redirect');

endif;