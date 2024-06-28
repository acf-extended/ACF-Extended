<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_AutoSync_Json')):

class ACFE_AutoSync_Json{
    
    // vars
    private $json_files = array();
    public $old_path = false;
    
    /**
     * construct
     */
    function __construct(){
        
        // hooks
        add_action('acf/update_field_group',    array($this, 'pre_update_field_group'), 9);
        add_action('acf/untrash_field_group',   array($this, 'pre_update_field_group'), 9);
        add_action('acf/update_field_group',    array($this, 'post_update_field_group'), 11);
        add_action('acf/untrash_field_group',   array($this, 'post_update_field_group'), 11);
        
        add_action('acf/trash_field_group',     array($this, 'pre_delete_field_group'), 9);
        add_action('acf/delete_field_group',    array($this, 'pre_delete_field_group'), 9);
        add_action('acf/trash_field_group',     array($this, 'post_delete_field_group'), 11);
        add_action('acf/delete_field_group',    array($this, 'post_delete_field_group'), 11);
        
        // override settings
        add_filter('acf/settings/json',         array($this, 'override_json'), 5);
        add_filter('acf/settings/save_json',    array($this, 'override_json_save'), 5);
        add_filter('acf/settings/load_json',    array($this, 'override_json_load'), 5);
        
        // setup
        $this->setup_json();
        
    }
    
    
    /**
     * override_json
     *
     * @param $value
     *
     * @return bool
     */
    function override_json($value){
        
        // not very elegant, but it allows
        // acf_update_setting('json', false) to work mid-page request
        if($value === false){
            return false;
        }
        
        return (bool) acf_get_setting('acfe/json', $value);
    }
    
    
    /**
     * override_json_save
     *
     * @param $path
     *
     * @return mixed|null
     */
    function override_json_save($path){
        return apply_filters('acfe/settings/json_save', $path);
    }
    
    
    /**
     * override_json_load
     *
     * @param $paths
     *
     * @return array
     */
    function override_json_load($paths){
        return (array) apply_filters('acfe/settings/json_load', $paths);
    }
    
    
    /**
     * is_json_enabled
     *
     * @return bool
     */
    function is_json_enabled(){
        return (bool) acf_get_setting('acfe/json');
    }
    
    
    /**
     * setup_json
     */
    function setup_json(){
        
        if($this->is_json_enabled()){
            $this->scan_json_folders();
        }
        
    }
    
    
    /**
     * scan_json_folders
     */
    function scan_json_folders(){
        
        // normalize paths
        $paths = (array) acf_get_setting('load_json');
        
        // loop paths
        foreach($paths as $path){
            
            // update setting
            if(is_dir($path)){
                acf_update_setting('acfe/json_found', true);
                break;
            }
            
        }
        
        // acf < 5.9 compatibility
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
            $this->scan_json_folders_compatibility();
        }
        
    }
    
    
    /**
     * scan_json_folders_compatibility
     *
     * ACF < 5.9 compatibility
     *
     * @return array
     */
    function scan_json_folders_compatibility(){
        
        $json_files = array();
        
        // get paths
        $paths = (array) acf_get_setting('load_json');
        
        foreach($paths as $path){
            
            // validate folder
            if(!is_dir($path)){
                continue;
            }
            
            // validate files
            $files = scandir($path);
            if(!$files){
                continue;
            }
            
            // loop files
            foreach($files as $filename){
                
                // ignore hidden files
                if($filename[0] === '.'){
                    continue;
                }
                
                // ignore sub directories
                $file = untrailingslashit( $path ) . '/' . $filename;
                if(is_dir($file)){
                    continue;
                }
                
                // ignore non json files
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if($ext !== 'json'){
                    continue;
                }
                
                // read json data
                $json = json_decode(file_get_contents($file), true);
                if(!is_array($json) || !isset($json['key'])){
                    continue;
                }
                
                // append data
                $json_files[ $json['key'] ] = $file;
                
            }
            
        }
        
        // store data and return
        $this->json_files = $json_files;
        
        // return
        return $json_files;
        
    }
    
    
    /**
     * get_json_files
     *
     * @return array
     */
    function get_json_files(){
        return $this->json_files;
    }
    
    
    /**
     * pre_update_field_group
     *
     * @param $field_group
     */
    function pre_update_field_group($field_group){
        
        $this->old_path = false;
        
        // json enabled
        if(!$this->is_json_enabled()){
            return;
        }
        
        // save file
        if(acfe_has_json_sync($field_group)){
            
            $old_path = untrailingslashit(acf_get_setting('save_json'));
            $new_path = $this->get_json_save_path($field_group);
            
            $this->old_path = $old_path;
            
            // set setting for native acf/update_field_group:10
            acf_update_setting('save_json', $new_path);
            
            
        // delete file
        }else{
            
            // delete file
            $this->delete_file($field_group);
            
            // do not save json file
            add_filter('acf/settings/json', array($this, '__return_false'));
            
        }
        
    }
    
    
    /**
     * post_update_field_group
     *
     * @param $field_group
     */
    function post_update_field_group($field_group){
        
        // json enabled
        if(!$this->is_json_enabled()){
            return;
        }
        
        // save file
        if(acfe_has_json_sync($field_group)){
            
            // set setting back to native
            acf_update_setting('save_json', $this->old_path);
            
            // reset old path
            $this->old_path = false;
            
            
        // delete file
        }else{
            
            // reset json setting
            remove_filter('acf/settings/json', array($this, '__return_false'));
            
        }
        
    }
    
    
    /**
     * pre_delete_field_group
     *
     * @param $field_group
     *
     * @return void
     */
    function pre_delete_field_group($field_group){
        
        $this->old_path = false;
        
        // json enabled
        if(!$this->is_json_enabled()){
            return;
        }
        
        // wp appends '__trashed' to end of 'key' (post_name)
        $field_group['key'] = str_replace('__trashed', '', $field_group['key']);
        
        // filters
        $delete = true;
        $delete = apply_filters("acfe/settings/should_delete_json",                           $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_json/ID={$field_group['ID']}",   $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_json/key={$field_group['key']}", $delete, $field_group);
        
        // do not delete json file
        if(!$delete){
            return add_filter('acf/settings/json', array($this, '__return_false'));
        }
        
        $old_path = untrailingslashit(acf_get_setting('save_json'));
        $new_path = $this->get_json_save_path($field_group);
        
        $this->old_path = $old_path;
        
        // set setting for native acf/update_field_group:10
        acf_update_setting('save_json', $new_path);
        
        
    }
    
    
    /**
     * post_delete_field_group
     *
     * @param $field_group
     *
     * @return void
     */
    function post_delete_field_group($field_group){
        
        // json enabled
        if(!$this->is_json_enabled()){
            return;
        }
        
        // reset json setting
        remove_filter('acf/settings/json', array($this, '__return_false'));
        
        if(!empty($this->old_path)){
            
            // set setting back to native
            acf_update_setting('save_json', $this->old_path);
            
            // reset old path
            $this->old_path = false;
            
        }
        
    }
    
    
    /**
     * delete_file
     *
     * @param $field_group
     *
     * @return void
     */
    function delete_file($field_group){
        
        // validate
        if(!$this->is_json_enabled()){
            return;
        }
        
        // filters
        $delete = true;
        $delete = apply_filters("acfe/settings/should_delete_json",                           $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_json/ID={$field_group['ID']}",   $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_json/key={$field_group['key']}", $delete, $field_group);
        
        if(!$delete){
            return;
        }
        
        // vars
        $path = $this->get_json_save_path($field_group);
        $filename = $this->get_filename($field_group);
        
        if(!$filename){
            return;
        }
        
        $file = untrailingslashit($path) . '/' . $filename;
        
        // delete
        if(is_readable($file)){
            unlink($file);
        }
        
    }
    
    
    /**
     * get_filename
     *
     * @param $field_group
     *
     * @return false|string
     */
    function get_filename($field_group){
        
        $version = acf_get_setting('version');
        
        // acf 6.2 compatibility
        if(acf_version_compare($version, '>=', '6.2') && acf_version_compare($version, '<', '6.3')){
            
            $instance = acf_get_instance('ACF_Local_JSON');
            
            if(method_exists($instance, 'get_files')){
                
                $load_path = '';
                $files = $instance->get_files();
                
                if(is_array($files) && isset($files[ $field_group['key'] ])){
                    $load_path = $files[ $field_group['key'] ];
                }
                
                $filename = apply_filters('acf/json/save_file_name', $field_group['key'] . '.json', $field_group, $load_path);
                
                if(!is_string($filename)){
                    return false;
                }
                
                $filename = sanitize_file_name($filename);
                
                // sanitize_file_name() can potentially remove all characters.
                if(empty($filename)){
                    return false;
                }
                
                return $filename;
            
            }
            
        // acf 6.3 compatibility
        }elseif(acf_version_compare($version, '>=', '6.3')){
            
            $instance = acf_get_instance('ACF_Local_JSON');
            
            if(method_exists($instance, 'get_filename')){
                return $instance->get_filename($field_group['key'], $field_group);
            }
            
        }
        
        // default
        return $field_group['key'] . '.json';
        
    }
    
    
    /**
     * __return_false
     *
     * @return false
     */
    function __return_false(){
        return false;
    }
    
    
    /**
     * get_json_save_path
     *
     * @param $field_group
     *
     * @return mixed|null
     */
    function get_json_save_path($field_group){
        
        // default
        $path = untrailingslashit(acf_get_setting('acfe/json_save'));
        
        // acf 6.2 compatibility
        // added save_json variations
        $path = acf_get_setting("save_json/type=acf-field-group",         $path);
        $path = acf_get_setting("save_json/name={$field_group['title']}", $path);
        $path = acf_get_setting("save_json/key={$field_group['key']}",    $path);
        
        // filters
        $path = apply_filters("acfe/settings/json_save/all",                       $path, $field_group);
        $path = apply_filters("acfe/settings/json_save/ID={$field_group['ID']}",   $path, $field_group);
        $path = apply_filters("acfe/settings/json_save/key={$field_group['key']}", $path, $field_group);
        
        return $path;
        
    }
    
}

