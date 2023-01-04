<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_manager')):

class acfe_module_manager{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/init', array($this, 'register_items'), 99);
        add_action('init',      array($this, 'register_items'));
        add_action('init',      array($this, 'register_post_types'));
    
    }
    
    
    /**
     * register_items
     */
    function register_items(){
        
        // get current hook
        // ini or acf/init
        $hook = current_filter();
        
        // query modules by register hook
        $modules = acfe_query_modules(array(
            'register' => $hook
        ));
        
        // bail early
        if(!$modules){
            return;
        }
        
        // loop modules
        foreach($modules as $module){
        
            // check active
            if(!$module->is_active()){
                continue;
            }
            
            // get loaded items
            $items = $module->get_local_items();
            $items = $module->apply_module_filters('acfe/module/register_items', $items);
            
            // loop items
            foreach($items as $item){
        
                // cleanup keys (ID, local, _valid...)
                $item = $module->prepare_item_for_export($item);
        
                // filters
                $item = $module->apply_module_filters('acfe/module/register_item_args', $item);
        
                // bail early
                if($item === false){
                    continue;
                }
        
                // bail early
                if(!$item['active']){
                    continue;
                }
        
                // actions
                $module->do_module_action('acfe/module/register_item', $item);
        
            }
        
        }
        
    }
    
    
    /**
     * register_post_types
     */
    function register_post_types(){
        
        // get all modules
        $modules = acfe_get_modules();
        
        // loop modules
        foreach($modules as $module){
    
            // check active
            if(!$module->is_active()){
                continue;
            }
            
            // capability
            $capability = acf_get_setting('show_admin') ? acf_get_setting('capability') : false;
            
            // arguments
            $args = wp_parse_args($module->args, array(
                'label'                 => '',
                'labels'                => array(),
                'supports'              => array('title'),
                'hierarchical'          => false,
                'public'                => false,
                'show_ui'               => true,
                'show_in_menu'          => false,
                'menu_icon'             => 'dashicons-layout',
                'show_in_admin_bar'     => false,
                'show_in_nav_menus'     => false,
                'can_export'            => false,
                'has_archive'           => false,
                'rewrite'               => false,
                'exclude_from_search'   => true,
                'publicly_queryable'    => false,
                'capabilities'          => array(
                    'publish_posts'         => $capability,
                    'edit_posts'            => $capability,
                    'edit_others_posts'     => $capability,
                    'delete_posts'          => $capability,
                    'delete_others_posts'   => $capability,
                    'read_private_posts'    => $capability,
                    'edit_post'             => $capability,
                    'delete_post'           => $capability,
                    'read_post'             => $capability,
                ),
                'acfe_admin_orderby'    => 'title',
                'acfe_admin_order'      => 'ASC',
                'acfe_admin_ppp'        => 999,
            ));
            
            // register post type
            register_post_type($module->post_type, $args);
            
        }
        
    }
    
}

acf_new_instance('acfe_module_manager');

endif;