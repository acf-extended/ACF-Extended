<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_AutoSync')):

class ACFE_AutoSync{
    
    private $php_files = array();
    private $json_files = array();
    
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
    
    /*
     * Override: Json
     */
    function override_json($value){
        return apply_filters('acfe/settings/json', $value);
    }
    
    /*
     * Override: Json Save
     */
    function override_json_save($path){
        return apply_filters('acfe/settings/json_save', $path);
    }
    
    /*
     * Override: Json Load
     */
    function override_json_load($paths){
        return (array) apply_filters('acfe/settings/json_load', $paths);
    }
    
    /*
     * Json Enabled
     */
    function is_json_enabled(){
        return (bool) acf_get_setting('json');
    }
    
    /*
     * Json: Setup
     */
    function setup_json(){
        
        if(!$this->is_json_enabled())
            return;
        
        $this->scan_json_folders();
        
    }
    
    /*
     * Json: Scan
     */
    function scan_json_folders(){
        
        $paths = (array) acf_get_setting('load_json');
        
        foreach($paths as $path){
            
            if(!is_dir($path))
                continue;
            
            acf_update_setting('acfe/json_found', true);
            break;
            
        }
    
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
        
            $this->scan_json_folders_compatibility();
        
        }
        
    }
    
    /*
     * Json: Scan - Compatibility (ACF < 5.9)
     */
    function scan_json_folders_compatibility(){
        
        $json_files = array();
        
        // Get paths
        $paths = (array) acf_get_setting('load_json');
        
        foreach($paths as $path){
            
            if(!is_dir($path))
                continue;
            
            $files = scandir($path);
            if(!$files)
                continue;
            
            foreach($files as $filename){
                
                // Ignore hidden files.
                if($filename[0] === '.')
                    continue;
                
                // Ignore sub directories.
                $file = untrailingslashit( $path ) . '/' . $filename;
                if(is_dir($file))
                    continue;
                
                // Ignore non JSON files.
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if($ext !== 'json')
                    continue;
                
                // Read JSON data.
                $json = json_decode(file_get_contents($file), true);
                if(!is_array($json) || !isset($json['key']))
                    continue;
                
                // Append data.
                $json_files[$json['key']] = $file;
                
            }
            
        }
        
        // Store data and return.
        $this->json_files = $json_files;
        
        return $json_files;
        
    }
    
    /*
     * Json: Get Files
     */
    function get_json_files(){
        return $this->json_files;
    }
    
    /*
     * PHP Enabled
     */
    function is_php_enabled(){
        return (bool) acf_get_setting('acfe/php');
    }
    
    /*
     * PHP: Setup
     */
    function setup_php(){
        
        if(!$this->is_php_enabled())
            return;
    
        global $pagenow;
        $files = $this->scan_php_folders();
        
        // Do not include PHP files in ACF Admin
        if(($pagenow === 'edit.php' && acf_maybe_get_GET('post_type') === 'acf-field-group' && !acf_maybe_get_GET('page')) || ($pagenow === 'post.php' && get_post_type(acf_maybe_get_GET('post')) === 'acf-field-group'))
            return;
    
        foreach($files as $key => $file){
        
            require_once($file);
        
        }
        
    }
    
    /*
     * PHP: Scan
     */
    function scan_php_folders(){
    
        $php_files = array();
        $paths = (array) acf_get_setting('acfe/php_load');
    
        foreach($paths as $path){
            
            $path = untrailingslashit($path);
            
            if(!is_dir($path))
                continue;
    
            acf_update_setting('acfe/php_found', true);
    
            $files = glob($path . '/group_*.php');
    
            if(!$files)
                continue;
    
            foreach($files as $file){
    
                $key = pathinfo($file, PATHINFO_FILENAME);
        
                // Append data.
                $php_files[$key] = $file;
                
            }
            
        }
    
        // Store data and return.
        $this->php_files = $php_files;
    
        return $php_files;
        
    }
    
    /*
     * PHP: Get Files
     */
    public function get_php_files(){
        return $this->php_files;
    }
    
    /*
     * PHP: Update Field Group
     */
    function update_field_group($field_group){
        
        // Bail early
        if(!$this->is_php_enabled())
            return;
    
        // Bail early
        if(!acfe_has_php_sync($field_group))
            return;
        
        // Vars
        $id = $field_group['ID'];
        $key = $field_group['key'];
        $path = untrailingslashit(acf_get_setting('acfe/php_save'));
        
        // Backup path
        $new_path = $path;
        
        // Filters
        $new_path = apply_filters("acfe/settings/php_save/all",        $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/php_save/ID={$id}",   $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/php_save/key={$key}", $new_path, $field_group);
        
        $diff = $path !== $new_path;
        
        // Custom path
        if($diff)
            acf_update_setting('acfe/php_save', $new_path);
        
        // Save File
        $this->save_file($key, $field_group);
        
        // Restore
        if($diff)
            acf_update_setting('acfe/php_save', $path);
        
    }
    
    /*
     * PHP: Delete Field Group
     */
    function delete_field_group($field_group){
    
        // Bail early
        if(!$this->is_php_enabled())
            return;
    
        // Bail early
        if(!acfe_has_php_sync($field_group))
            return;
        
        // WP appends '__trashed' to end of 'key' (post_name).
        $key = str_replace('__trashed', '', $field_group['key']);
        
        // Delete file.
        $this->delete_file($key);
        
    }
    
    /*
     * PHP: Save File
     */
    function save_file($key, $field_group){
    
        $path = acf_get_setting('acfe/php_save');
        $file = untrailingslashit($path) . '/' . $key . '.php';
    
        if(!is_writable($path))
            return false;
    
        // Translation
        $l10n = acf_get_setting('l10n');
        $l10n_textdomain = acf_get_setting('l10n_textdomain');
    
        if(!$l10n || !$l10n_textdomain){
        
            $field_group['fields'] = acf_get_fields($field_group);
        
        }else{
        
            acf_update_setting('l10n_var_export', true);
        
            $field_group = acf_translate_field_group($field_group);
        
            // Reset store to allow fields translation
            $store = acf_get_store('fields');
            $store->reset();
        
            $field_group['fields'] = acf_get_fields($field_group);
        
            acf_update_setting('l10n_var_export', false);
        
        }
    
        // prepare for export
        $id = acf_extract_var($field_group, 'ID');
        $field_group = acf_prepare_field_group_for_export($field_group);
    
        // add modified time
        $field_group['modified'] = get_post_modified_time('U', true, $id, true);
    
        // Prepare
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
    
        // Save and return true if bytes were written.
        $result = file_put_contents($file, $output);
    
        return is_int($result);
        
    }
    
    /*
     * PHP: Delete File
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
    
    /*
     * Json: Pre update Field Group
     */
    function pre_update_field_group_json($field_group){
        
        if(!$this->is_json_enabled())
            return;
        
        if(!acfe_has_json_sync($field_group)){
            
            // Do not save json
            add_filter('acf/settings/json', array($this, '__return_false'));
            return;
            
        }
        
        // Vars
        $id = $field_group['ID'];
        $key = $field_group['key'];
        $path = untrailingslashit(acf_get_setting('save_json'));
        
        // Backup
        $new_path = $path;
        
        // Filters
        $new_path = apply_filters("acfe/settings/json_save/all",           $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/json_save/ID={$id}",      $new_path, $field_group);
        $new_path = apply_filters("acfe/settings/json_save/key={$key}",    $new_path, $field_group);
    
        // Set custom saving point
        if($path !== $new_path){
            
            // Backup original path
            $GLOBALS['acfe_json_original_path'] = $path;
            
            // Set custom path
            acf_update_setting('save_json', $new_path);
        
        }
        
    }
    
    /*
     * Json: Post Update Field Group
     */
    function post_update_field_group_json($field_group){
        
        if(!$this->is_json_enabled())
            return;
    
        // Json
        if(!acfe_has_json_sync($field_group)){
            
            // Original json setting
            remove_filter('acf/settings/json', array($this, '__return_false'));
            return;
            
        }
        
        if(isset($GLOBALS['acfe_json_original_path']) && !empty($GLOBALS['acfe_json_original_path'])){

            // Restore original path
            acf_update_setting('save_json', $GLOBALS['acfe_json_original_path']);

            // Remove backup
            unset($GLOBALS['acfe_json_original_path']);
            
        }
        
    }
    
    /*
     * Custom Return False
     */
    function __return_false(){
        return false;
    }
    
}

