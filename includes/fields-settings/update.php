<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Setting: Native functions
 */
add_filter('acfe/update/functions', 'acfe_update_functions', 0);
function acfe_update_functions($choices){
    
    return array(
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
    );
    
}

/**
 * Exclude layout advanced fields
 */
add_filter('acfe/update/exclude', 'acfe_update_exclude', 0, 2);
function acfe_update_exclude($exclude, $type){
    
    $excludes = array('message', 'accordion', 'tab', 'group', 'repeater', 'flexible_content', 'clone', 'acfe_dynamic_message');
    if(in_array($type, $excludes))
        $exclude = true;
    
    return $exclude;
    
}

foreach(acf_get_field_types_info() as $field){
    
    $type = $field['name'];
    
    $exclude = apply_filters('acfe/update/exclude', false, $type);
    if($exclude)
        continue;
    
    add_action('acf/render_field_settings/type=' . $type, 'acfe_update_settings', 991);
    
}

/**
 * Add Setting
 */
function acfe_update_settings($field){
    
    $choices = apply_filters('acfe/update/functions', array(), $field);
    if(empty($choices))
        return;
    
    // Settings
    acf_render_field_setting($field, array(
        'label'         => __('Filters'),
        'name'          => 'acfe_update',
        'key'           => 'acfe_update',
        'instructions'  => __('Filter value right before saving'),
        'type'          => 'repeater',
        'button_label'  => __('Add filter'),
        'required'      => false,
        'sub_fields'    => array(
            array(
                'label'         => 'Function',
                'name'          => 'acfe_update_function',
                'key'           => 'acfe_update_function',
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
        )
    ), false);
    
}

/**
 * Process Setting
 */
add_filter('acf/update_value', 'acfe_update_value', 99, 3);
function acfe_update_value($value, $post_id, $field){
    
    if(!isset($field['acfe_update']) || empty($field['acfe_update']))
        return $value;
    
    $exclude = apply_filters('acfe/update/exclude/', false, $field);
    if($exclude)
        return $value;
    
    foreach($field['acfe_update'] as $vkey => $function){
        
        // Fix possible ACF Clone Index
        if($vkey === 'acfcloneindex')
            continue;
        
        // Check filters
        $filters = array(
            'acfe/update/function/' . $function['acfe_update_function'] . '/key=' . $field['key'],
            'acfe/update/function/' . $function['acfe_update_function'] . '/name=' . $field['name'],
            'acfe/update/function/' . $function['acfe_update_function'] . '/type=' . $field['type'],
            'acfe/update/function/' . $function['acfe_update_function'],
        );
        
        $filter_call = false;
        foreach($filters as $filter){
            if(has_filter($filter))
                $filter_call = $filter;
        }
        
        if(!$filter_call && !is_callable($function['acfe_update_function']))
            continue;
        
        // Apply Filter
        if($filter_call)
            $value = apply_filters($filter_call, $value, $post_id, $field);
        
        // [or] Call Function
        else
            $value = call_user_func($function['acfe_update_function'], $value);
        
    }
    
    return $value;
}

/**
 * Process Setting: Variations
 */
if(function_exists('acf_add_filter_variations'))
    acf_add_filter_variations('acfe/update/exclude', array('type', 'name', 'key'), 1);

/**
 * Setting: ACF Clone Index fix for flexible duplicate
 */
add_filter('acf/update_field', 'acfe_update_value_clone_index');
function acfe_update_value_clone_index($field){
    
    if(isset($field['acfe_update']['acfcloneindex']))
        $field['acfe_update'] = false;
    
    return $field;
    
}