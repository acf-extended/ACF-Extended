<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Setting: ACFE addon functions
 */
add_filter('acfe/validate/function/acfe_get_user_by_id', 'acfe_get_user_by_id', 10, 3);
function acfe_get_user_by_id($result, $value, $field){
    return get_user_by('id', $value);
}

add_filter('acfe/validate/function/acfe_get_user_by_slug', 'acfe_get_user_by_slug', 10, 3);
function acfe_get_user_by_slug($result, $value, $field){
    return get_user_by('slug', $value);
}

add_filter('acfe/validate/function/acfe_get_user_by_email', 'acfe_get_user_by_email', 10, 3);
function acfe_get_user_by_email($result, $value, $field){
    return get_user_by('email', $value);
}

add_filter('acfe/validate/function/acfe_get_user_by_login', 'acfe_get_user_by_login', 10, 3);
function acfe_get_user_by_login($result, $value, $field){
    return get_user_by('login', $value);
}

add_filter('acfe/validate/function/acfe_value', 'acfe_value', 10, 3);
function acfe_value($result, $value, $field){
    return $value;
}

/**
 * Setting: Native functions
 */
add_filter('acfe/validate/functions', 'acfe_validate_functions', 0);
function acfe_validate_functions($choices){
    
    return array(
        'Global' => array(
            'acfe_value'               => 'Value (acfe_value)',
        ),
        
        'Exists' => array(
            'email_exists'              => 'Email exists (email_exists)',
            'post_type_exists'          => 'Post type exists (post_type_exists)',
            'taxonomy_exists'           => 'Taxonomy exists (taxonomy_exists)',
            'term_exists'               => 'Term exists (term_exists)',
            'username_exists'           => 'Username exists (username_exists)',
        ),
        
        'Is' => array(
            'is_email'                  => 'Is email (is_email)',
        ),
        
        'Sanitize' => array(
            'sanitize_email'            => 'Sanitize email (sanitize_email)',
            'sanitize_file_name'        => 'Sanitize file name (sanitize_file_name)',
            'sanitize_html_class'       => 'Sanitize html class (sanitize_html_class)',
            'sanitize_key'              => 'Sanitize key (sanitize_key)',
            'sanitize_meta'             => 'Sanitize meta (sanitize_meta)',
            'sanitize_mime_type'        => 'Sanitize mime type (sanitize_mime_type)',
            'sanitize_option'           => 'Sanitize option (sanitize_option)',
            'sanitize_text_field'       => 'Sanitize text field (sanitize_text_field)',
            'sanitize_title'            => 'Sanitize title (sanitize_title)',
            'sanitize_user'             => 'Sanitize user (sanitize_user)',
        ),
        
        'User' => array(
            'acfe_get_user_by_id'     => 'Get user by id (acfe_get_user_by_id)',
            'acfe_get_user_by_slug'   => 'Get user by slug (acfe_get_user_by_slug)',
            'acfe_get_user_by_email'  => 'Get user by email (acfe_get_user_by_email)',
            'acfe_get_user_by_login'  => 'Get user by login (acfe_get_user_by_login)',
            'is_user_logged_in'         => 'Is user logged in (is_user_logged_in)',
        )
    );
    
}

/**
 * Exclude layout advanced fields
 */
add_filter('acfe/validate/exclude', 'acfe_validate_exclude', 0, 2);
function acfe_validate_exclude($exclude, $type){
    
    $excludes = array('message', 'accordion', 'tab', 'group', 'repeater', 'flexible_content', 'clone', 'acfe_dynamic_message');
    if(in_array($type, $excludes))
        $exclude = true;
    
    return $exclude;
    
}

foreach(acf_get_field_types_info() as $field){
    
    $type = $field['name'];
    
    $exclude = apply_filters('acfe/validate/exclude', false, $type);
    if($exclude)
        continue;
    
    add_action('acf/render_field_settings/type=' . $type, 'acfe_validation_settings', 990);
    
}

/**
 * Add Setting
 */
