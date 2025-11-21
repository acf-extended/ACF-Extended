<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_compatibility')):

class acfe_compatibility{
    
    /**
     * construct
     */
    function __construct(){
        
        // global
        add_action('acf/init',                                      array($this, 'acf_init'), 98);
        add_action('acfe/init',                                     array($this, 'acfe_init'), 99);
    
        // fields
        add_filter('acf/validate_field_group',                      array($this, 'field_group_location_list'), 20);
        add_filter('acf/validate_field_group',                      array($this, 'field_group_instruction_tooltip'), 20);
        add_filter('acf/validate_field_group',                      array($this, 'field_group_display_title'), 20);
        add_filter('acf/validate_field',                            array($this, 'field_acfe_update'), 20);
        add_filter('acf/validate_field/type=group',                 array($this, 'field_seamless_style'), 20);
        add_filter('acf/validate_field/type=clone',                 array($this, 'field_seamless_style'), 20);
        add_filter('acf/validate_field/type=acfe_dynamic_message',  array($this, 'field_dynamic_message'), 20);
        add_filter('acf/validate_field/type=acfe_column',           array($this, 'field_column'), 20);
        add_filter('acf/validate_field/type=image',                 array($this, 'field_image'), 20);
        add_filter('acf/validate_field/type=file',                  array($this, 'field_image'), 20);
        add_filter('acf/validate_field/type=acfe_forms',            array($this, 'field_forms'), 20);
        add_filter('acf/validate_field/type=acfe_code_editor',      array($this, 'field_code_editor'), 20);
        add_filter('acfe/load_fields/type=flexible_content',        array($this, 'field_flexible_settings_title'), 20, 2);
        add_filter('acf/prepare_field/name=acfe_flexible_category', array($this, 'field_flexible_layout_categories'), 10, 2);
        
    }
    
    
    /**
     * acf_init
     *
     * acf/init:98
     *
     * Rename modules
     *
     * @since 0.8 (20/10/2019)
     */
    function acf_init(){
        
        /**
         * acfe/flexible/render/template
         *
         * Remove temporary fix for preview get_row() usage provided for ACFE 0.9.0.8
         */
        remove_filter('acfe/flexible/render/template', 'acfe_fix_flexible_content_preview_get_row', 0);
        
        /***
         * admin_body_class
         */
        add_filter('admin_body_class',  array($this, 'admin_body_class'));
    
        // settings list
        $settings = array(
            'acfe_php'                           => 'acfe/php',
            'php_save'                           => 'acfe/php_save',
            'php_load'                           => 'acfe/php_load',
            'php_found'                          => 'acfe/php_found',
            'acfe/modules/dynamic_block_types'   => 'acfe/modules/block_types',
            'acfe/modules/dynamic_forms'         => 'acfe/modules/forms',
            'acfe/modules/dynamic_options_pages' => 'acfe/modules/options_pages',
            'acfe/modules/dynamic_post_types'    => 'acfe/modules/post_types',
            'acfe/modules/dynamic_taxonomies'    => 'acfe/modules/taxonomies',
        );
        
        // loop settings
        foreach($settings as $old => $new){
            
            // get old setting 'acfe_php'
            $value = acf_get_setting($old);
    
            if($value !== null){
                
                // deprecated notice
                acfe_deprecated_setting($old, '0.8', $new);
                
                // update setting
                acf_update_setting($new, $value);
                
            }
            
        }
        
    }
    
    
    /**
     * admin_body_class
     *
     * Add class to admin body to fix acf postbox cog icon breaking line-height in WP 6.5
     *
     * @param $classes
     *
     * @return mixed|string
     */
    function admin_body_class($classes){
        
        if(!is_string($classes)){
            return $classes;
        }
        
        $version = (float) get_bloginfo('version');
        if(acf_version_compare($version, '>=', '6.5')){
            $classes .= ' acfe-fix-postboxes';
        }
        
        return $classes;
        
    }
    
    
    
