<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_post_type_objects
 *
 * Query & retrieve post types objects
 *
 * @param array $args
 *
 * @return array
 */
function acfe_get_post_type_objects($args = array()){
    
    // vars
    $return = array();
    
    // get post types
    $posts_types = acf_get_post_types($args);
    
    // loop
    foreach($posts_types as $post_type){
        
        // get object
        $object = get_post_type_object($post_type);
        
        // append
        $return[ $object->name ] = $object;
        
    }
    
    // return
    return $return;
    
}

/**
 * acfe_get_pretty_post_statuses
 *
 * Similar to acf_get_pretty_post_types() but for Post Statuses
 *
 * @param array $posts_statuses
 *
 * @return array
 */
function acfe_get_pretty_post_statuses($posts_statuses = array()){
    
    // get post statuses
    if(empty($posts_statuses)){
        $posts_statuses = get_post_stati();
    }
    
    // vars
    $ref = array();
    $return = array();
    
    // loop
    foreach($posts_statuses as $post_status){
        
        // vars
        $object = get_post_status_object($post_status);
        $label = $object->label;
        
        // append to return
        $return[ $object->name ] = $label;
    
        // increase counter
        if(!isset($ref[ $label ])){
            $ref[ $label ] = 0;
        }
        
        $ref[ $label ]++;
        
    }
    
    // get slugs
    foreach(array_keys($return) as $slug){
        
        // vars
        $label = $return[ $slug ];
        
        // append slug
        if($ref[ $label ] > 1){
            $return[ $slug ] .= " ({$slug})";
        }
        
    }
    
    // return
    return $return;
    
}