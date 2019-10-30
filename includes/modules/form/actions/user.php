<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_user')):

class acfe_form_user{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/user',                                           array($this, 'load'), 1, 3);
        add_action('acfe/form/prepare/user',                                        array($this, 'prepare'), 1, 3);
        add_action('acfe/form/submit/user',                                         array($this, 'submit'), 10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_user_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_user_load_meta',               array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_user_map_email',               array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_username',            array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_password',            array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_first_name',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_last_name',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_nickname',            array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_display_name',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_website',             array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_description',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_user_map_role',                array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/render_field/name=acfe_form_user_advanced_load',            array($this, 'advanced_load'));
        add_filter('acf/render_field/name=acfe_form_user_advanced_save_args',       array($this, 'advanced_save_args'));
        add_filter('acf/render_field/name=acfe_form_user_advanced_save',            array($this, 'advanced_save'));
        
    }
    
    function load($form, $post_id, $action){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $user_action = get_sub_field('acfe_form_user_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_user_load_values');
        $load_source = get_sub_field('acfe_form_user_load_source');
        $load_meta = get_sub_field('acfe_form_user_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
        
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
        
        // Custom User ID
        $_user_id = $load_source;

        // Current User
        if($load_source === 'current_user'){
            
            $_user_id = get_current_user_id();
            
        }
        
        // Current Post Author
        elseif($load_source === 'current_post_author'){
            
            if($post_info['type'] === 'post')
                $_user_id = get_post_field('post_author', $post_id);
            
        }
        
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
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_email]['value'] = $user_data->user_email;
                
            }
            
        }
        
        // Username
        if(acf_is_field_key($_username)){
            
            $key = array_search($_username, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_username]['value'] = $user_data->user_login;
                $form['map'][$_username]['maxlength'] = 60;
                
            }
            
        }
        
        // Password
        if(acf_is_field_key($_password)){
            
            $key = array_search($_password, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                //$form['map'][$_password]['value'] = $user_data->user_pass;
                
            }
            
        }
        
        // First name
        if(acf_is_field_key($_first_name)){
            
            $key = array_search($_first_name, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_first_name]['value'] = $user_data->first_name;
                
            }
            
        }
        
        // Last name
        if(acf_is_field_key($_last_name)){
            
            $key = array_search($_last_name, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_last_name]['value'] = $user_data->last_name;
                
            }
            
        }
        
        // Nickname
        if(acf_is_field_key($_nickname)){
            
            $key = array_search($_nickname, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_nickname]['value'] = $user_data->nickname;
                
            }
            
        }
        
        // Display name
        if(acf_is_field_key($_display_name)){
            
            $key = array_search($_display_name, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_display_name]['value'] = $user_data->display_name;
                
            }
            
        }
        
        // Website
        if(acf_is_field_key($_website)){
            
            $key = array_search($_website, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_website]['value'] = $user_data->website;
                
            }
            
        }
        
        // Description
        if(acf_is_field_key($_description)){
            
            $key = array_search($_description, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_description]['value'] = $user_data->description;
                
            }
            
        }
        
        // Role
        if(acf_is_field_key($_role)){
            
            $key = array_search($_role, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_role]['value'] = implode(', ', $user_data->roles);
                
            }
            
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
    
    function prepare($form, $post_id, $action){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $user_action = get_sub_field('acfe_form_user_action');
        
        // Mapping
        $map = array(
            'user_email'    => get_sub_field('acfe_form_user_map_email'),
            'user_login'    => get_sub_field('acfe_form_user_map_username'),
            'user_pass'     => get_sub_field('acfe_form_user_map_password'),
            'first_name'    => get_sub_field('acfe_form_user_map_first_name'),
            'last_name'     => get_sub_field('acfe_form_user_map_last_name'),
            'nickname'      => get_sub_field('acfe_form_user_map_nickname'),
            'display_name'  => get_sub_field('acfe_form_user_map_display_name'),
            'user_url'      => get_sub_field('acfe_form_user_map_website'),
            'description'   => get_sub_field('acfe_form_user_map_description'),
            'role'          => get_sub_field('acfe_form_user_map_role'),
        );
        
        // Fields
        $_target = get_sub_field('acfe_form_user_save_target');
        
        $_user_email_group = get_sub_field('acfe_form_user_save_email_group');
        $_user_email = $_user_email_group['acfe_form_user_save_email'];
        $_user_email_custom = $_user_email_group['acfe_form_user_save_email_custom'];
        
        $_user_login_group = get_sub_field('acfe_form_user_save_username_group');
        $_user_login = $_user_login_group['acfe_form_user_save_username'];
        $_user_login_custom = $_user_login_group['acfe_form_user_save_username_custom'];
        
        $_user_pass_group = get_sub_field('acfe_form_user_save_password_group');
        $_user_pass = $_user_pass_group['acfe_form_user_save_password'];
        $_user_pass_custom = $_user_pass_group['acfe_form_user_save_password_custom'];
        
        $_first_name_group = get_sub_field('acfe_form_user_save_first_name_group');
        $_first_name = $_first_name_group['acfe_form_user_save_first_name'];
        $_first_name_custom = $_first_name_group['acfe_form_user_save_first_name_custom'];
        
        $_last_name_group = get_sub_field('acfe_form_user_save_last_name_group');
        $_last_name = $_last_name_group['acfe_form_user_save_last_name'];
        $_last_name_custom = $_last_name_group['acfe_form_user_save_last_name_custom'];
        
        $_nickname_group = get_sub_field('acfe_form_user_save_nickname_group');
        $_nickname = $_nickname_group['acfe_form_user_save_nickname'];
        $_nickname_custom = $_nickname_group['acfe_form_user_save_nickname_custom'];
        
        $_display_name_group = get_sub_field('acfe_form_user_save_display_name_group');
        $_display_name = $_display_name_group['acfe_form_user_save_display_name'];
        $_display_name_custom = $_display_name_group['acfe_form_user_save_display_name_custom'];
        
        $_user_url_group = get_sub_field('acfe_form_user_save_website_group');
        $_user_url = $_user_url_group['acfe_form_user_save_website'];
        $_user_url_custom = $_user_url_group['acfe_form_user_save_website_custom'];
        
        $_description_group = get_sub_field('acfe_form_user_save_description_group');
        $_description = $_description_group['acfe_form_user_save_description'];
        $_description_custom = $_description_group['acfe_form_user_save_description_custom'];
        
        $_role = get_sub_field('acfe_form_user_save_role');
        
        // args
        $args = array();
        
        // Insert user
        $_user_id = 0;
        
        // Update user
        if($user_action === 'update_user'){
            
            // Custom User ID
            $_user_id = $_target;
            
            // Current User
            if($_target === 'current_user'){
                
                $_user_id = get_current_user_id();
            
            }
            
            // Current Post Author
            elseif($_target === 'current_post_author'){
                
                if($post_info['type'] === 'post')
                    $_user_id = get_post_field('post_author', $post_info['id']);
                
                // Invalid User ID
                if(!$_user_id)
                    return;
                
            }
            
            // ID
            $args['ID'] = $_user_id;
            
        }
        
        // Email
        if(!empty($map['user_email'])){
            
            $args['user_email'] = acfe_form_map_field_value($map['user_email'], $_POST['acf'], $_user_id);
            
        }elseif($_user_email === 'custom'){
            
            $args['user_email'] = acfe_form_map_field_value($_user_email_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Username
        if(!empty($map['user_login'])){
            
            $args['user_login'] = acfe_form_map_field_value($map['user_login'], $_POST['acf'], $_user_id);
            
        }elseif($_user_login === 'custom'){
            
            $args['user_login'] = acfe_form_map_field_value($_user_login_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Password
        if(!empty($map['user_pass'])){
            
            $args['user_pass'] = acfe_form_map_field_value($map['user_pass'], $_POST['acf'], $_user_id);
            
        }elseif($_user_pass === 'generate_password'){
            
            $args['user_pass'] = wp_generate_password(8, false);
            
        }elseif($_user_pass === 'custom'){
            
            $args['user_pass'] = acfe_form_map_field_value($_user_pass_custom, $_POST['acf'], $_user_id);
            
        }
        
        // First name
        if(!empty($map['first_name'])){
            
            $args['first_name'] = acfe_form_map_field_value($map['first_name'], $_POST['acf'], $_user_id);
            
        }elseif($_first_name === 'custom'){
            
            $args['first_name'] = acfe_form_map_field_value($_first_name_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Last name
        if(!empty($map['last_name'])){
            
            $args['last_name'] = acfe_form_map_field_value($map['last_name'], $_POST['acf'], $_user_id);
            
        }elseif($_last_name === 'custom'){
            
            $args['last_name'] = acfe_form_map_field_value($_last_name_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Nickname
        if(!empty($map['nickname'])){
            
            $args['nickname'] = acfe_form_map_field_value($map['nickname'], $_POST['acf'], $_user_id);
            
        }elseif($_nickname === 'custom'){
            
            $args['nickname'] = acfe_form_map_field_value($_nickname_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Display name
        if(!empty($map['display_name'])){
            
            $args['display_name'] = acfe_form_map_field_value($map['display_name'], $_POST['acf'], $_user_id);
            
        }elseif($_display_name === 'custom'){
            
            $args['display_name'] = acfe_form_map_field_value($_display_name_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Website
        if(!empty($map['user_url'])){
            
            $args['user_url'] = acfe_form_map_field_value($map['user_url'], $_POST['acf'], $_user_id);
            
        }elseif($_user_url === 'custom'){
            
            $args['user_url'] = acfe_form_map_field_value($_user_url_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Description
        if(!empty($map['description'])){
            
            $args['description'] = acfe_form_map_field_value($map['description'], $_POST['acf'], $_user_id);
            
        }elseif($_description === 'custom'){
            
            $args['description'] = acfe_form_map_field_value($_description_custom, $_POST['acf'], $_user_id);
            
        }
        
        // Role
        if(!empty($map['role'])){
            
            $args['role'] = acfe_form_map_field_value($map['role'], $_POST['acf'], $_user_id);
            
        }elseif(!empty($_role)){
            
            $args['role'] = $_role;
            
        }
        
        $args = apply_filters('acfe/form/submit/user_args',                     $args, $user_action, $form, $action);
        $args = apply_filters('acfe/form/submit/user_args/form=' . $form_name,  $args, $user_action, $form, $action);
        
        if(!empty($action))
            $args = apply_filters('acfe/form/submit/user_args/action=' . $action, $args, $user_action, $form, $action);
        
        // Insert User
        if($user_action === 'insert_user'){
            
            if(!isset($args['user_email']) || !isset($args['user_login']) || !isset($args['user_pass'])){
                
                $args = false;
                
            }
            
        }
        
        if($args === false)
            return;
        
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
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_user_save_meta');
        
        if(!empty($save_meta)){
            
            $data = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($data)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post('user_' . $_user_id, $data);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
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
add_filter('acfe/form/load/user_id/form=<?php echo $form_name; ?>', 'my_form_user_values_source', 10, 3);
function my_form_user_values_source($user_id, $form, $action){
    
    /**
     * @int     $user_id    User ID used as source
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
    
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
add_filter('acfe/form/submit/user_args/form=<?php echo $form_name; ?>', 'my_form_user_args', 10, 4);
function my_form_user_args($args, $type, $form, $action){
    
    /**
     * @array   $args   The generated user arguments
     * @string  $type   Action type: 'insert_user' or 'update_user'
     * @array   $form   The form settings
     * @string  $action The action alias name
     */
    
    
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
 * At this point the user is already saved into the database
 */
add_action('acfe/form/submit/user/name=<?php echo $form_name; ?>', 'my_form_user_save', 10, 5);
function my_form_user_save($user_id, $type, $args, $form, $action){
    
    /**
     * @int     $user_id    The targeted user ID
     * @string  $type       Action type: 'insert_user' or 'update_user'
     * @array   $args       The generated user arguments
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
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