    /**
     * acfe_init
     *
     * acfe/init:99
     *
     * @since 0.8.9.3 (03/2023)
     */
    function acfe_init(){
    
        // get old setting
        $setting = acf_get_setting('acfe/modules/single_meta');
        
        if($setting !== null){
    
            // deprecated notice
            acfe_deprecated_setting('acfe/modules/single_meta', '0.8.9.3', 'acfe/modules/performance');
            
            // update setting
            if($setting){
                acf_update_setting('acfe/modules/performance', 'ultra');
            }
            
        }
        
    }
    
    
    /**
     * field_group_location_list
     *
     * acf/validate_field_group:20
     *
     * Field Group Location: Archive renamed to List
     *
     * @since 0.8 (20/10/2019)
     */
    function field_group_location_list($field_group){
        
        if(!acf_maybe_get($field_group, 'location')){
            return $field_group;
        }
        
        foreach($field_group['location'] as &$or){
            
            foreach($or as &$and){
                
                if(!isset($and['value'])){
                    continue;
                }
                
                // post type list
                // replace old 'my-post-type_archive'
                if($and['param'] === 'post_type' && acfe_ends_with($and['value'], '_archive')){
                    
                    $and['param'] = 'post_type_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
    
                // taxonomy list
                // replace old 'my-taxonomy_archive'
                }elseif($and['param'] === 'taxonomy' && acfe_ends_with($and['value'], '_archive')){
                    
                    $and['param'] = 'taxonomy_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
                    
                }
                
            }
            
        }
        
        return $field_group;
        
    }
    
    
    /**
     * field_group_instruction_tooltip
     *
     * Tooltip old parameter name compatibility
     *
     * @param $field_group
     *
     * @since 0.8.7.5 (11/12/2020)
     *
     * @return mixed
     */
    function field_group_instruction_tooltip($field_group){
        
        if(acf_maybe_get($field_group, 'instruction_placement') === 'acfe_instructions_tooltip'){
            $field_group['instruction_placement'] = 'tooltip';
        }
    
        return $field_group;
        
    }
    
    
    /**
     * field_group_display_title
     *
     * Migrate ACFE Display Title to native ACF Display Title (added in ACF 6.6+)
     *
     * @param $field_group
     *
     * @since 0.9.2 (20/11/2025)
     *
     * @return mixed
     */
    function field_group_display_title($field_group){
        
        // ACFE 6.6+
        if(acfe_is_acf_66() && isset($field_group['acfe_display_title'])){
            
            // check display title wasn't already set
            if(empty($field_group['display_title'])){
                $field_group['display_title'] = $field_group['acfe_display_title']; // assign old ACFE setting to new ACF setting
            }
            
            // unset old setting in all case
            unset($field_group['acfe_display_title']);
        }
    
        return $field_group;
        
    }
    
    
    /**
     * field_acfe_update
     *
     * acf/validate_field:20
     *
     * Field Filter Value: Removed
     *
     * @since 0.8 (20/10/2019)
     */
    function field_acfe_update($field){
        
        if(isset($field['acfe_update'])){
            unset($field['acfe_update']);
        }
        
        return $field;
        
    }
    
    
    /**
     * field_seamless_style
     *
     * acf/validate_field/type=group:20
     * acf/validate_field/type=clone:20
     *
     * Field Group/Clone: Fixed typo 'Seamless'
     *
     * @since 0.8.5 (15/03/2020)
     */
    function field_seamless_style($field){
        
        if($seamless = acf_maybe_get($field, 'acfe_seemless_style', false)){
            $field['acfe_seamless_style'] = $seamless;
            unset($field['acfe_seemless_style']);
        }
        
        return $field;
        
    }
    
    
    /**
     * field_dynamic_message
     *
     * acf/validate_field/type=acfe_dynamic_message:20
     *
     * Renamed 'Dynamic Message' field to 'Dynamic Render'
     *
     * @since 0.8.8.5 (03/09/2021)
     */
    function field_dynamic_message($field){
        
        $field['type'] = 'acfe_dynamic_render';
        
        return $field;
        
    }
    
    
    /**
     * field_column
     *
     * acf/validate_field/type=acfe_column:20
     *
     * Changed columns to 12 grid instead of 6
     *
     * @since 0.8.7.3 (29/09/2020)
     */
    function field_column($field){
    
        if(acfe_ends_with($field['columns'], '/6')){
        
            switch($field['columns']){
            
                case '1/6': {
                    $field['columns'] = '2/12';
                    break;
                }
            
                case '2/6': {
                    $field['columns'] = '4/12';
                    break;
                }
            
                case '3/6': {
                    $field['columns'] = '6/12';
                    break;
                }
            
                case '4/6': {
                    $field['columns'] = '8/12';
                    break;
                }
            
                case '5/6': {
                    $field['columns'] = '10/12';
                    break;
                }
            
                case '6/6': {
                    $field['columns'] = '12/12';
                    break;
                }
            
            }
        
        }
    
        return $field;
        
    }
    
    
    /**
     * field_image
     *
     * acf/validate_field/type=image:20
     * acf/validate_field/type=file:20
     *
     * Renamed setting 'acfe_uploader' to 'uploader' for image & file
     *
     * @since 0.8.7.5 (11/12/2020)
     */
    function field_image($field){
        
        if(acf_maybe_get($field, 'acfe_uploader')){
    
            $field['uploader'] = $field['acfe_uploader'];
            unset($field['acfe_uploader']);
            
        }
        
        return $field;
        
    }
    
    
    /**
     * field_forms
     *
     * acf/validate_field/type=forms:20
     *
     * 'forms' argument should return names and not id anymore
     *
     * @since 0.9 (07/02/2024)
     */
    function field_forms($field){
        
        if(!is_array($field['forms']) || empty($field['forms']) || !is_numeric($field['forms'][0])){
            return $field;
        }
        
        // loop allowed forms
        foreach(array_keys($field['forms']) as $k){
            
            // value
            $value = $field['forms'][ $k ];
            
            // deprecated return form id
            // we need to return form name
            if(is_numeric($value)){
                
                // get item
                $item = acfe_get_module('form')->get_item($value);
                
                // found item by ID
                // return name
                if($item){
                    $field['forms'][ $k ] = $item['name'];
                    
                    // not found
                    // remove
                }else{
                    unset($field['forms'][ $k ]);
                }
                
            }
            
        }
        
        return $field;
        
    }
    
    
    /**
     * field_code_editor
     *
     * acf/validate_field/type=acfe_code_editor:20
     *
     * Renamed 'return_entities' to 'return_format' for code editor
     *
     * @since 0.8.9.1
     *
     * @param $field
     */
    function field_code_editor($field){
        
        if(acf_maybe_get($field, 'return_entities')){
            
            if(!in_array('htmlentities', $field['return_format'])){
                $field['return_format'][] = 'htmlentities';
            }
            
            unset($field['return_entities']);
        
        }
        
        return $field;
        
    }
    
    
    /**
     * field_flexible_settings_title
     *
     * acfe/load_fields/type=flexible_content:20
     *
     * Field Flexible Content: Fix duplicated "layout_settings" & "layout_title"
     *
     * @since 0.8.4.5 (11/02/2020)
     */
    function field_flexible_settings_title($fields, $parent){
        
        // Check if is tool screen
        if(!acf_is_screen(acfe_get_acf_screen_id('acf-tools'))){
            return $fields;
        }
        
        foreach($fields as $_k => $_field){
            
            // field name
            $_field_name = acf_maybe_get($_field, 'name');
            
            // check 'acfe_flexible_layout_title' & 'layout_settings'
            if($_field_name !== 'acfe_flexible_layout_title' && $_field_name !== 'layout_settings'){
                continue;
            }
            
            // unset
            unset($fields[$_k]);
            
        }
        
        return $fields;
        
    }
    
    
    /**
     * field_flexible_layout_categories
     *
     * acf/prepare_field/name=acfe_flexible_category
     *
     * Field Flexible Content: Compatibility for Layout Categories
     *
     * @since 0.8.6.7 (16/07/2020)
     */
    function field_flexible_layout_categories($field){
        
        $value = acf_maybe_get($field, 'value');
        
        if(empty($value)){
            return $field;
        }
        
        if(is_string($value)){
            
            $explode = explode('|', $value);
            
            $choices = array();
            
            foreach($explode as $v){
                
                $v = trim($v);
                $choices[ $v ] = $v;
                
            }
            
            $field['choices'] = $choices;
            $field['value'] = $choices;
            
        }
        
        return $field;
        
    }
    
}

new acfe_compatibility();

endif;