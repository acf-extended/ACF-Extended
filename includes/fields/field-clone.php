<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_clone')):

class acfe_field_clone extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
    
        $this->name = 'clone';
        $this->defaults = array(
            'acfe_seamless_style'     => 0,
            'acfe_clone_modal'        => 0,
            'acfe_clone_modal_close'  => 0,
            'acfe_clone_modal_button' => '',
            'acfe_clone_modal_size'   => 'large',
        );
        
        $this->add_action('wp_ajax_acf/fields/clone/query', array($this, 'ajax_query'), 5);
        
    }
    
    
    /**
     * ajax_query
     *
     * wp_ajax_acf/fields/clone/query
     */
    function ajax_query(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // local field groups are added at priority 20
        add_filter('acf/load_field_groups', array($this, 'ajax_load_field_groups'), 25);
        
    }
    
    
    /**
     * ajax_load_field_groups
     *
     * @param $field_groups
     *
     * @return mixed
     */
    function ajax_load_field_groups($field_groups){
        
        // get reserved field groups
        $hidden = acfe_get_setting('reserved_field_groups', array());
        
        // loop
        foreach($field_groups as $i => $field_group){
            
            // hide
            if(in_array($field_group['key'], $hidden)){
                unset($field_groups[ $i ]);
            }
            
        }
        
        return $field_groups;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // seamless style
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
        
        // edit modal
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
                    ),
                    array(
                        'field'     => 'acfe_seamless_style',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                ),
            )
        ));
        
        // modal close
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
        
        // modal button
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
        
        // modal size
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
        
        // layout
        if($field['layout']){
            $field['wrapper']['class'] .= ' acfe-field-clone-layout-' . $field['layout'];
        }
        
        // edit modal
        if($field['acfe_clone_modal']){
    
            $field['wrapper']['data-acfe-clone-modal'] = 1;
            $field['wrapper']['data-acfe-clone-modal-button'] = $field['acfe_clone_modal_button'] ? $field['acfe_clone_modal_button'] : __('Edit', 'acf');
            $field['wrapper']['data-acfe-clone-modal-close'] = $field['acfe_clone_modal_close'];
            $field['wrapper']['data-acfe-clone-modal-size'] = $field['acfe_clone_modal_size'];
        
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     */
    function translate_field($field){
        
        $field['acfe_clone_modal_button'] = acf_translate($field['acfe_clone_modal_button']);
        
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_clone');

endif;