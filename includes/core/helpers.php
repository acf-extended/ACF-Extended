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
        if(!empty($acfe_flexible_render_template_path)){
            
            do_action('acfe/flexible/render/before_template', $field, $layout, $is_preview);
            do_action('acfe/flexible/render/before_template/name=' . $field['_name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/render/before_template/key=' . $field['key'], $field, $layout, $is_preview);
            
            do_action('acfe/flexible/layout/render/before_template/layout=' . $layout['name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/layout/render/before_template/name=' . $field['_name'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/layout/render/before_template/key=' . $field['key'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
            
            include($acfe_flexible_render_template_path);
            
            do_action('acfe/flexible/render/after_template', $field, $layout, $is_preview);
            do_action('acfe/flexible/render/after_template/name=' . $field['_name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/render/after_template/key=' . $field['key'], $field, $layout, $is_preview);
            
            do_action('acfe/flexible/layout/render/after_template/layout=' . $layout['name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/layout/render/after_template/name=' . $field['_name'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
            do_action('acfe/flexible/layout/render/after_template/key=' . $field['key'] . '&layout=' . $layout['name'], $field, $layout, $is_preview);
            
        }
        
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
        
        // URL: https://www.domain.com/template/style.js
        if(stripos($acfe_flexible_render_style, 'http://') === 0 || stripos($acfe_flexible_render_style, 'https://') === 0 || stripos($acfe_flexible_render_style, '//') === 0){
            
            $acfe_flexible_render_style_url = $acfe_flexible_render_style;
            
        }
        
        // Path: template/style.css
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
        
        // URL: https://www.domain.com/template/script.js
        if(stripos($acfe_flexible_render_script, 'http://') === 0 || stripos($acfe_flexible_render_script, 'https://') === 0 || stripos($acfe_flexible_render_script, '//') === 0){
            
            $acfe_flexible_render_script_url = $acfe_flexible_render_script;
            
        }
        
        // Path: template/script.js
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
    
    if(!acf_maybe_get($field, 'parent'))
        return false;
    
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
 * Add custom key to fields and all sub fields
 */
function acfe_field_add_key_recursive(&$field, $key, $value){
    
    if(isset($field['sub_fields'])){
        
        foreach($field['sub_fields'] as &$sub_field){
            
            acfe_field_add_key_recursive($sub_field, $key, $value);
            
        }
        
    }
    
    elseif(isset($field['layouts'])){
        
        foreach($field['layouts'] as &$layout){
            
            acfe_field_add_key_recursive($layout, $key, $value);
            
        }
        
    }
    
    $field[$key] = $value;
    
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

/**
 * Get Roles
 */
function acfe_get_roles($user_roles = array()){
    
	if(empty($user_roles)){
        
        $user_roles = array();
        
        global $wp_roles;
        
        if(is_multisite())
            $user_roles['super_admin'] = __('Super Admin');
        
        foreach($wp_roles->roles as $role => $settings){
            
            $user_roles[$role] = $settings['name'];
            
        }
		
	}
    
    return $user_roles;
    
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
 * Get post types objects
 */
function acfe_get_post_type_objects($args = array()){
    
    // vars
    $return = array();
    
    // Post Types
    $posts_types = acf_get_post_types($args);
    
    // Choices
    if(!empty($posts_types)){
        
        foreach($posts_types as $post_type){
            
            $post_type_object = get_post_type_object($post_type);
            
            $return[$post_type_object->name] = $post_type_object;
            
        }
        
    }
    
    return $return;
    
}

/**
 * Get taxonomy objects
 */
function acfe_get_taxonomy_objects($args = array()){
    
    // vars
    $return = array();
    
    // Post Types
    $taxonomies = acf_get_taxonomies($args);
    
    // Choices
    if(!empty($taxonomies)){
        
        foreach($taxonomies as $taxonomy){
            
            $taxonomy_object = get_taxonomy($taxonomy);
            
            $return[$taxonomy_object->name] = $taxonomy_object;
            
        }
        
    }
    
    return $return;
    
}

/**
 * Get post statuses
 */
function acfe_get_pretty_post_statuses($posts_statuses = array()){
	
	if(empty($posts_statuses)){
		
		$posts_statuses = get_post_stati(array(), 'names');
		
	}
    
    $return = array();
    
    // Choices
    if(!empty($posts_statuses)){
        
        foreach($posts_statuses as $post_status){
            
            $post_status_object = get_post_status_object($post_status);
            
            $return[$post_status_object->name] = $post_status_object->label . ' (' . $post_status_object->name . ')';
            
        }
        
    }
    
    return $return;
	
}

/**
 * Get forms
 */
function acfe_get_pretty_forms($forms = array()){
	
	if(empty($forms)){
        
        $forms = get_posts(array(
            'post_type'         => 'acfe-form',
            'posts_per_page'    => -1,
            'fields'            => 'ids',
        ));
		
	}
    
    $return = array();
    
    // Choices
    if(!empty($forms)){
        
        foreach($forms as $form_id){
            
            $form_name = get_the_title($form_id);
            
            $return[$form_id] = $form_name;
            
        }
        
    }
    
    return $return;
	
}

/**
 * Starts with
 */
function acfe_starts_with($haystack, $needle){
        
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);

}

/**
 * Ends with
 */
function acfe_ends_with($haystack, $needle){
        
    $length = strlen($needle);
    
    if($length == 0)
        return true;

    return (substr($haystack, -$length) === $needle);
    
}

add_filter('acf/load_field', 'acfe_load_field');
function acfe_load_field($field){
    
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group', 'acf_page_acf-tools')))
        return $field;
    
    // Everywhere
    $field = apply_filters('acfe/load_field', $field);
    
    // Admin
    if(acfe_form_is_admin()){
        
        $field = apply_filters('acfe/load_field_admin', $field);
        
    }
    
    // Front
    elseif(acfe_form_is_front()){
        
        $field = apply_filters('acfe/load_field_front', $field);
        
    }
    
    return $field;
    
}

if(function_exists('acf_add_filter_variations')){
    
    acf_add_filter_variations('acfe/load_field', array('type', 'name', 'key'), 0);
    acf_add_filter_variations('acfe/load_field_front', array('type', 'name', 'key'), 0);
    acf_add_filter_variations('acfe/load_field_admin', array('type', 'name', 'key'), 0);
    
}

function acfe_form_is_admin(){
    
    if((is_admin() && !wp_doing_ajax()) || (is_admin() && wp_doing_ajax() && acf_maybe_get_POST('_acf_screen') !== 'acfe_form' && acf_maybe_get_POST('_acf_screen') !== 'acf_form'))
        return true;
    
    return false;
    
}

function acfe_form_is_front(){
    
    if(!is_admin() || (is_admin() && wp_doing_ajax() && (acf_maybe_get_POST('_acf_screen') === 'acfe_form' || acf_maybe_get_POST('_acf_screen') === 'acf_form')))
        return true;
    
    return false;
    
}

function acfe_form_decrypt_args(){
    
    if(!acf_maybe_get_POST('_acf_form'))
        return false;
    
    $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
    
    if(empty($form))
        return false;
    
    return $form;
    
}

function acfe_array_insert_before($key, array &$array, $new_key, $new_value){
    
    if(!array_key_exists($key, $array))
        return $array;
    
    $new = array();
    
    foreach($array as $k => $value){
        
        if($k === $key)
            $new[$new_key] = $new_value;
        
        $new[$k] = $value;
        
    }
    
    return $new;
    
}

function acfe_array_insert_after($key, array &$array, $new_key, $new_value){
    
    if(!array_key_exists($key, $array))
        return $array;
    
    $new = array();
    
    foreach($array as $k => $value){
        
        $new[$k] = $value;
        
        if($k === $key)
            $new[$new_key] = $new_value;
        
    }
    
    return $new;
    
}

function acfe_add_validation_error($selector = '', $message = ''){
    
    // General error
    if(empty($selector))
        return acf_add_validation_error('', $message);
    
    $row = acf_get_loop('active');
    
    if($row){
        
        $field = acf_get_sub_field($selector, $row['field']);
        
    }
    
    else{
        
        $field = acf_get_field($selector);
        
    }
    
    
    
    // Field not found: General error
    if(!$field)
        return acf_add_validation_error('', $message);
    
    // Specific field error
    add_filter('acf/validate_value/key=' . $field['key'], function($valid) use($message){
        
        return $message;
        
    });
    
}

/*
 * Similar to acf_get_taxonomy_terms() but returns array('256' => 'Category name') instead of array('category:category_name' => 'Category name')
 */
function acfe_get_taxonomy_terms_ids($taxonomies = array()){
	
	// force array
	$taxonomies = acf_get_array($taxonomies);
	
	// get pretty taxonomy names
	$taxonomies = acf_get_pretty_taxonomies( $taxonomies );
	
	// vars
	$r = array();
	
	// populate $r
	foreach( array_keys($taxonomies) as $taxonomy ) {
		
		// vars
		$label = $taxonomies[ $taxonomy ];
		$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
		$terms = acf_get_terms(array(
			'taxonomy'		=> $taxonomy,
			'hide_empty' 	=> false
		));
		
		
		// bail early i no terms
		if( empty($terms) ) continue;
		
		
		// sort into hierachial order!
		if( $is_hierarchical ) {
			
			$terms = _get_term_children( 0, $terms, $taxonomy );
			
		}
		
		
		// add placeholder		
		$r[ $label ] = array();
		
		
		// add choices
		foreach( $terms as $term ) {
		
			$k = "{$term->term_id}"; 
			$r[ $label ][ $k ] = acf_get_term_title( $term );
			
		}
		
	}
		
	
	// return
	return $r;
	
}

function acfe_get_term_level($term, $taxonomy){
    
    $ancestors = get_ancestors($term, $taxonomy);
    
    return count($ancestors) + 1;
    
}

function acfe_number_suffix($num){
    
    if(!in_array(($num % 100), array(11,12,13))){
        
        switch($num % 10){
            case 1:  return $num . 'st';
            case 2:  return $num . 'nd';
            case 3:  return $num . 'rd';
        }
        
    }
    
    return $num . 'th';
    
}