function acfe_validation_settings($field){
    
    $exclude = apply_filters('acfe/validate/exclude', false, $field);
    if($exclude)
        return;
    
    $choices = apply_filters('acfe/validate/functions', array(), $field);
    if(empty($choices))
        return;
    
    // Settings
    acf_render_field_setting($field, array(
        'label'         => __('Validation'),
        'name'          => 'acfe_validate',
        'key'           => 'acfe_validate',
        'instructions'  => __('Validate value against rules'),
        'type'          => 'repeater',
        'button_label'  => __('Add rule'),
        'required'      => false,
        'layout'        => 'row',
        'sub_fields'    => array(
            array(
                'label'         => __('Rules'),
                'name'          => 'acfe_validate_rules_and',
                'key'           => 'acfe_validate_rules_and',
                'instructions'  => '',
                'type'          => 'repeater',
                'button_label'  => __('+ AND'),
                'required'      => false,
                'layout'        => 'table',
                'sub_fields'    => array(
                    array(
                        'label'         => 'Function',
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
                        'label'         => 'Operator',
                        'name'          => 'acfe_validate_operator',
                        'key'           => 'acfe_validate_operator',
                        'prefix'        => '',
                        '_name'         => '',
                        '_prepare'      => '',
                        'type'          => 'select',
                        'choices'       => array(
                            '==' => 'Equal',
                            '!=' => 'Not equal',
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
                        'label'         => 'Match',
                        'name'          => 'acfe_validate_match',
                        'key'           => 'acfe_validate_match',
                        'prefix'        => '',
                        '_name'         => '',
                        '_prepare'      => '',
                        'type'          => 'select',
                        'choices'       => array(
                            'true'  => 'True',
                            'false' => 'False',
                            'empty' => 'Empty',
                        ),
                        'instructions'  => false,
                        'required'      => false,
                        'wrapper'       => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                    ),
                )
            ),
            array(
                'label'         => 'Error',
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
 * Process Setting
 */
add_filter('acf/validate_value', 'acfe_validate_value', 99, 4);
function acfe_validate_value($valid, $value, $field, $input){
    
    if(!$valid)
        return $valid;
    
    if(!isset($field['acfe_validate']) || empty($field['acfe_validate']))
        return $valid;
    
    $exclude = apply_filters('acfe/validate/exclude', false, $field);
    if($exclude)
        return $valid;
    
    foreach($field['acfe_validate'] as $orkey => $rules){
        
        // Fix possible ACF Clone Index
        if($orkey === 'acfcloneindex')
            continue;
        
        $acfe_validate_rules_and = isset($rules['acfe_validate_rules_and']) && !empty($rules['acfe_validate_rules_and']);
        if(!$acfe_validate_rules_and)
            continue;
        
        $rule_match = true;
        
        foreach($rules['acfe_validate_rules_and'] as $andkey => $function){
            
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
            
            if(!$filter_call && !is_callable($function['acfe_validate_function']))
                continue;
            
            // Apply Filter
            if($filter_call)
                $result = apply_filters($filter_call, false, $value, $field);
            
            // [or] Call Function
            else
                $result = call_user_func($function['acfe_validate_function'], $value);
            
            // Vars
            $operator = $function['acfe_validate_operator'];
            $match = $function['acfe_validate_match'];
            
            // Equal
            if($operator === '==' && (($match === 'true' && $result) || ($match === 'false' && !$result) || ($match === 'empty' && empty($result)))){
                $rule_match = true;
            }
            
            // Not Equal
            elseif($operator === '!=' && (($match === 'true' && !$result) || ($match === 'false' && $result) || ($match === 'empty' && !empty($result)))){
                $rule_match = true;
            }

        }
        
        // Error
        $error = $rules['acfe_validate_error'];
        
        if($rule_match && !empty($error))
            $valid = $error;
        
        if(!$valid || is_string($valid))
            break;
        
    }
    
    return $valid;
    
}

/**
 * Process Setting: Variations
 */
if(function_exists('acf_add_filter_variations'))
    acf_add_filter_variations('acfe/validate/exclude', array('type', 'name', 'key'), 1);

/**
 * Setting: ACF Clone Index fix for flexible duplicate
 */
add_filter('acf/update_field', 'acfe_validate_value_clone_index');
function acfe_validate_value_clone_index($field){
    
    if(isset($field['acfe_validate']['acfcloneindex']))
        $field['acfe_validate'] = false;
    
    return $field;
    
}