<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Get Flexible
 */
if(!function_exists('get_flexible')){
    
function get_flexible($selector, $post_id = false){
    
    if(!have_rows($selector, $post_id))
        return;
    
    // Vars
    $field = acf_get_field($selector);
    $flexible = acf_get_field_type('flexible_content');
    $is_preview = false;
    
    // Actions
    do_action('acfe/flexible/enqueue', $field, $is_preview);
    do_action('acfe/flexible/enqueue/name=' . $field['_name'], $field, $is_preview);
    do_action('acfe/flexible/enqueue/key=' . $field['key'], $field, $is_preview);
    
    ob_start();
    
        while(have_rows($selector, $post_id)): the_row();
            
            // Vars
            $layout_name = get_row_layout();
            $layout = $flexible->get_layout($layout_name, $field);
            
            // Render: HTML Comment
            echo "\n" . '<!-- ' . $layout['label'] . ' -->' . "\n";
            
            // Render: Enqueue
            acfe_flexible_render_layout_enqueue($layout, $field);
            
            // Render: Template
            acfe_flexible_render_layout_template($layout, $field);

        endwhile;
    
    return ob_get_clean();
    
}

}

/**
 * The Flexible
 */
if(!function_exists('the_flexible')){
    
function the_flexible($selector, $post_id = false){
    
    echo get_flexible($selector, $post_id);
    
}

}

/**
 * Has Flexible
 */
if(!function_exists('has_flexible')){
    
function has_flexible($selector, $post_id = false){
    
    return have_rows($selector, $post_id);
    
}

}

/**
 * ACFE Flexible: Render Template
 */
function acfe_flexible_render_layout_template($layout, $field){
    
    // Vars
    global $is_preview;
    
    // Template
    $acfe_flexible_render_template = false;
    
    // Filters
    $acfe_flexible_render_template = apply_filters('acfe/flexible/render/template', $acfe_flexible_render_template, $field, $layout, $is_preview);
    $acfe_flexible_render_template = apply_filters('acfe/flexible/render/template/name=' . $field['_name'], $acfe_flexible_render_template, $field, $layout, $is_preview);
    $acfe_flexible_render_template = apply_filters('acfe/flexible/render/template/key=' . $field['key'], $acfe_flexible_render_template, $field, $layout, $is_preview);
    
    $acfe_flexible_render_template = apply_filters('acfe/flexible/layout/render/template/layout=' . $layout['name'], $acfe_flexible_render_template, $field, $layout, $is_preview);
    $acfe_flexible_render_template = apply_filters('acfe/flexible/layout/render/template/name=' . $field['_name'] . '&layout=' . $layout['name'], $acfe_flexible_render_template, $field, $layout, $is_preview);
    $acfe_flexible_render_template = apply_filters('acfe/flexible/layout/render/template/key=' . $field['key'] . '&layout=' . $layout['name'], $acfe_flexible_render_template, $field, $layout, $is_preview);
    
    // Render: Template
    if(!empty($acfe_flexible_render_template)){
        
        $acfe_flexible_render_template_path = false;
        
        // Full path
        if(file_exists($acfe_flexible_render_template)){
            
            $acfe_flexible_render_template_path = $acfe_flexible_render_template;
            
        }
        
        // Parent/child relative
        else{
            
            $acfe_flexible_render_template_path = locate_template(array($acfe_flexible_render_template));
            
        }
        
        // Include
        if(!empty($acfe_flexible_render_template_path))
            include($acfe_flexible_render_template_path);
        
    }
    
}

/**
 * ACFE Flexible: Render Enqueue
 */
