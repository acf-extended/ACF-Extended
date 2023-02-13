<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_post_type_upgrades')):

class acfe_module_post_type_upgrades{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_9'), 30);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_8'), 20);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_5'), 10);
        
    }
    
    /**
     * upgrade_0_8_9
     *
     * acfe/do_upgrade:30
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
    
            // get posts
            $posts = get_posts(array(
                'post_type'      => 'acfe-dpt',
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
            $module = acfe_get_module('post_type');
    
            // loop
            foreach($todo as $post_id){
        
                $name = get_post_field('post_name', $post_id);
                $settings = acfe_get_settings("modules.post_types.{$name}", array());
        
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
            acf_log('[ACF Extended] 0.8.9 Upgrade: Post Types');
            
        });
    
    }
    
    /**
     * upgrade_0_8_8
     *
     * acfe/do_upgrade:20
     *
     * @param $db_version
     */
    function upgrade_0_8_8($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.8')){
            return;
        }
    
        $old = acfe_get_settings('modules.dynamic_post_type.data', array());
        $new = acfe_get_settings('modules.post_types', array());
    
        acfe_delete_settings('modules.dynamic_post_type');
    
        // Check
        if(empty($old)){
            return;
        }
    
        // Log
        acf_log('[ACF Extended] 0.8.8 Upgrade: Post Types');
    
        // Update
        acfe_update_settings('modules.post_types', array_merge($old, $new));
        
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
    
        $old = get_option('acfe_dynamic_post_types', array());
        $new = acfe_get_settings('modules.post_types', array());
    
        delete_option('acfe_dynamic_post_types');
    
        if(empty($old)){
            return;
        }
    
        acf_log('[ACF Extended] 0.8.5 Upgrade: Post Types');
    
        // Update
        acfe_update_settings('modules.post_types', array_merge($old, $new));
        
    }
    
}

acf_new_instance('acfe_module_post_type_upgrades');

endif;