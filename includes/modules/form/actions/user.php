<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_user')):

class acfe_form_user{
    
    function __construct(){
        
        add_filter('acfe/form/load/action/user', array($this, 'load'), 1);
        add_action('acfe/form/submit/action/user', array($this, 'submit'), 1, 3);
        
    }
    
    function load($args){
        
        $form_name = acf_maybe_get($args, 'acfe_form_name');
        $form_id = acf_maybe_get($args, 'acfe_form_id');
        
        // Behavior
        $user_behavior = get_sub_field('acfe_form_user_behavior');
        
        // Update User
        if($user_behavior !== 'update_user')
            return $args;
        
        if(!get_sub_field('acfe_form_user_update_load'))
            return $args;
        
        $_user_id_data_group = get_sub_field('acfe_form_user_update_user_id_group');
        $_user_id_data = $_user_id_data_group['acfe_form_user_update_user_id'];
        $_user_id_data_custom = $_user_id_data_group['acfe_form_user_update_user_id_custom'];
        
        $_user_email = get_sub_field('acfe_form_user_update_email');
        $_user_username = get_sub_field('acfe_form_user_update_username');
        $_user_password = get_sub_field('acfe_form_user_update_password');
        
        $_user_first_name_group = get_sub_field('acfe_form_user_update_first_name_group');
        $_user_first_name = $_user_first_name_group['acfe_form_user_update_first_name'];
        
        $_user_last_name_group = get_sub_field('acfe_form_user_update_last_name_group');
        $_user_last_name = $_user_last_name_group['acfe_form_user_update_last_name'];
        
        $_user_nickname_group = get_sub_field('acfe_form_user_update_nickname_group');
        $_user_nickname = $_user_nickname_group['acfe_form_user_update_nickname'];
        
        $_user_display_name_group = get_sub_field('acfe_form_user_update_display_name_group');
        $_user_display_name = $_user_display_name_group['acfe_form_user_update_display_name'];
        
        $_user_role = get_sub_field('acfe_form_user_update_role');
        
        // var
        $_user_id = $args['post_id'];
        
        // Current post
        if($_user_id_data === 'current_user'){
            
            $_user_id = get_current_user_id();
        
        // Custom Post ID
        }elseif($_user_id_data === 'custom_user_id'){
            
            $_user_id = $_user_id_data_custom;
        
        }elseif(acf_is_field_key($_user_id_data)){
            
            $_user_id = get_field($_user_id_data);
        
        }
        
        $_user_id = apply_filters('acfe/form/load/user_id',                      $_user_id, $args);
        $_user_id = apply_filters('acfe/form/load/user_id/name=' . $form_name,   $_user_id, $args);
        $_user_id = apply_filters('acfe/form/load/user_id/id=' . $form_id,       $_user_id, $args);
        
        $user_data = get_userdata($_user_id);
        
        if(!empty($user_data)){
            
            // ID
            $args['post_id'] = 'user_' . $_user_id;
            
            // Email
            if(acf_is_field_key($_user_email)){
                
                $args['map'][$_user_email]['value'] = $user_data->user_email;
                
            }
            
            // Username
            if(acf_is_field_key($_user_username)){
                
                $args['map'][$_user_username]['value'] = $user_data->user_login;
                $args['map'][$_user_username]['maxlength'] = 60;
                
            }
            
            // Password
            if(acf_is_field_key($_user_password)){
                
                //$args['map'][$_user_password]['value'] = $user_data->user_pass;
                
            }
            
            // First name
            if(acf_is_field_key($_user_first_name)){
                
                $args['map'][$_user_first_name]['value'] = $user_data->first_name;
                
            }
            
            // Last name
            if(acf_is_field_key($_user_last_name)){
                
                $args['map'][$_user_last_name]['value'] = $user_data->last_name;
                
            }
            
            // Nickname
            if(acf_is_field_key($_user_nickname)){
                
                $args['map'][$_user_nickname]['value'] = $user_data->nickname;
                
            }
            
            // Display name
            if(acf_is_field_key($_user_display_name)){
                
                $args['map'][$_user_display_name]['value'] = $user_data->display_name;
                
            }
            
            // Role
            if(acf_is_field_key($_user_role)){
                
                $args['map'][$_user_role]['value'] = $user_data->role;
                
            }
        
        }
        
        return $args;
        
    }
    
