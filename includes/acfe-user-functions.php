<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_roles
 *
 * Retrieve all available roles.
 * Multisite super admin role will be included as 'super_admin'.
 * 'guest' role will be included for not logged in users.
 *
 * @param array $filter_roles
 *
 * @return array
 */
function acfe_get_roles($filter_roles = array()){
    
    // vars
    global $wp_roles;
    $roles = array();
    
    // multisite: prepend super admin role
    if(is_multisite()){
        $roles['super_admin'] = __('Super Admin');
    }
    
    // loop roles
    foreach(acfe_as_array($wp_roles->roles) as $role => $settings){
        $roles[ $role ] = $settings['name'];
    }
    
    // append guest role (not logged in)
    $roles['guest'] = __('Guest', 'acfe');
    
    // filter roles
    $filter_roles = acfe_as_array($filter_roles);
    if(!empty($filter_roles)){
        $roles = array_intersect_key($roles, array_flip($filter_roles)); // only keep roles in filter
    }
    
    // return
    return $roles;
    
}

/**
 * acfe_get_current_user_roles
 *
 * Retrieve currently logged user roles.
 * Not logged users will return 'guest' role.
 * Multisite super admin will return 'super_admin' role in addition to their regular roles.
 *
 * @return array
 */
function acfe_get_current_user_roles(){
    
    // get current user
    $current_user = wp_get_current_user();
    
    // not logged in
    if(empty($current_user->ID)){
        return array('guest');
    }
    
    // get roles
    $roles = acfe_get_user_roles($current_user);
    
    // filter
    $roles = apply_filters('acfe/load_current_user_roles', $roles);
    
    // return
    return $roles;
    
}


/**
 * acfe_get_user_roles
 *
 * @param $user_or_id
 *
 * @return array
 */
function acfe_get_user_roles($user_or_id){
    
    // default
    $roles = array();
    
    // prepare user
    $user = $user_or_id;
    
    // get user object
    if(is_numeric($user)){
        $user = get_user_by('id', $user);
    }
    
    // validate
    if(!$user instanceof WP_User){
        return $roles;
    }
    
    // get roles
    $roles = acfe_as_array($user->roles);
    
    // multisite: append super admin role if user is super admin
    if(is_multisite() && current_user_can('setup_network')){
        $roles[] = 'super_admin';
    }
    
    // filter
    $roles = apply_filters('acfe/load_user_roles', $roles, $user);
    
    // return
    return $roles;


}


/**
 * acfe_has_current_user_role
 *
 * @param $filter_roles
 *
 * @return bool
 */
function acfe_has_current_user_role($filter_roles){
    
    // get current user roles
    $user_roles = acfe_get_current_user_roles();
    
    // normalize roles as array
    $filter_roles = acfe_as_array($filter_roles);
    
    // check if role is in user roles
    foreach($filter_roles as $filter_role){
        if(in_array($filter_role, $user_roles, true)){
            return true;
        }
    }
    
    // not found
    return false;
}


/**
 * acfe_has_user_role
 *
 * @param $user_or_id
 * @param $filter_roles
 *
 * @return bool
 */
function acfe_has_user_role($user_or_id, $filter_roles){
    
    // get user roles
    $user_roles = acfe_get_user_roles($user_or_id);
    
    // normalize roles as array
    $filter_roles = acfe_as_array($filter_roles);
    
    // check if role is in user roles
    foreach($filter_roles as $filter_role){
        if(in_array($filter_role, $user_roles, true)){
            return true;
        }
    }
    
    // not found
    return false;
    
}