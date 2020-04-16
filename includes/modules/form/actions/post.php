<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_post')):

class acfe_form_post{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/post',                                           array($this, 'load'), 1, 3);
        add_action('acfe/form/prepare/post',                                        array($this, 'prepare'), 1, 3);
        add_action('acfe/form/submit/post',                                         array($this, 'submit'), 1, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_post_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_meta',               array(acfe()->acfe_form, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_target',             array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_source',             array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_type',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_status',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_title',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_name',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_content',       array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_author',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_parent',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_terms',         array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_type',           array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_status',         array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_title',          array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_name',           array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_content',        array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_author',         array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_parent',         array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_terms',          array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_save_target',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_load_source',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_parent',        array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_post_save_post_author',        array($this, 'prepare_choices_users'), 5);
        
        
        add_action('acf/render_field/name=acfe_form_post_advanced_load',            array($this, 'advanced_load'));
        add_action('acf/render_field/name=acfe_form_post_advanced_save_args',       array($this, 'advanced_save_args'));
        add_action('acf/render_field/name=acfe_form_post_advanced_save',            array($this, 'advanced_save'));
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
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
                
                $form['map'][$field_key]['value'] = acf_get_value($_post_id, $field);
                
            }
            
        }
        
        return $form;
        
    }
    
    function prepare($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Load values
	    $load_values = get_sub_field('acfe_form_post_load_values');
        
        // Pre-process
        $_post_content_group = get_sub_field('acfe_form_post_save_post_content_group');
        $_post_content = $_post_content_group['acfe_form_post_save_post_content'];
        $_post_content_custom = $_post_content_group['acfe_form_post_save_post_content_custom'];
        
        if($_post_content === 'custom')
            $_post_content = $_post_content_custom;
        
        $map = array();
        
        if($load_values){
	
	        // Mapping
	        $map = array(
		        'post_type'    => get_sub_field('acfe_form_post_map_post_type'),
		        'post_status'  => get_sub_field('acfe_form_post_map_post_status'),
		        'post_title'   => get_sub_field('acfe_form_post_map_post_title'),
		        'post_name'    => get_sub_field('acfe_form_post_map_post_name'),
		        'post_content' => get_sub_field('acfe_form_post_map_post_content'),
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
            'post_author'   => get_sub_field('acfe_form_post_save_post_author'),
            'post_parent'   => get_sub_field('acfe_form_post_save_post_parent'),
            'post_terms'    => get_sub_field('acfe_form_post_save_post_terms'),
        );
        
        $data = acfe_form_map_vs_fields($map, $fields, $current_post_id, $form);
        
        $_post_id = 0;
        
        // Insert Post
        if($post_action === 'insert_post'){
            
            // Fix nasty Elementor + YOAST infinite loop
            // Elementor bug report: https://github.com/elementor/elementor/issues/10998
            // YOAST bug report: https://github.com/Yoast/wordpress-seo/issues/14643
            
            add_filter('wpseo_should_index_links', '__return_false');
            
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
            
            if($data['post_title'] === 'generated_id')
                $args['post_title'] = $_post_id;
        
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
        if($args === false)
            return;
        
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
        
        if(!empty($action)){
        
            // Custom Query Var
            $custom_query_var = get_sub_field('acfe_form_custom_query_var');
            
            if(!empty($custom_query_var)){
                
                // Form name
                $form_name = acf_maybe_get($form, 'form_name');
                
                // Get post array
                $post_object = get_post($_post_id, 'ARRAY_A');
                
                $post_object['permalink'] = get_permalink($_post_id);
                $post_object['admin_url'] = admin_url('post.php?post=' . $_post_id . '&action=edit');
                
                $post_object = apply_filters('acfe/form/query_var/post',                    $post_object, $_post_id, $post_action, $args, $form, $action);
                $post_object = apply_filters('acfe/form/query_var/post/form=' . $form_name, $post_object, $_post_id, $post_action, $args, $form, $action);
                $post_object = apply_filters('acfe/form/query_var/post/action=' . $action,  $post_object, $_post_id, $post_action, $args, $form, $action);
                
                set_query_var($action, $post_object);
            
            }
        
        }
        
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
    
    function advanced_load($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_filter('acfe/form/load/post_id', 'my_form_post_values_source', 10, 3);
add_filter('acfe/form/load/post_id/form=<?php echo $form_name; ?>', 'my_form_post_values_source', 10, 3);
add_filter('acfe/form/load/post_id/action=my-post-action', 'my_form_post_values_source', 10, 3);</pre>
<br />
<pre data-codemirror="php-plain">
/**
 * @int     $post_id    Post ID used as source
 * @array   $form       The form settings
 * @string  $action     The action alias name
 */
add_filter('acfe/form/load/post_id/form=<?php echo $form_name; ?>', 'my_form_post_values_source', 10, 3);
function my_form_post_values_source($post_id, $form, $action){
    
    /**
     * Force to load values from the post ID 145
     */
    $post_id = 145;
    
    
    /**
     * Return
     */
    return $post_id;
    
}</pre><?php
        
    }
    
    function advanced_save_args($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_filter('acfe/form/submit/post_args', 'my_form_post_args', 10, 4);
add_filter('acfe/form/submit/post_args/form=<?php echo $form_name; ?>', 'my_form_post_args', 10, 4);
add_filter('acfe/form/submit/post_args/action=my-post-action', 'my_form_post_args', 10, 4);</pre>
<br />
<pre data-codemirror="php-plain">
/**
 * @array   $args   The generated post arguments
 * @string  $type   Action type: 'insert_post' or 'update_post'
 * @array   $form   The form settings
 * @string  $action The action alias name
 */
add_filter('acfe/form/submit/post_args/form=<?php echo $form_name; ?>', 'my_form_post_args', 10, 4);
function my_form_post_args($args, $type, $form, $action){
    
    /**
     * Force specific post title if the action type is 'insert_post'
     */
    if($type === 'insert_post'){
        
        $args['post_title'] = 'My title';
        
    }
    
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $my_post_field = get_field('my_field', 145);
    
    
    /**
     * Return arguments
     * Note: Return false will stop post & meta insert/update
     */
    return $args;
    
}</pre><?php
        
    }
    
    function advanced_save($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_action('acfe/form/submit/post', 'my_form_post_save', 10, 5);
add_action('acfe/form/submit/post/form=<?php echo $form_name; ?>', 'my_form_post_save', 10, 5);
add_action('acfe/form/submit/post/action=my-post-action', 'my_form_post_save', 10, 5);</pre>
<br />
<pre data-codemirror="php-plain">
/**
 * @int     $post_id    The targeted post ID
 * @string  $type       Action type: 'insert_post' or 'update_post'
 * @array   $args       The generated post arguments
 * @array   $form       The form settings
 * @string  $action     The action alias name
 *
 * Note: At this point the post & meta fields are already saved in the database
 */
add_action('acfe/form/submit/post/form=<?php echo $form_name; ?>', 'my_form_post_save', 10, 5);
function my_form_post_save($post_id, $type, $args, $form, $action){
    
    /**
     * Get the value from the form input named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the currently saved post
     */
    $my_post_field = get_field('my_field', $post_id);
    
}</pre><?php
        
    }
    
}

new acfe_form_post();

endif;