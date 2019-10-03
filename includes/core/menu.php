<?php

if(!defined('ABSPATH'))
    exit;

add_action('admin_menu', 'acfe_admin_settings_submenu_swap', 999);
function acfe_admin_settings_submenu_swap(){
    
    global $submenu;
    
    if(!isset($submenu['edit.php?post_type=acf-field-group']) || empty($submenu['edit.php?post_type=acf-field-group']))
        return;
    
    $temp_category = false;
    $temp_category_key = false;
    
    $temp_settings = false;
    $temp_settings_key = false;
    
    $temp_tools = false;
    $temp_tools_key = false;
    
    $temp_infos = false;
    $temp_infos_key = false;
    
    $temp_block_type = false;
    $temp_block_type_key = false;
    
    $temp_options = false;
    $temp_options_key = false;
    
    foreach($submenu['edit.php?post_type=acf-field-group'] as $ikey => $item){
        
        // ACFE: Field Group Category
        if($item[2] === 'edit-tags.php?taxonomy=acf-field-group-category'){
            $temp_category = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_category_key = $ikey;
        }
        
        // ACFE: Settings
        elseif($item[2] === 'acfe-settings'){
            $temp_settings = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_settings_key = $ikey;
        }
        
        // Tools
        elseif($item[2] === 'acf-tools'){
            $temp_tools = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_tools_key = $ikey;
        }
        
        // Infos
        elseif($item[2] === 'acf-settings-info'){
            $temp_infos = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_infos_key = $ikey;
        }
        
        // Block Types
        elseif($item[2] === 'edit.php?post_type=acfe-dbt'){
            $temp_block_type = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_block_type_key = $ikey;
        }
        
        // Options Pages
        elseif($item[2] === 'edit.php?post_type=acfe-dop'){
            $temp_options = $submenu['edit.php?post_type=acf-field-group'][$ikey];
            $temp_options_key = $ikey;
        }
        
    }
    
    // Swapping
    if($temp_tools_key !== false)
        $submenu['edit.php?post_type=acf-field-group'][$temp_tools_key] = $temp_category;
    
    if($temp_category_key !== false)
        $submenu['edit.php?post_type=acf-field-group'][$temp_category_key] = $temp_settings;
    
    if($temp_settings_key !== false)
        $submenu['edit.php?post_type=acf-field-group'][$temp_settings_key] = $temp_options;
    
    if($temp_options_key !== false)
        $submenu['edit.php?post_type=acf-field-group'][$temp_options_key] = $temp_infos;
    
    if($temp_infos_key !== false)
        $submenu['edit.php?post_type=acf-field-group'][$temp_infos_key] = $temp_tools;
    
    // ACF Pro 5.8 Block Types
    if($temp_block_type_key !== false){
        
        if($temp_infos_key !== false)
            $submenu['edit.php?post_type=acf-field-group'][$temp_infos_key] = $temp_block_type;
        
        if($temp_block_type_key !== false)
            $submenu['edit.php?post_type=acf-field-group'][$temp_block_type_key] = $temp_tools;
        
    }
    
}