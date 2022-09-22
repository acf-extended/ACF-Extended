<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_admin_menu')):

class acfe_admin_menu{
    
    /**
     * Construct
     */
    function __construct(){
    
        add_action('current_screen', array($this, 'current_screen'));
        add_action('admin_menu',     array($this, 'admin_menu'), 999);
        
    }
    
    /**
     * current_screen
     *
     * @param $screen
     */
    function current_screen($screen){
        
        // check version
        if(acf_version_compare(acf_get_setting('version'),  '>=', '5.9')){
    
            // allowed screens
            $allowed = array(
                'edit-acf-field-group-category',
                'edit-acfe-dbt',
                'acfe-dbt',
                'edit-acfe-dop',
                'acfe-dop',
                'edit-acfe-template',
                'acfe-template',
                'edit-acfe-form',
                'acfe-form'
            );
            
            // chgeck allowed
            if(acf_is_screen($allowed)){
                add_action('in_admin_header', array($this, 'in_admin_header'));
            }
            
        }
        
    }
    
    
    /**
     * in_admin_header
     */
    function in_admin_header(){
        acf_get_view('html-admin-navigation');
    }
    
    
    /**
     * admin_menu
     *
     * Swap menu items to the correct order
     */
    function admin_menu(){
        
        // global
        global $submenu;
        
        // bail early
        if(!acf_maybe_get($submenu, 'edit.php?post_type=acf-field-group')){
            return;
        }
        
        // vars
        $new_menu = array();
        $all_menu = $submenu['edit.php?post_type=acf-field-group'];
        
        // order
        $order = array(
            'edit.php?post_type=acf-field-group',
            'post-new.php?post_type=acf-field-group',
            'edit-tags.php?taxonomy=acf-field-group-category',
            'edit.php?post_type=acfe-dbt',
            'edit.php?post_type=acfe-form',
            'edit.php?post_type=acfe-dop',
            'acfe-settings',
            'acf-tools',
            'acf-settings-updates',
        );
        
        // loop
        foreach($submenu['edit.php?post_type=acf-field-group'] as $k => $item){
            
            //name
            $name = $item[2];
            
            // search
            $position = array_search($name, $order);
            
            // found position
            if($position !== false){
                $this->assign_submenu($new_menu, $position, $item, $all_menu, $k);
            }
            
        }
        
        // sort new menu
        ksort($new_menu);
        
        // assign new menu
        $submenu['edit.php?post_type=acf-field-group'] = $new_menu;
        
        // add menu items that are left
        if(!empty($all_menu)){
            $submenu['edit.php?post_type=acf-field-group'] = array_merge($new_menu, $all_menu);
        }
        
    }
    
    
    /**
     * assign_submenu
     *
     * @param $new_menu
     * @param $new_menu_key
     * @param $item
     * @param $all_menu
     * @param $all_menu_key
     */
    function assign_submenu(&$new_menu, $new_menu_key, $item, &$all_menu, $all_menu_key){
    
        $new_menu[ $new_menu_key ] = $item;
        unset($all_menu[ $all_menu_key ]);
        
    }
    
}

new acfe_admin_menu();

endif;