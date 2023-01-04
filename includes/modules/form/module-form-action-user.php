<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_user')):

class acfe_form_user{
    
    function __construct(){
    
        /*
         * Helpers
         */
        $helpers = acf_get_instance('acfe_dynamic_forms_helpers');
        
        /*
         * Action
         */
        add_filter('acfe/form/actions',                                             array($this, 'add_action'));
        add_filter('acfe/form/load/user',                                           array($this, 'load'),       10, 3);
        add_action('acfe/form/validation/user',                                     array($this, 'validation'), 10, 3);
        add_action('acfe/form/make/user',                                           array($this, 'make'),       10, 3);
        add_action('acfe/form/submit/user',                                         array($this, 'submit'),     10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_user_save_meta',               array($helpers, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_user_load_meta',               array($helpers, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_user',         array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_pass',         array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_login_remember',     array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_target',             array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_load_source',             array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_email',              array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_username',           array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_password',           array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_first_name',         array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_last_name',          array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_nickname',           array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_display_name',       array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_website',            array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_description',        array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_save_role',               array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_map_email',               array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_username',            array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_password',            array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_first_name',          array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_last_name',           array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_nickname',            array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_display_name',        array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_website',             array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_description',         array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_role',                array($helpers, 'map_fields_deep_no_custom'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_save_target',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_user_load_source',             array($this, 'prepare_choices'), 5);
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
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
        
        $rules = array(
            
            array(
                'key'        => $_email,
                'attributes' => array(
                    'value'  => $user_data->user_email,
                ),
            ),
    
            array(
                'key'        => $_username,
                'attributes' => array(
                    'value'      => $user_data->user_login,
                    'maxlength'  => 60,
                ),
            ),
            
            /*
            array(
                'key'        => $_password,
                'attributes' => array(
                    'value'  => $user_data->user_pass,
                ),
            ),
            */
    
            array(
                'key'        => $_first_name,
                'attributes' => array(
                    'value'  => $user_data->first_name,
                ),
            ),
    
            array(
                'key'        => $_last_name,
                'attributes' => array(
                    'value'  => $user_data->last_name,
                ),
            ),
    
            array(
                'key'        => $_nickname,
                'attributes' => array(
                    'value'  => $user_data->nickname,
                ),
            ),
            
            array(
                'key'        => $_display_name,
                'attributes' => array(
                    'value'  => $user_data->display_name,
                ),
            ),
            
            array(
                'key'        => $_website,
                'attributes' => array(
                    'value'  => $user_data->website,
                ),
            ),
            
            array(
                'key'        => $_description,
                'attributes' => array(
                    'value'  => $user_data->description,
                ),
            ),
            
            array(
                'key'        => $_role,
                'attributes' => array(
                    'value'  => implode(', ', $user_data->roles),
                ),
            ),
            
        );
        
        foreach($rules as $rule){
    
            if(acf_is_field_key($rule['key'])){
                
                // disable loading from meta if checked
                if(($key = array_search($rule['key'], $load_meta)) !== false){
                    unset($load_meta[ $key ]);
                }
        
                if(!isset($form['map'][ $rule['key'] ]) || $form['map'][ $rule['key'] ] !== false){
                    
                    foreach($rule['attributes'] as $attribute_key => $attribute_value){
                        
                        if(!isset($form['map'][ $rule['key'] ][ $attribute_key ])){
                            $form['map'][ $rule['key'] ][ $attribute_key ] = $attribute_value;
                        }
                        
                    }
                    
                }
        
            }
            
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
    
                $field = acf_get_field($field_key);
    
                if(!$field)
                    continue;
    
                if($field['type'] === 'clone' && $field['display'] === 'seamless'){
        
                    $sub_fields = acf_get_value('user_' . $_user_id, $field);
        
                    foreach($sub_fields as $sub_field_key => $value){
            
                        $form['map'][$sub_field_key]['value'] = $value;
            
                    }
        
                }else{
        
                    $form['map'][$field_key]['value'] = acf_get_value('user_' . $_user_id, $field);
        
                }
                
            }
            
        }
        
        return $form;
        
    }
    
    function validation($form, $current_post_id, $action){
        
        // action
        $user_action = get_sub_field('acfe_form_user_action');
        
        // errors
        $errors = array(
            'empty_user_pass'               => __('An error has occured. Please try again', 'acfe'),
            'invalid_email'                 => __('Invalid e-mail', 'acfe'),
            'invalid_email_password'        => __('Invalid e-mail or password', 'acfe'),
            'invalid_username'              => __('Invalid username', 'acfe'),
            'invalid_username_password'     => __('Invalid username or password', 'acfe'),
            'used_email'                    => __('E-mail address is already used', 'acfe'),
            'used_username'                 => __('Username is already used', 'acfe'),
            'long_username'                 => __('Username may not be longer than 60 characters.'),
        );
        
        // filters
        $errors = apply_filters_deprecated('acfe/form/validation/user/login_errors', array($errors), '0.8.8.8', 'acfe/form/validation/user_errors');
        $errors = apply_filters('acfe/form/validation/user_errors', $errors);
    
        // switch type
        switch($user_action){
    
            // insert user
            case 'insert_user':{
    
                // fields
                $user_email = get_sub_field('acfe_form_user_save_email');
                $user_email = acfe_form_map_field_value($user_email, $current_post_id, $form);
        
                // empty email
                if(empty($user_email) || !is_email($user_email)){
                    return acfe_add_validation_error('', $errors['invalid_email']);
            
                // email exists
                }elseif(email_exists($user_email)){
                    return acfe_add_validation_error('', $errors['used_email']);
                }
        
                break;
            }
    
            // update user
            case 'update_user':{
    
                // fields
                $target = get_sub_field('acfe_form_user_save_target');
                $target = acfe_form_map_field_value($target, $current_post_id, $form);
                $target = absint($target);
                
                $user_login = get_sub_field('acfe_form_user_save_username');
                $user_login = acfe_form_map_field_value($user_login, $current_post_id, $form);
    
                $user_email = get_sub_field('acfe_form_user_save_email');
                $user_email = acfe_form_map_field_value($user_email, $current_post_id, $form);
        
                // check user login exists
                if(!empty($user_login)){
            
                    // login too long
                    if(mb_strlen($user_login) > 60){
                        return acfe_add_validation_error('', $errors['long_username']);
                
                    // login already exists
                    }elseif(username_exists($user_login) && username_exists($user_login) !== $target){
                        return acfe_add_validation_error('', $errors['used_username']);
                    }
            
                }
    
                // check user email exists
                if(!empty($user_email)){
                    
                    $target_user = get_user_by('ID', $target);
                    
                    if($target_user && $user_email !== $target_user->user_email){
    
                        // invalid email
                        if(!is_email($user_email)){
                            return acfe_add_validation_error('', $errors['invalid_email']);
        
                        // email exists
                        }elseif(email_exists($user_email)){
                            return acfe_add_validation_error('', $errors['used_email']);
                        }
                        
                    }
        
                }
        
                break;
            }
        
            // log user
            case 'log_user':{
    
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
                $pass = wp_slash($pass);
    
                if(empty($login) || empty($pass)){
                    return acfe_add_validation_error('', $errors['empty_user_pass']);
                }
    
                // Email
                if($data['type'] === 'email'){
                    $this->validate_user_login('email', $login, $pass, $errors);
        
                // Username
                }elseif($data['type'] === 'username'){
                    $this->validate_user_login('username', $login, $pass, $errors);
        
                // Email || Username
                }elseif($data['type'] === 'email_username'){
        
                    // Email
                    if(is_email($login)){
                        $this->validate_user_login('email', $login, $pass, $errors);
            
                    // Username
                    }else{
                        $this->validate_user_login('username', $login, $pass, $errors);
                    }
        
                }
            
                break;
            }
            
        }
        
    }
    
    function validate_user_login($type, $login, $pass, $errors){
        
        if($type === 'email'){
            
            $login = sanitize_email($login);
            
            if(empty($login) || !is_email($login)){
                
                acfe_add_validation_error('', $errors['invalid_email']);
                return;
                
            }
            
            $user = get_user_by('email', $login);
            
            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                
                acfe_add_validation_error('', $errors['invalid_email_password']);
                return;
                
            }
            
        }elseif($type === 'username'){
            
            $login = sanitize_user($login);
            
            if(empty($login)){
                
                acfe_add_validation_error('', $errors['invalid_username']);
                return;
                
            }
            
            $user = get_user_by('login', $login);
            
            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                
                acfe_add_validation_error('', $errors['invalid_username_password']);
                return;
                
            }
            
        }
        
    }
    
    function make($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/user',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/user/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/user/action=' . $action,    $prepare, $form, $current_post_id, $action);
    
        if($prepare === false)
            return;
        
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
    
                $args['user_pass'] = wp_specialchars_decode($args['user_pass']);
                $args['user_pass'] = wp_slash($args['user_pass']);
                
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
            $_login_pass = wp_slash($_login_pass);
            
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
    
        // Form name
        $form_name = acf_maybe_get($form, 'name');
    
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
        
            // Replace the hash password with the real password
            if(acf_maybe_get($args, 'user_pass')){
            
                $user_array['user_pass'] = $args['user_pass'];
            
            }
    
            // Deprecated
            $user_array = apply_filters_deprecated("acfe/form/query_var/user",                    array($user_array, $_user_id, $user_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/user");
            $user_array = apply_filters_deprecated("acfe/form/query_var/user/form={$form_name}",  array($user_array, $_user_id, $user_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/user/form={$form_name}");
            $user_array = apply_filters_deprecated("acfe/form/query_var/user/action={$action}",   array($user_array, $_user_id, $user_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/user/action={$action}");
    
            // Output
            $user_array = apply_filters("acfe/form/output/user",                                       $user_array, $_user_id, $user_action, $args, $form, $action);
            $user_array = apply_filters("acfe/form/output/user/form={$form_name}",                     $user_array, $_user_id, $user_action, $args, $form, $action);
            $user_array = apply_filters("acfe/form/output/user/action={$action}",                      $user_array, $_user_id, $user_action, $args, $form, $action);
            
            // Old Query var
            $query_var = acfe_form_unique_action_id($form, 'user');
            
            if(!empty($action))
                $query_var = $action;
            
            set_query_var($query_var, $user_array);
            // ------------------------------------------------------------
            
            // Action Output
            $actions = get_query_var('acfe_form_actions', array());
    
            $actions['user'] = $user_array;
            
            if(!empty($action))
                $actions[$action] = $user_array;
            
            set_query_var('acfe_form_actions', $actions);
            // ------------------------------------------------------------
        
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
    
    function add_action($layouts){
        
        $layouts['layout_user'] = array(
            'key' => 'layout_user',
            'name' => 'user',
            'label' => 'User action',
            'display' => 'row',
            'sub_fields' => array(
    
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_user_action_docs',
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
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/user-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
        
                /*
                 * Layout: User Action
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_action',
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
                    'key' => 'field_acfe_form_user_action',
                    'label' => 'Action',
                    'name' => 'acfe_form_user_action',
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
                        'insert_user'   => 'Create user',
                        'update_user'   => 'Update user',
                        'log_user'      => 'Log user',
                    ),
                    'default_value' => 'insert_post',
                ),
                array(
                    'key' => 'field_acfe_form_user_custom_alias',
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
                    'placeholder' => 'User',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
        
                /*
                 * Layout: User Login
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_login',
                    'label' => 'Login',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_log_type',
                    'label' => 'Login type',
                    'name' => 'acfe_form_user_log_type',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'email'             => 'E-mail',
                        'username'          => 'Username',
                        'email_username'    => 'E-mail or username',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'email',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_user',
                    'label' => 'Login',
                    'name' => 'acfe_form_user_save_login_user',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_pass',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_save_login_pass',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_remember',
                    'label' => 'Remember me',
                    'name' => 'acfe_form_user_save_login_remember',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
        
                /*
                 * Layout: User Save
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_user_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'update_user',
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
                    'default_value' => 'current_user',
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
                    'key' => 'field_acfe_form_user_save_email',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_save_email',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_email',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_email_message',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_map_email_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_email',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_username',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_save_username',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_username',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_username_message',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_map_username_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_username',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_password',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_save_password',
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
                        'generate_password' => 'Generate password',
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
                                'field' => 'field_acfe_form_user_map_password',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_password_message',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_map_password_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_password',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_first_name',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_save_first_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_first_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_first_name_message',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_map_first_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_first_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_last_name',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_save_last_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(),
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
                                'field' => 'field_acfe_form_user_map_last_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_last_name_message',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_map_last_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_last_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_nickname',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_save_nickname',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_nickname',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_nickname_message',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_map_nickname_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_nickname',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_display_name',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_save_display_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_display_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_display_name_message',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_map_display_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_display_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_website',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_save_website',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
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
                                'field' => 'field_acfe_form_user_map_website',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_website_message',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_map_website_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_website',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_description_group',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_save_description_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_description',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                            'key' => 'field_acfe_form_user_save_description',
                            'label' => '',
                            'name' => 'acfe_form_user_save_description',
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
                                'custom' => 'WYSIWYG Editor',
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
                            'key' => 'field_acfe_form_user_save_description_custom',
                            'label' => '',
                            'name' => 'acfe_form_user_save_description_custom',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_user_save_description',
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
                    'key' => 'field_acfe_form_user_map_description_message',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_map_description_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_description',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_role',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_save_role',
                    'type' => 'acfe_user_roles',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_role',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'user_role' => '',
                    'field_type' => 'select',
                    'default_value' => '',
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
                    'key' => 'field_acfe_form_user_map_role_message',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_map_role_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_role',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
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
                    'key' => 'field_acfe_form_user_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_user_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
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
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
        
                /*
                 * Layout: User Load
                 */
                array(
                    'key' => 'acfe_form_user_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_user_load_values',
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
                    'key' => 'field_acfe_form_user_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_user_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
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
                    'default_value' => 'current_user',
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
                    'key' => 'field_acfe_form_user_map_email',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_map_email',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_username',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_map_username',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_password',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_map_password',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_first_name',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_map_first_name',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_last_name',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_map_last_name',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_nickname',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_map_nickname',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_display_name',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_map_display_name',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_website',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_map_website',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_description',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_map_description',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_role',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_map_role',
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
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_user_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
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

new acfe_form_user();

endif;