<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_validation')):

class acfe_field_validation{
    
    public $functions = array();
    
	function __construct(){
		
        // Actions
        add_action('load-post.php',                                     array($this, 'load'));
        add_action('wp_ajax_acf/field_group/render_field_settings',		array($this, 'field_types_action'), 5);
        
        // Filters
        add_filter('acf/validate_value',                                array($this, 'validate_value'), 99, 4);
        
        // Validation functions
        $this->functions = array(
        
            'General' => array(
                'value'                 => __('If value', 'acfe'),
                'strlen'                => __('If value length - strlen(value)', 'acfe'),
            ),
            
            'Exists' => array(
                'email_exists'          => __('If email exists - email_exists(value)', 'acfe'),
                'post_type_exists'      => __('If post type exists - post_type_exists(value)', 'acfe'),
                'taxonomy_exists'       => __('If taxonomy exists - taxonomy_exists(value)', 'acfe'),
                'term_exists'           => __('If term exists - term_exists(value)', 'acfe'),
                'username_exists'       => __('If username exists - username_exists(value)', 'acfe'),
            ),
            
            'Is' => array(
                'is_email'              => __('If is email - is_email(value))', 'acfe'),
                'is_user_logged_in'     => __('If is user logged in - is_user_logged_in()', 'acfe'),
            ),
            
            'Sanitize' => array(
                'sanitize_email'        => __('If sanitize email - sanitize_email(value)', 'acfe'),
                'sanitize_file_name'    => __('If sanitize file name - sanitize_file_name(value)', 'acfe'),
                'sanitize_html_class'   => __('If sanitize html class - sanitize_html_class(value)', 'acfe'),
                'sanitize_key'          => __('If sanitize key - sanitize_key(value)', 'acfe'),
                'sanitize_meta'         => __('If sanitize meta - sanitize_meta(value)', 'acfe'),
                'sanitize_mime_type'    => __('If sanitize mime type - sanitize_mime_type(value)', 'acfe'),
                'sanitize_option'       => __('If sanitize option - sanitize_option(value)', 'acfe'),
                'sanitize_text_field'   => __('If sanitize text field - sanitize_text_field(value)', 'acfe'),
                'sanitize_title'        => __('If sanitize title - sanitize_title(value)', 'acfe'),
                'sanitize_user'         => __('If sanitize user - sanitize_user(value)', 'acfe'),
            ),
            
            'User' => array(
                'get_user_by_id'        => __('If get user by id - get_user_by(\'id\', value)', 'acfe'),
                'get_user_by_slug'      => __('If get user by slug - get_user_by(\'slug\', value)', 'acfe'),
                'get_user_by_email'     => __('If get user by email - get_user_by(\'email\', value)', 'acfe'),
                'get_user_by_login'     => __('If get user by login - get_user_by(\'login\', value)', 'acfe'),
            )
            
        );
        
	}
    
