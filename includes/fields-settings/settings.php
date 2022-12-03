<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_settings')):

class acfe_field_settings{
    
    /**
     * construct
     */
    function __construct(){
        
        // actions
        add_action('acf/field_group/admin_head',                        array($this, 'load'));
        add_action('wp_ajax_acf/field_group/render_field_settings',     array($this, 'load_ajax'), 5);
        
        // filters
        add_filter('acfe/load_field',                                   array($this, 'load_field'), 20);
        add_filter('acfe/load_field',                                   array($this, 'load_field_additional'), 20);
        add_filter('acf/prepare_field',                                 array($this, 'prepare_field'), 20);
        
    }
    
    
    /**
     * load
     */
    function load(){
    
        if(!acf_is_filter_enabled('acfe/field_group/advanced')){
            return;
        }
    
        $this->prepare_settings();
        $this->add_settings();
        
    }
    
    
    /**
     * load_ajax
     */
    function load_ajax(){
        
        $post_id = acf_maybe_get_POST('post_id');
        $field_group = acf_get_field_group($post_id);
    
        if(!$field_group){
            return;
        }
    
        if(!acf_maybe_get($field_group, 'acfe_form')){
            return;
        }
    
        $this->add_settings();
        
    }
    
    
    /**
     * add_settings
     */
    function add_settings(){
    
        // exclude
        $exclude = array('accordion', 'acfe_column', 'tab');
        
        // get fields types
        foreach(acf_get_field_types_info() as $field){
            
            // field type
            $field_type = $field['name'];
            
            // check
            if(in_array($field_type, $exclude)){
                continue;
            }
            
            add_action("acf/render_field_settings/type={$field_type}", array($this, 'render_field_settings'), 99);
            
        }
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced Settings', 'acfe'),
            'name'          => 'acfe_settings',
            'key'           => 'acfe_settings',
            'instructions'  => __('Change field settings based on location'),
            'type'          => 'repeater',
            'button_label'  => __('Add settings'),
            'required'      => false,
            'layout'        => 'row',
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
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
                            'ID'            => false,
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
                                'required'          => 'Required',
                                'hide_field'        => 'Hide field',
                                'hide_label'        => 'Hide label',
                                'hide_instructions' => 'Hide instructions',
                                'default_value'     => 'Default value',
                                'placeholder'       => 'Placeholder',
                                'instructions'      => 'Instructions',
                                'custom'            => 'Custom setting',
                            )
                        ),
                        array(
                            'ID'            => false,
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
                            'ID'            => false,
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
                            'ID'            => false,
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
        ));
        
    }
    
    
    /**
     * load_field
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field($field){
        
        if(!acf_maybe_get($field, 'acfe_settings')){
            return $field;
        }
        
        $exclude = apply_filters('acfe/settings/exclude', false, $field);
        if($exclude){
            return $field;
        }
        
        foreach($field['acfe_settings'] as $k => $rule){
            
            // fix possible acf clone index
            if($k === 'acfcloneindex'){
                continue;
            }
            
            // screen
            $screen = isset($rule['acfe_settings_location']) ? $rule['acfe_settings_location'] : '';
            $screen_allow = false;
            
            // screen: all
            if(empty($screen)){
                $screen_allow = true;
            }
            
            // screen: admin
            elseif($screen === 'admin' && acfe_is_admin()){
                $screen_allow = true;
            }
            
            // screen: front
            elseif($screen === 'front' && acfe_is_front()){
                $screen_allow = true;
            }
            
            if(!$screen_allow){
                continue;
            }
            
            if(!acf_maybe_get($rule, 'acfe_settings_settings')){
                continue;
            }
            
            // properties
            $properties = $rule['acfe_settings_settings'];
            
            foreach($properties as $property){
                
                // required / hide field / hide label / default value / placeholder / instructions
                $property_name = $property['acfe_settings_setting_type'];
                
                // custom
                if($property['acfe_settings_setting_type'] === 'custom'){
                    
                    if(!isset($property['acfe_settings_setting_name']) || empty($property['acfe_settings_setting_name'])){
                        continue;
                    }
                    
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
    
    
    /**
     * load_field_additional
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field_additional($field){
    
        $hide_required = acf_maybe_get($field, 'hide_required');
    
        if($hide_required){
        
            if(is_bool($hide_required) || $hide_required === 'all' || ($hide_required === 'front' && acfe_is_front()) || $hide_required === 'admin' && acfe_is_admin()){
                $field['required'] = false;
            }
        
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return false
     */
    function prepare_field($field){
        
        $hide_field = acf_maybe_get($field, 'hide_field');
        
        if($hide_field){
            
            if(is_bool($hide_field) || $hide_field === 'all' || ($hide_field === 'front' && acfe_is_front()) || $hide_field === 'admin' && acfe_is_admin()){
                return false;
            }
            
        }
        
        $hide_label = acf_maybe_get($field, 'hide_label');
        
        if($hide_label){
    
            if(is_bool($hide_label) || $hide_label === 'all' || ($hide_label === 'front' && acfe_is_front()) || $hide_label === 'admin' && acfe_is_admin()){
                $field['label'] = '';
            }
            
        }
        
        $hide_instructions = acf_maybe_get($field, 'hide_instructions');
        
        if(is_bool($hide_instructions) || $hide_instructions === 'all' || ($hide_instructions === 'front' && acfe_is_front()) || $hide_instructions === 'admin' && acfe_is_admin()){
            $field['instructions'] = '';
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_settings
     */
    function prepare_settings(){
        
        $fields = array('acfe_settings', 'acfe_settings_location', 'acfe_settings_settings', 'acfe_settings_setting_type', 'acfe_settings_setting_name', 'acfe_settings_setting_operator', 'acfe_settings_setting_value');
        
        foreach($fields as $name){
            
            add_filter("acf/prepare_field/name={$name}", function($field){
    
                $field['prefix'] = str_replace('row-', '', $field['prefix']);
                $field['name'] = str_replace('row-', '', $field['name']);
    
                return $field;
    
            });
            
        }
        
    }
    
    
}

// initialize
new acfe_field_settings();

endif;