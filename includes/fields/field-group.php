<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_field')):

class acfe_field_group_field extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'group';
        $this->defaults = array(
            'acfe_seamless_style'     => 0,
            'acfe_group_modal'        => 0,
            'acfe_group_modal_close'  => 0,
            'acfe_group_modal_button' => '',
            'acfe_group_modal_size'   => 'large',
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        acf_render_field_setting($field, array(
            'label'         => __('Seamless Style', 'acfe'),
            'name'          => 'acfe_seamless_style',
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
            'instructions'  => __('Edit fields in a modal'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_seamless_style',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Edition modal: Close button'),
            'name'          => 'acfe_group_modal_close',
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
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return array
     */
    function prepare_field($field){
        
        // seamless style
        if($field['acfe_seamless_style']){
            $field['wrapper']['class'] .= ' acfe-seamless-style';
        }
        
        // class
        $field['wrapper']['class'] .= ' acfe-field-group-layout-' . $field['layout'];
        
        // modal edit
        if($field['acfe_group_modal']){
    
            $field['wrapper']['data-acfe-group-modal'] = $field['acfe_group_modal'];
            $field['wrapper']['data-acfe-group-modal-button'] = $field['acfe_group_modal_button'] ? $field['acfe_group_modal_button'] : __('Edit', 'acf');
            $field['wrapper']['data-acfe-group-modal-close'] = $field['acfe_group_modal_close'];
            $field['wrapper']['data-acfe-group-modal-size'] = $field['acfe_group_modal_size'];
        
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function translate_field($field){
        
        $field['acfe_group_modal_button'] = acf_translate($field['acfe_group_modal_button']);
        
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_group_field');

endif;