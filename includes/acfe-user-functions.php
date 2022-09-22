<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_roles
 *
 * Retrieve all available roles (working with WPMU)
 *
 * @param array $filtered_user_roles
 *
 * @return array
 */
function acfe_get_roles($filtered_user_roles = array()){
    
    $list = array();
    
    global $wp_roles;
    
    if(is_multisite()){
        $list['super_admin'] = __('Super Admin');
    }
    
    foreach($wp_roles->roles as $role => $settings){
        $list[ $role ] = $settings['name'];
    }
    
    $user_roles = $list;
    
    if(!empty($filtered_user_roles)){
        
        $user_roles = array();
        
        foreach($list as $role => $role_label){
            if(in_array($role, $filtered_user_roles)){
                $user_roles[$role] = $role_label;
            }
        }
        
    }
    
    return $user_roles;
    
}

/**
 * acfe_get_current_user_roles
 *
 * Retrieve currently logged user roles
 *
 * @return false|string[]
 */
function acfe_get_current_user_roles(){
    
    global $current_user;
    
    if(!is_object($current_user) || !isset($current_user->roles)){
        return false;
    }
    
    $roles = $current_user->roles;
    
    if(is_multisite() && current_user_can('setup_network')){
        $roles[] = 'super_admin';
    }
    
    return $roles;
    
}