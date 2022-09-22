<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * get_flexible
 *
 * Helper for the Flexible Content: Dynamic Render
 *
 * @param string $selector
 * @param false  $post_id
 *
 * @return false|string
 */
if(!function_exists('get_flexible')){
    
    function get_flexible($selector, $post_id = false){
        
        // Bail early
        if(!have_rows($selector, $post_id)){
            return false;
        }
        
        // Vars
        $flexible = acf_get_field_type('flexible_content');
        
        ob_start();
        
        while(have_rows($selector, $post_id)): the_row();
            
            // Vars
            $loop = acf_get_loop('active');
            $field = $loop['field'];
            
            // Bail early if not Flexible Content
            if($field['type'] !== 'flexible_content'){
                break;
            }
            
            $loop_i = acf_get_loop('active', 'i');
            $layout = $flexible->get_layout(get_row_layout(), $field);
            
            // First row
            if($loop_i === 0){
                
                // Global
                global $is_preview;
                
                // Vars
                if(!isset($is_preview)){
                    $is_preview = false;
                }
                
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
 * the_flexible
 *
 * Helper for the Flexible Content: Dynamic Render
 *
 * @param       $selector
 * @param false $post_id
 */
if(!function_exists('the_flexible')){
    
    function the_flexible($selector, $post_id = false){
        echo get_flexible($selector, $post_id);
    }
    
}

/**
 * has_flexible
 *
 * Helper for the Flexible Content: Dynamic Render
 *
 * @param       $selector
 * @param false $post_id
 *
 * @return bool
 */
if(!function_exists('has_flexible')){
    
    function has_flexible($selector, $post_id = false){
        return have_rows($selector, $post_id);
    }
    
}

/**
 * acfe_flexible_render_layout_template
 *
 * Find & include the Flexible Content Layouts PHP files
 *
 * @param $layout
 * @param $field
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

/**
 * acfe_flexible_render_layout_enqueue
 *
 * Find & Enqueue Scripts & Styles files for the Flexible Content
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
 * have_settings
 *
 * While loop function for the Flexible Content: Settings Modal feature
 *
 * @return bool
 */
if(!function_exists('have_settings')){
    
    function have_settings(){
        return have_rows('layout_settings');
    }
    
}

/**
 * the_setting
 *
 * Setup data for the Flexible Content: Settings Modal loop
 *
 * @return false|mixed
 */
if(!function_exists('the_setting')){
    
    function the_setting(){
        return the_row();
    }
    
}

/**
 * have_archive
 *
 * While loop function for the Dynamic Post Type: Archive Page feature
 *
 * @param false $_post_type
 *
 * @return bool
 */
if(!function_exists('have_archive')){
    
    function have_archive($post_type = false){
        
        global $acfe_archive_i, $acfe_archive_post_type;
        
        $acfe_archive_post_type = false;
        
        if(!isset($acfe_archive_i) || $acfe_archive_i === 0){
            
            $acfe_archive_i = 0;
            
            if(!$post_type){
                
                // try get_post_type()
                $post_type = get_post_type();
                
                if(!$post_type){
                    
                    // try get_queried_object()
                    $object = get_queried_object();
    
                    if(is_a($object, 'WP_Post_Type') && property_exists($object, 'has_archive')){
                        $post_type = $object->name;
                    }
                    
                }
                
            }
            
            if(!$post_type){
                return false;
            }
            
            if(!post_type_exists($post_type)){
                return false;
            }
            
            $post_type_object = get_post_type_object($post_type);
            
            if(empty($post_type_object)){
                return false;
            }
            
            if(!acfe_maybe_get($post_type_object, 'acfe_admin_archive')){
                return false;
            }
            
            $acfe_archive_post_type = $post_type;
            
            return true;
            
        }
        
        remove_filter('acf/pre_load_post_id', 'acfe_the_archive_post_id');
        
        return false;
        
    }
    
}

/**
 * the_archive
 *
 * Setup data for the Dynamic Post Type: Archive Page feature
 */
if(!function_exists('the_archive')){
    
    function the_archive(){
        
        global $acfe_archive_i;
        
        add_filter('acf/pre_load_post_id', 'acfe_the_archive_post_id', 10, 2);
        
        $acfe_archive_i++;
        
    }
    
}

/**
 * acfe_the_archive_post_id
 *
 * Dynamic Post Type: Archive Page helper
 *
 * @param $null
 * @param $post_id
 *
 * @return mixed|void
 */
function acfe_the_archive_post_id($null, $post_id){
    
    if($post_id !== false){
        return $null;
    }
    
    global $acfe_archive_post_type;
    
    if(empty($acfe_archive_post_type)){
        return $null;
    }
    
    return acf_get_valid_post_id("{$acfe_archive_post_type}_archive");
    
}

/**
 * acfe_get_post_id
 *
 * Universal way to always retrieve the correct ACF Post ID on front-end and back-end
 * Returns ACF formatted Post ID. ie: 12|term_24|user_56|my-options
 *
 * @param bool $format
 *
 * @return mixed|void
 */
function acfe_get_post_id($format = true){
    
    // Admin
    if(acfe_is_admin()){
        
        // Legacy ACF method (get_the_ID(), get_queried_object() etc...)
        $post_id = acf_get_valid_post_id();
        
        // Exclude local meta post ids
        if(function_exists('acfe_get_local_post_ids') && in_array($post_id, acfe_get_local_post_ids())){
            $post_id = false;
        }
        
        if($post_id){
            return $post_id;
        }
        
        // ACF Form Data
        $post_id = acf_get_form_data('post_id');
        
        // $_POST['_acf_post_id']
        if(!$post_id){
            $post_id = acf_maybe_get_POST('_acf_post_id', 0);
        }
        
        // $_REQUEST['post']
        if(!$post_id){
            $post_id = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : 0;
        }
        
        // $_REQUEST['post_id'] (ACF Block Type)
        if(!$post_id){
            $post_id = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
        }
        
        // $_REQUEST['user_id']
        if(!$post_id){
            $post_id = isset($_REQUEST['user_id']) ? 'user_' . absint($_REQUEST['user_id']) : 0;
        }
        
        // global $user_ID
        if(!$post_id){
            global $pagenow, $user_ID;
            $post_id = $pagenow === 'profile.php' && $user_ID !== null ? 'user_' . absint($user_ID) : 0;
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
    
        // ACF Form Data
        $post_id = acf_get_form_data('post_id');
        
        if(!$post_id){
            
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
                    $post_id = $object->name . '_archive';
                    
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
        
    }
    
    // Validate with filters
    $post_id = acf_get_valid_post_id($post_id);
    
    // Do not format
    if(!$format){
        
        // Decode
        $info = acf_decode_post_id($post_id);
        
        // Return raw id
        $post_id = $info['id'];
        
    }
    
    // return
    return $post_id;
    
}