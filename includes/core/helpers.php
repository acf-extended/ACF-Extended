<?php

if(!defined('ABSPATH'))
    exit;

/*
 * ACFE: Get Flexible
 * Helper for the Flexible Content: Dynamic Render
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

/*
 * ACFE: The Flexible
 * Helper for the Flexible Content: Dynamic Render
 */
if(!function_exists('the_flexible')){
    
function the_flexible($selector, $post_id = false){
    
    echo get_flexible($selector, $post_id);
    
}

}

/*
 * ACFE: Has Flexible
 * Helper for the Flexible Content: Dynamic Render
 */
if(!function_exists('has_flexible')){
    
function has_flexible($selector, $post_id = false){
    
    return have_rows($selector, $post_id);
    
}

}

/*
 * ACFE: Have Settings
 * While() loop function for the Flexible Content: Settings Modal feature
 */
if(!function_exists('have_settings')){
    
function have_settings(){
    
    return have_rows('layout_settings');
    
}

}

/*
 * ACFE: The Setting
 * Setup data for the Flexible Content: Settings Modal loop
 */
if(!function_exists('the_setting')){
    
function the_setting(){
    
    return the_row();
    
}

}

/*
 * ACFE: Have Archive
 * While() loop function for the Dynamic Post Type: Archive Page feature
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

/*
 * ACFE: The Archive
 * Setup data for the Dynamic Post Type: Archive Page feature
 */
if(!function_exists('the_archive')){
    
function the_archive(){
    
    global $acfe_archive_i;
    
    add_filter('acf/pre_load_post_id', 'acfe_the_archive_post_id', 10, 2);
    
    $acfe_archive_i++;
    
}

}

/*
 * ACFE: The Archive Post ID
 * Dynamic Post Type: Archive Page helper
 */
function acfe_the_archive_post_id($null, $post_id){
    
    if($post_id !== false)
        return $null;
    
    global $acfe_archive_post_type;
    
    if(empty($acfe_archive_post_type))
        return $null;
    
    $return = acf_get_valid_post_id($acfe_archive_post_type . '_archive');
    
    return $return;
    
}

/*
 * ACFE: Flexible Render Layout Template
 * Find & include the Flexible Content Layouts PHP files
 */
function acfe_flexible_render_layout_template($layout, $field){
    
    // Global
    global $is_preview, $col;
    $col = false;
    
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

/*
 * ACFE: Flexible Render Layout Enqueue
 * Find & Enqueue Scripts & Styles files for the Flexible Content
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

/*
 * ACFE: Get Field Group from Field
 * Retrieve the Field Group, starting from any field or sub field
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

/*
 * ACFE: Is Json
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
 * ACFE: Array Keys Recursive
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
 * ACFE: Locate File URL
 * Similar to locate_template(), but locate File URL instead
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
 * ACFE: Locate File Path
 * Similar to locate_template(), but locate File Path instead
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

/*
 * ACFE: Get Absolute Path to URL
 * Convert ABSPATH . '/url' to https://www.domain.com/url
 */
function acfe_get_abs_path_to_url($path = ''){
    
    $abspath = untrailingslashit(ABSPATH);
    
    $url = str_replace($abspath, site_url(), $path);
    $url = wp_normalize_path($url);
    
    return esc_url_raw($url);
    
}

/*
 * ACFE: Get Roles
 * Retrieve all available roles (working with WPMU)
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

/*
 * ACFE: Get Current User Roles
 * Retrieve currently logged user roles
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

/*
 * ACFE: Get Post Types Objects
 * Query & retrieve post types objects
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

/*
 * ACFE: Get Taxonomy Objects
 * Query & retrieve taxonomies objects
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

/*
 * ACFE: Get Pretty Post Statuses
 * Similar to acf_get_pretty_post_types() but for Post Statuses
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

/*
 * ACFE: Get Pretty Forms
 * Similar to acf_get_pretty_post_types() but for ACFE Forms
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

/*
 * ACFE: Ends with
 * Check if a strings starts with something
 */
function acfe_starts_with($haystack, $needle){
        
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);

}

/*
 * ACFE: Ends with
 * Check if a strings ends with something
 */
function acfe_ends_with($haystack, $needle){
        
    $length = strlen($needle);
    
    if($length == 0)
        return true;

    return (substr($haystack, -$length) === $needle);
    
}

/*
 * ACFE: Form Is Admin
 * Legacy way to check if current screen is back-end
 */
