<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_AutoSync_Php')):

class ACFE_AutoSync_Php{
    
    // vars
    private $php_files = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // hooks
        add_action('acf/update_field_group',    array($this, 'update_field_group'));
        add_action('acf/untrash_field_group',   array($this, 'update_field_group'));
        add_action('acf/trash_field_group',     array($this, 'delete_field_group'));
        add_action('acf/delete_field_group',    array($this, 'delete_field_group'));
        
        // setup
        $this->setup_php();
        
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
        
        // vars
        global $pagenow;
        $files = $this->scan_php_folders();
        
        // conditions
        $acf_field_groups = $pagenow === 'edit.php' && acf_maybe_get_GET('post_type') === 'acf-field-group' && !acf_maybe_get_GET('page');
        $acf_field_group = $pagenow === 'post.php' && get_post_type(acf_maybe_get_GET('post')) === 'acf-field-group';
        
        // do not include php files in acf ui
        if($acf_field_groups || $acf_field_group){
            return;
        }
        
        // loop files and require
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
            
            // normalize folder
            $path = untrailingslashit($path);
            if(!is_dir($path)){
                continue;
            }
            
            // update setting
            acf_update_setting('acfe/php_found', true);
            
            // normalize files
            $files = glob($path . '/group_*.php');
            if(!$files){
                continue;
            }
            
            // loop files
            foreach($files as $file){
                
                // extract key
                $key = pathinfo($file, PATHINFO_FILENAME);
        
                // append data
                $php_files[ $key ] = $file;
                
            }
            
        }
    
        // store data and return
        $this->php_files = $php_files;
        
        // return
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
        
        // save file
        if(acfe_has_php_sync($field_group)){
            $this->save_file($field_group);
        
        // delete file
        }else{
            $this->delete_file($field_group);
        }
        
    }
    
    
    /**
     * delete_field_group
     *
     * @param $field_group
     */
    function delete_field_group($field_group){
        
        // wp appends '__trashed' to end of 'key' (post_name)
        $field_group['key'] = str_replace('__trashed', '', $field_group['key']);
        
        $this->delete_file($field_group);
        
    }
    
    
    /**
     * save_file
     *
     * @param $field_group
     *
     * @return bool
     */
    function save_file($field_group){
        
        // bail early
        if(!$this->is_php_enabled()){
            return false;
        }
        
        // vars
        $path = $this->get_php_save_path($field_group);
        $file = untrailingslashit($path) . '/' . $field_group['key'] . '.php';
        
        // validate path
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
        
        // return
        return is_int($result);
        
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
        if(!$this->is_php_enabled()){
            return;
        }
        
        // filters
        $delete = true;
        $delete = apply_filters("acfe/settings/should_delete_php",                           $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_php/ID={$field_group['ID']}",   $delete, $field_group);
        $delete = apply_filters("acfe/settings/should_delete_php/key={$field_group['key']}", $delete, $field_group);
        
        // do not delete
        if(!$delete){
            return;
        }
        
        // vars
        $path = $this->get_php_save_path($field_group);
        $file = untrailingslashit($path) . '/' . $field_group['key'] . '.php';
        
        // delete
        if(is_readable($file)){
            unlink($file);
        }
        
    }
    
    
    /**
     * get_php_save_path
     *
     * @param $field_group
     *
     * @return mixed|null
     */
    function get_php_save_path($field_group){
        
        // default
        $path = untrailingslashit(acf_get_setting('acfe/php_save'));
        
        // filters
        $path = apply_filters("acfe/settings/php_save/all",                       $path, $field_group);
        $path = apply_filters("acfe/settings/php_save/ID={$field_group['ID']}",   $path, $field_group);
        $path = apply_filters("acfe/settings/php_save/key={$field_group['key']}", $path, $field_group);
        
        return $path;
        
    }
    
}

acf_new_instance('ACFE_AutoSync_Php');

endif;


/**
 * acfe_get_local_php_files
 *
 * @return mixed
 */
function acfe_get_local_php_files(){
    return acf_get_instance('ACFE_AutoSync_Php')->get_php_files();
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
 * acfe_get_php_save_path
 *
 * @param $field_group
 *
 * @return mixed
 */
function acfe_get_php_save_path($field_group){
    return acf_get_instance('ACFE_AutoSync_Php')->get_php_save_path($field_group);
}