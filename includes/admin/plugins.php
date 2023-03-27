<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_admin_plugins')):

class acfe_admin_plugins{
    
    /**
     * construct
     */
    function __construct(){
    
        add_filter('install_plugins_tabs',               array($this, 'install_plugins_tabs'));
        add_filter('install_plugins_table_api_args_acf', array($this, 'install_plugins_table_api_args'));
        add_action('install_plugins_acf',                array($this, 'install_plugins'));
        
    }
    
    
    /**
     * install_plugins_tabs
     *
     * @param $tabs
     *
     * @return mixed
     */
    function install_plugins_tabs($tabs){
        
        $tabs['acf'] = __('Advanced Custom Fields');
        
        return $tabs;
        
    }
    
    
    /**
     * install_plugins_table_api_args
     *
     * @param $args
     *
     * @return mixed
     */
    function install_plugins_table_api_args($args){
        
        global $paged;
        
        $args['search'] = 'acf';
        $args['page'] = $paged;
        $args['per_page'] = 12;
        
        return $args;
        
    }
    
    
    /**
     * install_plugins
     */
    function install_plugins(){
        display_plugins_table();
    }
    
}

new acfe_admin_plugins();

endif;