function acfe_form_is_admin(){
    
    _deprecated_function('ACF Extended: acfe_form_is_admin()', '0.8.8', "acfe_is_admin()");
    
    return acfe_is_admin();
    
}

/*
 * ACFE: Form Is Front
 * Legacy way to check if current screen is front-end
 */
function acfe_form_is_front(){
    
    _deprecated_function('ACF Extended: acfe_form_is_front()', '0.8.8', "acfe_is_front()");
    
    return acfe_is_front();
    
}

/*
 * ACFE: Is Front
 * Check if current screen is back-end
 */
function acfe_is_admin(){
    
    return !acfe_is_front();
    
}

/*
 * ACFE: Is Front
 * Check if current screen is front-end
 */
function acfe_is_front(){
    
    if(!is_admin() || (is_admin() && wp_doing_ajax() && (acf_maybe_get_POST('_acf_screen') === 'acfe_form' || acf_maybe_get_POST('_acf_screen') === 'acf_form')))
        return true;
    
    return false;
    
}

/*
 * ACFE: Form Decrypt Args
 * Wrapper to decrypt ACF & ACFE Forms arguments
 */
function acfe_form_decrypt_args(){
    
    if(!acf_maybe_get_POST('_acf_form'))
        return false;
    
    $form = json_decode(acf_decrypt($_POST['_acf_form']), true);
    
    if(empty($form))
        return false;
    
    return $form;
    
}

/*
 * ACFE: is Form Success
 * Check if the current page is a success form page
 */
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

/*
 * ACFE: Form is submitted
 * Legacy way to check if the current page is a success form page
 */
function acfe_form_is_submitted($form_name = false){
    
    _deprecated_function('ACF Extended - Dynamic Forms: "acfe_form_is_submitted()" function', '0.8.7.5', "acfe_is_form_success()");
    
    return acfe_is_form_success($form_name);
    
}

/*
 * ACFE: Form Unique Action ID
 * Legacy way to make actions names unique
 */
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

/*
 * ACFE: Form Get Actions
 * Retrieve all actions output
 */
function acfe_form_get_actions(){
    
    return get_query_var('acfe_form_actions', array());
    
}

/*
 * ACFE: Form Get Action
 * Retrieve the latest action output
 */
function acfe_form_get_action($name = false, $key = false){
    
    $actions = acfe_form_get_actions();
    
    // No action
    if(empty($actions))
        return false;
    
    // Action name
    if(!empty($name)){
        $return = acf_maybe_get($actions, $name, false);
    }else{
        $return = end($actions);
    }
    
    if($key !== false || is_numeric($key))
        $return = acf_maybe_get($return, $key, false);
    
    return $return;
    
}

/*
 * ACFE: Array Insert Before
 * Insert data before a specific array key
 */
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

/*
 * ACFE: Array Insert After
 * Insert data after a specific array key
 */
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

/*
 * ACFE: Array move
 * Move the array key from position $a to $b
 */
function acfe_array_move(&$array, $a, $b){
    
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
    
}

/*
 * ACFE: Add Validation Error
 * Similar to acf_add_validation_error() but allows to use field name or field key
 */
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
 * ACFE: Get Taxonomy Terms IDs
 * Similar to acf_get_taxonomy_terms()
 * Returns "array('256' => 'Category name')" instead of "array('category:category_name' => 'Category name')"
 */
