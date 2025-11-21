<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_compatibility')):

class acfe_field_flexible_content_compatibility{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acfe/flexible/layouts/handle_elements', array($this, 'handle_elements'), 0, 6);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layouts_icons'), 0, 3);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layouts_icons_settings'), 31, 3);
        add_filter('acfe/flexible/layouts/icons',           array($this, 'layouts_icons_edit'), 51, 3);
        add_filter('acfe/flexible/remove_actions',          array($this, 'remove_actions'), 50, 3);
        add_filter('acfe/flexible/wrapper_attributes',      array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/validate_field',          array($this, 'validate_field'));
        
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
     * @return mixed
     */
    function handle_elements($elements, $layout, $field, $i, $value, $prefix){
        
        // pre-ACf 6.5
        if(!acfe_is_acf_65()){
            
            // remove elements
            acfe_unset($elements, 'drag');
            acfe_unset($elements, 'original_title');
            acfe_unset($elements, 'disabled');
            
        }
        
        return $elements;
    }
    
    
    /**
     * layouts_icons
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed|string[]
     */
    function layouts_icons($icons, $layout, $field){
        
        // ACF 6.5+: bail early
        if(acfe_is_acf_65()){
            return $icons;
        }
        
        // pre-ACF 6.5
        // added data-context="layout" to unify add layout logic across all ACF versions
        // this fix the issue with layout not being added on top of the "+" button of a layout
        $icons = array(
            'add'       => '<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" data-context="layout" title="' . __('Add layout','acf') . '"></a>',
            'duplicate' => '<a class="acf-icon -duplicate small light acf-js-tooltip" href="#" data-name="duplicate-layout" title="' . __('Duplicate layout','acf') . '"></a>',
            'delete'    => '<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="' . __('Remove layout','acf') . '"></a>',
            'collapse'  => '<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="' . __('Click to toggle','acf') . '"></a>'
        );
        
        // pre-ACF 5.9
        // duplicate was introduced in ACF 5.9, before that ACFE added a custom "clone" button
        if(!acfe_is_acf_59()){
            acfe_unset($icons, 'duplicate');
        }
        
        return $icons;
        
    }
    
    
    /**
     * layouts_icons_settings
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layouts_icons_settings($icons, $layout, $field){
        
        // ACF 6.5+: bail early
        if(acfe_is_acf_65()){
            return $icons;
        }
        
        // check setting
        if(!$field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled'] || !$layout['acfe_flexible_settings']){
            return $icons;
        }
        
        // replace icon
        if(isset($icons['settings'])){
            $icons['settings'] = '<a class="acf-icon small acf-js-tooltip dashicons dashicons-admin-generic" href="#" data-name="acfe-settings" title="' . esc_attr__('Settings', 'acfe') . '"></a>';
        }
        
        
        return $icons;
        
    }
    
    
    /**
     * layouts_icons_edit
     *
     * @param $icons
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function layouts_icons_edit($icons, $layout, $field){
        
        // ACF 6.5+: bail early
        if(acfe_is_acf_65()){
            return $icons;
        }
        
        // check setting
        if(!$field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled']){
            return $icons;
        }
        
        // replace icon
        if(isset($icons['collapse'])){
            $icons['collapse'] = '<a class="acf-icon small acf-js-tooltip dashicons dashicons-edit" href="#" data-action="acfe-flexible-modal-edit" title="' . esc_attr__('Edit', 'acfe') . '"></a>';
        }
        
        
        return $icons;
        
    }
    
    
    /**
     * remove_actions
     *
     * @param $should_hide
     * @param $field
     * @param $position
     *
     * @return false|mixed
     */
    function remove_actions($should_hide, $field, $position){
        
        // pre-ACF 6.5: do not display top actions
        // this was introduced in ACF 6.5+
        if(!acfe_is_acf_65() && $position === 'top'){
            $should_hide = true;
        }
        
        return $should_hide;
        
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
        
        // pre-ACF 6.5: add legacy attribute for css compatibility
        if(!acfe_is_acf_65()){
            $wrapper['data-acfe-legacy'] = 1;
        }
        
        return $wrapper;
        
    }
    
    
    /**
     * validate_field
     *
     * Convert old settings names to new settings
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_field($field){
        
        /**
         * Actions
         */
        $actions = acf_get_array($field['acfe_flexible_add_actions']);
        
        // acfe_flexible_title_edition
        if(acf_maybe_get($field, 'acfe_flexible_title_edition')){
            acfe_unset($field, 'acfe_flexible_title_edition'); $actions[] = 'title';
        }
        
        // acfe_flexible_toggle
        if(acf_maybe_get($field, 'acfe_flexible_toggle')){
            acfe_unset($field, 'acfe_flexible_toggle'); $actions[] = 'toggle';
        }
        
        // acfe_flexible_copy_paste
        if(acf_maybe_get($field, 'acfe_flexible_copy_paste')){
            acfe_unset($field, 'acfe_flexible_copy_paste'); $actions[] = 'copy';
        }
        
        // acfe_flexible_lock
        if(acf_maybe_get($field, 'acfe_flexible_lock')){
            acfe_unset($field, 'acfe_flexible_lock'); $actions[] = 'lock';
        }
        
        // acfe_flexible_close_button
        if(acf_maybe_get($field, 'acfe_flexible_close_button')){
            acfe_unset($field, 'acfe_flexible_close_button'); $actions[] = 'close';
        }
        
        // acfe_flexible_clone
        if(acf_maybe_get($field, 'acfe_flexible_clone')){
            acfe_unset($field, 'acfe_flexible_clone'); $actions[] = 'clone';
        }
        
        $actions = array_unique($actions);
        $actions = array_values($actions);
        $field['acfe_flexible_add_actions'] = $actions;
        
        
        /**
         * Async Loading
         */
        $async = acf_get_array($field['acfe_flexible_async']);
        
        // acfe_flexible_disable_ajax_title
        if(acf_maybe_get($field, 'acfe_flexible_disable_ajax_title')){
            acfe_unset($field, 'acfe_flexible_disable_ajax_title'); $async[] = 'title';
        }
        
        // acfe_flexible_layouts_ajax
        if(acf_maybe_get($field, 'acfe_flexible_layouts_ajax')){
            acfe_unset($field, 'acfe_flexible_layouts_ajax'); $async[] = 'layout';
        }
        
        $async = array_unique($async);
        $async = array_values($async);
        $field['acfe_flexible_async'] = $async;
        
        
        /**
         * Remove Buttons
         */
        $hide = acf_get_array($field['acfe_flexible_remove_button']);
        
        // acfe_flexible_remove_add_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_add_button')){
            acfe_unset($field, 'acfe_flexible_remove_add_button'); $hide[] = 'add';
        }
        
        // acfe_flexible_remove_duplicate_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_duplicate_button')){
            acfe_unset($field, 'acfe_flexible_remove_duplicate_button'); $hide[] = 'duplicate';
        }
        
        // acfe_flexible_remove_delete_button
        if(acf_maybe_get($field, 'acfe_flexible_remove_delete_button')){
            acfe_unset($field, 'acfe_flexible_remove_delete_button'); $hide[] = 'delete';
        }
        
        $hide = array_unique($hide);
        $hide = array_values($hide);
        $field['acfe_flexible_remove_button'] = $hide;
        
        
        /**
         * Layouts State
         */
        if(acf_maybe_get($field, 'acfe_flexible_layouts_remove_collapse')){
            $field['acfe_flexible_layouts_state'] = 'force_open';
            acfe_unset($field, 'acfe_flexible_layouts_remove_collapse');
        }
        
        
        /**
         * Modal Edit
         */
        if(isset($field['acfe_flexible_modal_edition'])){
            $field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'] = $field['acfe_flexible_modal_edition'];
            acfe_unset($field, 'acfe_flexible_modal_edition');
        }
        
        
        /**
         * Modal Settings
         */
        if(isset($field['acfe_flexible_layouts_settings'])){
            $field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_enabled'] = $field['acfe_flexible_layouts_settings'];
            acfe_unset($field, 'acfe_flexible_layouts_settings');
        }
        
        // return
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_compatibility');

endif;