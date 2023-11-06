<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance_ultra_revisions')):

class acfe_performance_ultra_revisions{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acf/pre_update_metadata',  array($this, 'revision_pre_update'), 10, 5);
        add_filter('_wp_post_revision_fields', array($this, 'revision_fields'),     10, 2);
        
    }
    
    
    /**
     * revision_pre_update
     *
     * @param $null
     * @param $revision_id
     * @param $name
     * @param $value
     * @param $hidden
     *
     * @return bool|mixed
     */
    function revision_pre_update($null, $revision_id, $name, $value, $hidden){
        
        // bail early if not acf meta or not revision
        if($name !== 'acf' || acfe_get_performance_config('engine') !== 'ultra' || !wp_is_post_revision($revision_id)){
            return $null;
        }
        
        // get parent post id (original post)
        $post_id = wp_get_post_parent_id($revision_id);
        
        // check parent post has performance
        if(!acfe_is_object_performance_enabled($post_id)){
            return $null;
        }
    
        // get parent post id values when not in preview
        if(acf_maybe_get_POST('wp-preview') !== 'dopreview'){
        
            // get acf meta
            $value = acf_get_metadata($post_id, 'acf');
        
            // unslash for revision
            $value = wp_unslash($value);
        
        }
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($revision_id));
        
        $prefix = $hidden ? '_' : '';
        
        // update
        update_metadata($type, $id, "{$prefix}{$name}", $value);
        
        // do not save normally (already did it)
        return true;
        
    }
    
    
    /**
     * revision_fields
     *
     * @param $fields
     * @param $post
     *
     * @return mixed
     */
    function revision_fields($fields, $post = null){
        
        // validate page
        if(acf_is_screen('revision') || acf_is_ajax('get-revision-diffs')){
            
            // bail early if is restoring
            if(acf_maybe_get_GET('action') === 'restore'){
                return $fields;
            }
            
        // allow
        }else{
            
            // bail early (most likely saving a post)
            return $fields;
            
        }
        
        // vars
        $post_id = acf_maybe_get($post, 'ID', false);
        
        // compatibility with WP < 4.5 (test)
        if(!$post_id){
            
            global $post;
            $post_id = $post->ID;
            
        }
    
        // check post has performance
        if(acfe_get_object_performance_engine_name($post_id) !== 'ultra'){
            return $fields;
        }
        
        // get all postmeta
        $meta = get_post_meta($post_id);
        
        // bail early if no meta
        if(!$meta || !isset($meta['acf'])){
            return $fields;
        }
        
        // hook into specific revision field filter and return local value
        add_filter('_wp_post_revision_field_acf', array($this, 'revision_field'), 10, 4);
        
        $fields['acf'] = 'ACF';
        
        // return
        return $fields;
        
    }
    
    
    /**
     * revision_field
     *
     * revision field for acf
     *
     * @param $value
     * @param $field_name
     * @param $post
     * @param $direction
     *
     * @return bool|mixed|string
     */
    function revision_field($value, $field_name, $post = null, $direction = false){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // value has not yet been 'maybe_unserialize'
        $value = maybe_unserialize($value);
        
        // formatting
        if(is_array($value)){
            $value = print_r($value, true);
        }
        
        // return
        return $value;
        
    }
    
}

acf_new_instance('acfe_performance_ultra_revisions');

endif;