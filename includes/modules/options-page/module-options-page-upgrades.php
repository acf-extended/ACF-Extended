<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_options_page_upgrades')):

class acfe_module_options_page_upgrades{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_9'), 40);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_8'), 30);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_6'), 20);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_5'), 10);
        
    }
    
    
    /**
     * upgrade_0_8_9
     *
     * acfe/do_upgrade:40
     *
     * @param $db_version
     */
    function upgrade_0_8_9($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.9')){
            return;
        }
        
        // hook on init to load all WP components
        // post types, post statuses 'acf-disabled' etc...
        add_action('init', function(){
    
            // get options pages
            $posts = get_posts(array(
                'post_type'      => 'acfe-dop',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'post_status'    => 'any',
            ));
    
            $todo = array();
    
            foreach($posts as $post_id){
        
                if(acfe_is_module_v2_item($post_id)){
                    $todo[] = $post_id;
                }
        
            }
    
            if(!$todo){
                return;
            }
    
            // get module
            $module = acfe_get_module('options_page');
    
            // loop
            foreach($todo as $post_id){
        
                $name = get_post_field('post_name', $post_id);
                $settings = acfe_get_settings("modules.options_pages.{$name}", array());
        
                // db settings found
                if($settings){
            
                    // generate item
                    $item = wp_parse_args($settings, array(
                        'ID'   => $post_id,
                        'name' => $name,
                    ));
            
                    // import item (update db)
                    $module->import_item($item);
            
                }
        
            }
    
            // log
            acf_log('[ACF Extended] 0.8.9 Upgrade: Options Pages');
            
        });
    
    }
    
    
    /**
     * upgrade_0_8_8
     *
     * acfe/do_upgrade:30
     *
     * @param $db_version
     */
    function upgrade_0_8_8($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.8')){
            return;
        }
    
        $old = acfe_get_settings('modules.dynamic_option.data', array());
        $new = acfe_get_settings('modules.options_pages', array());
    
        acfe_delete_settings('modules.dynamic_option');
    
        // Check
        if(empty($old)){
            return;
        }
    
        // Log
        acf_log('[ACF Extended] 0.8.8 Upgrade: Options Pages');
    
        // Update
        acfe_update_settings('modules.options_pages', array_merge($old, $new));
        
    }
    
    
    /**
     * upgrade_0_8_6
     *
     * acfe/do_upgrade:20
     *
     * @param $db_version
     */
    function upgrade_0_8_6($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.6')){
            return;
        }
        
        // get options pages
        $posts = get_posts(array(
            'post_type'         => 'acfe-dop',
            'posts_per_page'    => -1,
            'fields'            => 'ids'
        ));
    
        if(!$posts){
            return;
        }
        
        $updated = false;
    
        foreach($posts as $post_id){
        
            $menu_slug = get_field('menu_slug', $post_id);
            $acfe_dop_name = get_field('acfe_dop_name', $post_id);
            $post_name = get_post_field('post_name', $post_id);
        
            // validate name
            if(!$acfe_dop_name){
                continue;
            }
        
            // Update empty 'menu_slug' fields in options pages
            if(empty($menu_slug)){
            
                // Page Title
                $page_title = get_post_field('post_title', $post_id);
            
                // Menu Title
                $menu_title = get_field('menu_title', $post_id);
            
                if(empty($menu_title)){
                    $menu_title = $page_title;
                }
            
                // Menu Slug
                $menu_slug = sanitize_title($menu_title);
            
                // Update field
                update_field('menu_slug', $menu_slug, $post_id);
            
                $updated = true;
            
            }
        
            // Upgrade old name to menu_slug
            if($acfe_dop_name === $post_name){
            
                // Get ACFE option
                $option = acfe_get_settings('modules.options_pages', array());
            
                // Check ACFE option
                if(isset($option[ $acfe_dop_name ])){
                
                    $register_args = $option[ $acfe_dop_name ];
                
                    // Delete old option page slug
                    unset($option[ $acfe_dop_name ]);
                
                    // Re-assign to menu_slug
                    $option[ $menu_slug ] = $register_args;
                
                    // Sort keys ASC
                    ksort($option);
                
                    // Update ACFE option
                    acfe_update_settings('modules.options_pages', $option);
                
                    // Update post: force menu slug as name
                    wp_update_post(array(
                        'ID'        => $post_id,
                        'post_name' => $menu_slug,
                    ));
                
                    $updated = true;
                
                }
            
            }
        
        }
    
        if($updated){
            acf_log('[ACF Extended] 0.8.6 Upgrade: Options Pages');
        }
        
    }
    
    
    /**
     * upgrade_0_8_5
     *
     * acfe/do_upgrade:10
     *
     * @param $db_version
     */
    function upgrade_0_8_5($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.5')){
            return;
        }
    
        $old = get_option('acfe_dynamic_options_pages', array());
        $new = acfe_get_settings('modules.options_pages', array());
    
        delete_option('acfe_dynamic_options_pages');
    
        if(empty($old)){
            return;
        }
    
        acf_log('[ACF Extended] 0.8.5 Upgrade: Options Pages');
    
        // Update
        acfe_update_settings('modules.options_pages', array_merge($old, $new));
        
    }
    
}

acf_new_instance('acfe_module_options_page_upgrades');

endif;