function acfe_flexible_render_layout_enqueue($layout, $field){
    
    // Vars
    global $is_preview;
    $handle = acf_slugify($field['name']) . '-layout-' . acf_slugify($layout['name']);
    
    /**
     * Actions
     */
    do_action('acfe/flexible/layout/enqueue/layout=' . $layout['name'], $field, $layout, $is_preview);
    do_action('acfe/flexible/layout/enqueue/name=' . $field['_name'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
    do_action('acfe/flexible/layout/enqueue/key=' . $field['key'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
    
    /**
     * Style
     */
    $acfe_flexible_render_style = false;
        
    // Filters
    $acfe_flexible_render_style = apply_filters('acfe/flexible/render/style', $acfe_flexible_render_style, $field, $layout, $is_preview);
    $acfe_flexible_render_style = apply_filters('acfe/flexible/render/style/name=' . $field['_name'], $acfe_flexible_render_style, $field, $layout, $is_preview);
    $acfe_flexible_render_style = apply_filters('acfe/flexible/render/style/key=' . $field['key'], $acfe_flexible_render_style, $field, $layout, $is_preview);
    
    $acfe_flexible_render_style = apply_filters('acfe/flexible/layout/render/style/layout=' . $layout['name'], $acfe_flexible_render_style, $field, $layout, $is_preview);
    $acfe_flexible_render_style = apply_filters('acfe/flexible/layout/render/style/name=' . $field['_name'] . '&layout=' . $layout['name'], $acfe_flexible_render_style, $field, $layout, $is_preview);
    $acfe_flexible_render_style = apply_filters('acfe/flexible/layout/render/style/key=' . $field['key'] . '&layout=' . $layout['name'], $acfe_flexible_render_style, $field, $layout, $is_preview);
    
    // Enqueue
    if(!empty($acfe_flexible_render_style)){
        
        $acfe_flexible_render_style_url = false;
        
        // Full path
        if(file_exists($acfe_flexible_render_style)){
            
            $acfe_flexible_render_style_url = $acfe_flexible_render_style;
            
        }
        
        // Parent/child relative
        else{
            
            $acfe_flexible_render_style_url = acfe_locate_file_url(array($acfe_flexible_render_style));
            
        }
        
        // Include
        if(!empty($acfe_flexible_render_style_url))
            wp_enqueue_style($handle, $acfe_flexible_render_style_url, array(), false, 'all');
    
    }
    
    /**
     * Script
     */
    $acfe_flexible_render_script = false;
    
    // Filters
    $acfe_flexible_render_script = apply_filters('acfe/flexible/render/script', $acfe_flexible_render_script, $field, $layout, $is_preview);
    $acfe_flexible_render_script = apply_filters('acfe/flexible/render/script/name=' . $field['_name'], $acfe_flexible_render_script, $field, $layout, $is_preview);
    $acfe_flexible_render_script = apply_filters('acfe/flexible/render/script/key=' . $field['key'], $acfe_flexible_render_script, $field, $layout, $is_preview);
    
    $acfe_flexible_render_script = apply_filters('acfe/flexible/layout/render/script/layout=' . $layout['name'], $acfe_flexible_render_script, $field, $layout, $is_preview);
    $acfe_flexible_render_script = apply_filters('acfe/flexible/layout/render/script/name=' . $field['_name'] . '&layout=' . $layout['name'], $acfe_flexible_render_script, $field, $layout, $is_preview);
    $acfe_flexible_render_script = apply_filters('acfe/flexible/layout/render/script/key=' . $field['key'] . '&layout=' . $layout['name'], $acfe_flexible_render_script, $field, $layout, $is_preview);
    
    // Enqueue
    if(!empty($acfe_flexible_render_script)){
        
        $acfe_flexible_render_script_url = false;
        
        // Full path
        if(file_exists($acfe_flexible_render_script)){
            
            $acfe_flexible_render_script_url = $acfe_flexible_render_script;
            
        }
        
        // Parent/child relative
        else{
            
            $acfe_flexible_render_script_url = acfe_locate_file_url(array($acfe_flexible_render_script));
            
        }
        
        // Include
        if(!empty($acfe_flexible_render_script_url))
            wp_enqueue_script($handle, $acfe_flexible_render_script_url, array(), false, true);
        
    }
    
}

/**
 * Get Field Group from Field
 */
function acfe_get_field_group_from_field($field){
    
    $field_parent = $field['parent'];
    
    if(!$field_ancestors = acf_get_field_ancestors($field))
        return acf_get_field_group($field_parent);
    
    // Reverse for DESC order (Top field first)
    $field_ancestors = array_reverse($field_ancestors);
    
    $field_top_ancestor = $field_ancestors[0];
    $field_top_ancestor = acf_get_field($field_top_ancestor);
    
    return acf_get_field_group($field_top_ancestor['parent']);
    
}

/**
 * Is Json
 * Source: https://stackoverflow.com/a/6041773
 */
function acfe_is_json($string){
    
    // in case string = 1
    if(is_numeric($string))
        return false;
    
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
    
}

/**
 * Get Roles
 */
function acfe_get_roles(){
    
    global $wp_roles;
    $choices = array();
    
    if(is_multisite())
        $choices['super_admin'] = __('Super Admin');
    
    foreach($wp_roles->roles as $role => $settings){
        $choices[$role] = $settings['name'];
    }
    
    return $choices;
    
}

/**
 * Get Current Roles
 */
function acfe_get_current_user_roles(){
    
    global $current_user;
    
    if(!is_object($current_user) || !isset($current_user->roles))
        return false;
    
    $roles = $current_user->roles;
    if(is_multisite() && current_user_can('setup_network'))
        $roles[] = 'super_admin';
    
    return $roles;
    
}

/**
 * Folder Exists
 */
function acfe_folder_exists($folder){
    
    if(!is_dir(ACFE_THEME_PATH . '/' . $folder))
        return false;
    
    return true;
    
}

/**
 * Array Keys Recursive
 */
function acfe_array_keys_r($array){

    $keys = array_keys($array);

    foreach($array as $i){
        
        if(!is_array($i))
            continue;
        
        $keys = array_merge($keys, acfe_array_keys_r($i));
        
    }

    return $keys;
    
}

/**
 * Locate File URL
 * Check if file exists locally and return URL (supports parent/child theme)
 */
function acfe_locate_file_url($filenames){
    
    $located = '';
    
    foreach((array) $filenames as $filename){
        
        if(!$filename)
            continue;
        
        // Child
        if(file_exists(STYLESHEETPATH . '/' . $filename)){
            
            $located = get_stylesheet_directory_uri() . '/' . $filename;
            break;
            
        }
        
        // Parent
        elseif(file_exists(TEMPLATEPATH . '/' . $filename)){
            
            $located = get_template_directory_uri() . '/' . $filename;
            break;
            
        }
        
    }
 
    return $located;
    
}