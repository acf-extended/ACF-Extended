<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_wysiwyg')):

class acfe_field_flexible_content_wysiwyg{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/flexible/pre_render_layout', array($this, 'pre_render_layout'), 50, 5);
        add_action('acfe/flexible/render_layout',     array($this, 'render_layout'), 0, 5);
        
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
        add_filter('acf/prepare_field/type=wysiwyg', array($this, 'prepare_layout_editor'));
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
        remove_filter('acf/prepare_field/type=wysiwyg', array($this, 'prepare_layout_editor'));
    }
    
    
    /**
     * prepare_layout_editor
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_layout_editor($field){
        
        // delay init
        $field['delay'] = 1;
        $field['acfe_wysiwyg_auto_init'] = 1;
        
        // return
        return $field;
        
    }
    
    
    
}

acf_new_instance('acfe_field_flexible_content_wysiwyg');

endif;