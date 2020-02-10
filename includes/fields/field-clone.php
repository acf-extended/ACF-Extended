<?php

if(!defined('ABSPATH'))
    exit;

add_action('acf/render_field_settings/type=clone', 'acfe_field_clone_settings');
function acfe_field_clone_settings($field){
    
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

add_filter('acfe/field_wrapper_attributes/type=clone', 'acfe_field_clone_wrapper', 10, 2);
function acfe_field_clone_wrapper($wrapper, $field){
    
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

add_filter('acf/load_field_groups', 'acfe_field_clone_ajax_query');
function acfe_field_clone_ajax_query($field_groups){
    
    if(!acf_verify_ajax())
        return $field_groups;
    
    if(acf_maybe_get_POST('field_key') !== 'clone')
        return $field_groups;
    
    if(empty($field_groups))
        return $field_groups;
    
    foreach($field_groups as $i => $field_group){
        
        if(!in_array($field_group['key'], array(
            'group_acfe_author', 
            'group_acfe_dynamic_block_type', 
            'group_acfe_dynamic_form', 
            'group_acfe_dynamic_options_page', 
            'group_acfe_dynamic_post_type', 
            'group_acfe_dynamic_taxonomy')
        ))
            continue;
        
        unset($field_groups[$i]);
        
    }
    
    return $field_groups;
    
}

add_filter('acf/prepare_field/type=clone', 'acfe_field_clone_type_class', 99);
function acfe_field_clone_type_class($field){
    
    if(acf_maybe_get($field, 'acfe_seamless_style')){
        
        $field['wrapper']['class'] .= ' acfe-seamless-style';
        
    }
    
    if(acf_maybe_get($field, 'layout')){
        
        $field['wrapper']['class'] .= ' acfe-field-clone-layout-' . $field['layout'];
        
    }
    
    return $field;
    
}