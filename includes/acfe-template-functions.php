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
                
                // Render: Global Enqueue
                acfe_flexible_render_enqueue($field);
                
            }
            
            // Render: Layout Enqueue
            acfe_flexible_render_layout_enqueue($layout, $field);
            
            // Render: Layout Template
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
    global $is_preview, $col, $post; // allow $post to be used in the template
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
    do_action("acfe/flexible/render/before_template", $field, $layout, $is_preview);
    
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
                
                $file_preview = substr($file, 0, -strlen($extension)-1);
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
    do_action("acfe/flexible/render/after_template", $field, $layout, $is_preview);
    
}

add_action('acfe/flexible/render/before_template', '_acfe_flexible_render_layout_template_before', 10, 3);
function _acfe_flexible_render_layout_template_before($field, $layout, $is_preview){
    
    // vars
    $name = $field['_name'];
    $key = $field['key'];
    $l_name = $layout['name'];
    
    // variations
    do_action("acfe/flexible/render/before_template/name={$name}",                  $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/key={$key}",                    $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/layout={$l_name}",              $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/name={$name}&layout={$l_name}", $field, $layout, $is_preview);
    do_action("acfe/flexible/render/before_template/key={$key}&layout={$l_name}",   $field, $layout, $is_preview);
    
    // deprecated
    do_action_deprecated("acfe/flexible/layout/render/before_template/layout={$l_name}",               array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/before_template/name={$name}&layout={$l_name}",  array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/name={$name}&layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/before_template/key={$key}&layout={$l_name}",    array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/before_template/key={$key}&layout={$l_name}");
    
}

add_action('acfe/flexible/render/after_template', '_acfe_flexible_render_layout_template_after', 10, 3);
function _acfe_flexible_render_layout_template_after($field, $layout, $is_preview){
    
    // vars
    $name = $field['_name'];
    $key = $field['key'];
    $l_name = $layout['name'];
    
    // variations
    do_action("acfe/flexible/render/after_template/name={$name}",                  $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/key={$key}",                    $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/layout={$l_name}",              $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/name={$name}&layout={$l_name}", $field, $layout, $is_preview);
    do_action("acfe/flexible/render/after_template/key={$key}&layout={$l_name}",   $field, $layout, $is_preview);
    
    // deprecated
    do_action_deprecated("acfe/flexible/layout/render/after_template/layout={$l_name}",                array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/after_template/name={$name}&layout={$l_name}",   array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/name={$name}&layout={$l_name}");
    do_action_deprecated("acfe/flexible/layout/render/after_template/key={$key}&layout={$l_name}",     array($field, $layout, $is_preview), '0.8.6.7', "acfe/flexible/render/after_template/key={$key}&layout={$l_name}");
    
}


/**
 * acfe_flexible_render_enqueue
 *
 * Enqueue global scripts & styles for the Flexible Content
 *
 * @param $field
 */
function acfe_flexible_render_enqueue($field){
    
    // global
    global $is_preview;
    
    // actions
    do_action("acfe/flexible/enqueue",                        $field, $is_preview);
    do_action("acfe/flexible/enqueue/name={$field['_name']}", $field, $is_preview);
    do_action("acfe/flexible/enqueue/key={$field['key']}",    $field, $is_preview);
    
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
        
        // convert to array
        if(is_string($style)){
            $style = array(
                'src' => $style,
            );
        }
        
        // defaults args
        $style = wp_parse_args($style, array(
            'handle' => $handle,
            'src'    => '',
            'deps'   => array(),
            'ver'    => false,
            'media'  => 'all',
        ));
        
        // src url starts with current domain
        // remove it and let acfe_locate_file_url() handle it
        if(stripos($style['src'], home_url()) === 0){
            $style['src'] = str_replace(home_url(), '', $style['src']);
        }
        
        // clone for front-end
        $style_front = $style;
        
        // locate src
        $style_front['src'] = acfe_locate_file_url($style_front['src']);
        
        // enqueue front-end + preview
        if(!empty($style_front['src'])){
            wp_enqueue_style($style_front['handle'], $style_front['src'], $style_front['deps'], $style_front['ver'], $style_front['media']);
        }
        
        // preview mode
        // make sure the src is not a distant url
        if($is_preview && stripos($style['src'], 'http://') !== 0 && stripos($style['src'], 'https://') !== 0 && stripos($style['src'], '//') !== 0){
            
            // clone for preview
            $style_preview = $style;
            
            // retrieve extension
            $path = pathinfo($style_preview['src']);
            $extension = $path['extension'];
            
            // append "-preview" to src
            $style_preview['src']  = substr($style_preview['src'], 0, -strlen($extension)-1);
            $style_preview['src'] .= '-preview.' . $extension;
            
            // locate src
            $style_preview['src'] = acfe_locate_file_url($style_preview['src']);
            
            // append "-preview" to handle
            $style_preview['handle'] = "{$style_preview['handle']}-preview";
            
            // enqueue preview
            if(!empty($style_preview['src'])){
                wp_enqueue_style($style_preview['handle'], $style_preview['src'], $style_preview['deps'], $style_preview['ver'], $style_preview['media']);
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
        
        // convert to array
        if(is_string($script)){
            $script = array(
                'src' => $script,
            );
        }
        
        // defaults args
        $script = wp_parse_args($script, array(
            'handle' => $handle,
            'src'    => '',
            'deps'   => array(),
            'ver'    => false,
            'args'   => true,
        ));
        
        // src url starts with current domain
        // remove it and let acfe_locate_file_url() handle it
        if(stripos($script['src'], home_url()) === 0){
            $script['src'] = str_replace(home_url(), '', $script['src']);
        }
        
        // clone for front-end
        $script_front = $script;
        
        // locate
        $script_front['src'] = acfe_locate_file_url($script_front['src']);
        
        // front-end with distant script
        if(!$is_preview || (stripos($script['src'], 'http://') === 0 || stripos($script['src'], 'https://') === 0 || stripos($script['src'], '//') === 0)){
            
            if(!empty($script_front['src'])){
                wp_enqueue_script($script_front['handle'], $script_front['src'], $script_front['deps'], $script_front['ver'], $script_front['args']);
            }
            
        // front-end/preview with local script
        }else{
            
            // clone for preview
            $script_preview = $script;
            
            // retrieve extension
            $path = pathinfo($script_preview['src']);
            $extension = $path['extension'];
            
            // append "-preview" to src
            $script_preview['src']  = substr($script_preview['src'], 0, -strlen($extension)-1);
            $script_preview['src'] .= '-preview.' . $extension;
            
            // locate src
            $script_preview['src'] = acfe_locate_file_url($script_preview['src']);
            
            // append "-preview" to handle
            $script_preview['handle'] = "{$script_preview['handle']}-preview";
            
            // enqueue preview
            if(!empty($script_preview['src'])){
                wp_enqueue_script($script_preview['handle'], $script_preview['src'], $script_preview['deps'], $script_preview['ver'], $script_preview['args']);
                
            // enqueue front-end
            }elseif(!empty($script_front['src'])){
                wp_enqueue_script($script_front['handle'], $script_front['src'], $script_front['deps'], $script_front['ver'], $script_front['args']);
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
 * Universal way to always retrieve the correct ACF Post ID on front-end, back-end and ajax
 *
 * Format:
 *
 * raw:   12   | term_24 | user_56 | my-options
 * id:    12   | 24      | 56      | my-options
 * type:  post | term    | user    | options
 * array: array('id' => 12, 'type' => 'post')
 *
 * @param string $format - raw, id, type or array
 *
 * @return mixed|void
 */
function acfe_get_post_id($format = 'raw'){
    
    // deprecated format: true/false
    $format = $format === true ? 'raw' : $format;
    $format = $format === false ? 'id' : $format;
    
    // vars
    $is_ajax  = wp_doing_ajax();
    $is_front = !is_admin() && !$is_ajax;
    $is_admin = is_admin() && !$is_ajax;
    
    // ajax request
    if($is_ajax){
        
        // passed in acf_form_data()
        // passed during acf/save_post
        $post_id = acf_get_form_data('post_id');
        
        // form submission
        if(!$post_id){
            $post_id = acf_maybe_get_POST('_acf_post_id', 0);
        }
        
        // passed in acf_form_data()
        // passed in acf.data > acf.prepareForAjax
        if(!$post_id){
            $post_id = acf_maybe_get_POST('post_id', 0);
        }
        
    // admin request
    }elseif($is_admin){
        
        // passed in acf_form_data()
        // passed during acf/save_post & acf/validate_save_post
        $post_id = acf_get_form_data('post_id');
        
        // form submission
        if(!$post_id){
            $post_id = acf_maybe_get_POST('_acf_post_id', 0);
        }
        
        // post url param
        if(!$post_id){
            $post_id = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : 0;
        }
        
        // acf block type request
        if(!$post_id){
            $post_id = isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;
        }
        
        // url param
        if(!$post_id){
            $post_id = isset($_REQUEST['user_id']) ? 'user_' . absint($_REQUEST['user_id']) : 0;
        }
        
        // profile
        if(!$post_id){
            global $pagenow, $user_ID;
            $post_id = $pagenow === 'profile.php' && $user_ID !== null ? 'user_' . absint($user_ID) : 0;
        }
        
        // term url param
        if(!$post_id){
            $post_id = isset($_REQUEST['tag_ID']) ? 'term_' . absint($_REQUEST['tag_ID']) : 0;
        }
        
        // options page
        // must be before post type list because post type archive is on edit.php
        if(!$post_id){
            
            global $plugin_page;
            if(isset($plugin_page)){
                $page = acf_get_options_page($plugin_page);
                if($page){
                    $post_id = $page['post_id'];
                }
            }
            
        }
        
        // post type list
        if(!$post_id){
            global $pagenow, $typenow;
            $post_id = $pagenow === 'edit.php' ? "{$typenow}_options" : 0;
        }
        
        // term list
        if(!$post_id){
            global $pagenow, $taxnow;
            $post_id = $pagenow === 'edit-tags.php' ? "tax_{$taxnow}_options" : 0;
        }
        
        // user list
        if(!$post_id){
            global $pagenow;
            $post_id = $pagenow === 'users.php' ? "user_options" : 0;
        }
        
        // attachment list
        if(!$post_id){
            global $pagenow;
            $post_id = $pagenow === 'upload.php' ? "attachment_options" : 0;
        }
        
        // settings
        if(!$post_id && function_exists('get_current_screen')){
            
            $setting_pages = array('options-general', 'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink');
            if(in_array(get_current_screen()->id, $setting_pages)){
                $post_id = get_current_screen()->id;
            }
        }
        
        // common post id
        if(!$post_id){
            $post_id = (int) get_the_ID();
        }
        
    // front-end request
    }elseif($is_front){
        
        // default
        $post_id = 0;
        
        // passed in acf_form_data()
        // passed during acf/save_post & acf/validate_save_post
        if(doing_action('acf/save_post') || doing_action('acf/validate_save_post')){
            $post_id = acf_get_form_data('post_id');
        }
        
        // common post id (within a loop)
        if(!$post_id){
            if(in_the_loop() || is_singular()){
                $post_id = (int) get_the_ID();
            }
        }
        
        // get queries object
        if(!$post_id){
            
            $object = get_queried_object();
            
            if(is_object($object)){
                
                // post/page object
                if(isset($object->post_type, $object->ID)){
                    
                    $post_id = (int) $object->ID;
                    
                // post type archive object
                }elseif(isset($object->hierarchical, $object->name, $object->acfe_admin_archive)){
                    
                    $post_id = "{$object->name}_archive";
                    
                // user object
                }elseif(isset($object->roles, $object->ID)){
                    
                    $post_id = "user_{$object->ID}";
                    
                // term object
                }elseif(isset($object->taxonomy, $object->term_id)){
                    
                    $post_id = "term_{$object->term_id}";
                    
                // comment object
                }elseif(isset($object->comment_ID)){
                    
                    $post_id = "comment_{$object->comment_ID}";
                    
                }
                
            }
            
        }
        
        // passed in acf_form_data()
        // passed during acf/save_post & acf/validate_save_post
        if(!$post_id){
            $post_id = acf_get_form_data('post_id');
        }
        
        // fallback common post id
        if(!$post_id){
            $post_id = (int) get_the_ID();
        }
        
    }
    
    // default
    if(!$post_id){
        $post_id = 0;
    }
    
    // allow for option == options
    if($post_id === 'option'){
        $post_id = 'options';
    }
    
    // append language code
    if($post_id == 'options'){
        $dl = acf_get_setting('default_language');
        $cl = acf_get_setting('current_language');
        
        if($cl && $cl !== $dl){
            $post_id .= '_' . $cl;
        }
    }
    
    // filter for 3rd party
    $post_id = apply_filters('acf/validate_post_id', $post_id, $post_id);
    
    // decoded post id
    $decoded = acf_decode_post_id($post_id);
    
    // return id
    if($format === 'id'){
        return $decoded['id'];
        
    // return type
    }elseif($format === 'type'){
        return $decoded['type'];
    
    // return array
    }elseif($format === 'array'){
        return $decoded;
    }
    
    // return raw
    return $post_id;

}