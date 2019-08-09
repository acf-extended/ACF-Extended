<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Admin: Plugins Tab
 */
add_filter('install_plugins_tabs', 'acfe_admin_plugins_tabs');
function acfe_admin_plugins_tabs($tabs){
    
    $tabs['acf'] = __('Advanced Custom Fields');
    
    return $tabs;
    
}

/**
 * Admin: Plugins Args
 */
add_filter('install_plugins_table_api_args_acf', 'acfe_admin_plugins_args');
function acfe_admin_plugins_args($args){
    
    global $paged;
    
    $args['search'] = 'acf';
    $args['page'] = $paged;
    $args['per_page'] = 12;
    
    return $args;
    
}

/**
 * Admin: Plugins HTML
 */
add_action('install_plugins_acf', 'acfe_admin_plugins_html');
function acfe_admin_plugins_html(){
    
    display_plugins_table();
    
}