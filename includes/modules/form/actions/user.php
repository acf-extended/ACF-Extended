<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_user')):

class acfe_form_user{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/user',                                           array($this, 'load'),       1, 3);
        add_action('acfe/form/validation/user',                                     array($this, 'validation'), 1, 3);
        add_action('acfe/form/prepare/user',                                        array($this, 'prepare'),    1, 3);
        add_action('acfe/form/submit/user',                                         array($this, 'submit'),     1, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_user_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_user_load_meta',               array(acfe()->acfe_form, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_user',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_pass',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_remember',     array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_target',             array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_load_source',             array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_email',              array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_username',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_password',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_first_name',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_last_name',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_nickname',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_display_name',       array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_website',            array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_description',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_role',               array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_map_email',               array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_username',            array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_password',            array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_first_name',          array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_last_name',           array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_nickname',            array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_display_name',        array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_website',             array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_description',         array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_role',                array(acfe()->acfe_form, 'map_fields_deep_no_custom'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_target',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_user_load_source',             array($this, 'prepare_choices'), 5);
        
        add_action('acf/render_field/name=acfe_form_user_advanced_load',            array($this, 'advanced_load'));
        add_action('acf/render_field/name=acfe_form_user_advanced_save_args',       array($this, 'advanced_save_args'));
        add_action('acf/render_field/name=acfe_form_user_advanced_save',            array($this, 'advanced_save'));
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        // Action
        $user_action = get_sub_field('acfe_form_user_action');
	
	    // Bail early if Log user
	    if($user_action === 'log_user')
		    return $form;
        
        // Load values
        $load_values = get_sub_field('acfe_form_user_load_values');
        $load_meta = get_sub_field('acfe_form_user_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
	
	    $_user_id = get_sub_field('acfe_form_user_load_source');
        $_email = get_sub_field('acfe_form_user_map_email');
        $_username = get_sub_field('acfe_form_user_map_username');
        $_password = get_sub_field('acfe_form_user_map_password');
        $_first_name = get_sub_field('acfe_form_user_map_first_name');
        $_last_name = get_sub_field('acfe_form_user_map_last_name');
        $_nickname = get_sub_field('acfe_form_user_map_nickname');
        $_display_name = get_sub_field('acfe_form_user_map_display_name');
        $_website = get_sub_field('acfe_form_user_map_website');
        $_description = get_sub_field('acfe_form_user_map_description');
        $_role = get_sub_field('acfe_form_user_map_role');
        
        // Map {field:name} {get_field:name} {query_var:name}
        $_user_id = acfe_form_map_field_value_load($_user_id, $current_post_id, $form);
	    $_email = acfe_form_map_field_value_load($_email, $current_post_id, $form);
	    $_username = acfe_form_map_field_value_load($_username, $current_post_id, $form);
	    $_password = acfe_form_map_field_value_load($_password, $current_post_id, $form);
	    $_first_name = acfe_form_map_field_value_load($_first_name, $current_post_id, $form);
	    $_last_name = acfe_form_map_field_value_load($_last_name, $current_post_id, $form);
	    $_nickname = acfe_form_map_field_value_load($_nickname, $current_post_id, $form);
	    $_display_name = acfe_form_map_field_value_load($_display_name, $current_post_id, $form);
	    $_website = acfe_form_map_field_value_load($_website, $current_post_id, $form);
	    $_description = acfe_form_map_field_value_load($_description, $current_post_id, $form);
	    $_role = acfe_form_map_field_value_load($_role, $current_post_id, $form);
        
        $_user_id = apply_filters('acfe/form/load/user_id',                      $_user_id, $form, $action);
        $_user_id = apply_filters('acfe/form/load/user_id/form=' . $form_name,   $_user_id, $form, $action);
        
        if(!empty($action))
            $_user_id = apply_filters('acfe/form/load/user_id/action=' . $action, $_user_id, $form, $action);
        
        // Invalid User ID
        if(!$_user_id)
            return $form;
        
        $user_data = get_userdata($_user_id);
        
        // Check if userdata has been found
        if(!$user_data)
            return $form;
        
        // Email
        if(acf_is_field_key($_email)){
            
            $key = array_search($_email, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_email]['value'] = $user_data->user_email;
            
        }
        
        // Username
        if(acf_is_field_key($_username)){
            
            $key = array_search($_username, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_username]['value'] = $user_data->user_login;
	        $form['map'][$_username]['maxlength'] = 60;
            
        }
        
        // Password
        if(acf_is_field_key($_password)){
            
            $key = array_search($_password, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        //$form['map'][$_password]['value'] = $user_data->user_pass;
            
        }
        
        // First name
        if(acf_is_field_key($_first_name)){
            
            $key = array_search($_first_name, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_first_name]['value'] = $user_data->first_name;
            
        }
        
        // Last name
        if(acf_is_field_key($_last_name)){
            
            $key = array_search($_last_name, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_last_name]['value'] = $user_data->last_name;
            
        }
        
        // Nickname
        if(acf_is_field_key($_nickname)){
            
            $key = array_search($_nickname, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_nickname]['value'] = $user_data->nickname;
            
        }
        
        // Display name
        if(acf_is_field_key($_display_name)){
            
            $key = array_search($_display_name, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_display_name]['value'] = $user_data->display_name;
            
        }
        
        // Website
        if(acf_is_field_key($_website)){
            
            $key = array_search($_website, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_website]['value'] = $user_data->website;
            
        }
        
        // Description
        if(acf_is_field_key($_description)){
            
            $key = array_search($_description, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_description]['value'] = $user_data->description;
            
        }
        
        // Role
        if(acf_is_field_key($_role)){
            
            $key = array_search($_role, $load_meta);
            
            if($key !== false)
                unset($load_meta[$key]);
	
	        $form['map'][$_role]['value'] = implode(', ', $user_data->roles);
            
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                $form['map'][$field_key]['value'] = acf_get_value('user_' . $_user_id, $field);
                
            }
            
        }
        
        return $form;
        
    }
    
    function validation($form, $current_post_id, $action){
        
        // Action
        $user_action = get_sub_field('acfe_form_user_action');
        
        if($user_action !== 'log_user')
            return;
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');

        // Fields
	    $data = array(
            'type'  => get_sub_field('acfe_form_user_log_type'),
            'login' => get_sub_field('acfe_form_user_save_login_user'),
            'pass'  => get_sub_field('acfe_form_user_save_login_pass'),
        );
	
	    $data['login'] = acfe_form_map_field_value($data['login'], $current_post_id, $form);
	    $data['pass'] = acfe_form_map_field_value($data['pass'], $current_post_id, $form);
	
	    $login = false;
	    $pass = false;
        
        // Email
        if(!empty($data['login'])){
            
            $login = $data['login'];
            
        }
        
        // Password
        if(!empty($data['pass'])){
            
            $pass = $data['pass'];
            
        }
        
        $pass = wp_specialchars_decode($pass);
        
        if(empty($login) || empty($pass)){
            
            acfe_add_validation_error('', 'An error has occured. Please try again');
            return;
            
        }
        
        // Email
        if($data['type'] === 'email'){
            
            $this->validate_user_login('email', $login, $pass);
            
        }
        
        // Username
        elseif($data['type'] === 'username'){
            
            $this->validate_user_login('username', $login, $pass);
            
        }
        
        // Email || Username
        elseif($data['type'] === 'email_username'){
            
            // Email
            if(is_email($login)){
                
                $this->validate_user_login('email', $login, $pass);
            
            // Username
            }else{
                
                $this->validate_user_login('username', $login, $pass);
                
            }
            
        }
        
    }
    
    function validate_user_login($type = 'email', $login, $pass){
        
        if($type === 'email'){
            
            $login = sanitize_email($login);
            
            if(empty($login) || !is_email($login)){
                
                acfe_add_validation_error('', 'Invalid e-mail');
                return;
                
            }
            
            $user = get_user_by('email', $login);
            
            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                
                acfe_add_validation_error('', 'Invalid e-mail or password');
                return;
                
            }
            
        }elseif($type === 'username'){
            
            $login = sanitize_user($login);
            
            if(empty($login)){
                
                acfe_add_validation_error('', 'Invalid username');
                return;
                
            }
            
            $user = get_user_by('login', $login);
            
            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                
                acfe_add_validation_error('', 'Invalid username or password');
                return;
                
            }
            
        }
        
    }
    
    function prepare($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        // Action
        $user_action = get_sub_field('acfe_form_user_action');
	
	    // Load values
	    $load_values = get_sub_field('acfe_form_user_load_values');
        
        // Pre-process
        $_description_group = get_sub_field('acfe_form_user_save_description_group');
        $_description = $_description_group['acfe_form_user_save_description'];
        $_description_custom = $_description_group['acfe_form_user_save_description_custom'];
        
        if($_description === 'custom')
            $_description = $_description_custom;
	
	    $map = array();
	
	    if($load_values){
		
		    // Mapping
		    $map = array(
			    'user_email'   => get_sub_field( 'acfe_form_user_map_email' ),
			    'user_login'   => get_sub_field( 'acfe_form_user_map_username' ),
			    'user_pass'    => get_sub_field( 'acfe_form_user_map_password' ),
			    'first_name'   => get_sub_field( 'acfe_form_user_map_first_name' ),
			    'last_name'    => get_sub_field( 'acfe_form_user_map_last_name' ),
			    'nickname'     => get_sub_field( 'acfe_form_user_map_nickname' ),
			    'display_name' => get_sub_field( 'acfe_form_user_map_display_name' ),
			    'user_url'     => get_sub_field( 'acfe_form_user_map_website' ),
			    'description'  => get_sub_field( 'acfe_form_user_map_description' ),
			    'role'         => get_sub_field( 'acfe_form_user_map_role' ),
		    );
		
	    }
        
        // Fields
        $fields = array(
            'target'            => get_sub_field('acfe_form_user_save_target'),
            
            'login_type'        => get_sub_field('acfe_form_user_log_type'),
            'login_user'        => get_sub_field('acfe_form_user_save_login_user'),
            'login_pass'        => get_sub_field('acfe_form_user_save_login_pass'),
            'login_remember'    => get_sub_field('acfe_form_user_save_login_remember'),
            
            'user_email'        => get_sub_field('acfe_form_user_save_email'),
            'user_login'        => get_sub_field('acfe_form_user_save_username'),
            'user_pass'         => get_sub_field('acfe_form_user_save_password'),
            'first_name'        => get_sub_field('acfe_form_user_save_first_name'),
            'last_name'         => get_sub_field('acfe_form_user_save_last_name'),
            'nickname'          => get_sub_field('acfe_form_user_save_nickname'),
            'display_name'      => get_sub_field('acfe_form_user_save_display_name'),
            'user_url'          => get_sub_field('acfe_form_user_save_website'),
            'description'       => $_description,
            'role'              => get_sub_field('acfe_form_user_save_role'),
        );
        
        $data = acfe_form_map_vs_fields($map, $fields, $current_post_id, $form);
        
        // args
        $args = array();
        
        // Insert user
        $_user_id = 0;
        
        // Insert || Update
        if($user_action === 'insert_user' || $user_action === 'update_user'){
            
            // Update user
            if($user_action === 'update_user'){
                
                $_user_id = $data['target'];
                
                // Invalid User ID
                if(!$_user_id)
                    return;
                
                // ID
                $args['ID'] = $_user_id;
                
            }
            
            // Email
            if(!empty($data['user_email'])){
	
	            if(is_array($data['user_email']))
		            $data['user_email'] = acfe_array_to_string($data['user_email']);
                
                $args['user_email'] = $data['user_email'];
                
            }
            
            // Username
            if(!empty($data['user_login'])){
	
	            if(is_array($data['user_login']))
		            $data['user_login'] = acfe_array_to_string($data['user_login']);
                
                $args['user_login'] = $data['user_login'];
                
            }
            
            // Password
            if(!empty($data['user_pass'])){
	
	            if(is_array($data['user_pass']))
		            $data['user_pass'] = acfe_array_to_string($data['user_pass']);
                
                $args['user_pass'] = $data['user_pass'];
                
            }
            
            // First name
            if(!empty($data['first_name'])){
	
	            if(is_array($data['first_name']))
		            $data['first_name'] = acfe_array_to_string($data['first_name']);
                
                $args['first_name'] = $data['first_name'];
                
            }
            
            // Last name
            if(!empty($data['last_name'])){
	
	            if(is_array($data['last_name']))
		            $data['last_name'] = acfe_array_to_string($data['last_name']);
                
                $args['last_name'] = $data['last_name'];
                
            }
            
            // Nickname
            if(!empty($data['nickname'])){
	
	            if(is_array($data['nickname']))
		            $data['nickname'] = acfe_array_to_string($data['nickname']);
                
                $args['nickname'] = $data['nickname'];
                
            }
            
            // Display name
            if(!empty($data['display_name'])){
	
	            if(is_array($data['display_name']))
		            $data['display_name'] = acfe_array_to_string($data['display_name']);
                
                $args['display_name'] = $data['display_name'];
                
            }
            
            // Website
            if(!empty($data['user_url'])){
	
	            if(is_array($data['user_url']))
		            $data['user_url'] = acfe_array_to_string($data['user_url']);
                
                $args['user_url'] = $data['user_url'];
                
            }
            
            // Description
            if(!empty($data['description'])){
	
	            if(is_array($data['description']))
		            $data['description'] = acfe_array_to_string($data['description']);
                
                $args['description'] = $data['description'];
                
            }
            
            // Role
            if(!empty($data['role'])){
	
	            if(is_array($data['role']))
		            $data['role'] = acfe_array_to_string($data['role']);
                
                $args['role'] = $data['role'];
                
            }
            
            $args = apply_filters('acfe/form/submit/user_args',                     $args, $user_action, $form, $action);
            $args = apply_filters('acfe/form/submit/user_args/form=' . $form_name,  $args, $user_action, $form, $action);
            
            if(!empty($action))
                $args = apply_filters('acfe/form/submit/user_args/action=' . $action, $args, $user_action, $form, $action);
	
	        if($args === false)
		        return false;
            
            // Insert User
            if($user_action === 'insert_user'){
                
                // Bail early if no e-mail
                if(!isset($args['user_email']))
                    return false;
                
                // No login? Fallback to e-mail
	            if(!isset($args['user_login']))
	                $args['user_login'] = $args['user_email'];
	            
	            // No password? Fallback to generated password
	            if(!isset($args['user_pass']))
		            $args['user_pass'] = wp_generate_password(8, false);
                
            }
            
            // Insert User
            if($user_action === 'insert_user'){
                
                $_insert_user = wp_insert_user($args);
                
            }
            
            // Update User
            elseif($user_action === 'update_user'){
                
                $_insert_user = wp_update_user($args);
                
                if(!is_wp_error($_insert_user)){
                
                    $_user_id = $_insert_user;
                    
                    // Update User Login + Nicename
                    if(acf_maybe_get($args, 'user_login')){
                        
                        // Sanitize
                        $sanitized_user_login = sanitize_user($args['user_login'], true);
                        
                        // Filter
                        $pre_user_login = apply_filters('pre_user_login', $sanitized_user_login);
                        
                        // Trim
                        $user_login = trim($pre_user_login);
                        
                        $error = false;
                        
                        if(empty($user_login)){
                            
                            $error = new WP_Error('empty_user_login', __('Cannot create a user with an empty login name.'));
                            
                        }elseif(mb_strlen($user_login) > 60){
                            
                            $error = new WP_Error('user_login_too_long', __('Username may not be longer than 60 characters.'));
                            
                        }
                        
                        if(username_exists($user_login)){
                            
                            $error = new WP_Error('existing_user_login', __('Sorry, that username already exists!'));
                            
                        }
                        
                        $user_nicename = sanitize_user($user_login, true);
                        
                        if(mb_strlen($user_nicename) > 50){
                            
                            $error = new WP_Error('user_nicename_too_long', __('Nicename may not be longer than 50 characters.'));
                            
                        }
                        
                        $user_nicename = sanitize_title($user_nicename);
                        
                        $user_nicename = apply_filters('pre_user_nicename', $user_nicename);
                        
                        if(!is_wp_error($error)){
                        
                            global $wpdb;
                            
                            $wpdb->update($wpdb->users, 
                                array(
                                    'user_login'    => $user_login,
                                    'user_nicename' => $user_nicename,
                                ), 
                                array(
                                    'ID' => $_user_id
                                )
                            );
                        
                        }
                        
                    }
                    
                }
                
            }
        
        }
        
        // Log User
        elseif($user_action === 'log_user'){
            
            $_insert_user = false;
	
	        $_login_user = false;
	        $_login_pass = false;
            $_login_remember = false;
            
            // Email
            if(!empty($data['login_user'])){
                
                $_login_user = $data['login_user'];
                
            }
            
            // Password
            if(!empty($data['login_pass'])){
                
                $_login_pass = $data['login_pass'];
                
            }
            
            // Remember me
            if(!empty($data['login_remember'])){
	
	            $_login_remember = $data['login_remember'];
             
            }

            $_login_pass = wp_specialchars_decode($_login_pass);
            
            // Email
            if($data['login_type'] === 'email'){
                
                $_login_user = sanitize_email($_login_user);
                $user = get_user_by('email', $_login_user);
                
            }
            
            // Username
            elseif($data['login_type'] === 'username'){
                
                $_login_user = sanitize_user($_login_user);
                $user = get_user_by('login', $_login_user);
                
            }
            
            // Email || Username
            elseif($data['login_type'] === 'email_username'){
                
                // Email
                if(is_email($_login_user)){
                    
                    $user = get_user_by('email', $_login_user);
                
                // Username
                }else{
                    
                    $user = get_user_by('login', $_login_user);
                    
                }
                
            }
	
	        $_login_remember = boolval($_login_remember);
            
            // Login
            $_insert_user = wp_signon(array(
                'user_login'    => $user->user_login,
                'user_password' => $_login_pass,
                'remember'      => $_login_remember
            ), is_ssl());
            
            // User Error
            if(is_wp_error($_insert_user))
                return;
            
            $_insert_user = $_insert_user->ID;
            
        }
        
        // User Error
        if(is_wp_error($_insert_user))
            return;
        
        $_user_id = $_insert_user;
        
        // Save meta
        do_action('acfe/form/submit/user',                     $_user_id, $user_action, $args, $form, $action);
        do_action('acfe/form/submit/user/form=' . $form_name,  $_user_id, $user_action, $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/user/action=' . $action, $_user_id, $user_action, $args, $form, $action);
        
    }
    
    function submit($_user_id, $user_action, $args, $form, $action){
        
        if(!empty($action)){
        
            // Custom Query Var
            $custom_query_var = get_sub_field('acfe_form_custom_query_var');
            
            if(!empty($custom_query_var)){
                
                // Form name
                $form_name = acf_maybe_get($form, 'form_name');
                
                // Get user array
                $user_object = get_user_by('ID', $_user_id);
                
                if(isset($user_object->data)){
                    
                    // return array
                    $user = json_decode(json_encode($user_object->data), true);
                    
                    $user_object_meta = get_user_meta($user['ID']);
                    
                    $user_meta = array();
                    
                    foreach($user_object_meta as $k => $v){
                        
                        if(!isset($v[0]))
                            continue;
                        
                        $user_meta[$k] = $v[0];
                        
                    }
                    
                    $user_array = array_merge($user, $user_meta);
                    
                    $user_array['permalink'] = get_author_posts_url($_user_id);
                    $user_array['admin_url'] = admin_url('user-edit.php?user_id=' . $_user_id);
                    
                    $user_array = apply_filters('acfe/form/query_var/user',                    $user_array, $_user_id, $user_action, $args, $form, $action);
                    $user_array = apply_filters('acfe/form/query_var/user/form=' . $form_name, $user_array, $_user_id, $user_action, $args, $form, $action);
                    $user_array = apply_filters('acfe/form/query_var/user/action=' . $action,  $user_array, $_user_id, $user_action, $args, $form, $action);
                    
                    set_query_var($action, $user_array);
                
                }
            
            }
        
        }
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_user_save_meta');
        
        if(!empty($save_meta)){
            
            $meta = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($meta)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post('user_' . $_user_id, $meta);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
    /**
     *  User: Select2 Choices
     */
    function prepare_choices($field){
        
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
<pre>
add_filter('acfe/form/load/user_id', 'my_form_user_values_source', 10, 3);
add_filter('acfe/form/load/user_id/form=<?php echo $form_name; ?>', 'my_form_user_values_source', 10, 3);
add_filter('acfe/form/load/user_id/action=my-user-action', 'my_form_user_values_source', 10, 3);
</pre>
<br />
<pre>
/**
 * @int     $user_id    User ID used as source
 * @array   $form       The form settings
 * @string  $action     The action alias name
 */
add_filter('acfe/form/load/user_id/form=<?php echo $form_name; ?>', 'my_form_user_values_source', 10, 3);
function my_form_user_values_source($user_id, $form, $action){
    
    /**
     * Force to load values from the user ID 12
     */
    $user_id = 12;
    
    
    /**
     * Return
     */
    return $user_id;
    
}
</pre><?php
        
    }
    
    function advanced_save_args($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_filter('acfe/form/submit/user_args', 'my_form_user_args', 10, 4);
add_filter('acfe/form/submit/user_args/form=<?php echo $form_name; ?>', 'my_form_user_args', 10, 4);
add_filter('acfe/form/submit/user_args/action=my-user-action', 'my_form_user_args', 10, 4);
</pre>
<br />
<pre>
/**
 * @array   $args   The generated user arguments
 * @string  $type   Action type: 'insert_user' or 'update_user'
 * @array   $form   The form settings
 * @string  $action The action alias name
 */
add_filter('acfe/form/submit/user_args/form=<?php echo $form_name; ?>', 'my_form_user_args', 10, 4);
function my_form_user_args($args, $type, $form, $action){
    
    /**
     * Force specific first name if the action type is 'insert_user'
     */
    if($type === 'insert_user'){
        
        $args['first_name'] = 'My name';
        
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
    
}
</pre><?php
        
    }
    
    function advanced_save($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_action('acfe/form/submit/user', 'my_form_user_save', 10, 5);
add_action('acfe/form/submit/user/form=<?php echo $form_name; ?>', 'my_form_user_save', 10, 5);
add_action('acfe/form/submit/user/action=my-user-action', 'my_form_user_save', 10, 5);
</pre>
<br />
<pre>
/**
 * @int     $user_id    The targeted user ID
 * @string  $type       Action type: 'insert_user' or 'update_user'
 * @array   $args       The generated user arguments
 * @array   $form       The form settings
 * @string  $action     The action alias name
 *
 * Note: At this point the user is already saved into the database
 */
add_action('acfe/form/submit/user/form=<?php echo $form_name; ?>', 'my_form_user_save', 10, 5);
function my_form_user_save($user_id, $type, $args, $form, $action){
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the currently saved user
     */
    $my_user_field = get_field('my_field', 'user_' . $user_id);
    
}
</pre><?php
        
    }
    
}

new acfe_form_user();

endif;