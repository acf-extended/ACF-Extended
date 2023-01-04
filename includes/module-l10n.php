<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_l10n')):

class acfe_module_l10n{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/module/updated_item',       array($this, 'updated_item'), 10, 2);
        add_filter('acfe/module/register_item_args', array($this, 'register_item_args'), 10, 2);
    
    }
    
    
    /**
     * updated_item
     *
     * @param $item
     * @param $module
     */
    function updated_item($item, $module){
        
        foreach($module->l10n as $k){
            acfe_register_translate($item[ $k ], ucfirst($k), "ACF Extended: {$module->get_label('name')}");
        }
        
    }
    
    
    /**
     * register_item_args
     *
     * @param $item
     * @param $module
     *
     * @return mixed
     */
    function register_item_args($item, $module){
    
        foreach($module->l10n as $k){
            $item[ $k ] = acfe_translate($item[ $k ], ucfirst($k), "ACF Extended: {$module->get_label('name')}");
        }
        
        return $item;
        
    }
    
}

acf_new_instance('acfe_module_l10n');

endif;