    /**
     * Load
     */
    function load(){
        
        if(!acf_is_screen('acf-field-group'))
            return;
        
        $this->field_types_action();
        
        // Fix: Repeater
        add_filter('acf/prepare_field/name=acfe_validate',              array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_location',     array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_rules_and',    array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_function',     array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_operator',     array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_match',        array($this, 'fix_repeater'));
        add_filter('acf/prepare_field/name=acfe_validate_error',        array($this, 'fix_repeater'));
        
        // Fix: Clone
        add_filter('acf/update_field',                                  array($this, 'fix_clone'));
        
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
            if(in_array($field_type, array('message', 'accordion', 'tab', 'acfe_button', 'acfe_column', 'acfe_dynamic_message', 'group', 'repeater', 'flexible_content', 'clone')))
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
        
        $choices = apply_filters('acfe/validate/functions', $this->functions, $field);
        
        if(empty($choices))
            return;
        
        // Settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced validation', 'acfe'),
            'name'          => 'acfe_validate',
            'key'           => 'acfe_validate',
            'instructions'  => __('Validate value against rules', 'acfe'),
            'type'          => 'repeater',
            'button_label'  => __('Add validation', 'acfe'),
            'required'      => false,
            'layout'        => 'row',
            'sub_fields'    => array(
                array(
                    'label'             => __('Location', 'acfe'),
                    'name'              => 'acfe_validate_location',
                    'key'               => 'acfe_validate_location',
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
                    'label'         => __('Rules', 'acfe'),
                    'name'          => 'acfe_validate_rules_and',
                    'key'           => 'acfe_validate_rules_and',
                    'instructions'  => '',
                    'type'          => 'repeater',
                    'button_label'  => __('+ AND', 'acfe'),
                    'required'      => false,
                    'layout'        => 'table',
                    'sub_fields'    => array(
                        array(
                            'label'         => __('Function', 'acfe'),
                            'name'          => 'acfe_validate_function',
                            'key'           => 'acfe_validate_function',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'choices'       => $choices,
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                        array(
                            'label'         => 'Operator / Value',
                            'name'          => 'acfe_validate_operator',
                            'key'           => 'acfe_validate_operator',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'choices'       => array(
                                'Operators' => array(
                                    '=='        => '==',
                                    '!='        => '!=',
                                    '>'         => '>',
                                    '>='        => '>=',
                                    '<'         => '<',
                                    '<='        => '<=',
                                    'contains'  => __('Contains', 'acfe'),
                                    '!contains'  => __('Doesn\'t contain', 'acfe'),
                                    'starts'    => __('Starts with', 'acfe'),
                                    '!starts'    => __('Doesn\'t start with', 'acfe'),
                                    'ends'      => __('Ends with', 'acfe'),
                                    '!ends'      => __('Doesn\'t end with', 'acfe'),
                                ),
                                'Values'     => array(
                                    'true'  => '== true',
                                    'false' => '== false',
                                    'null'  => '== null',
                                    'empty' => '== (empty)',
                                    '!empty' => '!= (empty)',
                                )
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
                            'name'          => 'acfe_validate_match',
                            'key'           => 'acfe_validate_match',
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
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '==',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '>',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '>=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '<',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '<=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'contains',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!contains',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'starts',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!starts',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'ends',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!ends',
                                    )
                                ),
                            )
                        ),
                    )
                ),
                array(
                    'label'         => __('Error', 'acfe'),
                    'name'          => 'acfe_validate_error',
                    'key'           => 'acfe_validate_error',
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
                ),
            )
        ), false);
        
    }
    
    /**
     * Validate
     */
    function validate_value($valid, $value, $field, $input){
        
        if(!$valid)
            return $valid;
        
        if(!acf_maybe_get($field, 'acfe_validate'))
            return $valid;
        
        foreach($field['acfe_validate'] as $k => $rule){
            
            // Fix possible ACF Clone Index
            if($k === 'acfcloneindex')
                continue;
            
            // Screen
            $screen = isset($rule['acfe_validate_location']) ? $rule['acfe_validate_location'] : '';
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
            
            if(!acf_maybe_get($rule, 'acfe_validate_rules_and'))
                continue;
            
            $rule_match = true;
            
            foreach($rule['acfe_validate_rules_and'] as $k => $function){
                
                if(!$rule_match)
                    break;
                
                $rule_match = false;
                
                // Check filters
                $filters = array(
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/key=' . $field['key'],
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/name=' . $field['name'],
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/type=' . $field['type'],
                    'acfe/validate/function/' . $function['acfe_validate_function'],
                );
                
                $filter_call = false;
                foreach($filters as $filter){
                    
                    if(has_filter($filter))
                        $filter_call = $filter;
                    
                }
                
                // Filter
                if($filter_call){
                    
                    $result = apply_filters($filter_call, false, $value, $field);
                    
                }
                
                // Class
                elseif(is_callable(array($this, $function['acfe_validate_function']))){
                    
                    $result = call_user_func(array($this, $function['acfe_validate_function']), $value);
                    
                }
                
                // Function
                elseif(is_callable($function['acfe_validate_function'])){
                    
                    $result = call_user_func($function['acfe_validate_function'], $value);
                    
                }
                
                // Nothing
                else{
                    
                    continue;
                    
                }
                
                // Vars
                $operator = $function['acfe_validate_operator'];
                $match = $function['acfe_validate_match'];
                
                if($operator === '==' && $result == $match){
                    $rule_match = true;
                }
                
                elseif($operator === '!=' && $result != $match){
                    $rule_match = true;
                }
                
                elseif($operator === '>' && $result > $match){
                    $rule_match = true;
                }
                
                elseif($operator === '>=' && $result >= $match){
                    $rule_match = true;
                }
                
                elseif($operator === '<' && $result < $match){
                    $rule_match = true;
                }
                
                elseif($operator === '<=' && $result <= $match){
                    $rule_match = true;
                }
                
                elseif($operator === 'contains' && stripos($result, $match) !== false){
                    $rule_match = true;
                }
                
                elseif($operator === '!contains' && stripos($result, $match) === false){
                    $rule_match = true;
                }
                
                elseif($operator === 'starts' && stripos($result, $match) === 0){
                    $rule_match = true;
                }
                
                elseif($operator === '!starts' && stripos($result, $match) !== 0){
                    $rule_match = true;
                }
                
                elseif($operator === 'ends' && acfe_ends_with($result, $match)){
                    $rule_match = true;
                }
                
                elseif($operator === '!ends' && !acfe_ends_with($result, $match)){
                    $rule_match = true;
                }
                
                elseif($operator === 'true' && $result === true){
                    $rule_match = true;
                }
                
                elseif($operator === 'false' && $result === false){
                    $rule_match = true;
                }
                
                elseif($operator === 'null' && $result === null){
                    $rule_match = true;
                }
                
                elseif($operator === 'empty' && empty($result)){
                    $rule_match = true;
                }
                
                elseif($operator === '!empty' && !empty($result)){
                    $rule_match = true;
                }

            }
            
            // Error
            $error = $rule['acfe_validate_error'];
            
            if($rule_match && !empty($error))
                $valid = $error;
            
            if(!$valid || is_string($valid))
                break;
            
        }
        
        return $valid;
        
    }
    
    function get_user_by_id($value){
        
        return get_user_by('id', $value);
        
    }

    function get_user_by_slug($value){
        
        return get_user_by('slug', $value);
        
    }

    function get_user_by_email($value){
        
        return get_user_by('email', $value);
        
    }

    function get_user_by_login($value){
        
        return get_user_by('login', $value);
        
    }
    
    function value($value){
        
        return $value;
        
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
        
        if(isset($field['acfe_validate']['acfcloneindex']))
            $field['acfe_validate'] = false;
        
        return $field;
        
    }
    
}

// initialize
new acfe_field_validation();

endif;