acf_new_instance('ACFE_AutoSync');

endif;

/*
 * Helper: Get PHP Files
 */
function acfe_get_local_php_files(){
    return acf_get_instance('ACFE_AutoSync')->get_php_files();
}

/*
 * Helper: Get Json Files
 */
if(!function_exists('acf_get_local_json_files') && acf_version_compare(acf_get_setting('version'),  '<', '5.9')){

function acf_get_local_json_files(){
    return acf_get_instance('ACFE_AutoSync')->get_json_files();
}

}

/**
 * Helper: Sync available
 */
function acfe_is_sync_available($field_group){
    
    $key = acf_maybe_get($field_group, 'key');
    $id = acf_maybe_get($field_group, 'ID');
    
    // Bail early
    if(empty($key) || empty($id))
        return false;
    
    acf_enable_filter('local');
    
    $field_group = acf_get_local_field_group($key);
    
    acf_disable_filter('local');
    
    if(!$field_group)
        return false;
    
    $private = acf_maybe_get($field_group, 'private', false);
    $local = acf_maybe_get($field_group, 'local', false);
    $modified = acf_maybe_get($field_group, 'modified', 0);
    
    if($private || $local !== 'json')
        return false;
    
    if($modified && $modified > get_post_modified_time('U', true, $id, true))
        return true;
    
    return false;
    
}

function acfe_has_json_sync($field_group){
    return in_array('json', (array) acf_maybe_get($field_group, 'acfe_autosync', array()));
}

function acfe_has_php_sync($field_group){
    return in_array('php', (array) acf_maybe_get($field_group, 'acfe_autosync', array()));
}

function acfe_get_local_php_file($field_group){
    
    $key = $field_group;
    
    if(is_array($field_group) && isset($field_group['key']))
        $key = $field_group['key'];
    
    $php_files = acfe_get_local_php_files();
    
    if(isset($php_files[$key])){
        
        return $php_files[$key];
        
    }
    
    return false;
    
}

function acfe_get_local_json_file($field_group){
    
    $key = $field_group;
    
    if(is_array($field_group) && isset($field_group['key']))
        $key = $field_group['key'];
    
    $json_files = acf_get_local_json_files();
    
    if(isset($json_files[$key])){
        
        return $json_files[$key];
        
    }
    
    return false;
    
}