<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_db')):

class acfe_module_db{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/module/updated_item',  array($this, 'updated_item'), 10, 2);
        add_action('acfe/module/trashed_item',  array($this, 'deleted_item'), 10, 2);
        add_action('acfe/module/deleted_item',  array($this, 'deleted_item'), 10, 2);
        add_action('acfe/module/include_items', array($this, 'include_items'));
    
    }
    
    
    /**
     * updated_item
     *
     * acfe/module/updated_item
     *
     * @param $item
     * @param $module
     */
    function updated_item($item, $module){
    
        if(!$this->has_settings($module)){
            return;
        }
        
        // cleanup keys
        $export = $module->prepare_item_for_export($item);
        
        // on update
        if($item['ID']){
            
            // get raw item from db
            $raw_item = $module->get_item($item['ID']);

            // delete old settings if name changed
            if($raw_item['name'] && $raw_item['name'] !== $item['name'] && !acf_is_filter_enabled('acfe/module/update_unique_name')){
                $this->delete_settings($module, $raw_item['name']);
            }
        
        }
    
        // settings
        $settings = $this->get_settings($module);
        $settings[ $item['name'] ] = $export;
    
        // update setting
        ksort($settings);
        $this->update_settings($module, $settings);
        
    }
    
    
    /**
     * deleted_item
     *
     * acfe/module/deleted_item
     *
     * @param $item
     * @param $module
     */
    function deleted_item($item, $module){
    
        if(!$this->has_settings($module)){
            return;
        }
    
        // WP appends '__trashed' to end of 'name' (post_name).
        $name = str_replace('__trashed', '', $item['name']);
    
        // delete settings
        $this->delete_settings($module, $name);
        
    }
    
    
    /**
     * include_items
     *
     * acfe/module/include_items
     *
     * @param $module
     */
    function include_items($module){
    
        if(!$this->has_settings($module)){
            return;
        }
        
        // get db settings
        $settings = acf_get_array($this->get_settings($module));
        
        // loop
        foreach($settings as $key => $item){
            
            // set local
            $item['local'] = 'db';
            $item['local_file'] = "{$module->settings}.{$key}";
            
            // add local item
            $module->add_local_item($item);
        
        }
    
    }
    
    
    /**
     * has_settings
     *
     * @param $module
     *
     * @return bool
     */
    function has_settings($module){
        return !empty($module->settings);
    }
    
    
    /**
     * get_settings
     *
     * @param $module
     * @param $selector
     * @param $default
     *
     * @return mixed
     */
    function get_settings($module, $selector = null, $default = null){
        
        if($selector === null){
            $selector = $module->settings;
        }else{
            $selector = "{$module->settings}.{$selector}";
        }
        
        return acfe_get_settings($selector, $default);
        
    }
    
    
    /**
     * update_settings
     *
     * @param $module
     * @param $selector
     * @param $value
     */
    function update_settings($module, $selector = null, $value = null){
        
        if($value === null){
            $value = $selector;
            $selector = $module->settings;
        }else{
            $selector = "{$module->settings}.{$selector}";
        }
        
        acfe_update_settings($selector, $value);
        
    }
    
    
    /**
     * delete_settings
     *
     * @param $module
     * @param $selector
     *
     * @return mixed
     */
    function delete_settings($module, $selector = null){
        
        if($selector === null){
            $selector = $module->settings;
        }else{
            $selector = "{$module->settings}.{$selector}";
        }
        
        return acfe_delete_settings($selector);
        
    }
    
}

acf_new_instance('acfe_module_db');

endif;