function acfe_get_taxonomy_terms_ids($taxonomies = array()){
    
    // force array
    $taxonomies = acf_get_array($taxonomies);
    
    // get pretty taxonomy names
    $taxonomies = acf_get_taxonomy_labels($taxonomies);
    
    // vars
    $r = array();
    
    // populate $r
    foreach(array_keys($taxonomies) as $taxonomy){
        
        // vars
        $label = $taxonomies[$taxonomy];
        $is_hierarchical = is_taxonomy_hierarchical($taxonomy);
        
        $terms = acf_get_terms(array(
            'taxonomy'      => $taxonomy,
            'hide_empty'    => false
        ));
        
        // bail early if no terms
        if(empty($terms))
            continue;
        
        // sort into hierachial order!
        if($is_hierarchical){
            
            $terms = _get_term_children(0, $terms, $taxonomy);
            
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

/*
 * ACFE: Get Term Level
 * Retrive the Term Level number
 */
function acfe_get_term_level($term, $taxonomy){
    
    $ancestors = get_ancestors($term, $taxonomy);
    
    return count($ancestors) + 1;
    
}

/*
 * ACFE: Numer Suffix
 * Adds 1"st", 2"nd", 3"rd" to number
 */
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

/*
 * ACFE: Array to String
 * Convert an array to string
 */
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

/*
 * ACFE: Get ACF Screen ID
 * Legacy way to check if the current admin screen is ACF Field Group UI, ACF Tools, ACF Updates screens etc...
 */
function acfe_get_acf_screen_id($page = ''){

    $prefix = sanitize_title( __("Custom Fields", 'acf') );
    
    if(empty($page))
        return $prefix;
    
    return $prefix . '_page_' . $page;
    
}

/*
 * ACFE: Is Admin Screen
 * Check if the current admin screen is ACF Field Group UI, ACF tools, ACF Updates screens etc...
 */
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
        
        // Reserved
        $post_types = array_merge($post_types, acfe_get_setting('reserved_post_types', array()));
        
        // Field Group Category
        $field_group_category = $screen->post_type === 'post' && $screen->taxonomy === 'acf-field-group-category';
        
    }
    
    if(in_array($screen->post_type, $post_types) || $field_group_category)
        return true;
    
    return false;
    
}

/*
 * ACFE: Is Dev
 * Check if the developer mode is enabled
 */
function acfe_is_dev(){
    
    return acf_get_setting('acfe/dev', false) || (defined('ACFE_dev') && ACFE_dev);
    
}

/*
 * ACFE: Is Super Dev
 * Only for awesome developers!
 */
function acfe_is_super_dev(){
    
    return acf_get_setting('acfe/super_dev', false) || (defined('ACFE_super_dev') && ACFE_super_dev);
    
}

/*
 * ACFE: Update Setting
 * Similar to acf_update_setting() but with the 'acfe' prefix
 */
function acfe_update_setting($name, $value){
    
    return acf_update_setting("acfe/{$name}", $value);
    
}

/*
 * ACFE: Append Setting
 * Similar to acf_append_setting() but with the 'acfe' prefix
 */
function acfe_append_setting($name, $value){
    
    return acf_append_setting("acfe/{$name}", $value);
    
}

/*
 * ACFE: Get Setting
 * Similar to acf_get_setting() but with the 'acfe' prefix
 */
function acfe_get_setting($name, $value = null){
    
    return acf_get_setting("acfe/{$name}", $value);
    
}

/*
 * ACFE: Get Locations Array
 * Legacy way to retrieve Field Groups Locations data in ACF 5.8 versions
 */
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

/*
 * ACFE: Render Field Group Locations HTML
 * Legacy way to display Field Groups Locations in ACF 5.8 versions
 */
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

/*
 * ACFE: Unset
 * Safely remove an array key
 */
function acfe_unset(&$array, $key){

    if(isset($array[$key]))
        unset($array[$key]);

}

/*
 * ACFE: Unarray
 * Retrieve and return only the first value of an array
 */
function acfe_unarray($val){
    
    if(is_array($val)){
        return reset($val);
    }
    
    return $val;
}

/*
 * ACFE: Get Post ID
 * Universal way to always retrieve the correct ACF Post ID on front-end and back-end
 * Return ACF formatted Post ID. ie: 12|term_24|user_56|my-options
 */
function acfe_get_post_id(){
    
    // Admin
    if(acfe_is_admin()){
        
        // Legacy ACF method
        $post_id = acf_get_valid_post_id();
        
        // Exclude local meta post ids
        if(function_exists('acfe_get_local_post_ids')){
    
            $exclude_post_ids = acfe_get_local_post_ids();
    
            if(in_array($post_id, $exclude_post_ids))
                $post_id = false;
            
        }
    
        if($post_id)
            return $post_id;
    
        // ACF Form Data
        $post_id = acf_get_form_data('post_id');
    
        // $_POST['_acf_post_id']
        if(!$post_id){
            $post_id = acf_maybe_get_POST('_acf_post_id');
        }
    
        // $_REQUEST['post']
        if(!$post_id){
            $post_id = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : 0;
        }
    
        // $_REQUEST['post_id'] - ACF Block Type
        if(!$post_id){
            $post_id = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
        }
        
        // $_REQUEST['user_id']
        if(!$post_id){
            $post_id = isset($_REQUEST['user_id']) ? 'user_' . absint($_REQUEST['user_id']) : 0;
        }
        
        // $_REQUEST['tag_ID']
        if(!$post_id){
            $post_id = isset($_REQUEST['tag_ID']) ? 'term_' . absint($_REQUEST['tag_ID']) : 0;
        }
        
        // Default
        if(!$post_id){
            $post_id = 0;
        }
        
        
    // Front
    }else{
        
        // vars
        $object = get_queried_object();
        $post_id = 0;
    
        if(is_object($object)){
    
            // Post
            if(isset($object->post_type, $object->ID)){
        
                $post_id = $object->ID;
            
            // Post Type Archive
            }elseif(isset($object->hierarchical, $object->name, $object->acfe_admin_archive)){
                
                // Validate with ACF filter (for multilang)
                $post_id = acf_get_valid_post_id($object->name . '_archive');
    
            // User
            }elseif(isset($object->roles, $object->ID)){
        
                $post_id = 'user_' . $object->ID;
        
            // Term
            }elseif(isset($object->taxonomy, $object->term_id)){
        
                $post_id = 'term_' . $object->term_id;
        
            // Comment
            }elseif(isset($object->comment_ID)){
        
                $post_id = 'comment_' . $object->comment_ID;
        
            }
            
        }
        
    }
    
    return $post_id;
    
}

/*
 * ACFE: Highlight Code
 */
function acfe_highlight(){
    
    ini_set("highlight.comment", "#555");
    
    static $on = false;
    
    if(!$on){
        
        ob_start();
        
    }else{
        
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

/*
 * ACFE: Remove Class Filter
 * Remove hook from inaccessible PHP class
 * https://gist.github.com/tripflex/c6518efc1753cf2392559866b4bd1a53
 */
function acfe_remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
    
    global $wp_filter;
    
    // Check that filter actually exists first
    if ( ! isset( $wp_filter[ $tag ] ) ) {
        return FALSE;
    }
    
    /**
     * If filter config is an object, means we're using WordPress 4.7+ and the config is no longer
     * a simple array, rather it is an object that implements the ArrayAccess interface.
     *
     * To be backwards compatible, we set $callbacks equal to the correct array as a reference (so $wp_filter is updated)
     *
     * @see https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/
     */
    if ( is_object( $wp_filter[ $tag ] ) && isset( $wp_filter[ $tag ]->callbacks ) ) {
        // Create $fob object from filter tag, to use below
        $fob       = $wp_filter[ $tag ];
        $callbacks = &$wp_filter[ $tag ]->callbacks;
    } else {
        $callbacks = &$wp_filter[ $tag ];
    }
    
    // Exit if there aren't any callbacks for specified priority
    if ( ! isset( $callbacks[ $priority ] ) || empty( $callbacks[ $priority ] ) ) {
        return FALSE;
    }
    
    // Loop through each filter for the specified priority, looking for our class & method
    foreach ( (array) $callbacks[ $priority ] as $filter_id => $filter ) {
        
        // Filter should always be an array - array( $this, 'method' ), if not goto next
        if ( ! isset( $filter['function'] ) || ! is_array( $filter['function'] ) ) {
            continue;
        }
        
        // If first value in array is not an object, it can't be a class
        if ( ! is_object( $filter['function'][0] ) ) {
            continue;
        }
        
        // Method doesn't match the one we're looking for, goto next
        if ( $filter['function'][1] !== $method_name ) {
            continue;
        }
        
        // Method matched, now let's check the Class
        if ( get_class( $filter['function'][0] ) === $class_name ) {
            
            // WordPress 4.7+ use core remove_filter() since we found the class object
            if ( isset( $fob ) ) {
                // Handles removing filter, reseting callback priority keys mid-iteration, etc.
                $fob->remove_filter( $tag, $filter['function'], $priority );
                
            } else {
                // Use legacy removal process (pre 4.7)
                unset( $callbacks[ $priority ][ $filter_id ] );
                // and if it was the only filter in that priority, unset that priority
                if ( empty( $callbacks[ $priority ] ) ) {
                    unset( $callbacks[ $priority ] );
                }
                // and if the only filter for that tag, set the tag to an empty array
                if ( empty( $callbacks ) ) {
                    $callbacks = array();
                }
                // Remove this filter from merged_filters, which specifies if filters have been sorted
                unset( $GLOBALS['merged_filters'][ $tag ] );
            }
            
            return TRUE;
        }
    }
    
    return FALSE;
}

function acfe_remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
    return acfe_remove_class_filter( $tag, $class_name, $method_name, $priority );
}

/*
 * ACFE: Get Fields
 * Similar to get_fields() but with field keys only. Useful to inject & preload values
 */
function acfe_get_fields($post_id = false, $format_value = false){
    
    // vars
    $fields = get_field_objects($post_id, $format_value);
    $meta = array();
    
    // bail early
    if(!$fields)
        return false;
    
    // populate
    foreach($fields as $k => $field){
        
        $meta[ $field['key'] ] = $field['value'];
        
    }
    
    // return
    return $meta;

}

/*
 * ACFE: Is Dynamic Preview
 * Check if currently in ACFE FlexibleContent Preview or ACF Block Type Preview
 */
function acfe_is_dynamic_preview(){
    
    global $is_preview;
    
    // Flexible Content
    if(isset($is_preview) && $is_preview){
        
        return true;
        
    // Block Type
    }elseif(wp_doing_ajax() && acf_maybe_get_POST('query')){
        
        $query = acf_maybe_get_POST('query');
        
        if(acf_maybe_get($query, 'preview'))
            return true;
        
    }
    
    return false;
    
}

/*
 * ACFE: Is Gutenberg
 * Check if current screen is block editor
 */
function acfe_is_gutenberg(){
    
    // bail early if not defined
    if(!function_exists('get_current_screen')) return false;
    
    // vars
    $current_screen = get_current_screen();
    
    // no screen
    if(!$current_screen) return false;
    
    // check screen
    if((method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) || (function_exists('is_gutenberg_page') && is_gutenberg_page())){
        return true;
    }
    
    // return false
    return false;
    
}

/*
 * ACFE: Maybe Get
 * Similar to acf_maybe_get() but also works with OBJECTS
 */
function acfe_maybe_get($array = array(), $key = 0, $default = null){
    
    if(is_object($array)){
        return isset($array->{$key}) ? $array->{$key} : $default;
    }
    
    return acf_maybe_get($array, $key, $default);
    
}

/*
 * ACF: Maybe Get REQUEST
 * Similar to acf_maybe_get_POST() but works with $_REQUEST
 */
function acfe_maybe_get_REQUEST($key = '', $default = null){
    
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    
}

/*
 * ACFE: Extract Sub Field form Layout & Set Value
 */
function acfe_extract_sub_field(&$layout, $name, $value){
    
    $sub_field = false;
    
    // loop
    foreach($layout['sub_fields'] as $k => $row){
        
        if($row['name'] !== $name)
            continue;
        
        $sub_field = acf_extract_var($layout['sub_fields'], $k);
        break;
        
    }
    
    if(!$sub_field)
        return false;
    
    // Reset keys
    $layout['sub_fields'] = array_values($layout['sub_fields']);
    
    // Add value
    if(isset($value[$sub_field['key']])){
        
        $sub_field['value'] = $value[$sub_field['key']];
        
    }elseif(isset($sub_field['default_value'])){
        
        $sub_field['value'] = $sub_field['default_value'];
        
    }
    
    return $sub_field;
    
}

/*
 * Clone of wp_get_registered_image_subsizes (WP 5.3 only)
 * https://developer.wordpress.org/reference/functions/wp_get_registered_image_subsizes/
 */
function acfe_get_registered_image_sizes($filter = false){
    
    $additional_sizes   = wp_get_additional_image_sizes();
    $all_sizes          = array();
    
    $wp_sizes           = get_intermediate_image_sizes();
    $wp_sizes[]         = 'full';
    
    foreach($wp_sizes as $size_name){
        
        if($filter && $size_name !== $filter)
            continue;
        
        $size_data = array(
            'name'   => $size_name,
            'width'  => 0,
            'height' => 0,
            'crop'   => false,
        );
        
        // For sizes added by plugins and themes.
        if(isset( $additional_sizes[ $size_name ]['width'])){
            $size_data['width'] = (int) $additional_sizes[ $size_name ]['width'];
            // For default sizes set in options.
        }else{
            $size_data['width'] = (int) get_option("{$size_name}_size_w");
        }
        
        if(isset($additional_sizes[ $size_name ]['height'])){
            $size_data['height'] = (int) $additional_sizes[ $size_name ]['height'];
        }else{
            $size_data['height'] = (int) get_option("{$size_name}_size_h");
        }
        
        if(isset($additional_sizes[ $size_name ]['crop'])){
            $size_data['crop'] = $additional_sizes[ $size_name ]['crop'];
        }else{
            $size_data['crop'] = get_option("{$size_name}_crop");
        }
        
        if(!is_array( $size_data['crop']) || empty($size_data['crop'])){
            $size_data['crop'] = (bool) $size_data['crop'];
        }
        
        $all_sizes[ $size_name ] = $size_data;
        
    }
    
    if($filter && isset($all_sizes[ $filter ]))
        return $all_sizes[ $filter ];
    
    return $all_sizes;
    
}