<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_popup')):

class acfe_field_flexible_content_popup{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/flexible/render_popup',         array($this, 'render_popup_select'));
        add_action('acfe/flexible/render_popup',         array($this, 'render_popup_actions'));
        add_action('acfe/flexible/render_popup_select',  array($this, 'render_popup_select_content'));
        add_action('acfe/flexible/render_popup_actions', array($this, 'render_popup_actions_content'));
        
    }
    
    
    /**
     * render_popup_select
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select($field){
        
        // select layout
        echo '<script type="text-html" class="tmpl-popup">';
        do_action('acfe/flexible/render_popup_select', $field);
        echo '</script>';
        
    }
    
    
    /**
     * render_popup_actions
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_actions($field){
        
        // ACF 6.5+ only
        if(!acfe_is_acf_65()){
            return;
        }
        
        // layout more actions
        echo '<script type="text-html" class="tmpl-more-layout-actions">';
        do_action('acfe/flexible/render_popup_actions', $field);
        echo '</script>';
        
    }
    
    
    /**
     * render_popup_select_content
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select_content($field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // wrapper
        echo '<ul>';
        
        // loop layouts
        foreach($field['layouts'] as $layout){
            
            // vars
            $l_name = $layout['name'];
            $atts = array(
                'href'        => '#',
                'data-layout' => $layout['name'],
                'data-min'    => $layout['min'],
                'data-max'    => $layout['max'],
                'title'       => acf_esc_html($layout['label']),
            );
            
            // atts
            $atts = apply_filters("acfe/flexible/layouts/select_atts",                                 $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/select_atts/name={$name}",                    $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/select_atts/key={$key}",                      $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/select_atts/layout={$l_name}",                $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/select_atts/name={$name}&layout={$l_name}",   $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/select_atts/key={$key}&layout={$l_name}",     $atts, $layout, $field);
            
            // label
            $label = $layout['label'];
            $label = apply_filters("acfe/flexible/layouts/select_label",                               $label, $layout, $field);
            $label = apply_filters("acfe/flexible/layouts/select_label/name={$name}",                  $label, $layout, $field);
            $label = apply_filters("acfe/flexible/layouts/select_label/key={$key}",                    $label, $layout, $field);
            $label = apply_filters("acfe/flexible/layouts/select_label/layout={$l_name}",              $label, $layout, $field);
            $label = apply_filters("acfe/flexible/layouts/select_label/name={$name}&layout={$l_name}", $label, $layout, $field);
            $label = apply_filters("acfe/flexible/layouts/select_label/key={$key}&layout={$l_name}",   $label, $layout, $field);
            
            printf('<li><a %s>%s</a></li>', acf_esc_attrs($atts), acf_esc_html($label));
            
        }
        
        echo '</ul>';
        
    }
    
    
    /**
     * render_popup_actions_content
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_actions_content($field){
        
        // get buttons
        $buttons = $this->get_popup_actions_buttons($field);
        
        // bail early
        if(empty($buttons)){
            return;
        }
        
        // wrapper
        echo '<ul role="menu" tabindex="-1">';
        
        // loop buttons
        foreach($buttons as $button){
            echo '<li>' . $button . '</li>';
        }
        
        echo '</ul>';
    }
    
    
    /**
     * get_popup_actions_buttons
     *
     * @param $field
     *
     * @return mixed|null
     */
    function get_popup_actions_buttons($field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // buttons
        $buttons = array(
            'rename'  => '<a class="acf-rename-layout" data-action="rename-layout" href="#" role="menuitem">' . esc_html__('Rename', 'acf') . '</a>',
            'disable' => '<a class="acf-toggle-layout disable" data-action="toggle-layout" href="#" role="menuitem">' . esc_html__('Disable', 'acf') . '</a><a class="acf-toggle-layout enable" data-action="toggle-layout" href="#" role="menuitem">' . esc_html__('Enable', 'acf') . '</a>',
        );
        
        // filter buttons
        $buttons = apply_filters("acfe/flexible/layouts/more_action_buttons",              $buttons, $field);
        $buttons = apply_filters("acfe/flexible/layouts/more_action_buttons/name={$name}", $buttons, $field);
        $buttons = apply_filters("acfe/flexible/layouts/more_action_buttons/key={$key}",   $buttons, $field);
        
        return $buttons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_popup');

endif;