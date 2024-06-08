<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_custom')):

class acfe_module_form_action_custom extends acfe_module_form_action{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'custom';
        $this->title = __('Custom action', 'acfe');
        
        $this->item = array(
            'action' => 'custom',
            'name'   => '',
        );
        
        $this->validate = array('name');
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_custom:9
     *
     * @param $action
     * @param $form
     *
     * @return mixed
     */
    function prepare_action($action, $form){
    
        // prepare action
                    $action = apply_filters("acfe/form/prepare_{$action['name']}",                      $action, $form);
        if($action){$action = apply_filters("acfe/form/prepare_{$action['name']}/form={$form['name']}", $action, $form);}
        
        // return
        return $action;
        
    }
    
    
    /**
     * validate_action
     *
     * acfe/form/validate_custom:9
     *
     * @param $form
     * @param $action
     */
    function validate_action($form, $action){
    
        // validate action
        do_action("acfe/form/validate_{$action['name']}",                      $form, $action);
        do_action("acfe/form/validate_{$action['name']}/form={$form['name']}", $form, $action);
    
    }
    
    
    /**
     * make_action
     *
     * acfe/form/make_custom:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
    
        // hooks
        do_action("acfe/form/submit_{$action['name']}",                      $form, $action);
        do_action("acfe/form/submit_{$action['name']}/form={$form['name']}", $form, $action);
    
    }
    
    /**
     * validate_name
     *
     * @param $value
     *
     * @return false|string|void
     */
    function validate_name($value){
        
        $actions = acfe_get_form_action_types();
        $names = array('form'); // reserved
        
        // get actions names
        foreach($actions as $action){
            $names[] = $action->name;
        }
        
        // do not allow existing action name
        if(in_array($value, $names)){
            return __('This action name is reserved', 'acfe');
        }
        
        return false;
        
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
                    echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/custom-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
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
                'instructions' => __('Target this action using hooks.', 'acfe'),
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-instruction-placement' => 'field'
                ),
                'default_value' => '',
                'placeholder' => __('Custom', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),

        );
        
    }
    
}

acfe_register_form_action_type('acfe_module_form_action_custom');

endif;