acf_new_instance('ACFE_AutoSync_Json');

endif;


/**
 * acf_get_local_json_files
 *
 * @return mixed
 */
if(!function_exists('acf_get_local_json_files') && acf_version_compare(acf_get_setting('version'),  '<', '5.9')){

function acf_get_local_json_files(){
    return acf_get_instance('ACFE_AutoSync_Json')->get_json_files();
}

}


/**
 * acfe_is_sync_available
 *
 * @param $field_group
 *
 * @return bool
 */
function acfe_is_sync_available($field_group){
    
    $key = acf_maybe_get($field_group, 'key');
    $id = acf_maybe_get($field_group, 'ID');
    
    // bail early
    if(empty($key) || empty($id)){
        return false;
    }
    
    acf_enable_filter('local');
    
    $field_group = acf_get_local_field_group($key);
    
    acf_disable_filter('local');
    
    if(!$field_group){
        return false;
    }
    
    $private = acf_maybe_get($field_group, 'private', false);
    $local = acf_maybe_get($field_group, 'local', false);
    $modified = acf_maybe_get($field_group, 'modified', 0);
    
    if($private || $local !== 'json'){
        return false;
    }
    
    if($modified && $modified > get_post_modified_time('U', true, $id, true)){
        return true;
    }
    
    return false;
    
}


/**
 * acfe_has_json_sync
 *
 * @param $item
 *
 * @return bool
 */
function acfe_has_json_sync($item){
    return in_array('json', (array) acf_maybe_get($item, 'acfe_autosync', array()));
}


/**
 * acfe_get_local_json_file
 *
 * @param $field_group
 *
 * @return false
 */
function acfe_get_local_json_file($field_group){
    
    $key = $field_group;
    
    if(is_array($field_group) && isset($field_group['key'])){
        $key = $field_group['key'];
    }
    
    $json_files = acf_get_local_json_files();
    
    if(isset($json_files[ $key ])){
        return $json_files[ $key ];
    }
    
    return false;
    
}


/**
 * acfe_get_json_save_path
 *
 * @param $field_group
 *
 * @return mixed
 */
function acfe_get_json_save_path($field_group){
    return acf_get_instance('ACFE_AutoSync_Json')->get_json_save_path($field_group);
}