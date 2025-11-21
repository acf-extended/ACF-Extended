<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_hooks')):

class acfe_field_flexible_content_hooks{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/flexible/render_layout_settings',     array($this, 'render_layout_settings'), 9, 3);
        add_filter('acfe/flexible/layouts/label_prepend',      array($this, 'layout_label_prepend'), 9, 3);
        add_filter('acfe/flexible/layouts/label_atts',         array($this, 'layout_label_atts'), 9, 3);
        add_filter('acfe/flexible/load_fields',                array($this, 'load_fields'), 9, 2);
        add_filter('acfe/flexible/layouts/model',              array($this, 'layout_model'), 9, 3);
        add_filter('acfe/flexible/div_values',                 array($this, 'div_values'), 9, 2);
        add_filter('acfe/flexible/remove_actions',             array($this, 'remove_actions'), 9, 3);
        add_filter('acfe/flexible/action_wrapper',             array($this, 'action_wrapper'), 9, 3);
        add_action('acfe/flexible/render_popup',               array($this, 'render_popup'), 9, 1);
        add_filter('acfe/flexible/layouts/div',                array($this, 'layout_div'), 9, 6);
        add_filter('acfe/flexible/prepare_layout',             array($this, 'prepare_layout'), 9, 5);
        add_action('acfe/flexible/pre_render_layout',          array($this, 'pre_render_layout'), 9, 5);
        add_action('acfe/flexible/render_layout',              array($this, 'render_layout'), 9, 5);
        add_filter('acfe/flexible/layouts/handle',             array($this, 'layout_handle'), 9, 6);
        add_action('acfe/flexible/layouts/controls',           array($this, 'layout_controls'), 9, 5);
        add_filter('acfe/flexible/action_button',              array($this, 'action_button'), 9, 3);
        add_filter('acfe/flexible/action_buttons',             array($this, 'action_buttons'), 9, 3);
        add_filter('acf/fields/flexible_content/layout_attrs', array($this, 'layout_attrs'), 9, 4);
        add_filter('acfe/flexible/layouts/handle_elements',    array($this, 'handle_elements'), 9, 6);
        add_filter('acfe/flexible/layouts/icons',              array($this, 'layout_icons'), 9, 3);
        add_filter('acfe/flexible/layout_disabled',            array($this, 'layout_disabled'), 9, 3);
        add_filter('acfe/flexible/layout_renamed',             array($this, 'layout_renamed'), 9, 3);
        
    }
    
    
    /**
     * render_layout_settings
     *
     * @param $field
     * @param $layout
     * @param $prefix
     *
     * @return void
     */
    function render_layout_settings($field, $layout, $prefix){
        
        // vars
        $name = $field['name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        do_action("acfe/flexible/render_layout_settings/name={$name}",                  $field, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/key={$key}",                    $field, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/layout={$l_name}",              $field, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/name={$name}&layout={$l_name}", $field, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/key={$key}&layout={$l_name}",   $field, $layout, $prefix);
        
    }
    
    
    /**
     * layout_label_prepend
     *
     * @param $prepend
     * @param $layout
     * @param $field
     *
     * @return mixed|null
     */
    function layout_label_prepend($prepend, $layout, $field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $prepend = apply_filters("acfe/flexible/layouts/label_prepend/name={$name}",                  $prepend, $layout, $field);
        $prepend = apply_filters("acfe/flexible/layouts/label_prepend/key={$key}",                    $prepend, $layout, $field);
        $prepend = apply_filters("acfe/flexible/layouts/label_prepend/layout={$l_name}",              $prepend, $layout, $field);
        $prepend = apply_filters("acfe/flexible/layouts/label_prepend/name={$name}&layout={$l_name}", $prepend, $layout, $field);
        $prepend = apply_filters("acfe/flexible/layouts/label_prepend/key={$key}&layout={$l_name}",   $prepend, $layout, $field);
        
        return $prepend;
        
    }
    
    
    /**
     * layout_label_atts
     *
     * @param $atts
     * @param $layout
     * @param $field
     *
     * @return mixed|null
     */
    function layout_label_atts($atts, $layout, $field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $atts = apply_filters("acfe/flexible/layouts/label_atts/name={$name}",                  $atts, $layout, $field);
        $atts = apply_filters("acfe/flexible/layouts/label_atts/key={$key}",                    $atts, $layout, $field);
        $atts = apply_filters("acfe/flexible/layouts/label_atts/layout={$l_name}",              $atts, $layout, $field);
        $atts = apply_filters("acfe/flexible/layouts/label_atts/name={$name}&layout={$l_name}", $atts, $layout, $field);
        $atts = apply_filters("acfe/flexible/layouts/label_atts/key={$key}&layout={$l_name}",   $atts, $layout, $field);
        
        return $atts;
    
    }
    
    
    /**
     * load_fields
     *
     * @param $fields
     * @param $field
     *
     * @return mixed|null
     */
    function load_fields($fields, $field){
        
        // vars
        $name = $field['name'];
        $key = $field['key'];
        
        // variations
        $fields = apply_filters("acfe/flexible/load_fields/name={$name}", $fields, $field);
        $fields = apply_filters("acfe/flexible/load_fields/key={$key}",   $fields, $field);
        
        return $fields;
        
    }
    
    
    /**
     * layout_model
     *
     * @param $model
     * @param $field
     * @param $layout
     *
     * @return mixed|null
     */
    function layout_model($model, $field, $layout){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $model = apply_filters("acfe/flexible/layouts/model/name={$name}",                  $model, $field, $layout);
        $model = apply_filters("acfe/flexible/layouts/model/key={$key}",                    $model, $field, $layout);
        $model = apply_filters("acfe/flexible/layouts/model/layout={$l_name}",              $model, $field, $layout);
        $model = apply_filters("acfe/flexible/layouts/model/name={$name}&layout={$l_name}", $model, $field, $layout);
        $model = apply_filters("acfe/flexible/layouts/model/key={$key}&layout={$l_name}",   $model, $field, $layout);
        
        return $model;
        
    }
    
    
    /**
     * div_values
     *
     * @param $values
     * @param $field
     *
     * @return mixed|null
     */
    function div_values($values, $field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // variations
        $values = apply_filters("acfe/flexible/div_values/name={$name}", $values, $field);
        $values = apply_filters("acfe/flexible/div_values/key={$key}",   $values, $field);
        
        return $values;
        
    }
    
    
    /**
     * remove_actions
     *
     * @param $should_hide
     * @param $field
     * @param $position
     *
     * @return mixed|null
     */
    function remove_actions($should_hide, $field, $position){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // variations
        $should_hide = apply_filters("acfe/flexible/remove_actions/name={$name}", $should_hide, $field, $position);
        $should_hide = apply_filters("acfe/flexible/remove_actions/key={$key}",   $should_hide, $field, $position);
        
        return $should_hide;
        
    }
    
    
    /**
     * action_wrapper
     *
     * @param $wrapper
     * @param $field
     * @param $position
     *
     * @return mixed|null
     */
    function action_wrapper($wrapper, $field, $position){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // variations
        $wrapper = apply_filters("acfe/flexible/action_wrapper/name={$name}", $wrapper, $field, $position);
        $wrapper = apply_filters("acfe/flexible/action_wrapper/key={$key}",   $wrapper, $field, $position);
        
        return $wrapper;
        
    }
    
    
    /**
     * render_popup
     *
     * @param $field
     *
     * @return void
     */
    function render_popup($field){
        
        do_action("acfe/flexible/render_popup/name={$field['_name']}", $field);
        do_action("acfe/flexible/render_popup/key={$field['key']}",    $field);
        
    }
    
    
    /**
     * layout_div
     *
     * @param $div
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed|null
     */
    function layout_div($div, $layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}",                  $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}",                    $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/layout={$l_name}",              $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}&layout={$l_name}", $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}&layout={$l_name}",   $div, $layout, $field, $i, $value, $prefix);
        
        return $div;
        
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
     * @return mixed|null
     */
    function prepare_layout($layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $layout = apply_filters("acfe/flexible/prepare_layout/name={$name}",                  $layout, $field, $i, $value, $prefix);
        $layout = apply_filters("acfe/flexible/prepare_layout/key={$key}",                    $layout, $field, $i, $value, $prefix);
        $layout = apply_filters("acfe/flexible/prepare_layout/layout={$l_name}",              $layout, $field, $i, $value, $prefix);
        $layout = apply_filters("acfe/flexible/prepare_layout/name={$name}&layout={$l_name}", $layout, $field, $i, $value, $prefix);
        $layout = apply_filters("acfe/flexible/prepare_layout/key={$key}&layout={$l_name}",   $layout, $field, $i, $value, $prefix);
        
        return $layout;
        
    }
    
    
    /**
     * pre_render_layout
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return void
     */
    function pre_render_layout($layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        do_action("acfe/flexible/pre_render_layout/name={$name}",                  $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/pre_render_layout/key={$key}",                    $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/pre_render_layout/layout={$l_name}",              $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/pre_render_layout/name={$name}&layout={$l_name}", $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/pre_render_layout/key={$key}&layout={$l_name}",   $layout, $field, $i, $value, $prefix);
        
    }
    
    
    /**
     * render_layout
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return void
     */
    function render_layout($layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        do_action("acfe/flexible/render_layout/name={$name}",                  $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/render_layout/key={$key}",                    $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/render_layout/layout={$l_name}",              $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/render_layout/name={$name}&layout={$l_name}", $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/render_layout/key={$key}&layout={$l_name}",   $layout, $field, $i, $value, $prefix);
        
    }
    
    
    /**
     * layout_handle
     *
     * @param $handle
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed|null
     */
    function layout_handle($handle, $layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $handle = apply_filters("acfe/flexible/layouts/handle/name={$name}",                  $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/key={$key}",                    $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/layout={$l_name}",              $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/name={$name}&layout={$l_name}", $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/key={$key}&layout={$l_name}",   $handle, $layout, $field, $i, $value, $prefix);
        
        return $handle;
        
    }
    
    
    /**
     * layout_controls
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return void
     */
    function layout_controls($layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        do_action("acfe/flexible/layouts/controls/name={$name}",                  $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/layouts/controls/key={$key}",                    $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/layouts/controls/layout={$l_name}",              $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/layouts/controls/name={$name}&layout={$l_name}", $layout, $field, $i, $value, $prefix);
        do_action("acfe/flexible/layouts/controls/key={$key}&layout={$l_name}",   $layout, $field, $i, $value, $prefix);
        
    }
    
    
    /**
     * action_button
     *
     * @param $button_add
     * @param $field
     * @param $position
     *
     * @return mixed|null
     */
    function action_button($button_add, $field, $position){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // variations
        $button_add = apply_filters("acfe/flexible/action_button/name={$name}", $button_add, $field, $position);
        $button_add = apply_filters("acfe/flexible/action_button/key={$key}",   $button_add, $field, $position);
        
        return $button_add;
        
    }
    
    
    /**
     * action_buttons
     *
     * @param $buttons
     * @param $field
     * @param $position
     *
     * @return mixed|null
     */
    function action_buttons($buttons, $field, $position){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // variations
        $buttons = apply_filters("acfe/flexible/action_buttons/name={$name}", $buttons, $field, $position);
        $buttons = apply_filters("acfe/flexible/action_buttons/key={$key}",   $buttons, $field, $position);
        
        return $buttons;
        
    }
    
    
    /**
     * layout_attrs
     *
     * @param $attrs
     * @param $field
     * @param $layout
     * @param $i
     *
     * @return mixed|null
     */
    function layout_attrs($attrs, $field, $layout, $i){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $attrs = apply_filters("acf/fields/flexible_content/layout_attrs/name={$name}", $attrs, $field, $layout, $i);
        $attrs = apply_filters("acf/fields/flexible_content/layout_attrs/key={$key}",   $attrs, $field, $layout, $i);
        
        return $attrs;
        
    }
    
    
    /**
     * handle_elements
     *
     * @param $elements
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed|null
     */
    function handle_elements($elements, $layout, $field, $i, $value, $prefix){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $elements = apply_filters("acfe/flexible/layouts/handle_elements/name={$name}",                  $elements, $layout, $field, $i, $value, $prefix);
        $elements = apply_filters("acfe/flexible/layouts/handle_elements/key={$key}",                    $elements, $layout, $field, $i, $value, $prefix);
        $elements = apply_filters("acfe/flexible/layouts/handle_elements/layout={$l_name}",              $elements, $layout, $field, $i, $value, $prefix);
        $elements = apply_filters("acfe/flexible/layouts/handle_elements/name={$name}&layout={$l_name}", $elements, $layout, $field, $i, $value, $prefix);
        $elements = apply_filters("acfe/flexible/layouts/handle_elements/key={$key}&layout={$l_name}",   $elements, $layout, $field, $i, $value, $prefix);
        
        return $elements;
        
    }
    
    
    /**
     * layout_icons
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed|null
     */
    function layout_icons($icons, $layout, $field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // variations
        $icons = apply_filters("acfe/flexible/layouts/icons/name={$name}",                  $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/key={$key}",                    $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/name={$name}&layout={$l_name}", $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/key={$key}&layout={$l_name}",   $icons, $layout, $field);
        
        return $icons;
        
    }
    
    
    /**
     * layout_disabled
     *
     * @param $disabled
     * @param $field
     * @param $i
     *
     * @return mixed|null
     */
    function layout_disabled($disabled, $field, $i){
        
        // variations
        $disabled = apply_filters("acfe/flexible/layout_disabled/name={$field['_name']}", $disabled, $field, $i);
        $disabled = apply_filters("acfe/flexible/layout_disabled/key={$field['key']}",    $disabled, $field, $i);
        
        return $disabled;
        
    }
    
    
    /**
     * layout_renamed
     *
     * @param $renamed
     * @param $field
     * @param $i
     *
     * @return mixed|null
     */
    function layout_renamed($renamed, $field, $i){
        
        // variations
        $renamed = apply_filters("acfe/flexible/layout_renamed/name={$field['_name']}", $renamed, $field, $i);
        $renamed = apply_filters("acfe/flexible/layout_renamed/key={$field['key']}",    $renamed, $field, $i);
        
        return $renamed;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_hooks');

endif;