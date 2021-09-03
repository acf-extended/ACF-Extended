<?php

if(!defined('ABSPATH'))
    exit;

/**
 * acfe_get_registered_image_sizes
 *
 * Clone of wp_get_registered_image_subsizes. Available since WP 5.3
 * https://developer.wordpress.org/reference/functions/wp_get_registered_image_subsizes/
 *
 * @param false $filter
 *
 * @return array|mixed
 */
function acfe_get_registered_image_sizes($filter = false){
    
    $additional_sizes   = wp_get_additional_image_sizes();
    $all_sizes          = array();
    
    $wp_sizes           = get_intermediate_image_sizes();
    $wp_sizes[]         = 'full';
    
    foreach($wp_sizes as $size_name){
        
        if($filter && $size_name !== $filter)
            continue;
        
        $size_data = array(
            'name'   => $size_name,
            'width'  => 0,
            'height' => 0,
            'crop'   => false,
        );
        
        // For sizes added by plugins and themes.
        if(isset( $additional_sizes[ $size_name ]['width'])){
            $size_data['width'] = (int) $additional_sizes[ $size_name ]['width'];
            // For default sizes set in options.
        }else{
            $size_data['width'] = (int) get_option("{$size_name}_size_w");
        }
        
        if(isset($additional_sizes[ $size_name ]['height'])){
            $size_data['height'] = (int) $additional_sizes[ $size_name ]['height'];
        }else{
            $size_data['height'] = (int) get_option("{$size_name}_size_h");
        }
        
        if(isset($additional_sizes[ $size_name ]['crop'])){
            $size_data['crop'] = $additional_sizes[ $size_name ]['crop'];
        }else{
            $size_data['crop'] = get_option("{$size_name}_crop");
        }
        
        if(!is_array( $size_data['crop']) || empty($size_data['crop'])){
            $size_data['crop'] = (bool) $size_data['crop'];
        }
        
        $all_sizes[ $size_name ] = $size_data;
        
    }
    
    if($filter && isset($all_sizes[ $filter ]))
        return $all_sizes[ $filter ];
    
    return $all_sizes;
    
}

/**
 * acfe_remove_class_filter
 *
 * Remove hook from inaccessible PHP class
 * https://gist.github.com/tripflex/c6518efc1753cf2392559866b4bd1a53
 *
 * @param        $tag
 * @param string $class_name
 * @param string $method_name
 * @param int    $priority
 *
 * @return bool
 */
function acfe_remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
    
    global $wp_filter;
    
    // Check that filter actually exists first
    if ( ! isset( $wp_filter[ $tag ] ) ) {
        return FALSE;
    }
    
    /**
     * If filter config is an object, means we're using WordPress 4.7+ and the config is no longer
     * a simple array, rather it is an object that implements the ArrayAccess interface.
     *
     * To be backwards compatible, we set $callbacks equal to the correct array as a reference (so $wp_filter is updated)
     *
     * @see https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/
     */
    if ( is_object( $wp_filter[ $tag ] ) && isset( $wp_filter[ $tag ]->callbacks ) ) {
        // Create $fob object from filter tag, to use below
        $fob       = $wp_filter[ $tag ];
        $callbacks = &$wp_filter[ $tag ]->callbacks;
    } else {
        $callbacks = &$wp_filter[ $tag ];
    }
    
    // Exit if there aren't any callbacks for specified priority
    if ( ! isset( $callbacks[ $priority ] ) || empty( $callbacks[ $priority ] ) ) {
        return FALSE;
    }
    
    // Loop through each filter for the specified priority, looking for our class & method
    foreach ( (array) $callbacks[ $priority ] as $filter_id => $filter ) {
        
        // Filter should always be an array - array( $this, 'method' ), if not goto next
        if ( ! isset( $filter['function'] ) || ! is_array( $filter['function'] ) ) {
            continue;
        }
        
        // If first value in array is not an object, it can't be a class
        if ( ! is_object( $filter['function'][0] ) ) {
            continue;
        }
        
        // Method doesn't match the one we're looking for, goto next
        if ( $filter['function'][1] !== $method_name ) {
            continue;
        }
        
        // Method matched, now let's check the Class
        if ( get_class( $filter['function'][0] ) === $class_name ) {
            
            // WordPress 4.7+ use core remove_filter() since we found the class object
            if ( isset( $fob ) ) {
                // Handles removing filter, reseting callback priority keys mid-iteration, etc.
                $fob->remove_filter( $tag, $filter['function'], $priority );
                
            } else {
                // Use legacy removal process (pre 4.7)
                unset( $callbacks[ $priority ][ $filter_id ] );
                // and if it was the only filter in that priority, unset that priority
                if ( empty( $callbacks[ $priority ] ) ) {
                    unset( $callbacks[ $priority ] );
                }
                // and if the only filter for that tag, set the tag to an empty array
                if ( empty( $callbacks ) ) {
                    $callbacks = array();
                }
                // Remove this filter from merged_filters, which specifies if filters have been sorted
                unset( $GLOBALS['merged_filters'][ $tag ] );
            }
            
            return TRUE;
        }
    }
    
    return FALSE;
}

/**
 * acfe_remove_class_action
 *
 * @param        $tag
 * @param string $class_name
 * @param string $method_name
 * @param int    $priority
 *
 * @return bool
 */
function acfe_remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
    return acfe_remove_class_filter( $tag, $class_name, $method_name, $priority );
}