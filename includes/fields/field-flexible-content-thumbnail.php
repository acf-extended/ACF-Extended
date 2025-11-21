<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_thumbnail')):

class acfe_field_flexible_content_thumbnail{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',              array($this, 'defaults_field'), 3);
        add_filter('acfe/flexible/defaults_layout',             array($this, 'defaults_layout'), 3);
        
        add_action('acfe/flexible/render_field_settings',       array($this, 'render_field_settings'), 3);
        add_action('acfe/flexible/render_layout_settings',      array($this, 'render_layout_settings'), 25, 3);
        add_filter('acfe/flexible/validate_field',              array($this, 'validate_thumbnail'));
        add_filter('acfe/flexible/wrapper_attributes',          array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/select_atts',         array($this, 'select_atts'), 10, 3);
        add_filter('acfe/flexible/layouts/select_label',        array($this, 'select_label'), 20, 3);
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_layouts_thumbnails'] = false;
        
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
    
        $layout['acfe_flexible_thumbnail'] = false;
        
        return $layout;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Thumbnails', 'acfe'),
            'name'          => 'acfe_flexible_layouts_thumbnails',
            'key'           => 'acfe_flexible_layouts_thumbnails',
            'instructions'  => __('Set a thumbnail for each layouts', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#layouts-thumbnails" target="_blank">' . __('See documentation', 'acfe') . '</a>',
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
    function render_layout_settings($field, $layout, $prefix){
        
        if(!$field['acfe_flexible_layouts_thumbnails']){
            return;
        }
        
        // Title
        echo '</li>';
        acf_render_field_wrap(array(
            'label' => __('Thumbnail', 'acfe'),
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
    
    
    /**
     * validate_thumbnail
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_thumbnail($field){
        
        if(acfe_is_admin_screen()){
            return $field;
        }
        
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
    
    
    /**
     * wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function wrapper_attributes($wrapper, $field){
        
        // check setting
        if(!$field['acfe_flexible_layouts_thumbnails']){
            return $wrapper;
        }
    
        $wrapper['data-acfe-flexible-thumbnails'] = 1;
        
        return $wrapper;
        
    }
    
    
    /**
     * select_atts
     *
     * @param $atts
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function select_atts($atts, $layout, $field){
        
        // check setting
        if(!$field['acfe_flexible_layouts_thumbnails']){
            return $atts;
        }
        
        // set thumbnail
        //$atts['data-thumbnail'] = 1;
        
        // return
        return $atts;
        
    }
    
    
    /**
     * select_label
     *
     * @param $label
     * @param $layout
     * @param $field
     *
     * @return mixed|string
     */
    function select_label($label, $layout, $field){
        
        // check setting
        if(!$field['acfe_flexible_layouts_thumbnails']){
            return $label;
        }
        
        // thumbnail
        $thumbnail = $this->get_thumbnail_url($layout);
        
        // prepend
        $prepend = array(
            'class' => 'acfe-fc-layout-thumb',
        );
        
        // thumbnail not found
        if(!$thumbnail){
            $prepend['class'] .= ' -not-found';
        }
        
        $prepend = '<div ' . acf_esc_atts($prepend) . '>';
        
        if($thumbnail){
            $prepend .= '<img src="' . esc_url($thumbnail) . '" />';
        }
        
        $prepend .= '</div>';
        
        return $prepend . $label;
        
    }
    
    
    /**
     * get_thumbnail_url
     *
     * @param $layout
     *
     * @return false|mixed|string
     */
    function get_thumbnail_url($layout){
        
        // check thumbnail
        $thumbnail_url = $layout['acfe_flexible_thumbnail'];
        if(empty($thumbnail_url)){
            return false;
        }
        
        // attachment id
        if(is_numeric($thumbnail_url)){
            
            // get attachment url
            $thumbnail_url = wp_get_attachment_url($thumbnail_url);
            if(empty($thumbnail_url)){
                return false;
            }
            
        }
        
        // return url
        return $thumbnail_url;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_thumbnail');

endif;