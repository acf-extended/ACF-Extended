<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_async')):

class acfe_field_flexible_content_async{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 5);
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 5);
        
        add_filter('acfe/flexible/wrapper_attributes',      array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/model',           array($this, 'layout_model'), 10, 3);
        
        // Ajax
        add_action('wp_ajax_acfe/flexible/models',          array($this, 'ajax_layout_model'));
        add_action('wp_ajax_nopriv_acfe/flexible/models',   array($this, 'ajax_layout_model'));
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_disable_ajax_title'] = false;
        $field['acfe_flexible_layouts_ajax'] = false;
        
        return $field;
        
    }
    
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Disable Legacy Title Ajax'),
            'name'          => 'acfe_flexible_disable_ajax_title',
            'key'           => 'acfe_flexible_disable_ajax_title',
            'instructions'  => __('Disable the native ACF Layout Title Ajax call. More informations: <a href="https://www.advancedcustomfields.com/resources/acf-fields-flexible_content-layout_title/" target="_blank">ACF documentation</a>.'),
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
    
        // Layouts ajax
        acf_render_field_setting($field, array(
            'label'         => __('Asynchronous Layouts'),
            'name'          => 'acfe_flexible_layouts_ajax',
            'key'           => 'acfe_flexible_layouts_ajax',
            'instructions'  => __('Add layouts using Ajax method. This setting increase performance on complex Flexible Content'),
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
    
    function wrapper_attributes($wrapper, $field){
        
        if($field['acfe_flexible_layouts_ajax'])
            $wrapper['data-acfe-flexible-ajax'] = 1;
    
        // Remove ajax 'layout_title' call
        $disable = $field['acfe_flexible_disable_ajax_title'];
        $disable = apply_filters("acfe/flexible/remove_ajax_title",                         $disable, $field);
        $disable = apply_filters("acfe/flexible/remove_ajax_title/name={$field['_name']}",  $disable, $field);
        $disable = apply_filters("acfe/flexible/remove_ajax_title/key={$field['key']}",     $disable, $field);
    
        if($disable)
            $wrapper['data-acfe-flexible-remove-ajax-title'] = 1;
        
        return $wrapper;
        
    }
    
    function layout_model($return, $field, $layout){
        
        if(!$field['acfe_flexible_layouts_ajax'])
            return $return;
    
        $i = 'acfcloneindex';
        $id = 'acfcloneindex';
        $value = array();
        $prefix = $field['name'] . '[' . $id .  ']';
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
    
        $div = array(
            'class'         => 'layout acf-clone',
            'data-id'       => 'acfcloneindex',
            'data-layout'   => $layout['name']
        );
    
        $div = apply_filters("acfe/flexible/layouts/div",                               $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}",                  $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}",                    $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/layout={$l_name}",              $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}&layout={$l_name}", $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}&layout={$l_name}",   $div, $layout, $field, $i, $value, $prefix);
    
        echo '<div ' . acf_esc_attrs($div) . '></div>';
        
        return true;
        
    }
    
    function ajax_layout_model(){
        
        // options
        $options = acf_parse_args($_POST, array(
            'field_key' => '',
            'layout'    => '',
        ));
        
        $field = acf_get_field($options['field_key']);
        if(!$field)
            die;
    
        $acfe_instance = acf_get_instance('acfe_field_flexible_content');
        $field = acf_prepare_field($field);
        
        foreach($field['layouts'] as $k => $layout){
            
            if($layout['name'] !== $options['layout'])
                continue;
    
            $acfe_instance->render_layout($field, $layout, 'acfcloneindex', array());
            die;
            
        }
        
        die;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_async');

endif;