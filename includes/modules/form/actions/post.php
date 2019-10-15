<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_post')):

class acfe_form_post{
    
	function __construct(){
        
        add_filter('acfe/form/load/action/post', array($this, 'load'), 1);
        add_action('acfe/form/submit/action/post', array($this, 'submit'), 1, 3);
        
    }
    
    function load($args){
        
        $form_name = acf_maybe_get($args, 'acfe_form_name');
        $form_id = acf_maybe_get($args, 'acfe_form_id');
        
        // Behavior
        $post_behavior = get_sub_field('acfe_form_post_behavior');
        
        // Update Post
        if($post_behavior !== 'update_post')
            return $args;
        
        if(!get_sub_field('acfe_form_post_update_load'))
            return $args;
        
        $_post_id_group = get_sub_field('acfe_form_post_update_post_id_group');
        $_post_id_data = $_post_id_group['acfe_form_post_update_post_id'];
        $_post_id_custom = $_post_id_group['acfe_form_post_update_post_id_custom'];
        
        $_post_type = get_sub_field('acfe_form_post_update_post_type');
        $_post_status = get_sub_field('acfe_form_post_update_post_status');
        
        $_post_title_group = get_sub_field('acfe_form_post_update_post_title_group');
        $_post_title = $_post_title_group['acfe_form_post_update_post_title'];
        
        $_post_name_group = get_sub_field('acfe_form_post_update_post_name_group');
        $_post_name = $_post_name_group['acfe_form_post_update_post_name'];
        
        $_post_content_group = get_sub_field('acfe_form_post_update_post_content_group');
        $_post_content = $_post_content_group['acfe_form_post_update_post_content'];
        
        $_post_author_group = get_sub_field('acfe_form_post_update_post_author_group');
        $_post_author = $_post_author_group['acfe_form_post_update_post_author'];
        
        // var
        $_post_id = $args['post_id'];
        
        // Current post
        if($_post_id_data === 'current_post'){
            
            $_post_id = acf_get_valid_post_id();
        
        // Custom Post ID
        }elseif($_post_id_data === 'custom_post_id'){
            
            $_post_id = acfe_form_map_field_get_value($_post_id_custom);
        
        // Field
        }elseif(acf_is_field_key($_post_id_data)){
            
            $_post_id = get_field($_post_id_data);
        
        }
        
        $_post_id = apply_filters('acfe/form/load/post_id',                      $_post_id, $args);
        $_post_id = apply_filters('acfe/form/load/post_id/name=' . $form_name,   $_post_id, $args);
        $_post_id = apply_filters('acfe/form/load/post_id/id=' . $form_id,       $_post_id, $args);
        
        // ID
        $args['post_id'] = $_post_id;
        
        // Post type
        if(acf_is_field_key($_post_type)){
            
            $args['map'][$_post_type]['value'] = get_post_field('post_type', $_post_id);
            
        }
        
        // Post status
        if(acf_is_field_key($_post_status)){
            
            $args['map'][$_post_status]['value'] = get_post_field('post_status', $_post_id);
            
        }
        
        // Post title
        if(acf_is_field_key($_post_title)){
            
            $args['map'][$_post_title]['value'] = get_post_field('post_title', $_post_id);
            
        }
        
        // Post name
        if(acf_is_field_key($_post_name)){
            
            $args['map'][$_post_name]['value'] = get_post_field('post_name', $_post_id);
            
        }
        
        // Post content
        if(acf_is_field_key($_post_content)){
            
            $args['map'][$_post_content]['value'] = get_post_field('post_content', $_post_id);
            
        }
        
        // Post author
        if(acf_is_field_key($_post_author)){
            
            $args['map'][$_post_author]['value'] = get_post_field('post_author', $_post_id);
            
        }
        
        return $args;
        
    }
    
