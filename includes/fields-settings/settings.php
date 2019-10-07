<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_settings')):

class acfe_field_settings{
    
	function __construct(){
		
        // Actions
        add_action('load-post.php',                                     array($this, 'load'));
        add_action('wp_ajax_acf/field_group/render_field_settings',		array($this, 'field_types_action'), 5);
        
        // Filters
        add_filter('acfe/load_field',                                   array($this, 'load_field'));
        add_filter('acf/prepare_field',                                 array($this, 'prepare_field'));
        
	}
    
    /**
     * Load
     */
    function load(){
        
        if(!acf_is_screen('acf-field-group'))
            return;
        
        $this->field_types_action();
        
        // Fix: Repeater
        add_filter('acf/prepare_field/name=acfe_settings',                  array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_location',         array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_settings',         array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_setting_type',     array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_setting_name',     array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_setting_operator', array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_settings_setting_value',    array($this, 'fix_repeater'));
        
        // Fix: Clone
        add_filter('acf/update_field',                                      array($this, 'fix_clone'));
        
    }
    
    /**
     * Get field types
     */
    function field_types_action(){
        
        // Get Fields Types
        foreach(acf_get_field_types_info() as $field){
            
            // Field type
            $field_type = $field['name'];
            
            // Exclude
            if(in_array($field_type, array('message', 'accordion', 'tab', 'acfe_button', 'acfe_column', 'acfe_dynamic_message')))
                continue;
            
            add_action('acf/render_field_settings/type=' . $field_type, array($this, 'render_field_settings'), 990);
            
        }
        
    }
    
