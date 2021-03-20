<?php

if(!defined('ABSPATH'))
    exit;

add_action('current_screen', 'acfe_screen_header');
function acfe_screen_header($screen){
    
    if(acf_version_compare(acf_get_setting('version'),  '<', '5.9'))
        return;
    
    if(!acf_is_screen(array('edit-acf-field-group-category', 'edit-acfe-dbt', 'acfe-dbt', 'edit-acfe-dop', 'acfe-dop', 'edit-acfe-template', 'acfe-template', 'edit-acfe-form', 'acfe-form')))
        return;
    
    add_action('in_admin_header', function(){
        acf_get_view('html-admin-navigation');
    });
    
}

add_action('admin_menu', 'acfe_admin_settings_submenu_swap', 999);
function acfe_admin_settings_submenu_swap(){
    
    global $submenu;
    
    if(!acf_maybe_get($submenu, 'edit.php?post_type=acf-field-group'))
        return;
    
    $_submenu = $submenu['edit.php?post_type=acf-field-group'];
    $array = array();
    
    foreach($submenu['edit.php?post_type=acf-field-group'] as $k => $item){
        
        // Field Groups
        if($item[2] === 'edit.php?post_type=acf-field-group'){
            
            $array[0] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Add New
        elseif($item[2] === 'post-new.php?post_type=acf-field-group'){
            
            $array[1] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Categories
        elseif($item[2] === 'edit-tags.php?taxonomy=acf-field-group-category'){
            
            $array[2] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Block Types
        elseif($item[2] === 'edit.php?post_type=acfe-dbt'){
            
            $array[3] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Forms
        elseif($item[2] === 'edit.php?post_type=acfe-form'){
            
            $array[4] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Options
        elseif($item[2] === 'edit.php?post_type=acfe-dop'){
            
            $array[5] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Settings
        elseif($item[2] === 'acfe-settings'){
            
            $array[6] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Tools
        elseif($item[2] === 'acf-tools'){
            
            $array[7] = $item;
            unset($_submenu[$k]);
            
        }
        
        // Updates
        elseif($item[2] === 'acf-settings-updates'){
            
            $array[8] = $item;
            unset($_submenu[$k]);
            
        }
        
    }
    
    // Sort
    ksort($array);
    
    // Default submenu
    $submenu['edit.php?post_type=acf-field-group'] = $array;
    
    // Add items left
    if(!empty($_submenu)){
    
        $submenu['edit.php?post_type=acf-field-group'] = array_merge($array, $_submenu);
        
    }
    
}