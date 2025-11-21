<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_actions')):

class acfe_field_flexible_content_actions{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',        array($this, 'defaults_field'), 6);
        add_filter('acfe/flexible/render_field_settings', array($this, 'render_field_settings'), 6);
        add_filter('acfe/flexible/wrapper_attributes',    array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/icons',         array($this, 'layout_icons'), 11, 3);
        add_filter('acfe/flexible/action_buttons',        array($this, 'action_buttons'), 20, 3);
        add_filter('acfe/flexible/secondary_actions',     array($this, 'secondary_actions'), 10, 2);
        add_filter('acfe/flexible/render_popup',          array($this, 'render_popup_secondary_actions'), 10);
        
    }
    
    /**
     * defaults_field
     *
     * acfe/flexible/defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_add_actions'] = array();
        $field['acfe_flexible_close_button_label'] = '';
        
        return $field;
        
    }
    
    /**
     * render_field_settings
     *
     * acfe/flexible/render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        $choices = array();
        
        if(!acfe_is_acf_65()){
            $choices['title'] = __('Inline Title Edit', 'acfe');
            $choices['toggle'] = __('Toggle Layout', 'acfe');
        }
        
        $choices['copy'] = __('Copy/Paste Layout', 'acfe');
        $choices['lock'] = __('Lock Layouts', 'acfe');
        $choices['close'] = __('Close Layout Button', 'acfe');
    
        if(!acfe_is_acf_59()){
            $choices['clone'] = __('Clone', 'acfe');
        }
    
        acf_render_field_setting($field, array(
            'label'         => __('Additional Actions', 'acfe'),
            'name'          => 'acfe_flexible_add_actions',
            'key'           => 'acfe_flexible_add_actions',
            'instructions'  => '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'checkbox',
            'default_value'     => '',
            'layout'            => 'horizontal',
            'choices'           => $choices,
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
    
        acf_render_field_setting($field, array(
            'label'         => '',
            'name'          => 'acfe_flexible_close_button_label',
            'key'           => 'acfe_flexible_close_button_label',
            'instructions'  => '',
            'type'              => 'text',
            'default_value'     => '',
            'prepend'           => __('Close Label', 'acfe'),
            'placeholder'       => __('Close', 'acfe'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_add_actions',
                        'operator'  => '==',
                        'value'     => 'close',
                    ),
                )
            )
        ));
        
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
        
        $actions = $field['acfe_flexible_add_actions'];
        
        // copy/paste
        if(in_array('copy', $actions)){
            $wrapper['data-acfe-flexible-copy-paste'] = 1;
        }
        
        // lock
        if(in_array('lock', $actions)){
            
            $lock = true;
            $lock = apply_filters("acfe/flexible/lock",                        $lock, $field);
            $lock = apply_filters("acfe/flexible/lock/name={$field['_name']}", $lock, $field);
            $lock = apply_filters("acfe/flexible/lock/key={$field['key']}",    $lock, $field);
            
            if($lock){
                $wrapper['data-acfe-flexible-lock'] = 1;
            }
            
        }
        
        // close
        if(in_array('close', $actions)){
            $wrapper['data-acfe-flexible-close-button'] = 1;
        }
        
        return $wrapper;
        
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
    
        $actions = $field['acfe_flexible_add_actions'];
    
        // copy/paste
        if(in_array('copy', $actions)){
            
            // default icons
            if(acfe_is_acf_65()){
                
                // try to insert after 'add' icon
                if(isset($icons['add'])){
                    $icons = acfe_array_insert_after($icons, 'add', 'copy', '<a class="acf-js-tooltip" href="#" data-name="acfe-copy-layout" title="'. __('Copy Layout', 'acfe') .'"><span class="acf-icon -copy"></span></a>');
                    
                // otherwise, prepend it at the beginning
                }else{
                    $icons = array_merge(array(
                        'copy' => '<a class="acf-js-tooltip" href="#" data-name="acfe-copy-layout" title="'. __('Copy Layout', 'acfe') .'"><span class="acf-icon -copy"></span></a>',
                    ), $icons);
                }
                
                
            }else{
                
                $icons = array_merge(array(
                    'copy' => '<a class="acf-icon small light acf-js-tooltip dashicons dashicons-upload" href="#" data-name="acfe-copy-layout" title="'. __('Copy layout', 'acfe') .'"></a>'
                ), $icons);
                
            }
        
        }
        
        // clone
        if(in_array('clone', $actions)){
            
            // pre ACF 5.9
            if(!acfe_is_acf_59()){
                
                $icons = array_merge($icons, array(
                    'clone' => '<a class="acf-icon small light acf-js-tooltip dashicons dashicons-admin-page" href="#" data-name="acfe-clone-layout" title="'. __('Clone layout', 'acfe'). '"></a>'
                ));
                
            }
            
        }
        
        return $icons;
        
    }
    
    
    /**
     * action_buttons
     *
     * @param $buttons
     * @param $field
     * @param $position
     *
     * @return void
     */
    function action_buttons($buttons, $field, $position){
        
        // check settings
        if(!in_array('copy', $field['acfe_flexible_add_actions'])){
            return $buttons;
        }
        
        // add button has been removed
        if(!isset($buttons['add'])){
            return $buttons;
        }
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // secondary buttons
        $secondary_buttons = $this->get_secondary_buttons($field);
        if(empty($secondary_buttons)){
            return $buttons;
        }
        
        // button
        $button = array(
            'href'      => '#',
            'class'     => 'acf-button button',
            'data-name' => 'acfe-secondary-actions',
        );
        
        // hooks
        $button = apply_filters("acfe/flexible/action_button_secondary",              $button, $field);
        $button = apply_filters("acfe/flexible/action_button_secondary/name={$name}", $button, $field);
        $button = apply_filters("acfe/flexible/action_button_secondary/key={$key}",   $button, $field);
        
        // add button
        $buttons['secondary'] = '<a ' . acf_esc_atts($button) . '><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
        
        // return
        return $buttons;
        
    }
    
    
    /**
     * secondary_actions
     *
     * acfe/flexible/secondary_actions
     *
     * @param $actions
     * @param $field
     *
     * @return mixed
     */
    function secondary_actions($actions, $field){
        
        if(!in_array('copy', $field['acfe_flexible_add_actions'])){
            return $actions;
        }
        
        $actions['copy'] = '<a href="#" data-name="acfe-copy-layouts">' . __('Copy layouts', 'acfe') . '</a>';
        $actions['paste'] = '<a href="#" data-name="acfe-paste-layouts">' . __('Paste layouts', 'acfe') . '</a>';
        
        return $actions;
        
    }
    
    
    /**
     * render_popup_secondary_actions
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_secondary_actions($field){
        
        // secondary buttons
        $secondary_buttons = $this->get_secondary_buttons($field);
        if(empty($secondary_buttons)){
            return;
        }
        
        echo '<script type="text-html" class="tmpl-acfe-fc-secondary-popup">';
        
        foreach($secondary_buttons as $button){
            echo '<li>' . $button . '</li>';
        }
        
        echo '</script>';
        
    }
    
    
    /**
     * get_secondary_buttons
     *
     * @param $field
     *
     * @return mixed|null
     */
    function get_secondary_buttons($field){
        
        // vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // secondary buttons
        $buttons = array();
        $buttons = apply_filters("acfe/flexible/secondary_actions",               $buttons, $field);
        $buttons = apply_filters("acfe/flexible/secondary_actions/name={$name}",  $buttons, $field);
        $buttons = apply_filters("acfe/flexible/secondary_actions/key={$key}",    $buttons, $field);
        
        return $buttons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_actions');

endif;