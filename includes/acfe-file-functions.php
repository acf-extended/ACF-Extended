<?php

if(!defined('ABSPATH'))
    exit;

/**
 * acfe_get_abs_path_to_url
 *
 * Convert "/url" to "https://www.domain.com/url"
 *
 * @param string $path
 *
 * @return string
 */
function acfe_get_abs_path_to_url($path = ''){
    
    $abspath = untrailingslashit(ABSPATH);
    
    $url = str_replace($abspath, site_url(), $path);
    $url = wp_normalize_path($url);
    
    return esc_url_raw($url);
    
}

/**
 * acfe_locate_file_url
 *
 * Similar to locate_template(), but locate File URL instead
 * Check if file exists locally and return URL (supports parent/child theme)
 *
 * @param $filenames
 *
 * @return mixed|string
 */
function acfe_locate_file_url($filenames){
    
    $located = '';
    
    foreach((array) $filenames as $filename){
        
        if(!$filename)
            continue;
        
        // Direct URL: https://www.domain.com/folder/file.js
        if(stripos($filename, 'http://') === 0 || stripos($filename, 'https://') === 0 || stripos($filename, '//') === 0){
            
            $located = $filename;
            break;
            
        }else{
            
            $_filename = ltrim($filename, '/\\');
            $abspath = untrailingslashit(ABSPATH);
            
            // Child Theme
            if(file_exists(STYLESHEETPATH . '/' . $_filename)){
                
                $located = get_stylesheet_directory_uri() . '/' . $_filename;
                break;
                
            }
            
            // Parent Theme
            elseif(file_exists(TEMPLATEPATH . '/' . $_filename)){
                
                $located = get_template_directory_uri() . '/' . $_filename;
                break;
                
            }
            
            // Direct file path
            elseif(file_exists($filename)){
                
                $located = acfe_get_abs_path_to_url($filename);
                break;
                
            }
            
            // ABSPATH file path
            elseif(file_exists($abspath . '/' . $_filename)){
                
                $located = acfe_get_abs_path_to_url($abspath . '/' . $_filename);
                break;
                
            }
            
            // WP Content Dir
            elseif(file_exists(WP_CONTENT_DIR . '/' . $_filename)){
                
                $located = WP_CONTENT_URL . '/' . $_filename;
                break;
                
            }
            
        }
        
    }
    
    return $located;
    
}

/**
 * acfe_locate_file_path
 *
 * Similar to locate_template(), but locate File Path instead
 * Based on wp-includes\template.php:653
 *
 * @param $filenames
 *
 * @return mixed|string
 */
function acfe_locate_file_path($filenames){
    
    $located = '';
    
    foreach((array) $filenames as $filename){
        
        if(!$filename)
            continue;
        
        $_filename = ltrim($filename, '/\\');
        $abspath = untrailingslashit(ABSPATH);
        
        // Stylesheet file path
        if(file_exists(STYLESHEETPATH . '/' . $_filename)){
            
            $located = STYLESHEETPATH . '/' . $_filename;
            break;
            
        }
        
        // Template file path
        elseif(file_exists(TEMPLATEPATH . '/' . $_filename)){
            
            $located = TEMPLATEPATH . '/' . $_filename;
            break;
            
        }
        
        // Direct file path
        elseif(file_exists($filename)){
            
            $located = $filename;
            break;
            
        }
        
        // ABSPATH file path
        elseif(file_exists($abspath . '/' . $_filename)){
            
            $located = $abspath . '/' . $_filename;
            break;
            
        }
        
        // WP Content Dir
        elseif(file_exists(WP_CONTENT_DIR . '/' . $_filename)){
            
            $located = WP_CONTENT_DIR . '/' . $_filename;
            break;
            
        }
        
    }
    
    return $located;
    
}