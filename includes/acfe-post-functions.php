<?php

if(!defined('ABSPATH'))
    exit;

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
    
    // Post Types
    $posts_types = acf_get_post_types($args);
    
    // Choices
    if(!empty($posts_types)){
        
        foreach($posts_types as $post_type){
            
            $post_type_object = get_post_type_object($post_type);
            
            $return[ $post_type_object->name ] = $post_type_object;
            
        }
        
    }
    
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
    
    if(empty($posts_statuses)){
        
        $posts_statuses = get_post_stati(array(), 'names');
        
    }
    
    $return = array();
    
    // Choices
    if(!empty($posts_statuses)){
        
        foreach($posts_statuses as $post_status){
            
            $post_status_object = get_post_status_object($post_status);
            
            $return[$post_status_object->name] = $post_status_object->label . ' (' . $post_status_object->name . ')';
            
        }
        
    }
    
    return $return;
    
}