<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_performance_upgrades')):

class acfe_module_performance_upgrades{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_9_4'), 10);
        
    }
    
    
    /**
     * upgrade_0_8_9_4
     *
     * acfe/do_upgrade:10
     *
     * @param $db_version
     */
    function upgrade_0_8_9_4($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.9.4')){
            return;
        }
        
        $single_meta = acfe_get_settings('settings.acfe/modules/single_meta');
        
        if(!$single_meta){
            return;
        }
        
        // delete old setting
        acfe_delete_settings('settings.acfe/modules/single_meta');
        
        // log
        acf_log('[ACF Extended] 0.8.9.4 Upgrade: Performance Mode');
    
        // update
        acfe_update_settings('settings.acfe/modules/performance', 'ultra');
        
    }
    
}

acf_new_instance('acfe_module_performance_upgrades');

endif;