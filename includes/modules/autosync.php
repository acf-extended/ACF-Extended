<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_AutoSync')):

class ACFE_AutoSync{
    
    // vars
    private $php_files = array();
    private $json_files = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // PHP Save
        add_action('acf/update_field_group',    array($this, 'update_field_group'));
        add_action('acf/untrash_field_group',   array($this, 'update_field_group'));
        
        // PHP Delete
        add_action('acf/trash_field_group',     array($this, 'delete_field_group'));
        add_action('acf/delete_field_group',    array($this, 'delete_field_group'));
        
        
        // Json Save
        add_action('acf/update_field_group',    array($this, 'pre_update_field_group_json'), 9);
        add_action('acf/untrash_field_group',   array($this, 'pre_update_field_group_json'), 9);
        
        add_action('acf/update_field_group',    array($this, 'post_update_field_group_json'), 11);
        add_action('acf/untrash_field_group',   array($this, 'post_update_field_group_json'), 11);
        
        // Override Json
        add_filter('acf/settings/json',         array($this, 'override_json'), 5);
        add_filter('acf/settings/save_json',    array($this, 'override_json_save'), 5);
        add_filter('acf/settings/load_json',    array($this, 'override_json_load'), 5);
        
        // Setup
        $this->setup_php();
        $this->setup_json();
        
    }
    
    
    /**
     * override_json
     *
     * @param $value
     *
     * @return mixed|null
     */
    function override_json($value){
        return apply_filters('acfe/settings/json', $value);
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
        return (bool) acf_get_setting('json');
    }
    
    
    /**
     * setup_json
     */
    function setup_json(){
        
        if(!$this->is_json_enabled()){
            return;
        }
        
        $this->scan_json_folders();
        
    }
    
    
    /**
     * scan_json_folders
     */
    function scan_json_folders(){
        
        $paths = (array) acf_get_setting('load_json');
        
        foreach($paths as $path){
            
            if(!is_dir($path)){
                continue;
            }
            
            acf_update_setting('acfe/json_found', true);
            break;
            
        }
    
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
            
            if(!is_dir($path)){
                continue;
            }
            
            $files = scandir($path);
            if(!$files){
                continue;
            }
            
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
     * is_php_enabled
     *
     * @return bool
     */
    function is_php_enabled(){
        return (bool) acf_get_setting('acfe/php');
    }
    
    
    /**
     * setup_php
     */
    function setup_php(){
        
        if(!$this->is_php_enabled()){
            return;
        }
    
        global $pagenow;
        $files = $this->scan_php_folders();
        
        // do not include php files in acf admin
        if(($pagenow === 'edit.php' && acf_maybe_get_GET('post_type') === 'acf-field-group' && !acf_maybe_get_GET('page')) || ($pagenow === 'post.php' && get_post_type(acf_maybe_get_GET('post')) === 'acf-field-group')){
            return;
        }
    
        foreach($files as $key => $file){
            require_once($file);
        }
        
    }
    
    
    /**
     * scan_php_folders
     *
     * @return array
     */
    function scan_php_folders(){
    
        $php_files = array();
        $paths = (array) acf_get_setting('acfe/php_load');
    
        foreach($paths as $path){
            
            $path = untrailingslashit($path);
            
            if(!is_dir($path)){
                continue;
            }
    
            acf_update_setting('acfe/php_found', true);
    
            $files = glob($path . '/group_*.php');
    
            if(!$files){
                continue;
            }
    
            foreach($files as $file){
    
                $key = pathinfo($file, PATHINFO_FILENAME);
        
                // append data
                $php_files[$key] = $file;
                
            }
            
        }
    
        // store data and return
        $this->php_files = $php_files;
    
        return $php_files;
        
    }
    
    
    /**
     * get_php_files
     *
     * @return array
     */
    public function get_php_files(){
        return $this->php_files;
    }
    
    
    /**
     * update_field_group
     *
     * @param $field_group
     */
    function update_field_group($field_group){
        
        // bail early
        if(!$this->is_php_enabled()){
            return;
        }
    
        // bail early
        if(!acfe_has_php_sync($field_group)){
            return;
        }
        
        // vars
        $id = $field_group['ID'];
        $key = $field_group['key'];
        $path = untrailingslashit(acf_get_setting('acfe/php_save'));
        
        // backup path
        $new_path = $path;
        
        // filters
        $new_path = apply_filters("acfe/settings/php_save/all",        $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/php_save/ID={$id}",   $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/php_save/key={$key}", $new_path, $field_group);
        
        $diff = $path !== $new_path;
        
        // custom path
        if($diff){
            acf_update_setting('acfe/php_save', $new_path);
        }
        
        // save file
        $this->save_file($key, $field_group);
        
        // restore
        if($diff){
            acf_update_setting('acfe/php_save', $path);
        }
        
    }
    
    
    /**
     * delete_field_group
     *
     * @param $field_group
     */
    function delete_field_group($field_group){
    
        // bail early
        if(!$this->is_php_enabled()){
            return;
        }
    
        // bail early
        if(!acfe_has_php_sync($field_group)){
            return;
        }
        
        // wp appends '__trashed' to end of 'key' (post_name)
        $key = str_replace('__trashed', '', $field_group['key']);
        
        // delete file
        $this->delete_file($key);
        
    }
    
    
    /**
     * save_file
     *
     * @param $key
     * @param $field_group
     *
     * @return bool
     */
    function save_file($key, $field_group){
    
        $path = acf_get_setting('acfe/php_save');
        $file = untrailingslashit($path) . '/' . $key . '.php';
    
        if(!is_writable($path)){
            return false;
        }
    
        // translation
        $l10n = acf_get_setting('l10n');
        $l10n_textdomain = acf_get_setting('l10n_textdomain');
    
        if(!$l10n || !$l10n_textdomain){
            $field_group['fields'] = acf_get_fields($field_group);
        
        }else{
        
            acf_update_setting('l10n_var_export', true);
        
            $field_group = acf_translate_field_group($field_group);
        
            // reset store to allow fields translation
            $store = acf_get_store('fields');
            $store->reset();
        
            $field_group['fields'] = acf_get_fields($field_group);
        
            acf_update_setting('l10n_var_export', false);
    
            // reset store again to avoid storing translated fields
            // this fix an issue with acfml 2.0.2 which update fields as "!!__(!!'My Field!!', !!'my-textdomain!!')!!" in ACF UI
            $store->reset();
        
        }
    
        // prepare for export
        $id = acf_extract_var($field_group, 'ID');
        $field_group = acf_prepare_field_group_for_export($field_group);
    
        // add modified time
        $field_group['modified'] = get_post_modified_time('U', true, $id, true);
    
        // prepare
        $str_replace = array(
            "  "            => "\t",
            "'!!__(!!\'"    => "__('",
            "!!\', !!\'"    => "', '",
            "!!\')!!'"      => "')",
            "array ("       => "array("
        );
    
        $preg_replace = array(
            '/([\t\r\n]+?)array/'   => 'array',
            '/[0-9]+ => array/'     => 'array'
        );
    
        ob_start();
    
        echo "<?php " . "\r\n" . "\r\n";
        echo "if( function_exists('acf_add_local_field_group') ):" . "\r\n" . "\r\n";
    
        // code
        $code = var_export($field_group, true);
    
        // change double spaces to tabs
        $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
    
        // correctly formats "=> array("
        $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
    
        // echo
        echo "acf_add_local_field_group({$code});" . "\r\n" . "\r\n";
    
        echo "endif;";
    
        $output = ob_get_clean();
    
        // save and return true if bytes were written.
        $result = file_put_contents($file, $output);
    
        return is_int($result);
        
    }
    
    
    /**
     * delete_file
     *
     * @param $key
     *
     * @return bool
     */
    function delete_file($key){
    
        $path = acf_get_setting('acfe/php_save');
        $file = untrailingslashit($path) . '/' . $key . '.php';
    
        if(is_readable($file)){
            unlink($file);
            return true;
        }
    
        return false;
        
    }
    
    
    /**
     * pre_update_field_group_json
     *
     * @param $field_group
     */
    function pre_update_field_group_json($field_group){
        
        // json enabled
        if(!$this->is_json_enabled()){
            return false;
        }
    
        // bail early
        // do not save json
        if(!acfe_has_json_sync($field_group)){
            return add_filter('acf/settings/json', array($this, '__return_false'));
        }
        
        // vars
        $id = $field_group['ID'];
        $key = $field_group['key'];
        $path = untrailingslashit(acf_get_setting('save_json'));
        
        // backup
        $new_path = $path;
        
        // filters
        $new_path = apply_filters("acfe/settings/json_save/all",        $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/json_save/ID={$id}",   $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/json_save/key={$key}", $new_path, $field_group);
    
        // set custom saving point
        if($path !== $new_path){
            
            // backup original path
            $GLOBALS['acfe_json_original_path'] = $path;
            
            // set custom path
            acf_update_setting('save_json', $new_path);
        
        }
        
    }
    
    
    /**
     * post_update_field_group_json
     *
     * @param $field_group
     */
    function post_update_field_group_json($field_group){
        
        // json enabled
        if(!$this->is_json_enabled()){
            return false;
        }
        
        // bail early
        // restore original json setting
        if(!acfe_has_json_sync($field_group)){
            return remove_filter('acf/settings/json', array($this, '__return_false'));
        }
        
        if(isset($GLOBALS['acfe_json_original_path']) && !empty($GLOBALS['acfe_json_original_path'])){

            // restore original path
            acf_update_setting('save_json', $GLOBALS['acfe_json_original_path']);

            // remove backup
            unset($GLOBALS['acfe_json_original_path']);
            
        }
        
    }
    
    
    /**
     * __return_false
     *
     * @return false
     */
    function __return_false(){
        return false;
    }
    
}

acf_new_instance('ACFE_AutoSync');

endif;


/**
 * acfe_get_local_php_files
 *
 * @return mixed
 */
function acfe_get_local_php_files(){
    return acf_get_instance('ACFE_AutoSync')->get_php_files();
}


/**
 * acf_get_local_json_files
 *
 * @return mixed
 */
if(!function_exists('acf_get_local_json_files') && acf_version_compare(acf_get_setting('version'),  '<', '5.9')){

function acf_get_local_json_files(){
    return acf_get_instance('ACFE_AutoSync')->get_json_files();
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
 * acfe_has_php_sync
 *
 * @param $item
 *
 * @return bool
 */
function acfe_has_php_sync($item){
    return in_array('php', (array) acf_maybe_get($item, 'acfe_autosync', array()));
}


/**
 * acfe_get_local_php_file
 *
 * @param $field_group
 *
 * @return false|mixed
 */
function acfe_get_local_php_file($field_group){
    
    $key = $field_group;
    
    if(is_array($field_group) && isset($field_group['key'])){
        $key = $field_group['key'];
    }
    
    $php_files = acfe_get_local_php_files();
    
    if(isset($php_files[ $key ])){
        return $php_files[ $key ];
    }
    
    return false;
    
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