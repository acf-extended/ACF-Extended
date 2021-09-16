<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_post')):

class acfe_form_post{
    
    function __construct(){
        
        /*
         * Helpers
         */
        $helpers = acf_get_instance('acfe_dynamic_forms_helpers');
        
        /*
         * Action
         */
        add_filter('acfe/form/actions',                                             array($this, 'add_action'));
        add_filter('acfe/form/load/post',                                           array($this, 'load'), 10, 3);
        add_action('acfe/form/make/post',                                           array($this, 'make'), 10, 3);
        add_action('acfe/form/submit/post',                                         array($this, 'submit'), 10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_post_save_meta',               array($helpers, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_meta',               array($helpers, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_target',             array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_source',             array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_type',          array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_status',        array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_title',         array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_name',          array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_content',       array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_excerpt',       array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_author',        array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_parent',        array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_terms',         array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_type',           array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_status',         array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_title',          array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_name',           array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_content',        array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_excerpt',        array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_author',         array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_parent',         array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_terms',          array($helpers, 'map_fields_deep_no_custom'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_target',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_load_source',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_parent',        array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_author',        array($this, 'prepare_choices_users'), 5);
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_post_load_values');
        $load_meta = get_sub_field('acfe_form_post_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
    
        $_post_id = get_sub_field('acfe_form_post_load_source');
        $_post_type = get_sub_field('acfe_form_post_map_post_type');
        $_post_status = get_sub_field('acfe_form_post_map_post_status');
        $_post_title = get_sub_field('acfe_form_post_map_post_title');
        $_post_name = get_sub_field('acfe_form_post_map_post_name');
        $_post_content = get_sub_field('acfe_form_post_map_post_content');
        $_post_excerpt = get_sub_field('acfe_form_post_map_post_excerpt');
        $_post_author = get_sub_field('acfe_form_post_map_post_author');
        $_post_parent = get_sub_field('acfe_form_post_map_post_parent');
        $_post_terms = get_sub_field('acfe_form_post_map_post_terms');
        
        // Map {field:name} {get_field:name} {query_var:name}
        $_post_id = acfe_form_map_field_value_load($_post_id, $current_post_id, $form);
        $_post_type = acfe_form_map_field_value_load($_post_type, $current_post_id, $form);
        $_post_status = acfe_form_map_field_value_load($_post_status, $current_post_id, $form);
        $_post_title = acfe_form_map_field_value_load($_post_title, $current_post_id, $form);
        $_post_name = acfe_form_map_field_value_load($_post_name, $current_post_id, $form);
        $_post_content = acfe_form_map_field_value_load($_post_content, $current_post_id, $form);
        $_post_excerpt = acfe_form_map_field_value_load($_post_excerpt, $current_post_id, $form);
        $_post_author = acfe_form_map_field_value_load($_post_author, $current_post_id, $form);
        $_post_parent = acfe_form_map_field_value_load($_post_parent, $current_post_id, $form);
        $_post_terms = acfe_form_map_field_value_load($_post_terms, $current_post_id, $form);
        
        // Filters
        $_post_id = apply_filters('acfe/form/load/post_id',                      $_post_id, $form, $action);
        $_post_id = apply_filters('acfe/form/load/post_id/form=' . $form_name,   $_post_id, $form, $action);
        
        if(!empty($action))
            $_post_id = apply_filters('acfe/form/load/post_id/action=' . $action, $_post_id, $form, $action);
        
        // Invalid Post ID
        if(!$_post_id)
            return $form;
        
        // Post type
        if(acf_is_field_key($_post_type)){
            
            $key = array_search($_post_type, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_type]['value'] = get_post_field('post_type', $_post_id);
         
        }
        
        // Post status
        if(acf_is_field_key($_post_status)){
            
            $key = array_search($_post_status, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_status]['value'] = get_post_field('post_status', $_post_id);
         
        }
        
        // Post title
        if(acf_is_field_key($_post_title)){
            
            $key = array_search($_post_title, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_title]['value'] = get_post_field('post_title', $_post_id);
         
        }
        
        // Post name
        if(acf_is_field_key($_post_name)){
            
            $key = array_search($_post_name, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_name]['value'] = get_post_field('post_name', $_post_id);
         
        }
        
        // Post content
        if(acf_is_field_key($_post_content)){
            
            $key = array_search($_post_content, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_content]['value'] = get_post_field('post_content', $_post_id);
         
        }
        
        // Post excerpt
        if(acf_is_field_key($_post_excerpt)){
            
            $key = array_search($_post_excerpt, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_excerpt]['value'] = get_post_field('post_excerpt', $_post_id);
         
        }
        
        // Post author
        if(acf_is_field_key($_post_author)){
            
            $key = array_search($_post_author, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_author]['value'] = get_post_field('post_author', $_post_id);
         
        }
        
        // Post parent
        if(acf_is_field_key($_post_parent)){
            
            $key = array_search($_post_parent, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $form['map'][$_post_parent]['value'] = get_post_field('post_parent', $_post_id);
         
        }
        
        // Post terms
        if(acf_is_field_key($_post_terms)){
            
            $key = array_search($_post_terms, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
    
            $taxonomies = acf_get_taxonomies(array(
                'post_type' => get_post_type($_post_id)
            ));
    
            if(!empty($taxonomies)){
        
                $terms = array();
        
                foreach($taxonomies as $taxonomy){
            
                    $get_the_terms = get_the_terms($_post_id, $taxonomy);
                    if(!$get_the_terms || is_wp_error($get_the_terms))
                        continue;
            
                    $terms = array_merge($terms, $get_the_terms);
            
                }
        
                $return = wp_list_pluck($terms, 'term_id');
        
                $form['map'][$_post_terms]['value'] = $return;
        
            }
         
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                if(!$field)
                    continue;
                
                if($field['type'] === 'clone' && $field['display'] === 'seamless'){
                    
                    $sub_fields = acf_get_value($_post_id, $field);
                    
                    foreach($sub_fields as $sub_field_key => $value){
    
                        $form['map'][$sub_field_key]['value'] = $value;
                        
                    }
                    
                }else{
    
                    $form['map'][$field_key]['value'] = acf_get_value($_post_id, $field);
                    
                }
                
            }
            
        }
        
        return $form;
        
    }
    
    function make($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/post',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/post/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/post/action=' . $action,    $prepare, $form, $current_post_id, $action);
        
        if($prepare === false)
            return;
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_post_load_values');
        
        // Pre-process
        $_post_content_group = get_sub_field('acfe_form_post_save_post_content_group');
        $_post_content = $_post_content_group['acfe_form_post_save_post_content'];
        $_post_content_custom = $_post_content_group['acfe_form_post_save_post_content_custom'];
        
        if($_post_content === 'custom'){
            $_post_content = $_post_content_custom;
        }
    
        $_post_excerpt_group = get_sub_field('acfe_form_post_save_post_excerpt_group');
        $_post_excerpt = $_post_excerpt_group['acfe_form_post_save_post_excerpt'];
        $_post_excerpt_custom = $_post_excerpt_group['acfe_form_post_save_post_excerpt_custom'];
    
        if($_post_excerpt === 'custom'){
            $_post_excerpt = $_post_excerpt_custom;
        }
        
        $map = array();
        
        if($load_values){
    
            // Mapping
            $map = array(
                'post_type'    => get_sub_field('acfe_form_post_map_post_type'),
                'post_status'  => get_sub_field('acfe_form_post_map_post_status'),
                'post_title'   => get_sub_field('acfe_form_post_map_post_title'),
                'post_name'    => get_sub_field('acfe_form_post_map_post_name'),
                'post_content' => get_sub_field('acfe_form_post_map_post_content'),
                'post_excerpt' => get_sub_field('acfe_form_post_map_post_excerpt'),
                'post_author'  => get_sub_field('acfe_form_post_map_post_author'),
                'post_parent'  => get_sub_field('acfe_form_post_map_post_parent'),
                'post_terms'   => get_sub_field('acfe_form_post_map_post_terms'),
            );
    
        }
        
        // Fields
        $fields = array(
            'target'        => get_sub_field('acfe_form_post_save_target'),
            'post_type'     => get_sub_field('acfe_form_post_save_post_type'),
            'post_status'   => get_sub_field('acfe_form_post_save_post_status'),
            'post_title'    => get_sub_field('acfe_form_post_save_post_title'),
            'post_name'     => get_sub_field('acfe_form_post_save_post_name'),
            'post_content'  => $_post_content,
            'post_excerpt'  => $_post_excerpt,
            'post_author'   => get_sub_field('acfe_form_post_save_post_author'),
            'post_parent'   => get_sub_field('acfe_form_post_save_post_parent'),
            'post_terms'    => get_sub_field('acfe_form_post_save_post_terms'),
        );
        
        $data = acfe_form_map_vs_fields($map, $fields, $current_post_id, $form);
        
        $_post_id = 0;
        
        // Insert Post
        if($post_action === 'insert_post'){
            
            $_post_id = wp_insert_post(array(
                'post_title' => 'Post'
            ));
            
        }
        
        // Update Post
        elseif($post_action === 'update_post'){
            
            $_post_id = $data['target'];
            
        }
        
        // Invalid Post ID
        if(!$_post_id)
            return;
        
        $args = array();
        
        // ID
        $args['ID'] = $_post_id;
        
        // Post type
        if(!empty($data['post_type'])){
            
            if(is_array($data['post_type']))
                $data['post_type'] = acfe_array_to_string($data['post_type']);
            
            $args['post_type'] = $data['post_type'];
            
        }
        
        // Post status
        if(!empty($data['post_status'])){
    
            if(is_array($data['post_status']))
                $data['post_status'] = acfe_array_to_string($data['post_status']);
            
            $args['post_status'] = $data['post_status'];
        
        }
        
        // Post title
        if(!empty($data['post_title'])){
    
            if(is_array($data['post_title']))
                $data['post_title'] = acfe_array_to_string($data['post_title']);
            
            $args['post_title'] = $data['post_title'];
            
            if($data['post_title'] === 'generated_id'){
                $args['post_title'] = $_post_id;
            }elseif($data['post_title'] === '#generated_id'){
                $args['post_title'] = "#{$_post_id}";
            }
        
        }
        
        // Post name
        if(!empty($data['post_name'])){
    
            if(is_array($data['post_name']))
                $data['post_name'] = acfe_array_to_string($data['post_name']);
            
            $args['post_name'] = $data['post_name'];
            
            if($data['post_name'] === 'generated_id')
                $args['post_name'] = $_post_id;
        
        }
        
        // Post content
        if(!empty($data['post_content'])){
    
            if(is_array($data['post_content']))
                $data['post_content'] = acfe_array_to_string($data['post_content']);
            
            $args['post_content'] = $data['post_content'];
        
        }
        
        // Post excerpt
        if(!empty($data['post_excerpt'])){
    
            if(is_array($data['post_excerpt']))
                $data['post_excerpt'] = acfe_array_to_string($data['post_excerpt']);
            
            $args['post_excerpt'] = $data['post_excerpt'];
        
        }
        
        // Post author
        if(!empty($data['post_author'])){
    
            if(is_array($data['post_author']))
                $data['post_author'] = acfe_array_to_string($data['post_author']);
            
            $args['post_author'] = $data['post_author'];
        
        }
        
        // Post parent
        if(!empty($data['post_parent'])){
    
            if(is_array($data['post_author']))
                $data['post_author'] = acfe_array_to_string($data['post_author']);
            
            $args['post_parent'] = $data['post_parent'];
        
        }
        
        // Post terms
        if(!empty($data['post_terms'])){
            
            $terms = acf_array($data['post_terms']);
            
            // Tax input
            if(!empty($terms)){
                
                foreach($terms as $term){
                    
                    if(is_string($term) || is_numeric($term)){
    
                        $args['acfe_form_terms'][] = $term;
    
                    }elseif(is_array($term)){
    
                        foreach($term as $sub_term){
        
                            // String || Numeric
                            if(is_string($sub_term) || is_numeric($sub_term)){
            
                                $args['acfe_form_terms'][] = $sub_term;
            
                                // Array
                            }elseif(is_array($sub_term)){
            
                                if(!acf_maybe_get($sub_term, 'term_id'))
                                    continue;
            
                                $args['acfe_form_terms'][] = $sub_term['term_id'];
            
                                // Object
                            }elseif(is_object($sub_term) && is_a($sub_term, 'WP_Term')){
            
                                if(!isset($sub_term->term_id) || empty($sub_term->term_id))
                                    continue;
            
                                $args['acfe_form_terms'][] = $sub_term->term_id;
            
                            }
        
        
                        }
    
                    }elseif(is_object($term) && is_a($term, 'WP_Term')){
    
                        if(!isset($term->term_id) || empty($term->term_id))
                            continue;
    
                        $args['acfe_form_terms'][] = $term->term_id;
                     
                    }
    
                    
                    
                }
                
            }
        
        }
        
        // Args
        $args = apply_filters('acfe/form/submit/post_args',                     $args, $post_action, $form, $action);
        $args = apply_filters('acfe/form/submit/post_args/form=' . $form_name,  $args, $post_action, $form, $action);
        
        if(!empty($action))
            $args = apply_filters('acfe/form/submit/post_args/action=' . $action, $args, $post_action, $form, $action);
        
        // Bail early if false
        if($args === false){
            
            // Delete draft post
            if($post_action === 'insert_post'){
        
                wp_delete_post($_post_id, true);
        
            }
    
            return;
            
        }
        
        // Post terms pre-process (let post update first, for post type)
        $terms = array();
        
        if(acf_maybe_get($args, 'acfe_form_terms')){
            
            $terms = acf_extract_var($args, 'acfe_form_terms');
        
        }
        
        // Update Post
        $_post_id = wp_update_post($args);
    
        // Post terms process
        if(!empty($terms)){
        
            $term_objects = array();
            $term_create = array();
        
            foreach($terms as $term){
            
                if(is_numeric($term)){
                
                    $get_term = get_term($term);
                
                    if(empty($get_term) || is_wp_error($get_term))
                        continue;
                
                    $term_objects[$get_term->taxonomy][] = $get_term->term_id;
                
                }elseif(is_string($term)){
                
                    $explode = explode('|', $term);
                
                    // No taxonomy found in input
                    if(isset($explode[1])){
                    
                        $term_create[$explode[1]][] = $explode[0];
                    
                    }else{
                    
                        // Get post type
                        $post_type = 'post';
                    
                        if(isset($args['post_type']))
                            $post_type = $args['post_type'];
                    
                        $taxonomies = get_object_taxonomies($post_type);
                    
                        if(!empty($taxonomies)){
                        
                            $taxonomy = $taxonomies[0];
                        
                            $term_create[$taxonomy] = $explode[0];
                        
                        }
                    
                    }
                
                }
            
            }
        
            // Term Objects
            if(!empty($term_objects)){
            
                foreach($term_objects as $term_taxonomy => $term_ids){
                
                    wp_set_object_terms($args['ID'], $term_ids, $term_taxonomy, true);
                
                }
            
            }
        
            // Create Terms (with slugs)
            if(!empty($term_create)){
            
                foreach($term_create as $term_taxonomy => $term_slugs){
                
                    wp_set_object_terms($args['ID'], $term_slugs, $term_taxonomy, true);
                
                }
            
            }
        
        }
        
        // Submit
        do_action('acfe/form/submit/post',                     $_post_id, $post_action, $args, $form, $action);
        do_action('acfe/form/submit/post/form=' . $form_name,  $_post_id, $post_action, $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/post/action=' . $action, $_post_id, $post_action, $args, $form, $action);
        
    }
    
    function submit($_post_id, $post_action, $args, $form, $action){
    
        // Form name
        $form_name = acf_maybe_get($form, 'name');
    
        // Get post array
        $post_object = get_post($_post_id, 'ARRAY_A');
    
        $post_object['permalink'] = get_permalink($_post_id);
        $post_object['admin_url'] = admin_url('post.php?post=' . $_post_id . '&action=edit');
    
        // Retrieve Post Author data
        $post_author = $post_object['post_author'];
        $user_object = get_user_by('ID', $post_author);
    
        if(isset($user_object->data)){
        
            $user = json_decode(json_encode($user_object->data), true);
        
            $user_object_meta = get_user_meta($user['ID']);
        
            $user_meta = array();
        
            foreach($user_object_meta as $k => $v){
            
                if(!isset($v[0]))
                    continue;
            
                $user_meta[$k] = $v[0];
            
            }
        
            $user_array = array_merge($user, $user_meta);
        
            $user_array['permalink'] = get_author_posts_url($post_author);
            $user_array['admin_url'] = admin_url('user-edit.php?user_id=' . $post_author);
        
            $post_object['post_author_data'] = $user_array;
        
        }
        
        // Deprecated
        $post_object = apply_filters_deprecated("acfe/form/query_var/post",                    array($post_object, $_post_id, $post_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/post");
        $post_object = apply_filters_deprecated("acfe/form/query_var/post/form={$form_name}",  array($post_object, $_post_id, $post_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/post/form={$form_name}");
        $post_object = apply_filters_deprecated("acfe/form/query_var/post/action={$action}",   array($post_object, $_post_id, $post_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/post/action={$action}");
        
        // Output
        $post_object = apply_filters("acfe/form/output/post",                                       $post_object, $_post_id, $post_action, $args, $form, $action);
        $post_object = apply_filters("acfe/form/output/post/form={$form_name}",                     $post_object, $_post_id, $post_action, $args, $form, $action);
        $post_object = apply_filters("acfe/form/output/post/action={$action}",                      $post_object, $_post_id, $post_action, $args, $form, $action);
    
        // Old Query var
        $query_var = acfe_form_unique_action_id($form, 'post');
    
        if(!empty($action))
            $query_var = $action;
        
        set_query_var($query_var, $post_object);
        // ------------------------------------------------------------
        
        // Action Output
        $actions = get_query_var('acfe_form_actions', array());
        
        $actions['post'] = $post_object;
        
        if(!empty($action))
            $actions[$action] = $post_object;
        
        set_query_var('acfe_form_actions', $actions);
        // ------------------------------------------------------------
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_post_save_meta');
        
        if(!empty($save_meta)){
            
            $meta = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($meta)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post($_post_id, $meta);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
    /**
     *  Post: Select2 Choices
     */
    function prepare_choices($field){
        
        $field['choices']['current_post'] = 'Current: Post';
        $field['choices']['current_post_parent'] = 'Current: Post Parent';
        
        if(acf_maybe_get($field, 'value')){
            
            $field_type = acf_get_field_type('post_object');
            $field['post_type'] = acf_get_post_types();
            
            // load posts
            $posts = $field_type->get_posts($field['value'], $field);
            
            if($posts){
                    
                foreach(array_keys($posts) as $i){
                    
                    // vars
                    $post = acf_extract_var($posts, $i);
                    
                    // append to choices
                    $field['choices'][$post->ID] = $field_type->get_post_title($post, $field);
                    
                }
                
            }
        
        }
        
        return $field;
        
    }
    
    /**
     *  User: Select2 Choices
     */
    function prepare_choices_users($field){
        
        $field['choices']['current_user'] = 'Current User';
        $field['choices']['current_post_author'] = 'Current Post Author';
        
        if(acf_maybe_get($field, 'value')){
            
            $field_type = acf_get_field_type('user');
            
            // Clean value into an array of IDs.
            $user_ids = array_map('intval', acf_array($field['value']));
            
            // Find users in database (ensures all results are real).
            $users = acf_get_users(array(
                'include' => $user_ids
            ));
            
            // Append.
            if($users){
                
                foreach($users as $user){
                    $field['choices'][$user->ID] = $field_type->get_result($user, $field);
                }
                
            }
        
        }
        
        return $field;
        
    }
    
    function add_action($layouts){
        
        $layouts['layout_post'] = array(
            'key' => 'layout_post',
            'name' => 'post',
            'label' => 'Post action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_post_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/post-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
        
                /*
                 * Layout: Post Action
                 */
                array(
                    'key' => 'field_acfe_form_post_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_action',
                    'label' => 'Action',
                    'name' => 'acfe_form_post_action',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'insert_post' => 'Create post',
                        'update_post' => 'Update post',
                    ),
                    'default_value' => 'insert_post',
                ),
                array(
                    'key' => 'field_acfe_form_post_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Post',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
        
                /*
                 * Layout: Post Save
                 */
                array(
                    'key' => 'field_acfe_form_post_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_post_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_action',
                                'operator' => '==',
                                'value' => 'update_post',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_post',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_type',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_save_post_type',
                    'type' => 'acfe_post_types',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_type',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'post_type' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'name',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_type_message',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_map_post_type_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_type',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_status',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_save_post_status',
                    'type' => 'acfe_post_statuses',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_status',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'post_status' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'name',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_status_message',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_map_post_status_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_status',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
        
                array(
                    'key' => 'field_acfe_form_post_save_post_title',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_save_post_title',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'generated_id'  => 'Generated ID',
                        '#generated_id' => '#Generated ID',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_title',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
        
                array(
                    'key' => 'field_acfe_form_post_map_post_title_message',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_map_post_title_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_title',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_name',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_save_post_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'generated_id' => 'Generated ID',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_name',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
        
                array(
                    'key' => 'field_acfe_form_post_map_post_name_message',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_map_post_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_name',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_content_group',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_save_post_content_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_content',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_post_save_post_content',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_content',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'WYSIWYG editor',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_post_save_post_content_custom',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_content_custom',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_post_save_post_content',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'delay' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_content_message',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_map_post_content_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_content',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_excerpt_group',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_save_post_excerpt_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_excerpt',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_post_save_post_excerpt',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_excerpt',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'Textarea',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_post_save_post_excerpt_custom',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_excerpt_custom',
                            'type' => 'textarea',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_post_save_post_excerpt',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_excerpt_message',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_map_post_excerpt_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_excerpt',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_author',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_save_post_author',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_author',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_author_message',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_map_post_author_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_author',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_parent',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_save_post_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_parent',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_parent_message',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_map_post_parent_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_parent',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_terms',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_save_post_terms',
                    'type' => 'acfe_taxonomy_terms',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_terms',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'taxonomy' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 1,
                    'ui' => 1,
                    'ajax' => 0,
                    'choices' => array(
                    ),
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_terms_message',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_map_post_terms_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_terms',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_post_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
        
                /*
                 * Layout: Post Load
                 */
                array(
                    'key' => 'acfe_form_post_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_post_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_post_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_post',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
        
                array(
                    'key' => 'field_acfe_form_post_map_post_type',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_map_post_type',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_status',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_map_post_status',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_title',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_map_post_title',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_name',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_map_post_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_content',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_map_post_content',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_excerpt',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_map_post_excerpt',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_author',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_map_post_author',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_parent',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_map_post_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_terms',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_map_post_terms',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_post_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
                
            ),
            'min' => '',
            'max' => '',
        );
        
        return $layouts;
        
    }
    
}

new acfe_form_post();

endif;