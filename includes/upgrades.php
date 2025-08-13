<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_upgrades')):

class acfe_upgrades{
    
    /**
     * construct
     */
    function __construct(){
    
        // get db version
        $db_version = acfe_get_settings('version');
    
        // bail early if superior
        if($db_version && acf_version_compare($db_version, '>=', ACFE_VERSION)){
            return;
        }
    
        // do upgrade
        if($db_version){
            
            add_action('acf/init', function() use($db_version){
                do_action('acfe/do_upgrade', $db_version);
            }, 999);
    
        // do reset
        }else{
    
            // hook on init to load all WP components
            // post types, post statuses 'acf-disabled' etc...
            add_action('init', function(){
                do_action('acfe/do_reset');
            });
        
        }
    
        // get db settings
        $settings = acf_get_array(acfe_get_settings());
    
        // model
        $model = acfe_parse_args_r($settings, array(
            'version' => '',
            'modules' => array(
                'block_types'   => array(),
                'options_pages' => array(),
                'post_types'    => array(),
                'taxonomies'    => array(),
            )
        ));
    
        // assign version
        $model['version'] = ACFE_VERSION;
    
        // update db
        acfe_update_settings($model);
        
    }
    
}

acf_new_instance('acfe_upgrades');

endif;