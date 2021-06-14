<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_thumbnail')):

class acfe_field_flexible_content_thumbnail{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',              array($this, 'defaults_field'), 3);
        add_filter('acfe/flexible/defaults_layout',             array($this, 'defaults_layout'), 3);
        
        add_action('acfe/flexible/render_field_settings',       array($this, 'render_field_settings'), 3);
        add_action('acfe/flexible/render_layout_settings',      array($this, 'render_layout_settings'), 25, 3);
        add_filter('acfe/flexible/validate_field',              array($this, 'validate_thumbnail'));
        add_filter('acfe/flexible/wrapper_attributes',          array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/label_prepend',       array($this, 'label_prepend'), 10, 3);
        add_filter('acfe/flexible/layouts/label_atts',          array($this, 'label_atts'), 20, 3);
        
        add_filter('acf/fields/flexible_content/layout_title',  array($this, 'layout_title'), 0, 4);
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_layouts_thumbnails'] = false;
        
        return $field;
        
    }
    
    function defaults_layout($layout){
    
        $layout['acfe_flexible_thumbnail'] = false;
        
        return $layout;
        
    }
    
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Thumbnails'),
            'name'          => 'acfe_flexible_layouts_thumbnails',
            'key'           => 'acfe_flexible_layouts_thumbnails',
            'instructions'  => __('Set a thumbnail for each layouts'),
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
    
    function render_layout_settings($flexible, $layout, $prefix){
        
        if(!acf_maybe_get($flexible, 'acfe_flexible_layouts_thumbnails'))
            return;
        
        // Title
        echo '</li>';
        acf_render_field_wrap(array(
            'label' => __('Thumbnail'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_thumbnail_label',
            'wrapper' => array(
                'class' => 'acfe-flexible-field-setting',
            )
        ), 'ul');
        echo '<li>';
        
        // Fields
        acf_render_field_wrap(array(
            'label'         => false,
            'name'          => 'acfe_flexible_thumbnail',
            'type'          => 'image',
            'class'         => '',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_thumbnail'],
            'return_format' => 'array',
            'preview_size'  => 'thumbnail',
            'library'       => 'all',
        ), 'ul');
        
    }
    
    function validate_thumbnail($field){
        
        if(acfe_is_admin_screen())
            return $field;
        
        // Vars
        $name = $field['name'];
        $key = $field['key'];
        
        foreach($field['layouts'] as &$layout){
            
            // Vars
            $l_name = $layout['name'];
            $thumbnail = $layout['acfe_flexible_thumbnail'];
    
            // Flexible Thumbnails
            $thumbnail = apply_filters("acfe/flexible/thumbnail",                               $thumbnail, $field, $layout);
            $thumbnail = apply_filters("acfe/flexible/thumbnail/name={$name}",                  $thumbnail, $field, $layout);
            $thumbnail = apply_filters("acfe/flexible/thumbnail/key={$key}",                    $thumbnail, $field, $layout);
            $thumbnail = apply_filters("acfe/flexible/thumbnail/layout={$l_name}",              $thumbnail, $field, $layout);
            $thumbnail = apply_filters("acfe/flexible/thumbnail/name={$name}&layout={$l_name}", $thumbnail, $field, $layout);
            $thumbnail = apply_filters("acfe/flexible/thumbnail/key={$key}&layout={$l_name}",   $thumbnail, $field, $layout);
    
            // Deprecated
            $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/layout={$l_name}",               array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/layout={$l_name}");
            $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/name={$name}&layout={$l_name}",  array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/name={$name}&layout={$l_name}");
            $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/key={$key}&layout={$l_name}",    array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/key={$key}&layout={$l_name}");
    
            $layout['acfe_flexible_thumbnail'] = $thumbnail;
            
        }
        
        return $field;
        
    }
    
    function wrapper_attributes($wrapper, $field){
        
        // Check setting
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_thumbnails'))
            return $wrapper;
    
        $wrapper['data-acfe-flexible-thumbnails'] = 1;
        
        return $wrapper;
        
    }
    
    function label_prepend($prepend, $layout, $field){
    
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_thumbnails'))
            return $prepend;

        $prepend = array(
            'class' => 'acfe-flexible-layout-thumbnail',
        );
    
        // Modal disabled
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            $prepend['class'] .= ' acfe-flexible-layout-thumbnail-no-modal';
        }
    
        // Thumbnail
        $thumbnail = $layout['acfe_flexible_thumbnail'];
        $has_thumbnail = false;
    
        if(!empty($thumbnail)){
        
            $has_thumbnail = true;
            $prepend['style'] = "background-image:url({$thumbnail});";
        
            // Attachment ID
            if(is_numeric($thumbnail)){
            
                $has_thumbnail = false;
            
                if($thumbnail_src = wp_get_attachment_url($thumbnail)){
                    $has_thumbnail = true;
                    $prepend['style'] = "background-image:url({$thumbnail_src});";
                }
            
            }
        
        }
    
        // Thumbnail not found
        if(!$has_thumbnail){
            $prepend['class'] .= ' acfe-flexible-layout-thumbnail-not-found';
        }

        $prepend = '<div ' . acf_esc_atts($prepend) . '></div>';
        
        return $prepend;
        
    }
    
    function label_atts($atts, $layout, $field){
    
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_thumbnails'))
            return $atts;
        
        acfe_unset($atts, 'class');
        
        return $atts;
        
    }
    
    function layout_title($title, $field, $layout, $i){
        
        $title = preg_replace('#<div class="acfe-flexible-layout-thumbnail(.*?)</div>#', '', $title);
        
        return $title;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_thumbnail');

endif;