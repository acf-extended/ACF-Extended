<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_group_field')):

class acfe_field_group_field{
    
    /*
     * Construct
     */
    function __construct(){
    
        add_action('acf/render_field_settings/type=group',      array($this, 'render_field_settings'));
        add_filter('acfe/field_wrapper_attributes/type=group',  array($this, 'field_wrapper_attributes'), 10, 2);
        add_filter('acf/prepare_field/type=group',              array($this, 'prepare_field'), 99);
        
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
                        'field'     => 'acfe_group_modal',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal'),
            'name'          => 'acfe_group_modal',
            'key'           => 'acfe_group_modal',
            'instructions'  => __('Edit fields in a modal'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Close button'),
            'name'          => 'acfe_group_modal_close',
            'key'           => 'acfe_group_modal_close',
            'instructions'  => __('Display close button'),
            'type'          => 'true_false',
            'message'       => '',
            'default_value' => false,
            'ui'            => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_group_modal',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Text button'),
            'name'          => 'acfe_group_modal_button',
            'key'           => 'acfe_group_modal_button',
            'instructions'  => __('Text displayed in the edition modal button'),
            'type'          => 'text',
            'placeholder'   => __('Edit', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_group_modal',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Size'),
            'name'          => 'acfe_group_modal_size',
            'key'           => 'acfe_group_modal_size',
            'instructions'  => __('Choose the modal size'),
            'type'          => 'select',
            'choices'       => array(
                'small'     => 'Small',
                'medium'    => 'Medium',
                'large'     => 'Large',
                'full'      => 'Full',
            ),
            'default_value' => 'large',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_group_modal',
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
        
        if(isset($field['acfe_group_modal']) && !empty($field['acfe_group_modal'])){
            
            $wrapper['data-acfe-group-modal'] = 1;
            $wrapper['data-acfe-group-modal-button'] = __('Edit', 'acf');
            
            if(isset($field['acfe_group_modal_button']) && !empty($field['acfe_group_modal_button'])){
                
                $wrapper['data-acfe-group-modal-button'] = $field['acfe_group_modal_button'];
                
            }
            
            if(acf_maybe_get($field, 'acfe_group_modal_close')){
                
                $wrapper['data-acfe-group-modal-close'] = $field['acfe_group_modal_close'];
                
            }
            
            if(acf_maybe_get($field, 'acfe_group_modal_size')){
                
                $wrapper['data-acfe-group-modal-size'] = $field['acfe_group_modal_size'];
                
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
        
        $field['wrapper']['class'] .= ' acfe-field-group-layout-' . $field['layout'];
        
        return $field;
        
    }
    
}

new acfe_field_group_field();

endif;