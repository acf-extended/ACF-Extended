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
        
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 4);
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
        
        $field['acfe_flexible_layouts_settings'] = false;
        
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
        $layout['acfe_flexible_settings_size'] = 'medium';
        
        return $layout;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Settings Modal', 'acfe'),
            'name'          => 'acfe_flexible_layouts_settings',
            'key'           => 'acfe_flexible_layouts_settings',
            'instructions'  => __('Choose a field group to clone and to be used as a configuration modal', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/modal-settings#settings-modal" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
    }
    
    
    /**
     * render_layout_settings
     *
     * @param $flexible
     * @param $layout
     * @param $prefix
     */
    function render_layout_settings($flexible, $layout, $prefix){
        
        if(!acf_maybe_get($flexible, 'acfe_flexible_layouts_settings')){
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
            'label' => __('Clone settings', 'acfe'),
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
                'data-acfe-prepend' => 'Clone',
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
                'small'     => 'Small',
                'medium'    => 'Medium',
                'large'     => 'Large',
                'xlarge'    => 'Extra Large',
                'full'      => 'Full',
            ),
            'wrapper'       => array(
                'data-acfe-prepend' => 'Modal size',
            ),
            'default_value' => 'medium',
            'allow_null'    => 0,
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
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_settings')){
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
        
        if(empty($layout['sub_fields']) || !$field['acfe_flexible_layouts_settings']){
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
            'class'       => 'acfe-modal -settings',
            'data-size'   => acf_maybe_get($layout, 'acfe_flexible_settings_size', 'medium'),
            'data-footer' => __('Close', 'acfe'),
        );
        
        ?>
        <div <?php echo acf_esc_attrs($modal); ?>>
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
        
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_settings') || !acf_maybe_get($layout, 'acfe_flexible_settings')){
            return $icons;
        }
        
        $icons = array_merge($icons, array(
            'settings' => '<a class="acf-icon small acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-generic" href="#" title="Settings" data-acfe-flexible-settings="' . $layout['name'] . '"></a>'
        ));
        
        return $icons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_settings');

endif;