    function submit($form, $post_id, $acf){
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        // Behavior
        $user_behavior = get_sub_field('acfe_form_user_behavior');
        
        // Create User
        if($user_behavior === 'create_user'){
            
            $_user_email = get_sub_field('acfe_form_user_create_email');
            $_user_username = get_sub_field('acfe_form_user_create_username');
            $_user_password = get_sub_field('acfe_form_user_create_password');
            
            $_user_first_name_group = get_sub_field('acfe_form_user_create_first_name_group');
            $_user_first_name = $_user_first_name_group['acfe_form_user_create_first_name'];
            $_user_first_name_custom = $_user_first_name_group['acfe_form_user_create_first_name_custom'];
            
            $_user_last_name_group = get_sub_field('acfe_form_user_create_last_name_group');
            $_user_last_name = $_user_last_name_group['acfe_form_user_create_last_name'];
            $_user_last_name_custom = $_user_last_name_group['acfe_form_user_create_last_name_custom'];
            
            $_user_nickname_group = get_sub_field('acfe_form_user_create_nickname_group');
            $_user_nickname = $_user_nickname_group['acfe_form_user_create_nickname'];
            $_user_nickname_custom = $_user_nickname_group['acfe_form_user_create_nickname_custom'];
            
            $_user_display_name_group = get_sub_field('acfe_form_user_create_display_name_group');
            $_user_display_name = $_user_display_name_group['acfe_form_user_create_display_name'];
            $_user_display_name_custom = $_user_display_name_group['acfe_form_user_create_display_name_custom'];
            
            $_user_role = get_sub_field('acfe_form_user_create_role');
            
            $args = array();
            
            // Email
            $args['user_email'] = acfe_form_map_field_value($_user_email, $acf);
            
            // Username
            $args['user_login'] = acfe_form_map_field_value($_user_username, $acf);
            
            // Password
            $args['user_pass'] = '';
            
            if(acf_is_field_key($_user_password)){
                
                $args['user_pass'] = acfe_form_map_field_value($_user_password, $acf);
                
            }elseif($_user_password === 'generate_password'){
                    
                    $args['user_pass'] = wp_generate_password(8, false);
                    
            }
            
            // First name
            if(acf_is_field_key($_user_first_name)){
                
                $args['first_name'] = acfe_form_map_field_value($_user_first_name, $acf);
                
            }elseif($_user_first_name === 'custom'){
                
                $args['first_name'] = acfe_form_map_field_value($_user_first_name_custom, $acf);
                
            }
            
            // Last name
            if(acf_is_field_key($_user_last_name)){
                
                $args['last_name'] = acfe_form_map_field_value($_user_last_name, $acf);
                
            }elseif($_user_last_name === 'custom'){
                
                $args['last_name'] = acfe_form_map_field_value($_user_last_name_custom, $acf);
                
            }
            
            // Nickname
            if(acf_is_field_key($_user_nickname)){
                
                $args['nickname'] = acfe_form_map_field_value($_user_nickname, $acf);
                
            }elseif($_user_nickname === 'custom'){
                
                $args['nickname'] = acfe_form_map_field_value($_user_nickname_custom, $acf);
                
            }
            
            // Display name
            if(acf_is_field_key($_user_display_name)){
                
                $args['display_name'] = acfe_form_map_field_value($_user_display_name, $acf);
                
            }elseif($_user_display_name === 'custom'){
                
                $args['display_name'] = acfe_form_map_field_value($_user_display_name_custom, $acf);
                
            }
            
            // Role
            if(!empty($_user_role)){
                
                $args['role'] = acfe_form_map_field_value($_user_role, $acf);
                
            }
            
            $args = apply_filters('acfe/form/submit/insert_user_args',                      $args, $form);
            $args = apply_filters('acfe/form/submit/insert_user_args/name=' . $form_name,   $args, $form);
            $args = apply_filters('acfe/form/submit/insert_user_args/id=' . $form_id,       $args, $form);
            
            if($args === false)
                return;
            
            // User
            $_user_id = wp_insert_user($args);
            
            do_action('acfe/form/submit/insert_user',                       $form, $_user_id, $args);
            do_action('acfe/form/submit/insert_user/name=' . $form_name,    $form, $_user_id, $args);
            do_action('acfe/form/submit/insert_user/id=' . $form_id,        $form, $_user_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_user_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post('user_' . $_user_id, $data);
            
            }
            
        }
        
        // Update User
        elseif($user_behavior === 'update_user'){
            
            $_user_id_data_group = get_sub_field('acfe_form_user_update_user_id_group');
            $_user_id_data = $_user_id_data_group['acfe_form_user_update_user_id'];
            $_user_id_data_custom = $_user_id_data_group['acfe_form_user_update_user_id_custom'];
            
            $_user_email = get_sub_field('acfe_form_user_update_email');
            $_user_username = get_sub_field('acfe_form_user_update_username');
            $_user_password = get_sub_field('acfe_form_user_update_password');
            
            $_user_first_name_group = get_sub_field('acfe_form_user_update_first_name_group');
            $_user_first_name = $_user_first_name_group['acfe_form_user_update_first_name'];
            $_user_first_name_custom = $_user_first_name_group['acfe_form_user_update_first_name_custom'];
            
            $_user_last_name_group = get_sub_field('acfe_form_user_update_last_name_group');
            $_user_last_name = $_user_last_name_group['acfe_form_user_update_last_name'];
            $_user_last_name_custom = $_user_last_name_group['acfe_form_user_update_last_name_custom'];
            
            $_user_nickname_group = get_sub_field('acfe_form_user_update_nickname_group');
            $_user_nickname = $_user_nickname_group['acfe_form_user_update_nickname'];
            $_user_nickname_custom = $_user_nickname_group['acfe_form_user_update_nickname_custom'];
            
            $_user_display_name_group = get_sub_field('acfe_form_user_update_display_name_group');
            $_user_display_name = $_user_display_name_group['acfe_form_user_update_display_name'];
            $_user_display_name_custom = $_user_display_name_group['acfe_form_user_update_display_name_custom'];
            
            $_user_role = get_sub_field('acfe_form_user_update_role');
            
            // var
            $_user_id = false;
            
            // Current user
            if($_user_id_data === 'current_user'){
                
                $_user_id = get_current_user_id();
            
            // Custom User ID
            }elseif($_user_id_data === 'custom_user_id'){
                
                $_user_id = $_user_id_data_custom;
            
            }
            
            $args = array();
            
            // ID
            $args['ID'] = $_user_id;
            
            // Email
            if(!empty($_user_email)){
                
                $args['user_email'] = acfe_form_map_field_value($_user_email, $acf);
                
            }
            
            // Username
            if(!empty($_user_username)){
                
                $args['user_login'] = acfe_form_map_field_value($_user_username, $acf);
                
            }
            
            // Password
            if(!empty($_user_password)){
                
                if(acf_is_field_key($_user_password)){
                    
                    $args['user_pass'] = acfe_form_map_field_value($_user_password, $acf);
                    
                }elseif($_user_password === 'generate_password'){
                    
                    $args['user_pass'] = wp_generate_password(8, false);
                    
                }
                
            }
            
            // First name
            if(!empty($_user_first_name)){
                
                if(acf_is_field_key($_user_first_name)){
                    
                    $args['first_name'] = acfe_form_map_field_value($_user_first_name, $acf);
                    
                }elseif($_user_first_name === 'custom'){
                    
                    $args['first_name'] = acfe_form_map_field_value($_user_first_name_custom, $acf);
                    
                }
                
            }
            
            // Last name
            if(!empty($_user_last_name)){
                
                if(acf_is_field_key($_user_last_name)){
                    
                    $args['last_name'] = acfe_form_map_field_value($_user_last_name, $acf);
                    
                }elseif($_user_last_name === 'custom'){
                    
                    $args['last_name'] = acfe_form_map_field_value($_user_last_name_custom, $acf);
                    
                }
                
            }
            
            // Nickname
            if(!empty($_user_nickname)){
                
                if(acf_is_field_key($_user_nickname)){
                    
                    $args['nickname'] = acfe_form_map_field_value($_user_nickname, $acf);
                    
                }elseif($_user_nickname === 'custom'){
                    
                    $args['nickname'] = acfe_form_map_field_value($_user_nickname_custom, $acf);
                    
                }
                
            }
            
            // Display name
            if(!empty($_user_display_name)){
                
                if(acf_is_field_key($_user_display_name)){
                    
                    $args['display_name'] = acfe_form_map_field_value($_user_display_name, $acf);
                    
                }elseif($_user_display_name === 'custom'){
                    
                    $args['display_name'] = acfe_form_map_field_value($_user_display_name_custom, $acf);
                    
                }
                
            }
            
            // Role
            if(!empty($_user_role)){
                
                $args['role'] = acfe_form_map_field_value($_user_role, $acf);
                
            }
            
            $args = apply_filters('acfe/form/submit/update_user_args',                      $args, $form, $_user_id);
            $args = apply_filters('acfe/form/submit/update_user_args/name=' . $form_name,   $args, $form, $_user_id);
            $args = apply_filters('acfe/form/submit/update_user_args/id=' . $form_id,       $args, $form, $_user_id);
            
            if($args === false)
                return;
            
            // User
            $_user_id = wp_update_user($args);
            
            do_action('acfe/form/submit/update_user',                       $form, $_user_id, $args);
            do_action('acfe/form/submit/update_user/name=' . $form_name,    $form, $_user_id, $args);
            do_action('acfe/form/submit/update_user/id=' . $form_id,        $form, $_user_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_user_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post('user_' . $_user_id, $data);
            
            }
            
        }
        
    }
    
}

new acfe_form_user();

endif;