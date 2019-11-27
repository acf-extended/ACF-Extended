<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_compatibility')):

class acfe_compatibility{
    
	function __construct(){
        
        add_action('acf/init',              array($this, 'init'), 99);
        
        add_filter('pll_get_post_types',    array($this, 'polylang'), 10, 2);
        
	}
    
    function init(){
        
        /**
         * ACF Extended: 0.8
         * Settings: Renamed acfe_php* to acfe/php*
         */
        if(acf_get_setting('acfe_php') !== null){
            acf_update_setting('acfe/php', acf_get_setting('acfe_php'));
        }

        if(acf_get_setting('php_save') !== null){
            acf_update_setting('acfe/php_save', acf_get_setting('php_save'));
        }

        if(acf_get_setting('php_load') !== null){
            acf_update_setting('acfe/php_load', acf_get_setting('php_load'));
        }

        if(acf_get_setting('php_found') !== null){
            acf_update_setting('acfe/php_found', acf_get_setting('php_found'));
        }
        
        add_filter('acf/validate_field_group',      array($this, 'field_group_location_list'), 20);
        add_filter('acf/validate_field',            array($this, 'field_acfe_update'), 20);
        add_filter('pto/posts_orderby/ignore',      array($this, 'pto_acf_field_group'), 10, 3);
        add_action('admin_menu',                    array($this, 'cotto_submenu'), 999);
        add_filter('rank_math/metabox/priority',    array($this, 'rankmath_metaboxes_priority'));
        add_filter('wpseo_metabox_prio',            array($this, 'yoast_metaboxes_priority'));
        
    }
    
    /**
     * ACF Extended: 0.8
     * Field Group Location: Archive renamed to List
     */
    function field_group_location_list($field_group){
        
        if(!acf_maybe_get($field_group, 'location'))
            return $field_group;
        
        foreach($field_group['location'] as &$or){
            
            foreach($or as &$and){
                
                if(!isset($and['value']))
                    continue;
                
                // Post Type List
                if($and['param'] === 'post_type' && acfe_ends_with($and['value'], '_archive')){
                
                    $and['param'] = 'post_type_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
                
                }
                
                // Taxonomy List
                elseif($and['param'] === 'taxonomy' && acfe_ends_with($and['value'], '_archive')){
                    
                    $and['param'] = 'taxonomy_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
                    
                }
                
            }
            
        }
        
        return $field_group;
        
    }
    
    /**
     * ACF Extended: 0.8
     * Field Filter Value: Removed from this version
     */
    function field_acfe_update($field){
        
        if(!acf_maybe_get($field, 'acfe_update'))
            return $field;
        
        unset($field['acfe_update']);
        
        return $field;
        
    }
    
    /**
     * Plugin: Post Types Order
     * https://wordpress.org/plugins/post-types-order/
     * The plugin apply custom order to 'acf-field-group' Post Type. We have to fix this
     */
    function pto_acf_field_group($ignore, $orderby, $query){
        
        if(is_admin() && $query->is_main_query() && $query->get('post_type') === 'acf-field-group')
            $ignore = true;

        return $ignore;
        
    }
    
    /**
     * Plugin: Category Order and Taxonomy Terms Order
     * https://wordpress.org/plugins/taxonomy-terms-order/
     * The plugin add a submenu to 'Custom Fields' to order Field Group Categories. It's unecessary
     */
    function cotto_submenu(){
        
        remove_submenu_page('edit.php?post_type=acf-field-group', 'to-interface-acf-field-group');
        
    }
    
    /**
     * Plugin: Rank Math SEO
     * https://wordpress.org/plugins/seo-by-rank-math/
     * Fix the plugin post metabox which is always above ACF metaboxes
     */
    function rankmath_metaboxes_priority(){
        
        return 'default';
        
    }
    
    /**
     * Plugin: YOAST SEO
     * https://wordpress.org/plugins/wordpress-seo/
     * Fix the plugin post metabox which is always above ACF metaboxes
     */
    function yoast_metaboxes_priority(){
        
        return 'default';
        
    }
    
    /**
     * ACF Extended: 0.8.3
     * Modules: Enable PolyLang Translation for Modules Post Types
     * https://polylang.pro/doc/filter-reference/
     */
    function polylang($post_types, $is_settings){
        
        if($is_settings){
            
            unset($post_types['acfe-dbt']);
            unset($post_types['acfe-dt']);
            unset($post_types['acfe-dop']);
            unset($post_types['acfe-dpt']);
            unset($post_types['acfe-form']);
            
        }else{
            
            $post_types['acfe-dbt'] = 'acfe-dbt';
            $post_types['acfe-dt'] = 'acfe-dt';
            $post_types['acfe-dop'] = 'acfe-dop';
            $post_types['acfe-dpt'] = 'acfe-dpt';
            $post_types['acfe-form'] = 'acfe-form';
            
        }
        
        return $post_types;
        
    }
    
}

new acfe_compatibility();

endif;