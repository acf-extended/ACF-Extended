<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_post')):

class acfe_form_post{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/action/post',                                    array($this, 'load'), 1, 2);
        add_action('acfe/form/submit/action/post',                                  array($this, 'submit'), 1, 2);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_post_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_meta',               array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_type',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_status',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_title',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_name',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_content',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_author',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_parent',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_terms',          array(acfe()->acfe_form, 'map_fields_deep'));
        
    }
    
    function load($form, $post_id){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_post_load_values');
        $load_source = get_sub_field('acfe_form_post_load_source');
        $load_meta = get_sub_field('acfe_form_post_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
        
        $_post_type = get_sub_field('acfe_form_post_map_post_type');
        $_post_status = get_sub_field('acfe_form_post_map_post_status');
        $_post_title = get_sub_field('acfe_form_post_map_post_title');
        $_post_name = get_sub_field('acfe_form_post_map_post_name');
        $_post_content = get_sub_field('acfe_form_post_map_post_content');
        $_post_author = get_sub_field('acfe_form_post_map_post_author');
        $_post_parent = get_sub_field('acfe_form_post_map_post_parent');
        $_post_terms = get_sub_field('acfe_form_post_map_post_terms');
        
        $_post_id = 0;
        
        // Custom Post ID
        if($load_source !== 'current_post'){
            
            $_post_id = $load_source;
        
        }
        
        // Current Post
        elseif($load_source === 'current_post'){
            
            if($post_info['type'] === 'post')
                $_post_id = $post_info['id'];
            
        }
        
        $_post_id = apply_filters('acfe/form/load/action/post/' . $post_action . '_id',                      $_post_id, $form);
        $_post_id = apply_filters('acfe/form/load/action/post/' . $post_action . '_id/name=' . $form_name,   $_post_id, $form);
        $_post_id = apply_filters('acfe/form/load/action/post/' . $post_action . '_id/id=' . $form_id,       $_post_id, $form);
        
        // Invalid Post ID
        if(!$_post_id)
            return $form;
        
        // Post type
        if(acf_is_field_key($_post_type)){
            
            $key = array_search($_post_type, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_type]['value'] = get_post_field('post_type', $_post_id);
                
            }
            
        }
        
        // Post status
        if(acf_is_field_key($_post_status)){
            
            $key = array_search($_post_type, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_status]['value'] = get_post_field('post_status', $_post_id);
                
            }
            
        }
        
        // Post title
        if(acf_is_field_key($_post_title)){
            
            $key = array_search($_post_title, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_title]['value'] = get_post_field('post_title', $_post_id);
            
            }
            
        }
        
        // Post name
        if(acf_is_field_key($_post_name)){
            
            $key = array_search($_post_name, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_name]['value'] = get_post_field('post_name', $_post_id);
            
            }
            
        }
        
        // Post content
        if(acf_is_field_key($_post_content)){
            
            $key = array_search($_post_content, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_content]['value'] = get_post_field('post_content', $_post_id);
            
            }
            
        }
        
        // Post author
        if(acf_is_field_key($_post_author)){
            
            $key = array_search($_post_author, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_author]['value'] = get_post_field('post_author', $_post_id);
            
            }
            
        }
        
        // Post parent
        if(acf_is_field_key($_post_parent)){
            
            $key = array_search($_post_parent, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_parent]['value'] = get_post_field('post_parent', $_post_id);
            
            }
            
        }
        
        // Post terms
        if(acf_is_field_key($_post_terms)){
            
            $key = array_search($_post_terms, $load_meta);
            
            if($key !== false){
            
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
            
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                $form['map'][$field_key]['value'] = acf_get_value($_post_id, $field);
                
            }
            
        }
        
        return $form;
        
    }
    
    function submit($form, $post_id){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Mapping
        $map = array(
            'post_type'     => get_sub_field('acfe_form_post_map_post_type'),
            'post_status'   => get_sub_field('acfe_form_post_map_post_status'),
            'post_title'    => get_sub_field('acfe_form_post_map_post_title'),
            'post_name'     => get_sub_field('acfe_form_post_map_post_name'),
            'post_content'  => get_sub_field('acfe_form_post_map_post_content'),
            'post_author'   => get_sub_field('acfe_form_post_map_post_author'),
            'post_parent'   => get_sub_field('acfe_form_post_map_post_parent'),
            'post_terms'    => get_sub_field('acfe_form_post_map_post_terms'),
        );
        
        // Fields
        $_target = get_sub_field('acfe_form_post_save_target');
        
        $_post_type = get_sub_field('acfe_form_post_save_post_type');
        $_post_status = get_sub_field('acfe_form_post_save_post_status');
        
        $_post_title_group = get_sub_field('acfe_form_post_save_post_title_group');
        $_post_title = $_post_title_group['acfe_form_post_save_post_title'];
        $_post_title_custom = $_post_title_group['acfe_form_post_save_post_title_custom'];
        
        $_post_name_group = get_sub_field('acfe_form_post_save_post_name_group');
        $_post_name = $_post_name_group['acfe_form_post_save_post_name'];
        $_post_name_custom = $_post_name_group['acfe_form_post_save_post_name_custom'];
        
        $_post_content_group = get_sub_field('acfe_form_post_save_post_content_group');
        $_post_content = $_post_content_group['acfe_form_post_save_post_content'];
        $_post_content_custom = $_post_content_group['acfe_form_post_save_post_content_custom'];
        
        $_post_author = get_sub_field('acfe_form_post_save_post_author');
        $_post_parent = get_sub_field('acfe_form_post_save_post_parent');
        $_post_terms = get_sub_field('acfe_form_post_save_post_terms');
        
        $_post_id = 0;
        
        // Insert Post
        if($post_action === 'insert_post'){
            
            $temp_title = false;
            
            // Post title
            if(!empty($map['post_title'])){
                
                $temp_title = acfe_form_map_field_value($map['post_title'], $_POST['acf'], $_post_id);
                
            }elseif($_post_title === 'generated_id'){
                
                $temp_title = true;
                
            }elseif($_post_title === 'custom'){
                
                $temp_title = acfe_form_map_field_value($_post_title_custom, $_POST['acf'], $_post_id);
                
            }
            
            if(!empty($temp_title)){
                
                $_post_id = wp_insert_post(array(
                    'post_title' => 'Post'
                ));
            
            }
            
        }
        
        // Update Post
        elseif($post_action === 'update_post'){
            
            // Custom Post ID
            if($_target !== 'current_post'){
                
                $_post_id = $_target;
            
            }
            
            // Current Post
            elseif($_target === 'current_post'){
                
                if($post_info['type'] === 'post')
                    $_post_id = $post_info['id'];
                
            }
            
        }
        
        // Invalid Post ID
        if(!$_post_id)
            return;
        
        $args = array();
        
        // ID
        $args['ID'] = $_post_id;
        
        // Post type
        if(!empty($map['post_type'])){
            
            $args['post_type'] = acfe_form_map_field_value($map['post_type'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_type)){
            
            $args['post_type'] = $_post_type;
        
        }
        
        // Post status
        if(!empty($map['post_status'])){
            
            $args['post_status'] = acfe_form_map_field_value($map['post_status'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_status)){
            
            $args['post_status'] = $_post_status;
        
        }
        
        // Post title
        if(!empty($map['post_title'])){
            
            $args['post_title'] = acfe_form_map_field_value($map['post_title'], $_POST['acf'], $_post_id);
            
        }elseif($_post_title === 'generated_id'){
            
            $args['post_title'] = $_post_id;
            
        }elseif($_post_title === 'custom'){
            
            $args['post_title'] = acfe_form_map_field_value($_post_title_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post name
        if(!empty($map['post_name'])){
            
            $args['post_name'] = acfe_form_map_field_value($map['post_name'], $_POST['acf'], $_post_id);
            
        }elseif($_post_name === 'generated_id'){
            
            $args['post_name'] = $_post_id;
            
        }elseif($_post_name === 'custom'){
            
            $args['post_name'] = acfe_form_map_field_value($_post_name_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post content
        if(!empty($map['post_content'])){
            
            $args['post_content'] = acfe_form_map_field_value($map['post_content'], $_POST['acf'], $_post_id);
            
        }elseif($_post_content === 'custom'){
            
            $args['post_content'] = acfe_form_map_field_value($_post_content_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post author
        if(!empty($map['post_author'])){
            
            $args['post_author'] = acfe_form_map_field_value($map['post_author'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_author)){
            
            // Custom user ID
            $args['post_author'] = $_post_author;
            
            // Current User
            if($_post_author === 'current_user'){
                
                $args['post_author'] = get_current_user_id();
            
            // Current Post Author
            }elseif($_post_author === 'current_post_author'){
                
                if($post_info['type'] === 'post')
                    $args['post_author'] = get_post_field('post_author', $post_info['id']);
                
            }
            
        }
        
        // Post parent
        if(!empty($map['post_parent'])){
            
            $args['post_parent'] = acfe_form_map_field_value($map['post_parent'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_parent)){
            
            // Custom Post ID
            $args['post_parent'] = $_post_parent;
            
            // Current Post
            if($_post_parent === 'current_post'){
                
                if($post_info['type'] === 'post')
                    $args['post_parent'] = $post_info['id'];
                
            }
            
        }
        
        $terms = array();
        
        // Post terms
        if(!empty($map['post_terms'])){
            
            $terms = acf_array(acfe_form_map_field_value($map['post_terms'], $_POST['acf'], $_post_id));
            
        }elseif(!empty($_post_terms)){
            
            $terms = acf_array($_post_terms);
            
        }
        
        // Tax input
        if(!empty($terms)){
            
            foreach($terms as $term){
                
                $args['tax_input'][$term->taxonomy][] = $term->term_id;
                
            }
            
        }
        
        $args = apply_filters('acfe/form/submit/action/post/' . $post_action . '_args',                     $args, $form, $_post_id);
        $args = apply_filters('acfe/form/submit/action/post/' . $post_action . '_args/name=' . $form_name,  $args, $form, $_post_id);
        $args = apply_filters('acfe/form/submit/action/post/' . $post_action . '_args/id=' . $form_id,      $args, $form, $_post_id);
        
        if($args === false)
            return;
        
        // Update Post
        $_post_id = wp_update_post($args);
        
        do_action('acfe/form/submit/action/post/' . $post_action,                           $form, $_post_id, $args);
        do_action('acfe/form/submit/action/post/' . $post_action . '/name=' . $form_name,   $form, $_post_id, $args);
        do_action('acfe/form/submit/action/post/' . $post_action . '/id=' . $form_id,       $form, $_post_id, $args);
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_post_save_meta');
        
        if(!empty($save_meta)){
            
            $data = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($data)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post($_post_id, $data);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
        
        
    }
    
}

new acfe_form_post();

endif;