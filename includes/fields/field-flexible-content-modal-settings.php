<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_settings')):

class acfe_field_flexible_content_settings{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 4);
        add_filter('acfe/flexible/defaults_layout',         array($this, 'defaults_layout'), 4);
        
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 11);
        add_action('acfe/flexible/render_layout_settings',  array($this, 'render_layout_settings'), 20, 3);
        
        add_filter('acfe/flexible/load_fields',             array($this, 'load_fields'), 10, 2);
        add_filter('acfe/flexible/prepare_layout',          array($this, 'prepare_layout'), 30, 5);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layout_icons'), 30, 3);
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_modal_settings'] = array(
            'acfe_flexible_modal_settings_enabled'     => false,
            'acfe_flexible_modal_settings_size'        => 'large',
            'acfe_flexible_modal_settings_close'       => true,
            'acfe_flexible_modal_settings_close_label' => '',
        );
        
        return $field;
        
    }
    
    
    /**
     * defaults_layout
     *
     * @param $layout
     *
     * @return mixed
     */
    function defaults_layout($layout){
    
        $layout['acfe_flexible_settings'] = false;
        $layout['acfe_flexible_settings_size'] = false;
        
        return $layout;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        acf_render_field_setting($field, array(
            'label'         => __('Settings Modal', 'acfe'),
            'name'          => 'acfe_flexible_modal_settings',
            'key'           => 'acfe_flexible_modal_settings',
            'instructions'  => __('Display a settings modal for each layouts', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/modal-settings#settings-modal" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'          => 'group',
            'layout'        => 'block',
            'sub_fields'    => array(
                array(
                    'label'             => '',
                    'name'              => 'acfe_flexible_modal_settings_enabled',
                    'key'               => 'acfe_flexible_modal_settings_enabled',
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
                    'name'          => 'acfe_flexible_modal_settings_size',
                    'key'           => 'acfe_flexible_modal_settings_size',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        'small'     => __('Small', 'acfe'),
                        'medium'    => __('Medium', 'acfe'),
                        'large'     => __('Large', 'acfe'),
                        'xlarge'    => __('Extra Large', 'acfe'),
                        'full'      => __('Full', 'acfe'),
                    ),
                    'default_value' => 'large',
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => __('Default Size', 'acfe'),
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_settings_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_settings_close',
                    'key'           => 'acfe_flexible_modal_settings_close',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        '1'   => __('Enabled', 'acfe'),
                    ),
                    'allow_null'    => true,
                    'placeholder'   => __('Disabled', 'acfe'),
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => 'acfe_width_auto',
                        'id'    => '',
                        'data-acfe-prepend' => __('Close Button', 'acfe'),
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_settings_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_settings_close_label',
                    'key'           => 'acfe_flexible_modal_settings_close_label',
                    'type'          => 'text',
                    'prepend'       => __('Close Label', 'acfe'),
                    'instructions'  => false,
                    'required'      => false,
                    'default_value' => '',
                    'placeholder'   => __('Close', 'acfe'),
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => 'acfe_width_auto',
                        'id'    => '',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_settings_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            ),
                            array(
                                'field'     => 'acfe_flexible_modal_settings_close',
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
    
    
    /**
     * render_layout_settings
     *
     * @param $field
     * @param $layout
     * @param $prefix
     */
    function render_layout_settings($field, $layout, $prefix){
        
        if(!$field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled']){
            return;
        }
        
        acf_disable_filters();
        
        $choices = array();
        
        $field_groups = acf_get_field_groups();
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group){
                $choices[$field_group['key']] = $field_group['title'];
            }
            
        }
        
        acf_enable_filters();
        
        // Title
        echo '</li>';
        acf_render_field_wrap(array(
            'label' => __('Settings Modal', 'acfe'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_settings_label',
            'wrapper' => array(
                'class' => 'acfe-flexible-field-setting',
            )
        ), 'ul');
        echo '<li>';
        
        // Fields
        acf_render_field_wrap(array(
            'label'         => '',
            'name'          => 'acfe_flexible_settings',
            'type'          => 'select',
            'class'         => '',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_settings'],
            'choices'       => $choices,
            'wrapper'       => array(
                'data-acfe-prepend' => __('Clone fields', 'acfe'),
            ),
            'allow_null'    => 1,
            'multiple'      => 1,
            'ui'            => 1,
            'ajax'          => 0,
            'return_format' => 0,
        ), 'ul');
        
        acf_render_field_wrap(array(
            'label'         => '',
            'name'          => 'acfe_flexible_settings_size',
            'type'          => 'select',
            'class'         => '',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_settings_size'],
            'choices'       => array(
                'small'     => __('Small', 'acfe'),
                'medium'    => __('Medium', 'acfe'),
                'large'     => __('Large', 'acfe'),
                'xlarge'    => __('Extra Large', 'acfe'),
                'full'      => __('Full', 'acfe'),
            ),
            'wrapper'       => array(
                'data-acfe-prepend' => __('Modal size', 'acfe'),
            ),
            'allow_null'    => true,
            'placeholder'   => __('Default', 'acfe'),
            'multiple'      => 0,
            'ui'            => 0,
            'ajax'          => 0,
            'return_format' => 0,
        ), 'ul');
        
    }
    
    
    /**
     * load_fields
     *
     * @param $fields
     * @param $field
     *
     * @return mixed
     */
    function load_fields($fields, $field){
        
        // check setting
        if(!$field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled']){
            return $fields;
        }
        
        // Loop
        foreach($field['layouts'] as $i => $layout){
            
            $field_groups = acf_maybe_get($layout, 'acfe_flexible_settings', array());
            $field_groups = acf_get_array($field_groups);
            
            // Check
            if(empty($field_groups)){
                continue;
            }
            
            // Vars
            $key = "field_{$layout['key']}_settings";
            $name = 'layout_settings';
            $style = 'row';
            $field_group = acf_get_field_group($field_groups[0]);
            
            if($field_group){
                $style = $field_group['label_placement'] === 'left' ? 'row' : 'block';
            }
            
            // Add local
            acf_add_local_field(array(
                'label'                 => false,
                'key'                   => $key,
                'name'                  => $name,
                'type'                  => 'clone',
                'clone'                 => $field_groups,
                'display'               => 'group',
                'acfe_seamless_style'   => true,
                'layout'                => $style,
                'prefix_label'          => 0,
                'prefix_name'           => 1,
                'parent_layout'         => $layout['key'],
                'parent'                => $field['key']
            ));
            
            // Add sub field
            array_unshift($fields, acf_get_field($key));
            
        }
        
        return $fields;
        
    }
    
    
    /**
     * prepare_layout
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed
     */
    function prepare_layout($layout, $field, $i, $value, $prefix){
        
        if(empty($layout['sub_fields']) || !$field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled']){
            return $layout;
        }
    
        // subfield
        $sub_field = acfe_extract_sub_field($layout, 'layout_settings', $value);
    
        if(!$sub_field){
            return $layout;
        }
        
        // update prefix to allow for nested values
        $sub_field['prefix'] = $prefix;
        
        // modal
        $modal = array(
            'class' => "acfe-modal -settings acfe-modal-settings-{$field['_name']} acfe-modal-settings-{$field['key']} acfe-modal-settings-{$layout['name']}",
        );
        
        // modal size
        $modal['data-size'] = $field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_size'];
        
        if(!empty($layout['acfe_flexible_settings_size'])){
            $modal['data-size'] = $layout['acfe_flexible_settings_size'];
        }
        
        // modal close button
        if($field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close']){
            $modal['data-footer'] = __('Close', 'acfe');
            
            if(!empty($field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close_label'])){
                $modal['data-footer'] = $field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close_label'];
            }
        }
        
        ?>
        <div <?php echo acf_esc_atts($modal); ?>>
            <div class="acfe-modal-wrapper">
                <div class="acfe-modal-content">
                    <div class="acf-fields -top">
                        <?php acf_render_field_wrap($sub_field); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        
        return $layout;
        
    }
    
    
    /**
     * layout_icons
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layout_icons($icons, $layout, $field){
        
        if(!$field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled'] || !$layout['acfe_flexible_settings']){
            return $icons;
        }
        
        // try to before after 'collapse' icon
        if(isset($icons['collapse'])){
            $icons = acfe_array_insert_before($icons, 'collapse', 'settings', '<a class="acf-js-tooltip" href="#" data-name="acfe-settings" title="' . esc_attr__('Settings', 'acfe') . '"><span class="acf-icon -settings"></span></a>');
            
        // otherwise, append at the end
        }else{
            $icons['settings'] = '<a class="acf-js-tooltip" href="#" data-name="acfe-settings" title="' . esc_attr__('Settings', 'acfe') . '"><span class="acf-icon -settings"></span></a>';
        }
        
        return $icons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_settings');

endif;