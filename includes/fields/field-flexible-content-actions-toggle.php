<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_actions_toggle')):

class acfe_field_flexible_content_actions_toggle{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acfe/flexible/wrapper_attributes',          array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/load_fields',                 array($this, 'load_fields'), 10, 2);
        add_filter('acfe/flexible/layouts/div',                 array($this, 'layout_div'), 10, 6);
        add_filter('acfe/flexible/prepare_layout',              array($this, 'prepare_layout'), 10, 5);
        add_filter('acfe/flexible/layouts/icons',               array($this, 'layout_icons'), 11, 3);
        
        add_filter('acf/load_value/type=flexible_content',      array($this, 'load_value'), 10, 3);
        
    }
    
    /**
     * wrapper_attributes
     *
     * acfe/flexible/wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function wrapper_attributes($wrapper, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $wrapper;
        }
        
        if(in_array('toggle', $field['acfe_flexible_add_actions'])){
            $wrapper['data-acfe-flexible-toggle'] = 1;
        }
        
        return $wrapper;
        
    }
    
    /**
     * load_fields
     *
     * acfe/flexible/load_fields
     *
     * @param $fields
     * @param $field
     *
     * @return mixed
     */
    function load_fields($fields, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $fields;
        }
        
        // check setting
        if(!in_array('toggle', $field['acfe_flexible_add_actions'])){
            return $fields;
        }
        
        // loop layouts
        foreach($field['layouts'] as $i => $layout){
            
            // Vars
            $key = "field_{$layout['key']}_toggle";
            $name = 'acfe_flexible_toggle';
            
            // Add local
            acf_add_local_field(array(
                'label'                 => false,
                'key'                   => $key,
                'name'                  => $name,
                'type'                  => 'acfe_hidden',
                'required'              => false,
                'default_value'         => false,
                'parent_layout'         => $layout['key'],
                'parent'                => $field['key']
            ));
            
            // Add sub field
            array_unshift($fields, acf_get_field($key));
            
        }
        
        return $fields;
        
    }
    
    
    /**
     * layout_div
     *
     * @param $div
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layout_div($div, $layout, $field, $i, $value, $prefix){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $div;
        }
        
        // remove data-enabled="0|1"
        acfe_unset($div, 'data-enabled');
        
        // check toggle
        if(in_array('toggle', $field['acfe_flexible_add_actions'])){
            
            // add layout class
            if(!empty($value["field_{$layout['key']}_toggle"])){
                $div['class'] .= ' acfe-flexible-layout-hidden';
            }
            
        }
        
        return $div;
        
    }
    
    
    /**
     * prepare_layout
     *
     * acfe/flexible/prepare_layout
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
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $layout;
        }
        
        if(empty($layout['sub_fields'])){
            return $layout;
        }
        
        // check toggle
        if(!in_array('toggle', $field['acfe_flexible_add_actions'])){
            return $layout;
        }
        
        // extract toggle sub field
        $sub_field = acfe_extract_sub_field($layout, 'acfe_flexible_toggle', $value);
        
        if($sub_field){
            
            // update prefix to allow for nested values
            $sub_field['prefix'] = $prefix;
            $sub_field['class'] = 'acfe-flexible-layout-toggle';
            
            $sub_field = acf_validate_field($sub_field);
            $sub_field = acf_prepare_field($sub_field);
            
            $input_attrs = array();
            foreach(array('type', 'id', 'class', 'name', 'value') as $k){
                
                if(isset($sub_field[$k])){
                    $input_attrs[$k] = $sub_field[$k];
                }
                
            }
            
            // render input
            echo acf_get_hidden_input(acf_filter_attrs($input_attrs));
            
        }
        
        return $layout;
        
    }
    
    /**
     * layout_icons
     *
     * acfe/flexible/layouts/icons
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layout_icons($icons, $layout, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $icons;
        }
        
        // check toggle
        if(!in_array('toggle', $field['acfe_flexible_add_actions'])){
            return $icons;
        }
        
        // icon
        $icons = array_merge(array(
            'toggle' => '<a class="acf-icon small light acf-js-tooltip dashicons dashicons-hidden" href="#" title="'. __('Toggle layout', 'acfe') . '" data-name="acfe-toggle-layout"></a>'
        ), $icons);
        
        return $icons;
        
    }
    
    
    /**
     * load_value
     *
     * acf/load_value/type=flexible_content
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|mixed
     */
    function load_value($value, $post_id, $field){
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            return $value;
        }
        
        // bail early in admin
        if(is_admin() && !wp_doing_ajax()){
            return $value;
        }
        
        // bail early in preview
        if(acf_maybe_get_POST('action') === 'acfe/flexible/layout_preview'){
            return $value;
        }
        
        if(empty($field['layouts'])){
            return $value;
        }
        
        // check setting
        if(!in_array('toggle', $field['acfe_flexible_add_actions'])){
            return $value;
        }
        
        // vars
        $layouts = array();
        $value = acf_get_array($value);
        
        /**
         * construct layouts array with the virtual toggle field key
         *
         * my_layout  = field_layout_679a0607194c2_toggle
         * my_layout2 = field_layout_68ec6c8d60232_toggle
         */
        foreach($field['layouts'] as $layout){
            $layouts[ $layout['name'] ] = "field_{$layout['key']}_toggle";
        }
        
        // loop value
        foreach($value as $k => $row){
            
            if(!isset($layouts[ $row['acf_fc_layout'] ])){
                continue;
            }
            
            if(!acf_maybe_get($row, $layouts[ $row['acf_fc_layout'] ])){
                continue;
            }
            
            // vars
            $layout = acf_get_field_type('flexible_content')->get_layout($row['acf_fc_layout'], $field);
            $name = $field['_name'];
            $key = $field['key'];
            $l_name = $layout['name'];
            
            // filters
            $hide = true;
            $hide = apply_filters("acfe/flexible/toggle_hide",                               $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/name={$name}",                  $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/key={$key}",                    $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/layout={$l_name}",              $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/name={$name}&layout={$l_name}", $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/key={$key}&layout={$l_name}",   $hide, $row, $layout, $field);
            
            // should hide
            if($hide){
                unset($value[ $k ]);
            }
            
        }
        
        // reorder keys
        $value = array_values($value);
        
        // return value
        return $value;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_actions_toggle');

endif;