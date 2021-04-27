<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_clone')):

class acfe_field_clone{
    
    /*
     * Cosntruct
     */
    function __construct(){
        
        add_action('acf/render_field_settings/type=clone',      array($this, 'render_field_settings'));
        add_filter('acfe/field_wrapper_attributes/type=clone',  array($this, 'field_wrapper_attributes'), 10, 2);
        add_filter('acf/prepare_field/type=clone',              array($this, 'prepare_field'), 99);
        add_action('wp_ajax_acf/fields/clone/query',            array($this, 'ajax_query'), 5);
        
    }
    
    /*
     * Render Field Settings
     */
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Seamless Style', 'acfe'),
            'name'          => 'acfe_seamless_style',
            'key'           => 'acfe_seamless_style',
            'instructions'  => __('Enable better CSS integration: remove borders and padding'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'display',
                        'operator'  => '==',
                        'value'     => 'group',
                    ),
                    array(
                        'field'     => 'acfe_clone_modal',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal'),
            'name'          => 'acfe_clone_modal',
            'key'           => 'acfe_clone_modal',
            'instructions'  => __('Edit fields in a modal'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'display',
                        'operator'  => '==',
                        'value'     => 'group',
                    )
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Close button'),
            'name'          => 'acfe_clone_modal_close',
            'key'           => 'acfe_clone_modal_close',
            'instructions'  => __('Display close button'),
            'type'          => 'true_false',
            'message'       => '',
            'default_value' => false,
            'ui'            => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_clone_modal',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Text button'),
            'name'          => 'acfe_clone_modal_button',
            'key'           => 'acfe_clone_modal_button',
            'instructions'  => __('Text displayed in the edition modal button'),
            'type'          => 'text',
            'placeholder'   => __('Edit', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_clone_modal',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Size'),
            'name'          => 'acfe_clone_modal_size',
            'key'           => 'acfe_clone_modal_size',
            'instructions'  => __('Choose the modal size'),
            'type'          => 'select',
            'choices'       => array(
                'small'     => 'Small',
                'medium'    => 'Medium',
                'large'     => 'Large',
                'xlarge'    => 'Extra Large',
                'full'      => 'Full',
            ),
            'default_value' => 'large',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_clone_modal',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
    }
    
    /*
     * Field Wrapper Attributes
     */
    function field_wrapper_attributes($wrapper, $field){
    
        if(acf_maybe_get($field, 'acfe_clone_modal')){
        
            $wrapper['data-acfe-clone-modal'] = 1;
            $wrapper['data-acfe-clone-modal-button'] = __('Edit', 'acf');
        
            if(acf_maybe_get($field, 'acfe_clone_modal_button')){
            
                $wrapper['data-acfe-clone-modal-button'] = $field['acfe_clone_modal_button'];
            
            }
        
            if(acf_maybe_get($field, 'acfe_clone_modal_close')){
            
                $wrapper['data-acfe-clone-modal-close'] = $field['acfe_clone_modal_close'];
            
            }
        
            if(acf_maybe_get($field, 'acfe_clone_modal_size')){
            
                $wrapper['data-acfe-clone-modal-size'] = $field['acfe_clone_modal_size'];
            
            }
        
        }
    
        return $wrapper;
        
    }
    
    /*
     * Prepare Field
     */
    function prepare_field($field){
        
        if(acf_maybe_get($field, 'acfe_seamless_style')){
            
            $field['wrapper']['class'] .= ' acfe-seamless-style';
            
        }
        
        if(acf_maybe_get($field, 'layout')){
            
            $field['wrapper']['class'] .= ' acfe-field-clone-layout-' . $field['layout'];
            
        }
        
        return $field;
        
    }
    
    /*
     * Ajax Query
     */
    function ajax_query(){
        
        // validate
        if(!acf_verify_ajax())
            die();
        
        // local field groups are added at priortiy 20
        add_filter('acf/load_field_groups', array($this, 'load_field_groups'), 25);
    
    }
    
    /*
     * Load Field Groups
     */
    function load_field_groups($field_groups){
    
        // Hidden Local Field Groups
        $hidden = acfe_get_setting('reserved_field_groups', array());
        
        foreach($field_groups as $i => $field_group){
        
            if(!in_array($field_group['key'], $hidden))
                continue;
            
            unset($field_groups[$i]);
        
        }
    
        return $field_groups;
        
    }
    
}

new acfe_field_clone();

endif;