    function submit($form, $post_id, $acf){
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        // Behavior
        $post_behavior = get_sub_field('acfe_form_post_behavior');
        
        // Create Post
        if($post_behavior === 'create_post'){
            
            $_post_type = get_sub_field('acfe_form_post_create_post_type');
            $_post_status = get_sub_field('acfe_form_post_create_post_status');
            
            $_post_title_group = get_sub_field('acfe_form_post_create_post_title_group');
            $_post_title = $_post_title_group['acfe_form_post_create_post_title'];
            $_post_title_custom = $_post_title_group['acfe_form_post_create_post_title_custom'];
            
            $_post_name_group = get_sub_field('acfe_form_post_create_post_name_group');
            $_post_name = $_post_name_group['acfe_form_post_create_post_name'];
            $_post_name_custom = $_post_name_group['acfe_form_post_create_post_name_custom'];
            
            $_post_content_group = get_sub_field('acfe_form_post_create_post_content_group');
            $_post_content = $_post_content_group['acfe_form_post_create_post_content'];
            $_post_content_custom = $_post_content_group['acfe_form_post_create_post_content_custom'];
            
            $_post_author_group = get_sub_field('acfe_form_post_create_post_author_group');
            $_post_author = $_post_author_group['acfe_form_post_create_post_author'];
            $_post_author_custom = $_post_author_group['acfe_form_post_create_post_author_custom'];
            
            // Insert Post
            $_post_id = wp_insert_post(array(
                'post_title' => 'post'
            ));
            
            $args = array();
            
            // ID
            $args['ID'] = $_post_id;
            
            // Post type
            $args['post_type'] = acfe_form_map_field_value($_post_type, $acf);
            
            // Post status
            $args['post_status'] = acfe_form_map_field_value($_post_status, $acf);
            
            // Post title
            $args['post_title'] = $_post_id;
            
            if(acf_is_field_key($_post_title)){
                
                $args['post_title'] = acfe_form_map_field_value($_post_title, $acf);
                
            }elseif($_post_title === 'custom'){
                
                $args['post_title'] = acfe_form_map_field_value($_post_title_custom, $acf);
                
            }
            
            // Post name
            $args['post_name'] = $args['post_title'];
            
            if(acf_is_field_key($_post_name)){
                
                $args['post_name'] = acfe_form_map_field_value($_post_name, $acf);
                
            }elseif($_post_name === 'generated_id'){
                
                $args['post_name'] = $_post_id;
                
            }elseif($_post_name === 'custom'){
                
                $args['post_name'] = acfe_form_map_field_value($_post_name_custom, $acf);
                
            }
            
            // Post content
            if(acf_is_field_key($_post_content)){
                
                $args['post_content'] = acfe_form_map_field_value($_post_content, $acf);
                
            }elseif($_post_content === 'custom'){
                
                $args['post_content'] = acfe_form_map_field_value($_post_content_custom, $acf);
                
            }
            
            // Post author
            if($_post_author === 'current_user'){
                
                $args['post_author'] = get_current_user_id();
                
            }elseif($_post_author === 'custom_user_id'){
                
                $args['post_author'] = $_post_author_custom;
                
            }elseif(acf_is_field_key($_post_author)){
                
                $args['post_author'] = acfe_form_map_field_value($_post_author, $acf);
                
            }
            
            $args = apply_filters('acfe/form/submit/insert_post_args',                      $args, $form, $_post_id);
            $args = apply_filters('acfe/form/submit/insert_post_args/name=' . $form_name,   $args, $form, $_post_id);
            $args = apply_filters('acfe/form/submit/insert_post_args/id=' . $form_id,       $args, $form, $_post_id);
            
            if($args === false)
                return;
            
            // Update Post
            $_post_id = wp_update_post($args);
            
            do_action('acfe/form/submit/insert_post',                       $form, $_post_id, $args);
            do_action('acfe/form/submit/insert_post/name=' . $form_name,    $form, $_post_id, $args);
            do_action('acfe/form/submit/insert_post/id=' . $form_id,        $form, $_post_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_post_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post($_post_id, $data);
            
            }
            
        }
        
        // Update Post
        elseif($post_behavior === 'update_post'){
            
            $_post_id_group = get_sub_field('acfe_form_post_update_post_id_group');
            $_post_id_data = $_post_id_group['acfe_form_post_update_post_id'];
            $_post_id_custom = $_post_id_group['acfe_form_post_update_post_id_custom'];
            
            $_post_type = get_sub_field('acfe_form_post_update_post_type');
            $_post_status = get_sub_field('acfe_form_post_update_post_status');
            
            $_post_title_group = get_sub_field('acfe_form_post_update_post_title_group');
            $_post_title = $_post_title_group['acfe_form_post_update_post_title'];
            $_post_title_custom = $_post_title_group['acfe_form_post_update_post_title_custom'];
            
            $_post_name_group = get_sub_field('acfe_form_post_update_post_name_group');
            $_post_name = $_post_name_group['acfe_form_post_update_post_name'];
            $_post_name_custom = $_post_name_group['acfe_form_post_update_post_name_custom'];
            
            $_post_content_group = get_sub_field('acfe_form_post_update_post_content_group');
            $_post_content = $_post_content_group['acfe_form_post_update_post_content'];
            $_post_content_custom = $_post_content_group['acfe_form_post_update_post_content_custom'];
            
            $_post_author_group = get_sub_field('acfe_form_post_update_post_author_group');
            $_post_author = $_post_author_group['acfe_form_post_update_post_author'];
            $_post_author_custom = $_post_author_group['acfe_form_post_update_post_author_custom'];
            
            // var
            $_post_id = false;
            
            // Current post
            if($_post_id_data === 'current_post'){
                
                $_post_id = acf_get_valid_post_id();
            
            // Custom Post ID
            }elseif($_post_id_data === 'custom_post_id'){
                
                $_post_id = $_post_id_custom;
            
            // Field
            }elseif(acf_is_field_key($_post_id_data)){
                
                $_post_id = acfe_form_map_field_value($_post_id_data, $acf);
                
            }
            
            $args = array();
            
            // ID
            $args['ID'] = $_post_id;
            
            // Post type
            if(!empty($_post_type)){
                
                $args['post_type'] = acfe_form_map_field_value($_post_type, $acf);
                
            }
            
            // Post status
            if(!empty($_post_status)){
                
                $args['post_status'] = acfe_form_map_field_value($_post_status, $acf);
                
            }
            
            // Post title
            if(!empty($_post_title)){
                
                if(acf_is_field_key($_post_title)){
                    
                    $args['post_title'] = acfe_form_map_field_value($_post_title, $acf);
                    
                }elseif($_post_title === 'generated_id'){
                    
                    $args['post_title'] = $_post_id;
                    
                }elseif($_post_title === 'custom'){
                    
                    $args['post_title'] = acfe_form_map_field_value($_post_title_custom, $acf);
                    
                }
                
            }
            
            // Post name
            if(!empty($_post_name)){
                
                if(acf_is_field_key($_post_name)){
                    
                    $args['post_name'] = acfe_form_map_field_value($_post_name, $acf);
                    
                }elseif($_post_name === 'generated_id'){
                    
                    $args['post_name'] = $_post_id;
                    
                }elseif($_post_name === 'custom'){
                    
                    $args['post_name'] = acfe_form_map_field_value($_post_name_custom, $acf);
                    
                }
                
            }
            
            // Post content
            if(!empty($_post_content)){
                
                if(acf_is_field_key($_post_content)){
                    
                    $args['post_content'] = acfe_form_map_field_value($_post_content, $acf);
                    
                }elseif($_post_content === 'custom'){
                    
                    $args['post_content'] = acfe_form_map_field_value($_post_content_custom, $acf);
                    
                }
                
            }
            
            // Post author
            if(!empty($_post_author)){
                
                if($_post_author === 'current_user'){
                    
                    $args['post_author'] = get_current_user_id();
                    
                }elseif($_post_author === 'custom_user_id'){
                    
                    $args['post_author'] = $_post_author_custom;
                    
                }elseif(acf_is_field_key($_post_author)){
                    
                    $args['post_author'] = acfe_form_map_field_value($_post_author, $acf);
                    
                }
                
            }
            
            $args = apply_filters('acfe/form/submit/update_post_args',                      $args, $form, $_post_id);
            $args = apply_filters('acfe/form/submit/update_post_args/name=' . $form_name,   $args, $form, $_post_id);
            $args = apply_filters('acfe/form/submit/update_post_args/id=' . $form_id,       $args, $form, $_post_id);
            
            if($args === false)
                return;
            
            // Update Post
            $_post_id = wp_update_post($args);
            
            do_action('acfe/form/submit/update_post',                       $form, $_post_id, $args);
            do_action('acfe/form/submit/update_post/name=' . $form_name,    $form, $_post_id, $args);
            do_action('acfe/form/submit/update_post/id=' . $form_id,        $form, $_post_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_post_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post($_post_id, $data);
            
            }
            
        }
        
    }
    
}

new acfe_form_post();

endif;