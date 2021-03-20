<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_select')):

class acfe_field_flexible_content_select{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 10);
        add_filter('acfe/flexible/defaults_layout',         array($this, 'defaults_layout'), 10);
        
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 10);
        add_action('acfe/flexible/render_layout_settings',  array($this, 'render_layout_settings'), 10, 3);
        
        add_filter('acfe/flexible/wrapper_attributes',      array($this, 'wrapper_attributes'), 10, 2);
        add_filter("acfe/flexible/layouts/label_atts",      array($this, 'label_atts'), 10, 3);
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_modal'] = array(
            'acfe_flexible_modal_enabled'       => false,
            'acfe_flexible_modal_title'         => false,
            'acfe_flexible_modal_size'          => 'full',
            'acfe_flexible_modal_col'           => '4',
            'acfe_flexible_modal_categories'    => false,
        );
        
        return $field;
        
    }
    
    function defaults_layout($layout){
        
        $layout['acfe_flexible_category'] = false;
        
        return $layout;
        
    }
    
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Selection Modal'),
            'name'          => 'acfe_flexible_modal',
            'key'           => 'acfe_flexible_modal',
            'instructions'  => __('Select layouts in a modal'),
            'type'          => 'group',
            'layout'        => 'block',
            'sub_fields'    => array(
                array(
                    'label'             => '',
                    'name'              => 'acfe_flexible_modal_enabled',
                    'key'               => 'acfe_flexible_modal_enabled',
                    'type'              => 'true_false',
                    'instructions'      => '',
                    'required'          => false,
                    'wrapper'           => array(
                        'class' => 'acfe_width_auto',
                        'id'    => '',
                    ),
                    'message'           => '',
                    'default_value'     => false,
                    'ui'                => true,
                    'ui_on_text'        => '',
                    'ui_off_text'       => '',
                    'conditional_logic' => false,
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_title',
                    'key'           => 'acfe_flexible_modal_title',
                    'type'          => 'text',
                    'prepend'       => __('Title'),
                    'placeholder'   => 'Add Row',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_size',
                    'key'           => 'acfe_flexible_modal_size',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        'small'     => 'Small',
                        'medium'    => 'Medium',
                        'large'     => 'Large',
                        'xlarge'    => 'Extra Large',
                        'full'      => 'Full',
                    ),
                    'default_value' => 'full',
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => 'Size',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_col',
                    'key'           => 'acfe_flexible_modal_col',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ),
                    'default_value' => '4',
                    'wrapper'       => array(
                        'width' => '15',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => 'Cols',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_categories',
                    'key'           => 'acfe_flexible_modal_categories',
                    'type'          => 'true_false',
                    'message'       => __('Categories'),
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '15',
                        'class' => '',
                        'id'    => '',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            ),
            'wrapper' => array(
                'class' => 'acfe-field-setting-flex'
            )
        ));
        
    }
    
    function render_layout_settings($field, $layout, $prefix){
        
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_categories'])
            return;
        
        acf_render_field_wrap(array(
            'prepend'       => __('Category'),
            'name'          => 'acfe_flexible_category',
            'type'          => 'select',
            'ui'            => 1,
            'multiple'      => 1,
            'allow_custom'  => 1,
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_category'],
            'placeholder'   => __('Enter value'),
            'wrapper'       => array(
                'data-acfe-prepend' => 'Categories',
            ),
        ), 'ul');
        
    }
    
    function wrapper_attributes($wrapper, $field){
        
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_enabled'])
            return $wrapper;
        
        $wrapper['data-acfe-flexible-modal'] = 1;
        $wrapper['data-acfe-flexible-modal-col'] = $field['acfe_flexible_modal']['acfe_flexible_modal_col'];
        $wrapper['data-acfe-flexible-modal-size'] = $field['acfe_flexible_modal']['acfe_flexible_modal_size'];
    
        // Title
        if(!empty($field['acfe_flexible_modal']['acfe_flexible_modal_title']))
            $wrapper['data-acfe-flexible-modal-title'] = $field['acfe_flexible_modal']['acfe_flexible_modal_title'];
        
        return $wrapper;
        
    }
    
    function label_atts($atts, $layout, $field){
        
        // Category
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_categories'] || !$layout['acfe_flexible_category'])
            return $atts;
        
        $categories = $layout['acfe_flexible_category'];
        
        // Compatibility
        if(is_string($categories)){
            $categories = explode('|', $categories);
            $categories = array_map('trim', $categories);
        }
        
        $atts['data-acfe-flexible-category'] = $categories;
        
        return $atts;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_select');

endif;