<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module')):

class acfe_module{
    
    // vars
    public  $name           = '',
            $plural         = '',
            $post_type      = '',
            $args           = array(),
        
            $setting        = '',
            $settings       = '',
            $view           = '',
            $register       = '',
            
            $export_tool    = '',
            $import_tool    = '',
            $export_actions = array('php', 'json'),
            $export_files   = array(),
            $messages       = array(),
        
            $item           = array(),
            $validate       = array(),
            $columns        = array(),
            $alias          = array(),
            $l10n           = array(),
        
            $modified       = null;
    
    /**
     * construct
     */
    function __construct(){
        
        // setup
        $this->initialize();
        
        // register stores
        // todo move 'local-any-post_type' store outside module
        acf_register_store("local-{$this->post_type}");
        acf_register_store("local-any-{$this->post_type}");
        acf_register_store($this->post_type)->prop('multisite', true);
        
        $this->add_module_filter('acfe/module/register_field_groups', array($this, 'register_field_groups'), 9);
        $this->add_module_filter('acfe/module/register_items',        array($this, 'register_items'), 9);
        $this->add_module_filter('acfe/module/register_item_args',    array($this, 'register_item_args'), 9);
        $this->add_module_action('acfe/module/register_item',         array($this, 'register_item'), 9);
        $this->add_module_action('acfe/module/load_post',             array($this, 'load_post'), 9);
        $this->add_module_action('acfe/module/load_posts',            array($this, 'load_posts'), 9);
        $this->add_module_filter('acfe/module/edit_columns',          array($this, 'edit_columns'), 9);
        $this->add_module_action('acfe/module/edit_columns_html',     array($this, 'edit_columns_html'), 9, 2);
        $this->add_module_filter('acfe/module/validate_save_item',    array($this, 'validate_save_item'), 9);
        $this->add_module_filter('acfe/module/prepare_load_item',     array($this, 'prepare_load_item'), 9);
        $this->add_module_filter('acfe/module/prepare_save_item',     array($this, 'prepare_save_item'), 9);
        $this->add_module_action('acfe/module/updated_item',          array($this, 'updated_item'), 9);
        $this->add_module_action('acfe/module/trashed_item',          array($this, 'trashed_item'), 9);
        $this->add_module_action('acfe/module/untrashed_item',        array($this, 'untrashed_item'), 9);
        $this->add_module_action('acfe/module/deleted_item',          array($this, 'deleted_item'), 9);
        $this->add_module_action('acfe/module/imported_item',         array($this, 'imported_item'), 9);
        $this->add_module_filter('acfe/module/load_item',             array($this, 'load_item'), 9);
        $this->add_module_filter('acfe/module/load_items',            array($this, 'load_items'), 9);
        
        $this->add_action('acfe/do_reset',                            array($this, 'reset'));
    
        // add to reserved post types
        acfe_append_setting('reserved_post_types', $this->post_type);
        
    }
    
    
    /**
     * initialize
     */
    function initialize(){
        // ...
    }
    
    
    /**
     * is_active
     * @return bool
     */
    function is_active(){
        
        if(!$this->setting){
            return true;
        }
        
        return (bool) acfe_get_setting($this->setting);
        
    }
    
    
    /**
     * get_field_groups
     *
     * @return mixed
     */
    function get_field_groups(){
        return $this->apply_module_filters('acfe/module/register_field_groups', array());
    }
    
    
    /**
     * validate_item
     *
     * @param $item
     *
     * @return array|mixed
     */
    function validate_item($item = array()){
    
        // already valid
        if(is_array($item) && !empty($item['_valid'])){
            return $item;
        }
    
        // convert
        $item['ID']     = (int) acf_maybe_get($item, 'ID', 0);
        $item['active'] = (bool) acf_maybe_get($item, 'active', true);
        $item['_valid'] = true;
    
        // default item
        $defaults = wp_parse_args($this->item, array(
            'ID'    => 0,
            'name'  => '',
            'label' => '',
        ));
    
        // parse defaults
        $item = acfe_parse_args_r($item, $defaults);
    
        // process alias
        foreach($this->alias as $k => $alias){
            if(!empty($item[ $alias ])){
            
                // set 'page_title' = 'label'
                $item[ $k ] = $item[ $alias ];
            
            }
        }
    
        // filters
        $item = $this->apply_module_filters('acfe/module/validate_item', $item);
    
        return $item;
        
    }
    
    
    /**
     * update_item
     *
     * @param $item
     *
     * @return array
     */
    function update_item($item){
        
        // make sure name is unique in db
        $name = wp_unique_post_slug($item['name'], $item['ID'], (!empty($item['active']) ? 'publish' : 'acf-disabled'), $this->post_type, 0);
        
        if($item['name'] !== $name){
            $item['name'] = $name;
            acf_enable_filter('acfe/module/update_unique_name');
        }
    
        // validate
        $item = $this->validate_item($item);
        
        // prepare item
        $item = wp_unslash($item);
        $item = acfe_parse_types($item);
        // todo: do not parse types on 'values' => array()
    
        // modified as global var
        $this->modified = acf_extract_var($item, 'modified');
        
        // cleanup keys
        $export = $this->prepare_item_for_export($item);
        
        // array of data
        $save = array(
            'ID'             => $item['ID'],
            'post_status'    => $item['active'] ? 'publish' : 'acf-disabled',
            'post_type'      => $this->post_type,
            'post_title'     => $item['label'],
            'post_name'      => $item['name'],
            'post_content'   => maybe_serialize($export),
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        );
        
        // bypass post modified
        add_filter('wp_insert_post_data', array($this, 'bypass_post_modified'), 1, 2);
    
        // remove filter to avoid serialized data corruption
        remove_filter('content_save_pre', 'wp_targeted_link_rel');
    
        // slash
        $save = wp_slash($save);
    
        // update or insert
        if($item['ID']){
            wp_update_post($save);
        }else{
            $item['ID'] = wp_insert_post($save);
        }
        
        // remove bypass post modified
        remove_filter('wp_insert_post_data', array($this, 'bypass_post_modified'), 1);
        
        // flush cache
        $this->flush_cache($item);
    
        // actions
        $this->do_module_action('acfe/module/updated_item', $item);
    
        // delete _wp_old_slug meta
        delete_post_meta($item['ID'], '_wp_old_slug');
    
        // return
        return $item;
        
    }
    
    
    /**
     * bypass_post_modified
     *
     * bypass post_modified automatically set to current date by wp_update_post
     * https://brogramo.com/how-to-update-a-wordpress-post-without-updating-the-modified-date-using-wp_update_post/
     *
     * @param $data
     * @param $array
     *
     * @return mixed
     */
    function bypass_post_modified($data, $array){
    
        if(!isset($data['post_modified'], $data['post_modified_gmt']) || !$this->modified){
            return $data;
        }
        
        $data['post_modified'] = wp_date('Y-m-d H:i:s', $this->modified);
        $data['post_modified_gmt'] = get_gmt_from_date($data['post_modified']);
    
        return $data;
    
    }
    
    
    /**
     * trash_item
     *
     * @param $id
     *
     * @return bool
     */
    function trash_item($id){
        
        // disable filters to get from db
        acf_disable_filters();
        
        // get
        $item = $this->get_item($id);
        
        // bail early
        if(!$item || !$item['ID']){
            return false;
        }
        
        // trash
        wp_trash_post($item['ID']);
        
        // flush
        $this->flush_cache($item);
        
        // actions
        $this->do_module_action('acfe/module/trashed_item', $item);
        
        // return
        return true;
        
    }
    
    
    /**
     * untrash_item
     *
     * @param $id
     *
     * @return bool
     */
    function untrash_item($id){
        
        // disable filters to get from db
        acf_disable_filters();
        
        // get raw item (to avoid validate_item)
        $item = $this->get_raw_item($id);
        
        // bail early
        if(!$item || !$item['ID']){
            return false;
        }
        
        // untrash
        wp_untrash_post($item['ID']);
        
        // update item (new status etc...)
        $this->update_item($item);
        
        // already flushed in update_item
        // $this->flush_cache($item);
    
        // actions
        $this->do_module_action('acfe/module/untrashed_item', $item);
        
        // return
        return true;
    }
    
    
    /**
     * delete_item
     *
     * @param $id
     *
     * @return bool
     */
    function delete_item($id){
        
        // disable filters to get from db
        acf_disable_filters();
        
        // get
        $item = $this->get_item($id);
        
        // bail early
        if(!$item || !$item['ID']){
            return false;
        }
        
        // delete post
        wp_delete_post($item['ID'], true);
        
        // flush
        $this->flush_cache($item);
    
        // actions
        $this->do_module_action('acfe/module/deleted_item', $item);
        
        // return
        return true;
        
    }
    
    
    /**
     * import_item
     *
     * @param $item
     *
     * @return array
     */
    function import_item($item){
    
        // disable filters to ensure data is not modified by local, clone, etc.
        $filters = acf_disable_filters();
    
        // validate item (ensures all settings exist).
        $item = $this->validate_item($item);
    
        // prepare item for import (modifies settings).
        $item = $this->prepare_item_for_import($item);
    
        // save item
        $item = $this->update_item($item);
    
        // enable filters again.
        acf_enable_filters($filters);
    
        // actions
        $this->do_module_action('acfe/module/imported_item', $item);
    
        // return
        return $item;
        
    }
    
    
    /**
     * duplicate_item
     *
     * @param $id
     * @param $new_post_id
     *
     * @return array|false
     */
    function duplicate_item($id = 0, $new_post_id = 0){
        
        // get raw item
        $item = $this->get_raw_item($id);
        
        // bail early if item was not found
        if(!$item || !$item['ID']){
            return false;
        }
        
        // update attributes
        $item['ID'] = $new_post_id;
        
        // Add (copy) to title when apropriate
        if(!$new_post_id){
            $item['label'] .= ' (' . __('copy', 'acf') . ')';
        }
        
        // save item
        $item = $this->update_item($item);
    
        // actions
        $this->do_module_action('acfe/module/duplicated_item', $item);
        
        // return
        return $item;
        
    }
    
    
    /**
     * get_item
     *
     * @param $id
     *
     * @return array|false|mixed|null
     */
    function get_item($id = 0){
        
        // allow wp object
        if(is_object($id)){
            $id = $id->ID;
        }
        
        // check store
        $store = acf_get_store($this->post_type);
        if($store->has($id)){
            return $store->get($id);
        }
        
        // check local
        if($this->is_local_item($id)){
            $item = $this->get_local_item($id);
            
        // Then check db
        }else{
            $item = $this->get_raw_item($id);
        }
        
        // bail early
        if(!$item){
            return false;
        }
        
        // validate
        $item = $this->validate_item($item);
    
        // filters
        $item = $this->apply_module_filters('acfe/module/load_item', $item);
        
        // store
        $store->set($item['name'], $item);
        $store->alias($item['name'], $item['ID']);
        
        // return
        return $item;
        
    }
    
    
    /**
     * get_local_item
     *
     * @param $name
     *
     * @return array|false|mixed
     */
    function get_local_item($name = ''){
        
        $item = acf_get_local_store($this->post_type)->get($name);
        
        if(!$item){
            return false;
        }
    
        // validate item
        $item = $this->validate_item($item);
        
        return $item;
    }
    
    
    /**
     * get_raw_item
     *
     * @param $id
     *
     * @return array|false|mixed
     */
    function get_raw_item($id = 0){
    
        // get raw
        $post = $this->get_item_post($id);
    
        if(!$post){
            return false;
        }
    
        // bail early if incorrect post type
        if($post->post_type !== $this->post_type){
            return false;
        }
    
        // unserialize post content
        $item = acf_get_array(maybe_unserialize($post->post_content));
        
        // prepend id
        $item = wp_parse_args($item, array(
            'ID'    => $post->ID,
            'name'  => $post->post_name,
            'label' => $post->post_title,
        ));
        
        // validate item
        $item = $this->validate_item($item);
        
        // return
        return $item;
        
    }
    
    
    /**
     * get_item_post
     *
     * @param $id
     *
     * @return array|false|WP_Post|null
     */
    function get_item_post($id = 0){
        
        // get post if numeric
        if(is_numeric($id)){
            
            return get_post($id);
            
        // search posts if string
        }elseif(is_string($id)){
            
            // try cache
            $cache_key = acf_cache_key("{$this->post_type}_post:name:$id");
            $post_id   = wp_cache_get($cache_key, 'acfe');
            
            if($post_id === false){
                
                // query posts
                $posts = get_posts(array(
                    'name'                   => $id,
                    'posts_per_page'         => 1,
                    'post_type'              => $this->post_type,
                    'post_status'            => array('publish', 'acf-disabled', 'trash'),
                    'orderby'                => 'menu_order title',
                    'order'                  => 'ASC',
                    'suppress_filters'       => false,
                    'cache_results'          => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                ));
                
                // update post with non false value
                $post_id = $posts ? $posts[0]->ID : 0;
                
                // update cache
                wp_cache_set($cache_key, $post_id, 'acfe');
                
            }
            
            // check psot id and return psot when possible
            if($post_id){
                return get_post($post_id);
            }
        }
        
        // return
        return false;
        
    }
    
    
    /**
     * get_items
     *
     * retrieve raw + local items
     *
     * @return mixed
     */
    function get_items(){
        
        // vars
        $items = array();
        
        // raw items
        foreach($this->get_raw_items() as $raw_item){
            $items[] = $this->get_item($raw_item['ID']);
        }
        
        // check local filter
        $is_local = acf_is_filter_enabled('local');
        
        // enable filter
        if(!$is_local){
            acf_enable_filter('local');
        }
    
        // get local items
        $local = $this->get_local_items();
        
        if($local){
        
            // generate map of 'index' => 'name' data.
            $map = wp_list_pluck($items, 'name');
        
            // loop over items and update/append local
            foreach($local as $item){
            
                // update
                $i = array_search($item['name'], $map);
                if($i !== false){
                    unset($item['ID']);
                    $items[ $i ] = array_merge($items[ $i ], $item);
                
                // append
                }else{
                    $items[] = $this->get_item($item['name']);
                }
                
            }
        
            // sort list via name
            $items = wp_list_sort($items, array(
                'name' => 'ASC',
            ));
            
        }
        
        // disable filter
        if(!$is_local){
            acf_disable_filter('local');
        }
    
        // filters
        $items = $this->apply_module_filters('acfe/module/load_items', $items);
        
        // return
        return $items;
        
    }
    
    
    /**
     * get_local_items
     *
     * @return array
     */
    function get_local_items(){
        
        // vars
        $items = array();
        $local_items = acf_get_local_store($this->post_type)->get();
        
        foreach($local_items as $local_item){
            $items[] = $this->validate_item($local_item);
        }
        
        return $items;
        
    }
    
    
    /**
     * get_raw_items
     *
     * @return array
     */
    function get_raw_items(){
    
        // try cache
        $cache_key = acf_cache_key($this->post_type);
        $post_ids  = wp_cache_get($cache_key, 'acfe');
    
        if($post_ids === false){
        
            // query
            $posts = get_posts(array(
                'posts_per_page'         => -1,
                'post_type'              => $this->post_type,
                'orderby'                => 'menu_order title',
                'order'                  => 'ASC',
                'suppress_filters'       => false, // Allow WPML to modify the query
                'cache_results'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'post_status'            => array('publish', 'acf-disabled'),
            ));
        
            // update post ids with non false values
            $post_ids = array();
            foreach($posts as $post){
                $post_ids[] = $post->ID;
            }
        
            // update cache
            wp_cache_set($cache_key, $post_ids, 'acfe');
        
        }
    
        // loop and get raw
        $items = array();
    
        foreach($post_ids as $post_id){
            
            $raw_item = $this->get_raw_item($post_id);
            
            if($raw_item){
                $items[] = $raw_item;
            }
            
        }
        
        // return
        return $items;
        
    }
    
    
    /**
     * flush_cache
     *
     * @param $item
     */
    function flush_cache($item){
        
        // delete stored data.
        acf_get_store($this->post_type)->remove($item['name']);
        
        // flush cached post_id for this item name
        wp_cache_delete(acf_cache_key("{$this->post_type}_post:name:{$item['name']}"), 'acfe');
        
        // flush cached array of post_ids for collection of items
        wp_cache_delete(acf_cache_key($this->post_type), 'acfe');
        
    }
    
    
    /**
     * prepare_item_for_export
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_item_for_export($item = array()){
        
        // todo: different export for acf function such as acf_register_block_type() with no custom args like acfe_autosync
        
        // remove args
        acf_extract_vars($item, array('ID', 'local', 'local_file', '_valid'));
    
        // remove alias keys
        acf_extract_vars($item, $this->alias);
    
        // filters
        $item = $this->apply_module_filters('acfe/module/prepare_item_for_export', $item);
        
        return $item;
        
    }
    
    
    /**
     * prepare_item_for_import
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_item_for_import($item){
    
        // process alias
        foreach($this->alias as $k => $alias){
        
            if(!empty($item[ $k ])){
            
                // set 'label' = 'page_title'
                $item[ $alias ] = $item[ $k ];
            
            }
        }
    
        // filters
        $item = $this->apply_module_filters('acfe/module/prepare_item_for_import', $item);
        
        return $item;
        
    }
    
    
    /**
     * translate_item
     *
     * @param $item
     *
     * @return mixed
     */
    function translate_item($item = array()){
    
        foreach($this->l10n as $k){
            $item[ $k ] = acf_translate($item[ $k ]);
        }
        
        return $item;
    }
    
    
    /**
     * is_local_item
     *
     * @param $name
     *
     * @return bool
     */
    function is_local_item($name = ''){
        return acf_get_local_store($this->post_type)->has($name);
    }
    
    
    /**
     * add_local_item
     *
     * @param $item
     */
    function add_local_item($item){
    
        // apply default properties
        $item = wp_parse_args($item, array(
            'name'  => '',
            'label' => '',
            'local' => 'php',
        ));
        
        // append local file for php
        if($item['local'] === 'php'){
            
            // get php local file path
            $backtrace = debug_backtrace();
            
            // append php local file
            if(isset($backtrace[1]['file'])){
                $item['local_file'] = wp_normalize_path($backtrace[1]['file']);
            }
            
        }
        
        // prepare item keys (alias etc...)
        $item = $this->prepare_item_for_import($item);
    
        // generate name if not provided
        if(!$item['name']){
            $item['name'] = acf_slugify($item['label']);
        }
    
        // add item to store if it doesn't exist
        if(!$this->is_local_item($item['name'])){
            acf_get_local_store($this->post_type)->set($item['name'], $item);
        }
        
        // action
        $this->do_module_action('acfe/module/add_local_item', $item);
        
    }
    
    
    /**
     * export_code
     *
     * @param $code
     * @param $args
     *
     * @return mixed
     */
    function export_code($code, $args){
        return '';
    }
    
    
    /**
     * export_local_code
     *
     * @param $code
     * @param $args
     *
     * @return mixed
     */
    function export_local_code($code, $args){
        return $this->export_code($code, $args);
    }
    
    
    /**
     * get_export_tool
     *
     * @return string
     */
    function get_export_tool(){
        return !empty($this->export_tool) ? $this->export_tool : "acfe_module_{$this->name}_export";
    }
    
    
    /**
     * get_import_tool
     *
     * @return string
     */
    function get_import_tool(){
        return !empty($this->import_tool) ? $this->import_tool : "acfe_module_{$this->name}_import";
    }
    
    
    /**
     * get_export_url
     *
     * @param $action
     * @param $item
     *
     * @return string|void
     */
    function get_export_url($action, $item){
        
        $name = acf_maybe_get($item, 'name', $item);
        $tool = $this->get_export_tool();
        return admin_url("edit.php?post_type=acf-field-group&page=acf-tools&tool={$tool}&action={$action}&keys={$name}");
        
    }
    
    
    /**
     * get_export_link
     *
     * @param $action
     * @param $item
     * @param $text
     *
     * @return string
     */
    function get_export_link($action, $item, $text = ''){
    
        $name = acf_maybe_get($item, 'name', $item);
        $text = !empty($text) ? $text : $this->get_export_action_label($action);
        return '<a href="' . $this->get_export_url($action, $name) . '">' . $text . '</a>';
        
    }
    
    
    /**
     * get_export_links
     *
     * @param $item
     *
     * @return array
     */
    function get_export_links($item){
    
        $links = array();
        foreach($this->export_actions as $action){
            $links[] = $this->get_export_link($action, $item['name']);
        }
        
        return $links;
        
    }
    
    
    /**
     * get_export_action_label
     *
     * @param $action
     *
     * @return string
     */
    function get_export_action_label($action){
        return $action === 'php' ? 'PHP' : 'Json';
    }
    
    
    /**
     * get_label
     *
     * @param $type
     *
     * @return mixed|null
     */
    function get_label($type = 'singular_name'){
        
        $labels = acfe_maybe_get($this->args, 'labels');
        
        if(!empty($labels)){
            return acf_maybe_get($labels, $type);
        }
        
        return acfe_maybe_get($this->args, 'label');
        
    }
    
    
    /**
     * get_message
     *
     * @param $name
     *
     * @return mixed|null
     */
    function get_message($name){
        return acf_maybe_get($this->messages, $name);
    }
    
    
    /**
     * reset
     */
    function reset(){
        
        // disable sync to avoid generating files
        acfe_update_setting('php', false);
        acfe_update_setting('json', false);
        
        // get raw items
        $items = $this->get_raw_items();
        
        if(!empty($items)){
    
            foreach($items as $item){
        
                // update db local
                // do not use module->update_item() to avoid changing post date
                $this->do_module_action('acfe/module/updated_item', $item);
        
            }
    
            // Log
            $message = '[ACF Extended] ' . __('Reset', 'acfe') . ': ' . $this->get_label('name');
            acf_log($message);
            
        }
        
    }
    
    
    /**
     * do_action
     *
     * @param $tag
     * @param ...$args
     */
    function do_action($tag, ...$args){
        
        $args[] = $this;
        do_action_ref_array($tag, $args);
        
    }
    
    
    /**
     * apply_filters
     *
     * @param $tag
     * @param ...$args
     *
     * @return mixed
     */
    function apply_filters($tag, ...$args){
        
        $args[] = $this;
        return apply_filters_ref_array($tag, $args);
        
    }
    
    
    /**
     * do_module_action
     *
     * @param $tag
     * @param ...$args
     */
    function do_module_action($tag, ...$args){
        
        $args[] = $this;
        
        do_action_ref_array("{$tag}/module={$this->name}", $args);
        do_action_ref_array($tag, $args);
        
    }
    
    
    /**
     * apply_module_filters
     *
     * @param $tag
     * @param ...$args
     *
     * @return mixed
     */
    function apply_module_filters($tag, ...$args){
        
        $args[] = $this;
        
        $args[0] = apply_filters_ref_array("{$tag}/module={$this->name}", $args);
        $args[0] = apply_filters_ref_array($tag, $args);
        
        return $args[0];
        
    }
    
    
    /**
     * add_action
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        if(is_callable($function_to_add)){
            add_action($tag, $function_to_add, $priority, $accepted_args);
        }
        
    }
    
    
    /**
     * add_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        if(is_callable($function_to_add)){
            add_filter($tag, $function_to_add, $priority, $accepted_args);
        }
        
    }
    
    
    /**
     * add_module_action
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_module_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        $tag .= "/module={$this->name}";
        $this->add_action($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
    
    /**
     * add_module_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_module_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        $tag .= "/module={$this->name}";
        $this->add_filter($tag, $function_to_add, $priority, $accepted_args);
        
    }
    
}

endif;

// register store
acf_register_store('acfe-modules');


/**
 * acfe_register_module
 *
 * @param $class
 *
 * @return bool
 */
function acfe_register_module($class){
    
    // instantiate
    $module = new $class();
    
    // add to store
    acf_get_store('acfe-modules')->set($module->name, $module);
    
    // return
    return true;
    
}


/**
 * acfe_get_modules
 * @return array|mixed|null
 */
function acfe_get_modules(){
    return acf_get_store('acfe-modules')->get();
}


/**
 * acfe_get_module
 *
 * @param $module
 *
 * @return acfe_module|array|mixed|null
 */
function acfe_get_module($module){
    
    if($module instanceof acfe_module){
        return $module;
    }
    
    return acf_get_store('acfe-modules')->get($module);
}


/**
 * acfe_query_module
 *
 * @param array $query
 *
 * @return false|mixed
 */
function acfe_query_module($query = array()){
    
    $modules = acfe_query_modules($query);
    
    return current($modules);
    
}


/**
 * acfe_query_modules
 *
 * @param $query
 *
 * @return false|mixed
 */
function acfe_query_modules($query = array()){
    return acf_get_store('acfe-modules')->query($query);
}


/**
 * acfe_get_module_by_post_type
 *
 * @param $post_type
 *
 * @return false|mixed
 */
function acfe_get_module_by_post_type($post_type){
    return acfe_query_module(array('post_type' => $post_type));
}


/**
 * acfe_get_module_by_item
 *
 * @param $id
 *
 * @return false|mixed
 */
function acfe_get_module_by_item($id){
    
    // check array/object
    if(is_array($id) || is_object($id)){
        $id = acfe_maybe_get($id, 'ID');
    }
    
    $id = absint($id);
    if(!$id){
        return false;
    }
    
    return acfe_get_module_by_post_type(get_post_type($id));
}

/**
 * acfe_is_module_v2_item
 *
 * @param $post_id
 *
 * @return bool
 */
function acfe_is_module_v2_item($post_id){
    
    $post = get_post($post_id);
    $meta_name = false;
    
    // validate post
    if(!$post){
        return false;
    }
    
    // define meta name
    switch($post->post_type){
        
        case 'acfe-dbt': {
            $meta_name = 'name';
            break;
        }
    
        case 'acfe-form': {
            $meta_name = 'acfe_form_name';
            break;
        }
    
        case 'acfe-dop': {
            $meta_name = 'menu_slug';
            break;
        }
    
        case 'acfe-dpt': {
            $meta_name = 'acfe_dpt_name';
            break;
        }
    
        case 'acfe-dt': {
            $meta_name = 'acfe_dt_name';
            break;
        }
    
        case 'acfe-template': {
            $meta_name = 'acfe_template_active';
            break;
        }
        
    }
    
    // validate meta name
    if(!$meta_name){
        return false;
    }
    
    // get post meta
    $post_meta = get_post_meta($post_id, $meta_name, true);
    
    // validate old meta name
    // empty and not 0
    if(acf_is_empty($post_meta)){
        return false;
    }
    
    // get post content
    $post_content = maybe_unserialize($post->post_content);
    $post_content = acf_get_array($post_content);
    
    // post content already set
    if($post_content){
        return false;
    }
    
    // is module v2 item
    return true;
    
}