    /**
     * Add Setting
     */
    function render_field_settings($field){
        
        $valid = false;
        
        // Ajax
        if(acf_verify_ajax()){
            
            $field_group = acfe_get_field_group_from_field($field);
            
            if(acf_maybe_get($field_group, 'acfe_form'))
                $valid = true;
            
        }
        
        // Display
        else{
            
            if(acf_maybe_get($field, 'acfe_form'))
                $valid = true;
            
            if(!$valid && acf_maybe_get($field, '_name') === 'new_field'){
                
                $field_group_id = get_the_ID();
                
                if($field_group_id){
                    
                    $field_group = acf_get_field_group($field_group_id);
                    
                    if(acf_maybe_get($field_group, 'acfe_form'))
                        $valid = true;
                    
                }
                
            }
        
        }
        
        if(!$valid)
            return;
        
        // Settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced settings', 'acf'),
            'name'          => 'acfe_settings',
            'key'           => 'acfe_settings',
            'instructions'  => __('Change field settings based on location'),
            'type'          => 'repeater',
            'button_label'  => __('Add settings'),
            'required'      => false,
            'layout'        => 'row',
            'sub_fields'    => array(
                array(
                    'label'             => 'Location',
                    'name'              => 'acfe_settings_location',
                    'key'               => 'acfe_settings_location',
                    'type'              => 'select',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'choices'           => array(
                        'admin' => 'Administration',
                        'front' => 'Front-end',
                    ),
                    'allow_null'        => true,
                    'multiple'          => 0,
                    'ui'                => 0,
                    'return_format'     => 'value',
                    'ajax'              => 0,
                    'placeholder'       => 'Everywhere',
                ),
                array(
                    'label'         => __('Settings'),
                    'name'          => 'acfe_settings_settings',
                    'key'           => 'acfe_settings_settings',
                    'instructions'  => '',
                    'type'          => 'repeater',
                    'button_label'  => __('+'),
                    'required'      => false,
                    'layout'        => 'table',
                    'sub_fields'    => array(
                        array(
                            'label'         => 'Setting',
                            'name'          => 'acfe_settings_setting_type',
                            'key'           => 'acfe_settings_setting_type',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'choices'       => array(
                                'required'      => 'Required',
                                'hide_field'    => 'Hide field',
                                'hide_label'    => 'Hide label',
                                'default_value' => 'Default value',
                                'placeholder'   => 'Placeholder',
                                'instructions'  => 'Instructions',
                                'custom'        => 'Custom setting',
                            )
                        ),
                        array(
                            'label'         => 'Setting name',
                            'name'          => 'acfe_settings_setting_name',
                            'key'           => 'acfe_settings_setting_name',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'text',
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field'     => 'acfe_settings_setting_type',
                                        'operator'  => '==',
                                        'value'     => 'custom',
                                    )
                                )
                            )
                        ),
                        array(
                            'label'         => 'Operator / Value',
                            'name'          => 'acfe_settings_setting_operator',
                            'key'           => 'acfe_settings_setting_operator',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'choices'       => array(
                                'Values'     => array(
                                    'true'  => '= true',
                                    'false' => '= false',
                                    'empty' => '= (empty)',
                                ),
                                'Operators'     => array(
                                    '='        => '=',
                                ),
                            ),
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                        array(
                            'label'         => 'Value',
                            'name'          => 'acfe_settings_setting_value',
                            'key'           => 'acfe_settings_setting_value',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'text',
                            'instructions'  => false,
                            'placeholder'   => '',
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field'     => 'acfe_settings_setting_operator',
                                        'operator'  => '==',
                                        'value'     => '=',
                                    )
                                ),
                            )
                        ),
                    )
                ),
            )
        ), false);
        
    }
    
    /**
     * Load field
     */
    function load_field($field){
        
        if(!acf_maybe_get($field, 'acfe_settings'))
            return $field;
        
        $exclude = apply_filters('acfe/settings/exclude', false, $field);
        if($exclude)
            return $field;
        
        foreach($field['acfe_settings'] as $k => $rule){
            
            // Fix possible ACF Clone Index
            if($k === 'acfcloneindex')
                continue;
            
            // Screen
            $screen = isset($rule['acfe_settings_location']) ? $rule['acfe_settings_location'] : '';
            $screen_allow = false;
            
            // Screen: All
            if(empty($screen)){
                
                $screen_allow = true;
                
            }
            
            // Screen: Admin
            elseif($screen === 'admin' && acfe_form_is_admin()){
                
                $screen_allow = true;
                
            }
            
            // Screen: Front
            elseif($screen === 'front' && acfe_form_is_front()){
                
                $screen_allow = true;
                
            }
            
            if(!$screen_allow)
                continue;
            
            if(!acf_maybe_get($rule, 'acfe_settings_settings'))
                continue;
            
            // Properties
            $properties = $rule['acfe_settings_settings'];
            
            foreach($properties as $property){
                
                // Required / Hide field / Hide label / Default value / Placeholder / Instructions
                $property_name = $property['acfe_settings_setting_type'];
                
                // Custom
                if($property['acfe_settings_setting_type'] === 'custom'){
                    
                    if(!isset($property['acfe_settings_setting_name']) || empty($property['acfe_settings_setting_name']))
                        continue;
                    
                    $property_name = $property['acfe_settings_setting_name'];
                    
                }
                
                // = value
                if($property['acfe_settings_setting_operator'] === '='){
                    
                    $field[$property_name] = $property['acfe_settings_setting_value'];
                    
                }
                
                // = true
                elseif($property['acfe_settings_setting_operator'] === 'true'){
                    
                    $field[$property_name] = true;
                    
                }
                
                // = false
                elseif($property['acfe_settings_setting_operator'] === 'false'){
                    
                    $field[$property_name] = false;
                    
                }
                
                // = empty
                elseif($property['acfe_settings_setting_operator'] === 'empty'){
                    
                    $field[$property_name] = '';
                    
                }
                
            }
            
        }
        
        return $field;
        
    }
    
    /*
     * Additional settings
     */
    function prepare_field($field){
        
        if(isset($field['hide_field']) && !empty($field['hide_field'])){
            
            return false;
            
        }
        
        if(isset($field['hide_label']) && !empty($field['hide_label'])){
            
            $field['label'] = '';
            
        }
        
        return $field;
        
    }
    
    /**
     * Process Setting
     */
    function fix_repeater($field){
        
        $field['prefix'] = str_replace('row-', '', $field['prefix']);
        $field['name'] = str_replace('row-', '', $field['name']);
        
        return $field;
        
    }
    
    /**
     * Setting: ACF Clone Index fix for flexible duplicate
     */
    function fix_clone($field){
        
        if(isset($field['acfe_settings']['acfcloneindex']))
            $field['acfe_settings'] = false;
        
        return $field;
        
    }
    
}

// initialize
new acfe_field_settings();

endif;