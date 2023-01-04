<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_upgrades')):

class acfe_module_upgrades{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_8'));
        
    }
    
    
    /**
     * upgrade_0_8_8
     *
     * acfe/do_upgrade:10
     *
     * @param $db_version
     */
    function upgrade_0_8_8($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.8')){
            return;
        }
        
        acfe_delete_settings('modules.author');
        acfe_delete_settings('modules.dev');
        acfe_delete_settings('modules.meta');
        acfe_delete_settings('modules.option');
        acfe_delete_settings('modules.ui');
        acfe_delete_settings('upgrades');
        
    }
    
}

acf_new_instance('acfe_module_upgrades');

endif;