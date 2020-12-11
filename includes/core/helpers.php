<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Get Flexible
 */
if(!function_exists('get_flexible')){
    
function get_flexible($selector, $post_id = false){
    
    if(!have_rows($selector, $post_id))
        return false;
    
    // Vars
    $flexible = acf_get_field_type('flexible_content');
    
    ob_start();
        
        while(have_rows($selector, $post_id)): the_row();
        
            // Vars
            $loop = acf_get_loop('active');
            $field = $loop['field'];
            
            // Bail early if not Flexible Content
            if($field['type'] !== 'flexible_content')
                break;
    
            $loop_i = acf_get_loop('active', 'i');
            $layout = $flexible->get_layout(get_row_layout(), $field);
            
            // First row
            if($loop_i === 0){
                
                // Global
                global $is_preview;
                
                // Vars
                if(!isset($is_preview))
                    $is_preview = false;
                
                $name = $field['_name'];
                $key = $field['key'];
    
                // Actions
                do_action("acfe/flexible/enqueue",              $field, $is_preview);
                do_action("acfe/flexible/enqueue/name={$name}", $field, $is_preview);
                do_action("acfe/flexible/enqueue/key={$key}",   $field, $is_preview);
            
            }
            
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
 * Flexible: have_settings()
 */
if(!function_exists('have_settings')){
    
function have_settings(){
    
    return have_rows('layout_settings');
    
}

}

/**
 * Flexible: the_settings()
 */
if(!function_exists('the_setting')){
    
function the_setting(){
    
    return the_row();
    
}

}

/**
 * have_archive()
 */
if(!function_exists('have_archive')){

function have_archive($_post_type = false){
    
    global $acfe_archive_i, $acfe_archive_post_type;
    
    $acfe_archive_post_type = false;
    
    if(!isset($acfe_archive_i) || $acfe_archive_i === 0){
    
        $acfe_archive_i = 0;
    
        $post_type = get_post_type();
        
        if(!empty($_post_type))
            $post_type = $_post_type;
        
        if(!post_type_exists($post_type))
            return false;
            
        $post_type_object = get_post_type_object($post_type);
        
        if(empty($post_type_object))
            return false;
        
        if(!isset($post_type_object->acfe_admin_archive) || empty($post_type_object->acfe_admin_archive))
            return false;

        $acfe_archive_post_type = $post_type;
        
        return true;
        
    }
    
    remove_filter('acf/pre_load_post_id', 'acfe_the_archive_post_id');
    
    return false;
    
}

}

/**
 * the_archive()
 */
if(!function_exists('the_archive')){
    
function the_archive(){
    
    global $acfe_archive_i;
    
    add_filter('acf/pre_load_post_id', 'acfe_the_archive_post_id', 10, 2);
    
    $acfe_archive_i++;
    
}

}

function acfe_the_archive_post_id($null, $post_id){
    
    if($post_id !== false)
        return $null;
    
    global $acfe_archive_post_type;
    
    if(empty($acfe_archive_post_type))
        return $null;
    
    $return = acf_get_valid_post_id($acfe_archive_post_type . '_archive');
    
    return $return;
    
}

/**
 * ACFE Flexible: Render Template
 */
function acfe_flexible_render_layout_template($layout, $field){
    
    // Global
    global $is_preview;
    
    // Vars
    $name = $field['_name'];
    $key = $field['key'];
    $l_name = $layout['name'];
    
    // File
    $file = acf_maybe_get($layout, 'acfe_flexible_render_template');
    
    // Filters
    $file = apply_filters("acfe/flexible/render/template",                                      $file, $field, $layout, $is_preview);
    $file = apply_filters("acfe/flexible/render/template/name={$name}",                         $file, $field, $layout, $is_preview);
    $file = apply_filters("acfe/flexible/render/template/key={$key}",                           $file, $field, $layout, $is_preview);
    $file = apply_filters("acfe/flexible/render/template/layout={$l_name}",                     $file, $field, $layout, $is_preview);
    $file = apply_filters("acfe/flexible/render/template/name={$name}&layout={$l_name}",        $file, $field, $layout, $is_preview);
    $file = apply_filters("acfe/flexible/render/template/key={$key}&layout={$l_name}",          $file, $field, $layout, $is_preview);
    
    // Deprecated
    $file = apply_filters_deprecated("acfe/flexible/layout/render/template/layout={$l_name}",              array($file, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/template/layout={$l_name}");
    $file = apply_filters_deprecated("acfe/flexible/layout/render/template/name={$name}&layout={$l_name}", array($file, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/template/name={$name}&layout={$l_name}");
    $file = apply_filters_deprecated("acfe/flexible/layout/render/template/key={$key}&layout={$l_name}",   array($file, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/template/key={$key}&layout={$l_name}");
    
    // Before Template
    do_action("acfe/flexible/render/before_template",                                       $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/name={$name}",                          $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/key={$key}",                            $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/layout={$l_name}",                      $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/name={$name}&layout={$l_name}",         $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/key={$key}&layout={$l_name}",           $field, $layout, $is_preview);
    
    // Deprecated
    do_action_deprecated("acfe/flexible/layout/render/before_template/layout={$l_name}",               array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/before_template/name={$name}&layout={$l_name}",  array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/name={$name}&layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/before_template/key={$key}&layout={$l_name}",    array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/key={$key}&layout={$l_name}");
    
    // Check file
    if(!empty($file)){
    
        $file_found = acfe_locate_file_path($file);
        
        if(!empty($file_found)){
            
            // Front-end
            if(!$is_preview){
                
                // Include
                include($file_found);
                
            // Preview
            }else{
    
                $path = pathinfo($file);
                $extension = $path['extension'];
    
                $file_preview = substr($file,0, -strlen($extension)-1);
                $file_preview .= '-preview.' . $extension;
    
                $file_preview = acfe_locate_file_path($file_preview);
    
                // Include
                if(!empty($file_preview)){
    
                    include($file_preview);
                    
                }else{
    
                    include($file_found);
                    
                }
                
            }
            
        }
        
    }
    
    // After Template
    do_action("acfe/flexible/render/after_template",                                        $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/name={$name}",                           $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/key={$key}",                             $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/layout={$l_name}",                       $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/name={$name}&layout={$l_name}",          $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/key={$key}&layout={$l_name}",            $field, $layout, $is_preview);
    
    // Deprecated
    do_action_deprecated("acfe/flexible/layout/render/after_template/layout={$l_name}",                array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/after_template/name={$name}&layout={$l_name}",   array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/name={$name}&layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/after_template/key={$key}&layout={$l_name}",     array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/key={$key}&layout={$l_name}");
    
}

/**
 * ACFE Flexible: Render Enqueue
 *
 * @param $layout
 * @param $field
 */
function acfe_flexible_render_layout_enqueue($layout, $field){
    
    // Global
    global $is_preview;
    
    // Vars
    $name = $field['_name'];
    $key = $field['key'];
    $l_name = $layout['name'];
    $handle = acf_slugify($name) . '-layout-' . acf_slugify($l_name);
    
    // Files
    $style = acf_maybe_get($layout, 'acfe_flexible_render_style');
    $script = acf_maybe_get($layout, 'acfe_flexible_render_script');
    
    /**
     * Actions
     */
    do_action("acfe/flexible/enqueue/layout={$l_name}",                                 $field, $layout, $is_preview);
    do_action("acfe/flexible/enqueue/name={$name}&layout={$l_name}",                    $field, $layout, $is_preview);
    do_action("acfe/flexible/enqueue/key={$key}&layout={$l_name}",                      $field, $layout, $is_preview);
    
    // Deprecated
    do_action_deprecated("acfe/flexible/layout/enqueue/layout={$l_name}",               array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/enqueue/layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/enqueue/name={$name}&layout={$l_name}",  array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/enqueue/name={$name}&layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/enqueue/key={$key}&layout={$l_name}",    array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/enqueue/key={$key}&layout={$l_name}");
    
    /**
     * Style
     */
    $style = apply_filters("acfe/flexible/render/style",                                        $style, $field, $layout, $is_preview);
    $style = apply_filters("acfe/flexible/render/style/name={$name}",                           $style, $field, $layout, $is_preview);
    $style = apply_filters("acfe/flexible/render/style/key={$key}",                             $style, $field, $layout, $is_preview);
    $style = apply_filters("acfe/flexible/render/style/layout={$l_name}",                       $style, $field, $layout, $is_preview);
    $style = apply_filters("acfe/flexible/render/style/name={$name}&layout={$l_name}",          $style, $field, $layout, $is_preview);
    $style = apply_filters("acfe/flexible/render/style/key={$key}&layout={$l_name}",            $style, $field, $layout, $is_preview);
    
    // Deprecated
    $style = apply_filters_deprecated("acfe/flexible/layout/render/style/layout={$l_name}",                array($style, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/style/layout={$l_name}");
    $style = apply_filters_deprecated("acfe/flexible/layout/render/style/name={$name}&layout={$l_name}",   array($style, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/style/name={$name}&layout={$l_name}");
    $style = apply_filters_deprecated("acfe/flexible/layout/render/style/key={$key}&layout={$l_name}",     array($style, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/style/key={$key}&layout={$l_name}");
    
    // Check
    if(!empty($style)){
        
        // URL starting with current domain
        if(stripos($style, home_url()) === 0){
            
            $style = str_replace(home_url(), '', $style);
            
        }
        
        // Locate
        $style_file = acfe_locate_file_url($style);
        
        // Front-end
        if(!empty($style_file)){
            
            wp_enqueue_style($handle, $style_file, array(), false, 'all');
            
        }
        
        // Preview
        if($is_preview && stripos($style, 'http://') !== 0 && stripos($style, 'https://') !== 0 && stripos($style, '//') !== 0){
            
            $path = pathinfo($style);
            $extension = $path['extension'];
            
            $style_preview = substr($style,0, -strlen($extension)-1);
            $style_preview .= '-preview.' . $extension;
            
            $style_preview = acfe_locate_file_url($style_preview);
            
            // Enqueue
            if(!empty($style_preview)){
                
                wp_enqueue_style($handle . '-preview', $style_preview, array(), false, 'all');
                
            }
            
        }
        
    }
    
    /**
     * Script
     */
    $script = apply_filters("acfe/flexible/render/script",                                      $script, $field, $layout, $is_preview);
    $script = apply_filters("acfe/flexible/render/script/name={$name}",                         $script, $field, $layout, $is_preview);
    $script = apply_filters("acfe/flexible/render/script/key={$key}",                           $script, $field, $layout, $is_preview);
    $script = apply_filters("acfe/flexible/render/script/layout={$l_name}",                     $script, $field, $layout, $is_preview);
    $script = apply_filters("acfe/flexible/render/script/name={$name}&layout={$l_name}",        $script, $field, $layout, $is_preview);
    $script = apply_filters("acfe/flexible/render/script/key={$key}&layout={$l_name}",          $script, $field, $layout, $is_preview);
    
    // Deprecated
    $script = apply_filters_deprecated("acfe/flexible/layout/render/script/layout={$l_name}",              array($script, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/script/layout={$l_name}");
    $script = apply_filters_deprecated("acfe/flexible/layout/render/script/name={$name}&layout={$l_name}", array($script, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/script/name={$name}&layout={$l_name}");
    $script = apply_filters_deprecated("acfe/flexible/layout/render/script/key={$key}&layout={$l_name}",   array($script, $field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/script/key={$key}&layout={$l_name}");
    
    // Check
    if(!empty($script)){
    
        // URL starting with current domain
        if(stripos($script, home_url()) === 0){
    
            $script = str_replace(home_url(), '', $script);
        
        }
        
        // Locate
        $script_file = acfe_locate_file_url($script);
        
        // Front-end
        if(!$is_preview || (stripos($script, 'http://') === 0 || stripos($script, 'https://') === 0 || stripos($script, '//') === 0)){
    
            if(!empty($script_file)){
    
                wp_enqueue_script($handle, $script_file, array(), false, true);
                
            }
            
        }else{
    
            $path = pathinfo($script);
            $extension = $path['extension'];
    
            $script_preview = substr($script,0, -strlen($extension)-1);
            $script_preview .= '-preview.' . $extension;
    
            $script_preview = acfe_locate_file_url($script_preview);
    
            // Enqueue
            if(!empty($script_preview)){
        
                wp_enqueue_script($handle . '-preview', $script_preview, array(), false, true);
        
            }elseif(!empty($script_file)){
        
                wp_enqueue_script($handle, $script_file, array(), false, true);
        
            }
            
        }
        
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

/*
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

/*
 * Locate File URL
 * Check if file exists locally and return URL (supports parent/child theme)
 */
function acfe_locate_file_url($filenames){
    
    $located = '';
    
    foreach((array) $filenames as $filename){
        
        if(!$filename)
            continue;
        
        // Direct URL: https://www.domain.com/folder/file.js
        if(stripos($filename, 'http://') === 0 || stripos($filename, 'https://') === 0 || stripos($filename, '//') === 0){
    
            $located = $filename;
            break;
        
        }else{
    
            $_filename = ltrim($filename, '/\\');
            $abspath = untrailingslashit(ABSPATH);
    
            // Child Theme
            if(file_exists(STYLESHEETPATH . '/' . $_filename)){
        
                $located = get_stylesheet_directory_uri() . '/' . $_filename;
                break;
        
            }
        
            // Parent Theme
            elseif(file_exists(TEMPLATEPATH . '/' . $_filename)){
        
                $located = get_template_directory_uri() . '/' . $_filename;
                break;
        
            }

            // Direct file path
            elseif(file_exists($filename)){
    
                $located = acfe_get_abs_path_to_url($filename);
                break;
    
            }

            // ABSPATH file path
            elseif(file_exists($abspath . '/' . $_filename)){
    
                $located = acfe_get_abs_path_to_url($abspath . '/' . $_filename);
                break;
    
            }
        
            // WP Content Dir
            elseif(file_exists(WP_CONTENT_DIR . '/' . $_filename)){
        
                $located = WP_CONTENT_URL . '/' . $_filename;
                break;
        
            }
            
        }
        
    }
 
    return $located;
    
}

/*
 * Locate File Path
 * Based on wp-includes\template.php:653
 */
function acfe_locate_file_path($filenames){
    
    $located = '';
    
    foreach((array) $filenames as $filename){
        
        if(!$filename)
            continue;
        
        $_filename = ltrim($filename, '/\\');
        $abspath = untrailingslashit(ABSPATH);
        
        // Stylesheet file path
        if(file_exists(STYLESHEETPATH . '/' . $_filename)){
            
            $located = STYLESHEETPATH . '/' . $_filename;
            break;
            
        }

        // Template file path
        elseif(file_exists(TEMPLATEPATH . '/' . $_filename)){
            
            $located = TEMPLATEPATH . '/' . $_filename;
            break;
            
        }

        // Direct file path
        elseif(file_exists($filename)){
    
            $located = $filename;
            break;
    
        }

        // ABSPATH file path
        elseif(file_exists($abspath . '/' . $_filename)){
    
            $located = $abspath . '/' . $_filename;
            break;
    
        }

        // WP Content Dir
        elseif(file_exists(WP_CONTENT_DIR . '/' . $_filename)){
    
            $located = WP_CONTENT_DIR . '/' . $_filename;
            break;
    
        }
        
    }
    
    return $located;
    
}

/**
 * Convert ABSPATH . '/url' to https://www.domain.com/url
 */
function acfe_get_abs_path_to_url($path = ''){
    
    $abspath = untrailingslashit(ABSPATH);
    
    $url = str_replace($abspath, site_url(), $path);
    $url = wp_normalize_path($url);
    
    return esc_url_raw($url);
    
}

/**
 * Get Roles
 */
function acfe_get_roles($filtered_user_roles = array()){
    
    $list = array();
    
    global $wp_roles;
    
    if(is_multisite())
        $list['super_admin'] = __('Super Admin');
    
    foreach($wp_roles->roles as $role => $settings){
        
        $list[$role] = $settings['name'];
        
    }
    
    $user_roles = $list;
    
    if(!empty($filtered_user_roles)){
    
        $user_roles = array();
        
        foreach($list as $role => $role_label){
            
            if(!in_array($role, $filtered_user_roles))
                continue;
            
            $user_roles[$role] = $role_label;
            
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
            'orderby'           => 'title',
            'order'             => 'ASC',
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

function acfe_is_form_success($form_name = false){
    
    if(!acf_maybe_get_POST('_acf_form'))
        return false;
    
    $form = acfe_form_decrypt_args();
    
    if(empty($form))
        return false;
    
    if(!empty($form_name) && acf_maybe_get($form, 'name') !== $form_name)
        return false;
    
    return true;
    
}

function acfe_form_is_submitted($form_name = false){
    
    _deprecated_function('ACF Extended - Dynamic Forms: "acfe_form_is_submitted()" function', '0.8.7.5', "acfe_is_form_success()");
    
    return acfe_is_form_success($form_name);
    
}

function acfe_form_unique_action_id($form, $type){
    
    $name = $form['name'] . '-' . $type;
    
    global $acfe_form_uniqid;
    
    $acfe_form_uniqid = acf_get_array($acfe_form_uniqid);
    
    if(!isset($acfe_form_uniqid[$type])){
    
        $acfe_form_uniqid[$type] = 1;
        
    }
    
    if($acfe_form_uniqid[$type] > 1)
        $name = $name . '-' . $acfe_form_uniqid[$type];
    
    $acfe_form_uniqid[$type]++;
    
    return $name;
    
}

function acfe_form_get_actions(){
    
    return get_query_var('acfe_form_actions', array());
    
}

function acfe_form_get_action($name = false){
    
    $actions = acfe_form_get_actions();
    
    // No Action
    if(empty($actions))
        return false;
    
    // Last Action
    if(empty($name))
        return end($actions);
    
    if(isset($actions[$name]))
        return $actions[$name];
    
    return false;
    
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

function acfe_array_move(&$array, $a, $b){
    
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
    
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
		$label = $taxonomies[$taxonomy];
		$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
		
		$terms = acf_get_terms(array(
			'taxonomy'		=> $taxonomy,
			'hide_empty' 	=> false
		));
		
		// bail early if no terms
		if(empty($terms))
		    continue;
		
		// sort into hierachial order!
		if($is_hierarchical){
			
			$terms = _get_term_children( 0, $terms, $taxonomy );
			
		}
		
		// add placeholder		
		$r[ $label ] = array();
		
		// add choices
		foreach($terms as $term){
		
			$k = "{$term->term_id}"; 
			$r[$label][$k] = acf_get_term_title($term);
			
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

function acfe_array_to_string($array = array()){
	
	if(!is_array($array))
		return $array;
	
	if(empty($array))
		return false;
	
	if(acf_is_sequential_array($array)){
		
		foreach($array as $k => $v){
			
			if(!is_string($v))
				continue;
			
			return $v;
			
		}
		
	}elseif(acf_is_associative_array($array)){
		
		foreach($array as $k => $v){
			
			if(!is_string($v))
				continue;
			
			return $v;
			
		}
		
	}
	
	return false;
	
}

function acfe_get_acf_screen_id($page = ''){

    $prefix = sanitize_title( __("Custom Fields", 'acf') );
    
    if(empty($page))
        return $prefix;
    
    return $prefix . '_page_' . $page;
    
}

function acfe_is_admin_screen($modules = false){

    // bail early if not defined
    if(!function_exists('get_current_screen'))
        return false;

    // vars
    $screen = get_current_screen();

    // no screen
    if(!$screen)
        return false;
    
    $post_types = array(
        'acf-field-group',  // ACF
    );
    
    $field_group_category = false;
    
    // include ACF Extended Modules?
    if($modules){
        
        $post_types[] = 'acfe-dbt';     // Dynamic Block Type
        $post_types[] = 'acfe-dop';     // Dynamic Option Page
        $post_types[] = 'acfe-dpt';     // Dynamic Post Type
        $post_types[] = 'acfe-dt';      // Dynamic Taxonomy
        $post_types[] = 'acfe-form';    // Dynamic Form
        
        // Field Group Category
        $field_group_category = $screen->post_type === 'post' && $screen->taxonomy === 'acf-field-group-category';
        
    }
    
    if(in_array($screen->post_type, $post_types) || $field_group_category)
        return true;
    
    return false;
    
}

function acfe_is_dev(){
	
	return acf_get_setting('acfe/dev', false) || (defined('ACFE_dev') && ACFE_dev);
	
}

function acfe_is_super_dev(){
	
	return acf_get_setting('acfe/super_dev', false) || (defined('ACFE_super_dev') && ACFE_super_dev);
	
}

function acfe_update_setting($name, $value){
    
    return acf_update_setting("acfe/{$name}", $value);
    
}

function acfe_append_setting($name, $value){
    
    return acf_append_setting("acfe/{$name}", $value);
    
}

function acfe_get_setting($name, $value = null){
    
    return acf_get_setting("acfe/{$name}", $value);
    
}

function acfe_get_locations_array($locations){
    
    $return = array();
    $types = acf_get_location_rule_types();
    
    if(!$locations || !$types)
        return $return;
    
    $icon_default = 'admin-generic';
    
    $icons = array(
        'edit' => array(
            'post_type',
            'post_template',
            'post_status',
            'post_format',
            'post',
        ),
        'media-default' => array(
            'page_template',
            'page_type',
            'page_parent',
            'page',
        ),
        'admin-users' => array(
            'current_user',
            'user_form',
        ),
        'welcome-widgets-menus' => array(
            'widget',
            'nav_menu',
            'nav_menu_item',
        ),
        'category' => array(
            'taxonomy',
            'post_category',
            'post_taxonomy',
        ),
        'admin-comments' => array(
            'comment',
        ),
        'paperclip' => array(
            'attachment',
        ),
        'admin-settings' => array(
            'options_page',
        ),
        'businessman' => array(
            'current_user_role',
            'user_role',
        ),
        'admin-appearance' => array(
            'acfe_template'
        )
    );
    
    $rules = array();
    
    foreach($types as $key => $type){
        
        foreach($type as $slug => $name){
            
            $icon = $icon_default;
            
            foreach($icons as $_icon => $icon_slugs){
                
                if(!in_array($slug, $icon_slugs))
                    continue;
                
                $icon = $_icon;
                break;
                
            }
            
            $rules[$slug] = array(
                'name'  => $slug,
                'label' => $name,
                'icon'  => $icon
            );
            
        }
        
    }
    
    foreach($locations as $group){
        
        if(!acf_maybe_get($rules, $group['param']) || !acf_maybe_get($group, 'value'))
            continue;
        
        // init
        $rule = $rules[$group['param']];
        
        // vars
        $icon = $rule['icon'];
        $name = $rule['name'];
        $label = $rule['label'];
        $operator = $group['operator'] === '==' ? '=' : $group['operator'];
        $value = $group['value'];
        
        // Exception for Post/Page/page Parent ID
        if(in_array($group['param'], array('post', 'page', 'page_parent'))){
    
            $value = get_the_title((int) $value);
        
        }else{
    
            // Validate value
            $values = acf_get_location_rule_values($group);
    
            if(!empty($values) && is_array($values)){
        
                foreach($values as $value_slug => $value_name){
            
                    if($value != $value_slug)
                        continue;
            
                    $value = $value_name;
    
                    if(is_array($value_name) && isset($value_name[$value_slug])){
        
                        $value = $value_name[$value_slug];
        
                    }
            
                    break;
            
                }
        
            }
        
        }
        
        // html
        $title = $label . ' ' . $operator . ' ' . $value;
        
        $atts = array(
            'class' => 'acf-js-tooltip dashicons dashicons-' . $icon,
            'title' => $title
        );
        
        if($operator === '!='){
            
            $atts['style'] = 'color: #ccc;';
            
        }
        
        $html = '<span ' . acf_esc_attr($atts) . '></span>';
        
        $return[] = array(
            'html'              => $html,
            'icon'              => $icon,
            'title'             => $title,
            'name'              => $name,
            'label'             => $label,
            'operator'          => $operator,
            'value'             => $value,
        );
        
    }
    
    return $return;
    
}

function acfe_render_field_group_locations_html($field_group){
    
    foreach($field_group['location'] as $groups){
        
        $html = acfe_get_locations_array($groups);
        
        if($html){
            
            $array = array();
            
            foreach($html as $location){
                
                $array[] = $location['html'];
                
            }
            
            echo implode(' ', $array);
            
        }
        
    }
    
}

function acfe_unset(&$array, $key){

    if(isset($array[$key]))
        unset($array[$key]);

}

function acfe_unarray($val){
    
    if(is_array($val)){
        return reset($val);
    }
    
    return $val;
}

function acfe_get_post_id(){
    
    return acf_get_valid_post_id();
    
}

function acfe_highlight(){
    
    ini_set("highlight.comment", "#555");
    /*
    ini_set("highlight.keyword", "#0000BB"); // #4B2AFF
    ini_set("highlight.default", "#222222");
    ini_set("highlight.string", "#777777");
    */
    
    static $on = false;
    
    if ( !$on ) {
        ob_start();
    } else {
        $buffer = "<?php\n" . ob_get_contents();
        ob_end_clean();
        $code = highlight_string($buffer, true);
        
        $code = str_replace("&lt;?php<br />", '', $code);
        $code = str_replace("<code>", '', $code);
        $code = str_replace("</code>", '', $code);
        
        echo '<div class="acfe-pre-highlight">' . $code . '</div>';
    }
    
    $on = !$on;
    
}