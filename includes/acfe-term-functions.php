<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_taxonomy_objects
 *
 * Query & retrieve taxonomies objects
 *
 * @param array $args
 *
 * @return array
 */
function acfe_get_taxonomy_objects($args = array()){
    
    // vars
    $return = array();
    
    // Post Types
    $taxonomies = acf_get_taxonomies($args);
    
    // Choices
    if(!empty($taxonomies)){
        
        foreach($taxonomies as $taxonomy){
            
            $taxonomy_object = get_taxonomy($taxonomy);
            
            $return[ $taxonomy_object->name ] = $taxonomy_object;
            
        }
        
    }
    
    return $return;
    
}

/**
 * acfe_get_taxonomy_terms_ids
 *
 * Similar to acf_get_taxonomy_terms()
 * Returns "array('256' => 'Category name')" instead of "array('category:category_name' => 'Category name')"
 *
 * @param array $taxonomies
 *
 * @return array
 */
function acfe_get_taxonomy_terms_ids($taxonomies = array()){
    
    // force array
    $taxonomies = acf_get_array($taxonomies);
    
    // get pretty taxonomy names
    $taxonomies = acf_get_taxonomy_labels($taxonomies);
    
    // vars
    $r = array();
    
    // populate $r
    foreach(array_keys($taxonomies) as $taxonomy){
        
        // vars
        $label = $taxonomies[ $taxonomy ];
        $is_hierarchical = is_taxonomy_hierarchical($taxonomy);
        
        $terms = acf_get_terms(array(
            'taxonomy'      => $taxonomy,
            'hide_empty'    => false
        ));
        
        // bail early if no terms
        if(empty($terms)){
            continue;
        }
        
        // sort into hierachial order!
        if($is_hierarchical){
            $terms = _get_term_children(0, $terms, $taxonomy);
        }
        
        // add placeholder
        $r[ $label ] = array();
        
        // add choices
        foreach($terms as $term){
            $r[ $label ][ $term->term_id ] = acf_get_term_title($term);
        }
        
    }
    
    // return
    return $r;
    
}

/**
 * acfe_get_term_level
 *
 * Retrive the Term Level number
 *
 * @param $term
 * @param $taxonomy
 *
 * @return int
 */
function acfe_get_term_level($term, $taxonomy){
    
    $ancestors = get_ancestors($term, $taxonomy);
    
    return count($ancestors) + 1;
    
}