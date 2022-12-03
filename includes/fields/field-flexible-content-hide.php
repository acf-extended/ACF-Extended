<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_hide')):

class acfe_field_flexible_content_hide{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 7);
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 7);
        
        add_filter('acfe/flexible/validate_field',          array($this, 'validate_hide'));
        add_filter('acfe/flexible/remove_actions',          array($this, 'remove_actions'), 10, 2);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layout_icons'), 50, 3);
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_remove_button'] = array();
        
        return $field;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        $hide_choices = array(
            'collapse'  => 'Hide "Collapse"',
            'add'       => 'Hide "Add"',
            'delete'    => 'Hide "Delete"',
            'duplicate' => 'Hide "Duplicate"',
        );
    
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
            acfe_unset($hide_choices, 'duplicate');
        }
    
        // Hide Buttons
        acf_render_field_setting($field, array(
            'label'         => __('Hide Buttons', 'acfe'),
            'name'          => 'acfe_flexible_remove_button',
            'key'           => 'acfe_flexible_remove_button',
            'instructions'  => '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#hide-buttons" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'checkbox',
            'default_value'     => '',
            'layout'            => 'horizontal',
            'choices'           => $hide_choices,
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
     * validate_hide
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_hide($field){
        
        /**
         * old settings:
         *
         * acfe_flexible_remove_add_button
         * acfe_flexible_remove_duplicate_button
         * acfe_flexible_remove_delete_button
         */
    
        $hide = acf_get_array($field['acfe_flexible_remove_button']);
        
        // acfe_flexible_remove_add_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_add_button')){
            
            if(!in_array('add', $hide)) $hide[] = 'add';
            acfe_unset($field, 'acfe_flexible_remove_add_button');
            
        }
        
        // acfe_flexible_remove_duplicate_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_duplicate_button')){
            
            if(!in_array('duplicate', $hide)) $hide[] = 'duplicate';
            acfe_unset($field, 'acfe_flexible_remove_duplicate_button');
            
        }
        
        // acfe_flexible_remove_delete_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_delete_button')){
            
            if(!in_array('delete', $hide)) $hide[] = 'delete';
            acfe_unset($field, 'acfe_flexible_remove_delete_button');
            
        }
        
        $field['acfe_flexible_remove_button'] = $hide;
        
        return $field;
        
    }
    
    
    /**
     * remove_actions
     *
     * @param $return
     * @param $field
     *
     * @return bool|mixed
     */
    function remove_actions($return, $field){
        
        if(!in_array('add', $field['acfe_flexible_remove_button'])){
            return $return;
        }
        
        return true;
        
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
    
        if(in_array('add', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'add');
        }
    
        if(in_array('duplicate', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'duplicate');
        }
    
        if(in_array('delete', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'delete');
        }
        
        if(in_array('collapse', $field['acfe_flexible_remove_button'])){
            acfe_unset($icons, 'collapse');
        }
        
        return $icons;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_hide');

endif;