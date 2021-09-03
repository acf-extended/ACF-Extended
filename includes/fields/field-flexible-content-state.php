<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_state')):

class acfe_field_flexible_content_state{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 8);
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 8);
        
        add_filter('acfe/flexible/validate_field',          array($this, 'validate_state'));
        add_filter('acfe/flexible/wrapper_attributes',      array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/div',             array($this, 'layout_div'), 10, 3);
        add_filter('acfe/flexible/layouts/placeholder',     array($this, 'layout_placeholder'), 10, 3);
        add_filter('acfe/flexible/layouts/handle',          array($this, 'layout_handle'), 10, 3);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layout_icons'), 50, 3);
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_layouts_state'] = false;
        
        return $field;
        
    }
    
    function render_field_settings($field){
    
        // Layouts: Force State
        acf_render_field_setting($field, array(
            'label'         => __('Default Layouts State', 'acfe'),
            'name'          => 'acfe_flexible_layouts_state',
            'key'           => 'acfe_flexible_layouts_state',
            'instructions'  => __('Force layouts to be collapsed or opened', 'acfe'),
            'type'          => 'radio',
            'layout'        => 'horizontal',
            'default_value' => 'user',
            'placeholder'   => __('Default (User preference)', 'acfe'),
            'choices'       => array(
                'user'          => 'User preference',
                'collapse'      => 'Collapsed',
                'open'          => 'Opened',
                'force_open'    => 'Always opened',
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_modal_edit_enabled',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
        
    }
    
    function validate_state($field){
        
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_remove_collapse'))
            return $field;
        
        $field['acfe_flexible_layouts_state'] = 'force_open';
        
        return $field;
        
    }
    
    function wrapper_attributes($wrapper, $field){
        
        // Check setting
        if(($field['acfe_flexible_layouts_state'] !== 'open' && $field['acfe_flexible_layouts_state'] !== 'force_open') || $field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'])
            return $wrapper;
    
        $wrapper['data-acfe-flexible-open'] = 1;
        
        return $wrapper;
        
    }
    
    function layout_div($div, $layout, $field){
        
        if($field['acfe_flexible_layouts_state'] !== 'collapse')
            return $div;
        
        // Already in class
        if(in_array('-collapsed', explode(' ', $div['class'])))
            return $div;
        
        $div['class'] .= ' -collapsed';
        
        return $div;
        
    }
    
    function layout_placeholder($placeholder, $layout, $field){
    
        if($field['acfe_flexible_layouts_state'] === 'collapse' || $field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'])
            return $placeholder;
    
        // Already in class
        if(in_array('acf-hidden', explode(' ', $placeholder['class'])))
            return $placeholder;
        
        $placeholder['class'] .= ' acf-hidden';
        
        return $placeholder;
        
    }
    
    function layout_handle($handle, $layout, $field){
        
        if($field['acfe_flexible_layouts_state'] !== 'force_open')
            return $handle;
        
        acfe_unset($handle, 'data-name');
        
        return $handle;
        
    }
    
    function layout_icons($icons, $layout, $field){
    
        if($field['acfe_flexible_layouts_state'] !== 'force_open')
            return $icons;
        
        acfe_unset($icons, 'collapse');
        
        return $icons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_state');

endif;