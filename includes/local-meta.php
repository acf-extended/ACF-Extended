<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Local_Meta')):
    
class ACFE_Local_Meta{
    
    // Vars
    var $meta = array();
    var $curr_id = array();
    var $main_id = array();
    
    /*
     * Construct
     */
    function __construct(){
        
        // Filters
        add_filter('acf/pre_load_post_id',  array($this, 'pre_load_post_id'),   1, 2);
        add_filter('acf/pre_load_meta',     array($this, 'pre_load_meta'),      1, 2);
        add_filter('acf/pre_load_metadata', array($this, 'pre_load_metadata'),  1, 4);
        
    }
    
    /*
     * Add
     */
    function add($meta = array(), $post_id = 0, $is_main = false){
        
        // Capture meta
        if($this->is_request($meta)){
            $meta = $this->capture($meta, $post_id);
        }
        
        // Add to storage
        $this->meta[$post_id] = $meta;
        
        // Add to current ID
        $this->curr_id[] = $post_id;
        
        // Add to main ID
        if($is_main){
            $this->main_id[] = $post_id;
        }
        
        // Return meta.
        return $meta;
        
    }
    
    /*
     * Remove
     */
    function remove(){
        
        // unset meta
        unset($this->meta[end($this->curr_id)]);
        
        // reset main id
        if(end($this->curr_id) === end($this->main_id)){
            
            // remove last value of main id
            array_pop($this->main_id);
            
        }
        
        // remove last value of current id
        array_pop($this->curr_id);
        
    }
    
    /*
     * Preload Post ID
     */
    function pre_load_post_id($null, $post_id){
        
        if(!$post_id && $this->main_id && end($this->curr_id) === end($this->main_id)){
            return end($this->main_id);
        }
        
        return $null;
        
    }
    
    /*
     * Is Request
     */
    function is_request($meta = array()){
        return acf_is_field_key(key($meta));
    }
    
    /*
     * Capture
     */
    function capture($values = array(), $post_id = 0){
        
        // Reset meta.
        $this->meta[ $post_id ] = array();
        
        // Listen for any added meta.
        add_filter('acf/pre_update_metadata', array($this, 'capture_update_metadata'), 1, 5);
    
        // Simulate update.
        if($values){
            
            // Get hook variations
            $hook = acf_get_store('hook-variations')->get('acf/update_value');
            
            // Clone Hook
            $_hook = $hook;
            unset($_hook['variations'][1]); // unset name
            unset($_hook['variations'][2]); // unset key
            
            // Update hook variations
            acf_get_store('hook-variations')->set('acf/update_value', $_hook);
            
            // update values
            acf_update_values($values, $post_id);
            
            // Reset hook variations back to default
            acf_get_store('hook-variations')->set('acf/update_value', $hook);
            
        }
        
        // Remove listener filter.
        remove_filter('acf/pre_update_metadata', array($this, 'capture_update_metadata'), 1);
        
        // Return meta.
        return $this->meta[ $post_id ];
        
    }
    
    /*
     * Capture Update Metadata
     */
    function capture_update_metadata($null, $post_id, $name, $value, $hidden){
        
        $name = ($hidden ? '_' : '') . $name;
        $this->meta[ $post_id ][ $name ] = $value;
        
        // Return non null value to escape update process.
        return true;
        
    }
    
    /*
     * Preload Meta
     */
    function pre_load_meta($null, $post_id){
        
        if(isset($this->meta[ $post_id ])){
            return $this->meta[ $post_id ];
        }
        
        return $null;
        
    }
    
    /*
     * Preload Metadata
     */
    function pre_load_metadata($null, $post_id, $name, $hidden){
        
        $name = ($hidden ? '_' : '') . $name;
        
        if(isset($this->meta[ $post_id ])){
            
            if(isset($this->meta[ $post_id ][ $name ])){
                return $this->meta[ $post_id ][ $name ];
            }
            return '__return_null';
            
        }
        
        return $null;
        
    }
    
}

endif;

/*
 * acfe_setup_meta
 */
function acfe_setup_meta($meta = array(), $post_id = 0, $is_main = false){
    return acf_get_instance('ACFE_Local_Meta')->add($meta, $post_id, $is_main);
}

/*
 * acfe_reset_meta
 */
function acfe_reset_meta($post_id = null){
    return acf_get_instance('ACFE_Local_Meta')->remove();
}

/*
 * acfe_get_local_post_ids
 */
function acfe_get_local_post_ids(){
    
    $post_ids = array();
    
    // ACF Local Meta
    $acf_meta = acf_get_instance('ACF_Local_Meta')->meta;
    $post_ids = array_merge($post_ids, array_keys($acf_meta));
    
    // ACFE Local Meta
    $acfe_meta = acf_get_instance('ACFE_Local_Meta')->meta;
    $post_ids = array_merge($post_ids, array_keys($acfe_meta));
    
    return array_unique($post_ids);
    
}

/*
 * acfe_get_local_post_id
 */
function acfe_get_local_post_id(){
    
    $post_ids = acfe_get_local_post_ids();
    
    return end($post_ids);
    
}

/*
 * acfe_is_local_post_id
 */
function acfe_is_local_post_id($post_id){
    
    $local_post_ids = acfe_get_local_post_ids();
    
    return in_array($post_id, $local_post_ids);
    
}

/*
 * acfe_is_local_meta
 */
function acfe_is_local_meta(){
    
    return !empty(acfe_get_local_post_ids());
    
}