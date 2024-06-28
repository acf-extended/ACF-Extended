<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_user')):

class acfe_module_form_action_user extends acfe_module_form_action{
    
    public $errors;
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'user';
        $this->title = __('User action', 'acfe');
        
        $this->item = array(
            'action' => 'user',
            'type'   => 'insert_user', // insert_user | update_user | log_user
            'name'   => '',
            'validation' => true,
            'login'   => array(
                'type'         => '',
                'user'         => '',
                'pass'         => '',
                'remember'     => '',
            ),
            'save'   => array(
                'target'       => '',
                'user_email'   => '',
                'user_login'   => '',
                'user_pass'    => '',
                'first_name'   => '',
                'last_name'    => '',
                'nickname'     => '',
                'display_name' => '',
                'user_url'     => '',
                'description'  => '',
                'role'         => '',
                'log_user'     => false,
                'acf_fields'   => array(),
            ),
            'load'   => array(
                'source'       => '',
                'user_email'   => '',
                'user_login'   => '',
                'user_pass'    => '',
                'first_name'   => '',
                'last_name'    => '',
                'nickname'     => '',
                'display_name' => '',
                'user_url'     => '',
                'description'  => '',
                'role'         => '',
                'acf_fields'   => array(),
            ),
        );
    
        $this->fields = array('user_email', 'user_login', 'user_pass', 'first_name', 'last_name', 'nickname', 'display_name', 'user_url', 'description', 'role');
        
    }
    
    
    /**
     * load_action
     *
     * acfe/form/load_user:9
     *
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function load_action($form, $action){
        
        // check source
        if(!$action['load']['source']){
            return $form;
        }
    
        // apply template tags
        acfe_apply_tags($action['load']['source'], array('context' => 'load', 'format' => false));
        
        // vars
        $load = $action['load'];
        $user_id = acf_extract_var($load, 'source');
        $user_role = acf_extract_var($load, 'role');
        $acf_fields = acf_extract_var($load, 'acf_fields');
        $acf_fields = acf_get_array($acf_fields);
        $acf_fields_exclude = array();
        
        // filters
        $user_id = apply_filters("acfe/form/load_user_id",                          $user_id, $form, $action);
        $user_id = apply_filters("acfe/form/load_user_id/form={$form['name']}",     $user_id, $form, $action);
        $user_id = apply_filters("acfe/form/load_user_id/action={$action['name']}", $user_id, $form, $action);
        
        // bail early if no source
        if(!$user_id){
            return $form;
        }
        
        // get source user
        $user = get_user_by('ID', $user_id);
    
        // no user found
        if(!$user){
            return $form;
        }
        
        /**
         * load user fields
         *
         * $load = array(
         *     user_email => 'field_655af3dd3bd56'
         *     user_login => 'field_655af3dd3bd56'
         *     user_pass  => 'field_655af3dd3bd56'
         *     first_name => ''
         *     last_name  => ''
         * )
         */
        foreach($load as $user_field => $field_key){
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
                
                // check key exists in WP_User and is field key
                if(in_array($user_field, $this->fields) && !empty($field_key) && is_string($field_key) && acf_is_field_key($field_key)){
                    
                    // add field to excluded list
                    $acf_fields_exclude[] = $field_key;
                    
                    // exclude password
                    if($user_field === 'user_pass'){
                        continue;
                    }
                    
                    // assign user field as value
                    $form['map'][ $field_key ]['value'] = $user->{$user_field};
            
                }
                
            }
            
        }
    
        // load user role
        if(!empty($user_role) && is_string($user_role) && acf_is_field_key($user_role)){
            
            // field key
            $field_key = $user_role;
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
            
                // add field to excluded list
                $acf_fields_exclude[] = $field_key;
            
                // get roles
                $form['map'][ $field_key ]['value'] = $user->roles;
            
            }
        
        }
        
        // load acf values
        $form = $this->load_acf_values($form, "user_{$user_id}", $acf_fields, $acf_fields_exclude);
        
        // return
        return $form;
    
    }
    
    
    /**
     * setup_action
     *
     * @param $action
     * @param $form
     *
     * @return mixed
     */
    function setup_action($action, $form){
        
        // switch type
        switch($action['type']){
            
            // insert user
            case 'insert_user':{
                
                // tags context
                $opt     = array('context' => 'save');
                $opt_fmt = array('context' => 'save', 'format' => false);
                
                // apply tags
                acfe_apply_tags($action['save']['target'],       $opt_fmt);
                acfe_apply_tags($action['save']['log_user'],     $opt_fmt);
                acfe_apply_tags($action['save']['user_email'],   $opt_fmt);
                acfe_apply_tags($action['save']['user_login'],   $opt_fmt);
                acfe_apply_tags($action['save']['user_pass'],    $opt_fmt);
                acfe_apply_tags($action['save']['first_name'],   $opt_fmt);
                acfe_apply_tags($action['save']['last_name'],    $opt_fmt);
                acfe_apply_tags($action['save']['nickname'],     $opt_fmt);
                acfe_apply_tags($action['save']['display_name'], $opt_fmt);
                acfe_apply_tags($action['save']['user_url'],     $opt_fmt);
                acfe_apply_tags($action['save']['description'],  $opt);
                acfe_apply_tags($action['save']['role'],         $opt_fmt);
                
                // sanitize password
                $action['save']['user_pass'] = wp_specialchars_decode($action['save']['user_pass']);
    
                // fallback login to email if missing
                if(empty($action['save']['user_login'])){
                    $action['save']['user_login'] = $action['save']['user_email'];
                }
                
                // sanitize login
                $action['save']['user_login'] = sanitize_user($action['save']['user_login'], true);
                $action['save']['user_login'] = apply_filters('pre_user_login', $action['save']['user_login']);
                $action['save']['user_login'] = trim($action['save']['user_login']);
    
                break;
            }
            
            // update user
            case 'update_user':{
                
                // tags context
                $opt     = array('context' => 'save');
                $opt_fmt = array('context' => 'save', 'format' => false);
                
                // apply tags
                acfe_apply_tags($action['save']['target'],       $opt_fmt);
                acfe_apply_tags($action['save']['log_user'],     $opt_fmt);
                acfe_apply_tags($action['save']['user_email'],   $opt_fmt);
                acfe_apply_tags($action['save']['user_login'],   $opt_fmt);
                acfe_apply_tags($action['save']['user_pass'],    $opt_fmt);
                acfe_apply_tags($action['save']['first_name'],   $opt_fmt);
                acfe_apply_tags($action['save']['last_name'],    $opt_fmt);
                acfe_apply_tags($action['save']['nickname'],     $opt_fmt);
                acfe_apply_tags($action['save']['display_name'], $opt_fmt);
                acfe_apply_tags($action['save']['user_url'],     $opt_fmt);
                acfe_apply_tags($action['save']['description'],  $opt);
                acfe_apply_tags($action['save']['role'],         $opt_fmt);
                
                // sanitize password
                $action['save']['user_pass'] = wp_specialchars_decode($action['save']['user_pass']);
                
                // check user login is filled
                if(!empty($action['save']['user_login'])){
                    
                    // sanitize
                    $action['save']['user_login'] = sanitize_user($action['save']['user_login'], true);
                    $action['save']['user_login'] = apply_filters('pre_user_login', $action['save']['user_login']);
                    $action['save']['user_login'] = trim($action['save']['user_login']);
        
                }
                
                break;
            }
            
            // log user
            case 'log_user':{
                
                // tags opt
                $opt = array('context' => 'save', 'format' => false);
                
                // apply tags
                acfe_apply_tags($action['login']['type'],     $opt);
                acfe_apply_tags($action['login']['user'],     $opt);
                acfe_apply_tags($action['login']['pass'],     $opt);
                acfe_apply_tags($action['login']['remember'], $opt);
                
                // sanitize password
                $action['login']['pass'] = wp_specialchars_decode($action['login']['pass']);
                
                // switch login type
                switch($action['login']['type']){
                    
                    // email
                    case 'email':{
                        $action['login']['user'] = sanitize_email($action['login']['user']);
                        break;
                    }
                    
                    // username
                    case 'username':{
                        $action['login']['user'] = sanitize_user($action['login']['user']);
                        break;
                    }
                    
                    // email or username
                    case 'email_username':{
    
                        // email
                        if(is_email($action['login']['user'])){
                            $action['login']['user'] = sanitize_email($action['login']['user']);
        
                        // username
                        }else{
                            $action['login']['user'] = sanitize_user($action['login']['user']);
                        }
                        
                        break;
                    }
                }
                
                break;
            }
            
        }
        
        // return
        return $action;
        
    }
    
    
    /**
     * validate_action
     *
     * acfe/form/validate_user:9
     *
     * @param $form
     * @param $action
     */
    function validate_action($form, $action){
        
        // check built-in validation
        if(empty($action['validation'])){
            return false;
        }
        
        // errors
        $errors = array(
            'empty_user_pass'           => __('An error has occured. Please try again', 'acfe'),
            'invalid_email'             => __('Invalid e-mail', 'acfe'),
            'invalid_email_password'    => __('Invalid e-mail or password', 'acfe'),
            'invalid_username'          => __('Invalid username', 'acfe'),
            'invalid_username_password' => __('Invalid username or password', 'acfe'),
            'used_email'                => __('E-mail address is already used', 'acfe'),
            'used_username'             => __('Username is already used', 'acfe'),
            'long_username'             => __('Username may not be longer than 60 characters.'),
        );
        
        // filters
        $errors = apply_filters("acfe/form/validate_user_errors",                          $errors, $form, $action);
        $errors = apply_filters("acfe/form/validate_user_errors/form={$form['name']}",     $errors, $form, $action);
        $errors = apply_filters("acfe/form/validate_user_errors/action={$action['name']}", $errors, $form, $action);
        
        // apply tags
        $action = $this->setup_action($action, $form);
    
        // switch type
        switch($action['type']){
        
            // insert user
            case 'insert_user':{
                
                // check user login input is filled
                if(!empty($action['save']['user_login'])){
                    
                    // login too long
                    if(mb_strlen($action['save']['user_login']) > 60){
                        return acfe_add_validation_error('', $errors['long_username']);
                        
                    // login already exists
                    // note: username_exists() returns user ID if exists
                    }elseif(username_exists($action['save']['user_login'])){
                        return acfe_add_validation_error('', $errors['used_username']);
                    }
                    
                    // illegal login
                    $illegal_logins = (array) apply_filters('illegal_user_logins', array());
                    
                    if(in_array(strtolower($action['save']['user_login']), array_map('strtolower', $illegal_logins), true)){
                        return acfe_add_validation_error('', $errors['invalid_username']);
                    }
                    
                }
    
                // empty email
                if(empty($action['save']['user_email']) || !is_email($action['save']['user_email'])){
                    return acfe_add_validation_error('', $errors['invalid_email']);
        
                // email exists
                }elseif(email_exists($action['save']['user_email'])){
                    return acfe_add_validation_error('', $errors['used_email']);
                }
            
                break;
            }
        
            // update user
            case 'update_user':{
    
                // check user login input is filled
                if(!empty($action['save']['user_login'])){
        
                    // login too long
                    if(mb_strlen($action['save']['user_login']) > 60){
                        return acfe_add_validation_error('', $errors['long_username']);
            
                    // login already exists
                    // note: username_exists() returns user ID if exists
                    }elseif(username_exists($action['save']['user_login']) && username_exists($action['save']['user_login']) !== (int) $action['save']['target']){
                        return acfe_add_validation_error('', $errors['used_username']);
                    }
                    
                    // illegal login
                    $illegal_logins = (array) apply_filters('illegal_user_logins', array());
                    
                    if(in_array(strtolower($action['save']['user_login']), array_map('strtolower', $illegal_logins), true)){
                        return acfe_add_validation_error('', $errors['invalid_username']);
                    }
        
                }
                
                // check user email input is filled
                if(!empty($action['save']['user_email'])){
                    
                    // invalid email
                    if(!is_email($action['save']['user_email'])){
                        return acfe_add_validation_error('', $errors['invalid_email']);
                        
                    // email already exists
                    // note: email_exists() returns user ID if exists
                    }elseif(email_exists($action['save']['user_email']) && email_exists($action['save']['user_email']) !== (int) $action['save']['target']){
                        return acfe_add_validation_error('', $errors['used_email']);
                    }
                    
                }
            
                break;
            }
        
            // log user
            case 'log_user':{
    
                // vars
                $login = $action['login']['user'];
                $pass = $action['login']['pass'];
    
                // empty login or pass
                if(empty($login) || empty($pass)){
                    return acfe_add_validation_error('', $errors['empty_user_pass']);
                }
                
                // switch login type
                switch($action['login']['type']){
                    
                    // email
                    case 'email':{
                        
                        // already sanitized
                        if(empty($login) || !is_email($login)){
                            return acfe_add_validation_error('', $errors['invalid_email']);
                        }
                        
                        // get user
                        $user = get_user_by('email', $login);
    
                        if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                            return acfe_add_validation_error('', $errors['invalid_email_password']);
                        }
                        
                        break;
                    }
                    
                    // username
                    case 'username':{
                        
                        // already sanitized
                        if(empty($login)){
                            return acfe_add_validation_error('', $errors['invalid_username']);
                        }
                        
                        // get user
                        $user = get_user_by('login', $login);
    
                        if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                            return acfe_add_validation_error('', $errors['invalid_username_password']);
                        }
        
                        break;
                    }
                    
                    // email username
                    case 'email_username':{
    
                        // email
                        if(is_email($login)){
                            
                            // already sanitized
                            if(empty($login)){
                                return acfe_add_validation_error('', $errors['invalid_email']);
                            }
                            
                            // get user
                            $user = get_user_by('email', $login);
        
                            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                                return acfe_add_validation_error('', $errors['invalid_email_password']);
                            }
        
                        // username
                        }else{
                            
                            // already sanitized
                            if(empty($login)){
                                return acfe_add_validation_error('', $errors['invalid_username']);
                            }
                            
                            // get user
                            $user = get_user_by('login', $login);
        
                            if(!$user || !wp_check_password($pass, $user->data->user_pass, $user->ID)){
                                return acfe_add_validation_error('', $errors['invalid_username_password']);
                            }
        
                        }
                        
                        break;
                    }
                    
                }
            
                break;
            }
        
        }
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_user:9
     *
     * @param $action
     * @param $form
     *
     * @return mixed
     */
    function prepare_action($action, $form){
        
        // return
        return $action;
        
    }
    
    
    /**
     * make_action
     *
     * acfe/form/make_user:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
        
        // insert/update/log user
        $process = $this->process($form, $action);
        
        // validate
        if(!$process){
            return;
        }
        
        // process vars
        $user_id = $process['user_id'];
        $args = $process['args'];
        
        // output
        $this->generate_output($user_id, $args, $form, $action);
        
        // acf values
        $this->save_acf_fields("user_{$user_id}", $action);
        
        // hooks
        do_action("acfe/form/submit_user",                          $user_id, $args, $form, $action);
        do_action("acfe/form/submit_user/form={$form['name']}",     $user_id, $args, $form, $action);
        do_action("acfe/form/submit_user/action={$action['name']}", $user_id, $args, $form, $action);
    
    }
    
    
    /**
     * process
     *
     * @param $form
     * @param $action
     *
     * @return array|false
     */
    function process($form, $action){
        
        // apply tags
        $action = $this->setup_action($action, $form);
        
        // switch action type
        switch($action['type']){
            
            // insert/update user
            case 'insert_user':
            case 'update_user':{
                return $this->insert_user($form, $action);
            }
            
            // log user
            case 'log_user':{
                return $this->log_user($form, $action);
            }
            
        }
        
        return false;
        
    }
    
    
    /**
     * insert_user
     *
     * @param $form
     * @param $action
     *
     * @return array|false
     */
    function insert_user($form, $action){
        
        $args = array();
        $save = $action['save'];
        $user_id = (int) acf_extract_var($save, 'target');
        
        // update user
        if($action['type'] === 'update_user'){
            
            // stop action
            if(!$user_id){
                return false;
            }
            
            // set user to update
            $args['ID'] = $user_id;
            
        }
    
        // construct user arguments
        foreach($save as $user_field => $value){
        
            // name, slug, taxonomy, parent etc...
            if(in_array($user_field, $this->fields) && !acf_is_empty($value)){
                $args[ $user_field ] = $value;
            }
        
        }
    
        // filters
        $args = apply_filters("acfe/form/submit_user_args",                          $args, $form, $action);
        $args = apply_filters("acfe/form/submit_user_args/form={$form['name']}",     $args, $form, $action);
        $args = apply_filters("acfe/form/submit_user_args/action={$action['name']}", $args, $form, $action);
    
        // bail early
        if($args === false){
            return false;
        }
        
        // switch action type
        switch($action['type']){
            
            // insert user
            case 'insert_user':{
                
                // fallback for empty password
                // this should exist, but can be manually deleted within filter above
                // and throw a notice
                if(!isset($args['user_pass'])){
                    $args['user_pass'] = '';
                }
                
                // insert user
                $user_id = wp_insert_user($args);
                
                // validate
                if(!$user_id || is_wp_error($user_id)){
                    return false;
                }
                
                // log user once created
                if($action['save']['log_user']){
                    
                    // catch auth setcookie
                    // and assign $_COOKIE so we don't need to reload the page
                    add_action('set_auth_cookie',         array($this, 'set_auth_cookie'));
                    add_action('set_logged_in_cookie',    array($this, 'set_logged_in_cookie'));
                    
                    wp_clear_auth_cookie();
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    remove_action('set_auth_cookie',      array($this, 'set_auth_cookie'));
                    remove_action('set_logged_in_cookie', array($this, 'set_logged_in_cookie'));
                    
                }
                
                break;
            }
            
            // update user
            case 'update_user':{
                
                // update user
                $user_id = wp_update_user($args);
                
                // validate
                if(!$user_id || is_wp_error($user_id)){
                    return false;
                }
                
                // manually update login & nicename
                // we must use $wpdb->update() here because WP doesn't allow to change user login
                if(!empty($args['user_login']) && $args['user_login'] !== get_userdata($user_id)->user_login){
                    
                    // user_login is already sanitized in setup_action()
                    // prepare nicename
                    $user_nicename = mb_substr($args['user_login'], 0, 50); // max 50 chars
                    $user_nicename = sanitize_title($user_nicename);
                    $user_nicename = apply_filters('pre_user_nicename', $user_nicename);
                    
                    // global wpdb
                    global $wpdb;
                    
                    // manual update
                    // this logout the user (because user_login is changed)
                    $wpdb->update($wpdb->users,
                        array(
                            'user_login'    => $args['user_login'], // login
                            'user_nicename' => $user_nicename,      // url
                        ),
                        array(
                            'ID' => $user_id
                        )
                    );
                    
                    // we must re-log the user
                    // catch auth setcookie
                    // and assign $_COOKIE so we don't need to reload the page
                    add_action('set_auth_cookie',         array($this, 'set_auth_cookie'));
                    add_action('set_logged_in_cookie',    array($this, 'set_logged_in_cookie'));
                    
                    // we must clear cache since the user is updated above
                    clean_user_cache($user_id);
                    wp_clear_auth_cookie();
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    remove_action('set_auth_cookie',      array($this, 'set_auth_cookie'));
                    remove_action('set_logged_in_cookie', array($this, 'set_logged_in_cookie'));
                }
                
                break;
            }
            
        }
        
        return array(
            'user_id' => $user_id,
            'args'    => $args
        );
        
    }
    
    
    /**
     * log_user
     *
     * @param $form
     * @param $action
     *
     * @return array|false
     */
    function log_user($form, $action){
        
        $user = false;
        
        // switch login type
        switch($action['login']['type']){
            
            // email
            case 'email':{
                $user = get_user_by('email', $action['login']['user']);
                break;
            }
            
            // username
            case 'username':{
                $user = get_user_by('login', $action['login']['user']);
                break;
            }
            
            // email or username
            case 'email_username':{
                $field = is_email($action['login']['user']) ? 'email' : 'login';
                $user = get_user_by($field, $action['login']['user']);
                break;
            }
            
        }
        
        // validate
        if(!$user){
            return false;
        }
        
        // prepare arguments
        $args = array(
            'user_login'    => $user->user_login,
            'user_password' => $action['login']['pass'],
            'remember'      => boolval($action['login']['remember'])
        );
        
        // catch auth setcookie
        // and assign $_COOKIE so we don't need to reload the page
        add_action('set_auth_cookie',         array($this, 'set_auth_cookie'));
        add_action('set_logged_in_cookie',    array($this, 'set_logged_in_cookie'));
    
        // signon
        $user = wp_signon($args, is_ssl());
        
        remove_action('set_auth_cookie',      array($this, 'set_auth_cookie'));
        remove_action('set_logged_in_cookie', array($this, 'set_logged_in_cookie'));
    
        // validate
        if(!$user || is_wp_error($user)){
            return false;
        }
        
        // setup user for is_user_logged_in()
        wp_set_current_user($user->ID);
        
        // return
        return array(
            'user_id' => $user->ID,
            'args'    => $args
        );
        
    }
    
    
    /**
     * generate_output
     *
     * @param $user_id
     * @param $args
     * @param $form
     * @param $action
     */
    function generate_output($user_id, $args, $form, $action){
    
        // user object
        $user = $this->get_user_array($user_id);
    
        // replace hashed password with real password
        if(acf_maybe_get($args, 'user_pass')){
            $user['user_pass'] = $args['user_pass'];
        }
    
        // filters
        $user = apply_filters("acfe/form/submit_user_output",                          $user, $args, $form, $action);
        $user = apply_filters("acfe/form/submit_user_output/form={$form['name']}",     $user, $args, $form, $action);
        $user = apply_filters("acfe/form/submit_user_output/action={$action['name']}", $user, $args, $form, $action);
    
        // action output
        $this->set_action_output($user, $action);
        
    }
    
    
    /**
     * get_user_array
     *
     * @param $user_id
     *
     * @return array|false
     */
    function get_user_array($user_id){
        
        // bail early if user id is 0
        if(!$user_id){
            return false;
        }
        
        // user object
        $user = get_user_by('ID', $user_id);
        
        // validate
        if(!$user){
            return false;
        }
        
        // cast as array
        $user = (array) $user->data;
        
        // user meta
        $user_meta = get_user_meta($user_id);
        foreach($user_meta as $k => $v){
            if(isset($v[0])){
                $user[ $k ] = $v[0];
            }
        }
        
        // additional fields
        $user['permalink'] = get_author_posts_url($user_id);
        $user['admin_url'] = admin_url("user-edit.php?user_id=$user_id");
        
        // return
        return $user;
        
    }
    
    
    /**
     * set_auth_cookie
     *
     * @param $cookie
     *
     * @return void
     */
    function set_auth_cookie($cookie){
        
        $cookie_name = is_ssl() ? SECURE_AUTH_COOKIE : AUTH_COOKIE;
        $_COOKIE[ $cookie_name ] = $cookie;
        
    }
    
    
    /**
     * set_logged_in_cookie
     *
     * @param $cookie
     *
     * @return void
     */
    function set_logged_in_cookie($cookie){
        $_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
    }
    
    
    /**
     * prepare_load_action
     *
     * acfe/module/prepare_load_action
     *
     * @param $action
     *
     * @return array
     */
    function prepare_load_action($action){
        
        // login loop
        foreach(array_keys($action['login']) as $k){
            $action["login_{$k}"] = $action['login'][ $k ];
        }
        
        // save loop
        foreach(array_keys($action['save']) as $k){
            $action["save_{$k}"] = $action['save'][ $k ];
        }
        
        // groups
        $keys = array(
            'save' => array(
                'target'      => function($value){return !empty($value) && is_numeric($value);},
                'description' => function($value){return acfe_is_html(nl2br($value));},
            ),
            'load' => array(
                'source'      => function($value){return !empty($value) && is_numeric($value);},
            )
        );
        
        foreach($keys as $parent => $row){
            foreach($row as $key => $callback){
                
                // save: target
                $value = $action[ $parent ][ $key ];
                $action["{$parent}_{$key}_group"]["{$parent}_{$key}"] = $value;
                $action["{$parent}_{$key}_group"]["{$parent}_{$key}_custom"] = '';
                
                if(call_user_func_array($callback, array($value))){
                    $action["{$parent}_{$key}_group"]["{$parent}_{$key}"] = 'custom';
                    $action["{$parent}_{$key}_group"]["{$parent}_{$key}_custom"] = $value;
                }
                
            }
        }
        
        // load loop
        $load_active = false;
        
        foreach(array_keys($action['load']) as $k){
            
            $action["load_{$k}"] = $action['load'][ $k ];
            
            if(!empty($action['load'][ $k ])){
                $load_active = true;
            }
            
        }
        
        $action['load_active'] = $load_active;
        
        // cleanup
        unset($action['action']);
        unset($action['login']);
        unset($action['save']);
        unset($action['load']);
        
        return $action;
        
    }
    
    
    /**
     * prepare_save_action
     *
     * acfe/module/prepare_save_action
     *
     * @param $action
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_action($action){
        
        $save = $this->item;
        
        // general
        $save['type'] = $action['type'];
        $save['name'] = $action['name'];
        $save['validation'] = $action['validation'];
        
        // login loop
        foreach(array_keys($save['login']) as $k){
            
            // taxonomy => save_taxonomy
            if(acf_maybe_get($action, "login_{$k}")){
                $save['login'][ $k ] = $action["login_{$k}"];
            }
            
        }
        
        // save loop
        foreach(array_keys($save['save']) as $k){
            
            // taxonomy => save_taxonomy
            if(acf_maybe_get($action, "save_{$k}")){
                $save['save'][ $k ] = $action["save_{$k}"];
            }
            
        }
        
        // groups
        $keys = array(
            'save' => array('target', 'description'),
            'load' => array('source'),
        );
        
        foreach($keys as $parent => $row){
            foreach($row as $key){
                
                $group = $action["{$parent}_{$key}_group"];
                $save[ $parent ][ $key ] = $group[ $key ];
                
                if($group[ $key ] === 'custom'){
                    $save[ $parent ][ $key ] = $group["{$key}_custom"];
                }
                
            }
        }
        
        // check load switch activated
        if($action['load_active']){
            
            // load loop
            foreach(array_keys($save['load']) as $k){
                
                // taxonomy => load_taxonomy
                if(acf_maybe_get($action, "load_{$k}")){
                    
                    $value = $action["load_{$k}"];
                    $save['load'][ $k ] = $value;
                    
                    // assign to save array when field_key
                    if(isset($save['save'][ $k ]) && !empty($value) && is_string($value) && acf_is_field_key($value)){
                        $save['save'][ $k ] = "{field:$value}";
                    }
                    
                }
                
            }
            
        }
        
        // save: target
        if($action['type'] === 'update_user' && empty($save['save']['target'])){
            $save['save']['target'] = '{user}';
        }
        
        // load: source
        if($action['load_active'] && empty($save['load']['source'])){
            $save['load']['source'] = '{user}';
        }
        
        return $save;
        
    }
    
    
    /**
     * prepare_action_for_export
     *
     * @param $action
     *
     * @return mixed
     */
    function prepare_action_for_export($action){
        
        if($action['type'] === 'log_user'){
            
            unset($action['save']);
            unset($action['load']);
            
        }else{
            
            unset($action['login']);
            
            if($action['type'] === 'insert_user'){
                unset($action['save']['target']);
            }
            
            if(empty($action['load']['source'])){
                unset($action['load']);
            }
            
        }
        
        return $action;
        
    }
    
    
    /**
     * register_layout
     *
     * @param $layout
     *
     * @return array
     */
    function register_layout($layout){
    
        return array(
    
            /**
             * documentation
             */
            array(
                'key' => 'field_doc',
                'label' => '',
                'name' => '',
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
    
            /**
             * action
             */
            array(
                'key' => 'field_tab_action',
                'label' => __('Action', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_type',
                'label' => __('Action', 'acfe'),
                'name' => 'type',
                'type' => 'radio',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    'insert_user' => __('Create user', 'acfe'),
                    'update_user' => __('Update user', 'acfe'),
                    'log_user'    => __('Log user', 'acfe'),
                ),
                'default_value' => 'insert_user',
            ),
            
            array(
                'key' => 'field_validation',
                'label' => __('Validation', 'acfe'),
                'name' => 'validation',
                'type' => 'true_false',
                'instructions' => __('(Optional) Automatically validate fields', 'acfe'),
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-instruction-placement' => 'field'
                ),
                'message' => __('Built-in validation', 'acfe'),
                'default_value' => 0,
                'ui' => false,
                'ui_on_text' => '',
                'ui_off_text' => '',
                'conditional_logic' => array(),
            ),
            
            array(
                'key' => 'field_name',
                'label' => __('Action name', 'acfe'),
                'name' => 'name',
                'type' => 'acfe_slug',
                'instructions' => __('(Optional) Target this action using hooks.', 'acfe'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-instruction-placement' => 'field'
                ),
                'default_value' => '',
                'placeholder' => __('User', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
    
            /**
             * log
             */
            array(
                'key' => 'field_tab_login',
                'label' => __('Login', 'acfe'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'log_user',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-no-preference' => true,
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_login_type',
                'label' => __('Login type', 'acfe'),
                'name' => 'login_type',
                'type' => 'radio',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    'email'          => __('E-mail', 'acfe'),
                    'username'       => __('Username', 'acfe'),
                    'email_username' => __('E-mail or username', 'acfe'),
                ),
                'allow_null' => 0,
                'other_choice' => 0,
                'default_value' => '',
                'layout' => 'vertical',
                'return_format' => 'value',
                'save_other_choice' => 0,
                'conditional_logic' => array(),
            ),
            array(
                'key' => 'field_login_user',
                'label' => __('Login', 'acfe'),
                'name' => 'login_user',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_login_pass',
                'label' => __('Password', 'acfe'),
                'name' => 'login_pass',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_login_remember',
                'label' => __('Remember me', 'acfe'),
                'name' => 'login_remember',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
    
            /**
             * save
             */
            array(
                'key' => 'field_tab_save',
                'label' => __('Save', 'acfe'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '!=',
                            'value' => 'log_user',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-no-preference' => true,
                ),
                'placement' => 'top',
                'endpoint' => 0,
            ),
            
            array(
                'key' => 'field_save_target_group',
                'label' => __('Target', 'acfe'),
                'name' => 'save_target_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'update_user',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_save_target',
                        'label' => '',
                        'name' => 'target',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            '{user}'             => __('Current User', 'acfe'),
                            '{post:post_author}' => __('Current Post Author', 'acfe'),
                            'custom'             => __('User Selector', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_save_target_custom',
                        'label' => '',
                        'name' => 'target_custom',
                        'type' => 'user',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_target',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_user_email',
                'label' => __('Email', 'acfe'),
                'name' => 'save_user_email',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_user_login',
                'label' => __('Username', 'acfe'),
                'name' => 'save_user_login',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_user_pass',
                'label' => __('Password', 'acfe'),
                'name' => 'save_user_pass',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    '{generate_password}' => __('Generate Password', 'acfe')
                ),
                'default_value' => array(
                ),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_first_name',
                'label' => __('First name', 'acfe'),
                'name' => 'save_first_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_last_name',
                'label' => __('Last name', 'acfe'),
                'name' => 'save_last_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_nickname',
                'label' => __('Nickname', 'acfe'),
                'name' => 'save_nickname',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_display_name',
                'label' => __('Display name', 'acfe'),
                'name' => 'save_display_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_user_url',
                'label' => __('Website', 'acfe'),
                'name' => 'save_user_url',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_description_group',
                'label' => __('Description', 'acfe'),
                'name' => 'save_description_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_save_description',
                        'label' => '',
                        'name' => 'description',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            'custom' => __('Content Editor', 'acfe'),
                        ),
                        'default_value' => array(
                        ),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax',
                    ),
                    array(
                        'key' => 'field_save_description_custom',
                        'label' => '',
                        'name' => 'description_custom',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_description',
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
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                    ),
                ),
            ),
            array(
                'key' => 'field_save_role',
                'label' => __('Role', 'acfe'),
                'name' => 'save_role',
                'type' => 'acfe_user_roles',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'user_role' => '',
                'field_type' => 'select',
                'default_value' => '',
                'allow_null' => 1,
                'placeholder' => __('Default', 'acfe'),
                'multiple' => 0,
                'ui' => 1,
                'choices' => array(),
                'ajax' => 1,
                'layout' => '',
                'toggle' => 0,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_log_user',
                'label' => __('Log user', 'acfe'),
                'name' => 'save_log_user',
                'type' => 'true_false',
                'instructions' => __('Log user once created', 'acfe'),
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => '',
                'ui_off_text' => '',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'insert_user',
                        ),
                    ),
                ),
            ),
    
            array(
                'key' => 'field_save_acf_fields',
                'label' => __('Save ACF fields', 'acfe'),
                'name' => 'save_acf_fields',
                'type' => 'checkbox',
                'instructions' => __('Which ACF fields should be saved as metadata', 'acfe'),
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'allow_custom' => 0,
                'default_value' => array(),
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
                'save_custom' => 0,
                'conditional_logic' => array(),
            ),
    
            /**
             * load
             */
            array(
                'key' => 'field_tab_load',
                'label' => __('Load', 'acfe'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-no-preference' => true,
                ),
                'placement' => 'top',
                'endpoint' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '!=',
                            'value' => 'log_user',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_active',
                'label' => __('Load Values', 'acfe'),
                'name' => 'load_active',
                'type' => 'true_false',
                'instructions' => __('Fill inputs with values', 'acfe'),
                'required' => 0,
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
                'conditional_logic' => array(),
            ),
            
            
            array(
                'key' => 'field_load_source_group',
                'label' => __('Source', 'acfe'),
                'name' => 'load_source_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
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
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_load_source',
                        'label' => '',
                        'name' => 'source',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            '{user}'             => __('Current User', 'acfe'),
                            '{post:post_author}' => __('Current Post Author', 'acfe'),
                            'custom'             => __('User Selector', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_load_source_custom',
                        'label' => '',
                        'name' => 'source_custom',
                        'type' => 'user',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_load_source',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    )
                ),
            ),
            
            array(
                'key' => 'field_load_user_email',
                'label' => __('Email', 'acfe'),
                'name' => 'load_user_email',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_user_email'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_user_login',
                'label' => __('Username', 'acfe'),
                'name' => 'load_user_login',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_user_login'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_user_pass',
                'label' => __('Password', 'acfe'),
                'name' => 'load_user_pass',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_user_pass'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_first_name',
                'label' => __('First name', 'acfe'),
                'name' => 'load_first_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_first_name'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_last_name',
                'label' => __('Last name', 'acfe'),
                'name' => 'load_last_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_last_name'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_nickname',
                'label' => __('Nickname', 'acfe'),
                'name' => 'load_nickname',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_nickname'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_display_name',
                'label' => __('Display name', 'acfe'),
                'name' => 'load_display_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_display_name'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_user_url',
                'label' => __('Website', 'acfe'),
                'name' => 'load_user_url',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_user_url'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_description',
                'label' => __('Description', 'acfe'),
                'name' => 'load_description',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_description'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_role',
                'label' => __('Role', 'acfe'),
                'name' => 'load_role',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_role'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
    
            array(
                'key' => 'field_load_acf_fields',
                'label' => __('Load ACF fields', 'acfe'),
                'name' => 'load_acf_fields',
                'type' => 'checkbox',
                'instructions' => __('Select which ACF fields should have their values loaded', 'acfe'),
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
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
                'choices' => array(),
                'allow_custom' => 0,
                'default_value' => array(),
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
                'save_custom' => 0,
            ),

        );
        
    }
    
}

acfe_register_form_action_type('acfe_module_form_action_user');

endif;