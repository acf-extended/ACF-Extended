<?php

use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_third_party')):

class acfe_third_party{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('pto/posts_orderby/ignore',              array($this, 'pto_posts_orderby_ignore'), 10, 3);
        add_filter('pto/get_options',                       array($this, 'pto_get_options'));
        add_action('admin_menu',                            array($this, 'cotto_submenu'), 999);
        add_filter('rank_math/metabox/priority',            array($this, 'rankmath_metaboxes_priority'));
        add_filter('wpseo_metabox_prio',                    array($this, 'yoast_metaboxes_priority'));
        add_filter('pll_get_post_types',                    array($this, 'polylang_get_post_types'), 10, 2);
        add_action('elementor/documents/register_controls', array($this, 'elementor_register_controls'));
        add_filter('wpgraphql_acf_supported_fields',        array($this, 'wpgraphql_acf_supported_fields'));
        add_filter('wpgraphql_acf_register_graphql_field',  array($this, 'wpgraphql_acf_register_graphql_field'), 10, 4);
        
    }
    
    
    /**
     * pto_posts_orderby_ignore
     *
     * Fix Post Types Order which apply custom order to ACF Field Group Post Type
     * https://wordpress.org/plugins/post-types-order/
     *
     * @param $ignore
     * @param $orderby
     * @param $query
     *
     * @return bool|mixed
     */
    function pto_posts_orderby_ignore($ignore, $orderby, $query){
        
        if(is_admin() && $query->is_main_query() && $query->get('post_type') === 'acf-field-group'){
            $ignore = true;
        }
        
        return $ignore;
        
    }
    
    
    /**
     * pto_get_options
     *
     * Fix Post Types Order which apply a drag & drop UI on ACF Field Group UI
     * https://wordpress.org/plugins/post-types-order/
     *
     * @param $options
     *
     * @return array
     */
    function pto_get_options($options){
        
        $options['show_reorder_interfaces']['acf-field-group'] = 'hide';
        
        return $options;
        
    }
    
    
    /**
     * cotto_submenu
     *
     * Fix Category Order and Taxonomy Terms Order which adds an unnecessary submenu to 'Custom Fields' to order Field Group Categories
     */
    function cotto_submenu(){
        remove_submenu_page('edit.php?post_type=acf-field-group', 'to-interface-acf-field-group');
    }
    
    
    /**
     * rankmath_metaboxes_priority
     *
     * Fix RankMath post metabox which is always above ACF metaboxes
     * https://wordpress.org/plugins/seo-by-rank-math/
     *
     * @return string
     */
    function rankmath_metaboxes_priority(){
        return 'default';
    }
    
    
    /**
     * yoast_metaboxes_priority
     *
     * Fix YOAST post metabox which is always above ACF metaboxes
     * https://wordpress.org/plugins/wordpress-seo/
     *
     * @return string
     */
    function yoast_metaboxes_priority(){
        return 'default';
    }
    

    /**
     * polylang_get_post_types
     *
     * Enable Polylang translation for the form module
     * https://polylang.pro/doc/filter-reference/
     * Since 0.8.3
     *
     * @param $post_types
     * @param $is_settings
     *
     * @return mixed
     */
    function polylang_get_post_types($post_types, $is_settings){
        
        if($is_settings){
            
            unset($post_types['acfe-form']);
            unset($post_types['acfe-template']);
            
        }else{
            
            $post_types['acfe-form'] = 'acfe-form';
            $post_types['acfe-template'] = 'acfe-template';
            
        }
        
        return $post_types;
        
    }
    
    
    /**
     * elementor_register_controls
     *
     * Fix Elementor listing all private ACF Extended Field Groups in Dynamic ACF Tags options list
     * Since 0.8.8
     */
    function elementor_register_controls(){
        
        // make sure we're on Elementor edit mode
        if(acf_maybe_get_GET('action') !== 'elementor'){
            return;
        }
        
        // hide reserved field groups in Dynamic Tags
        add_filter('acf/load_field_groups', function($field_groups){
            
            // Hidden Local Field Groups
            $hidden = acfe_get_setting('reserved_field_groups', array());
            
            foreach($field_groups as $i => $field_group){
                
                if(!in_array($field_group['key'], $hidden)) continue;
                
                unset($field_groups[$i]);
                
            }
            
            $field_groups = array_values($field_groups);
            
            return $field_groups;
            
        }, 25);
        
    }
    
    
    /**
     * wpgraphql_acf_supported_fields
     *
     * @param $fields
     *
     * @since 0.8.8.2 (27/04/2021)
     *
     * @return mixed
     */
    function wpgraphql_acf_supported_fields($fields){
        
        $acfe_fields = array(
            'acfe_advanced_link',
            'acfe_code_editor',
            'acfe_column',
            'acfe_forms',
            'acfe_hidden',
            'acfe_post_statuses',
            'acfe_post_types',
            'acfe_slug',
            'acfe_taxonomies',
            'acfe_taxonomy_terms',
            'acfe_user_roles',
        );
        
        return array_merge($fields, $acfe_fields);
        
    }
    
    
    /**
     * wpgraphql_acf_register_graphql_field
     *
     * @param $field_config
     * @param $type_name
     * @param $field_name
     * @param $config
     *
     * @since 0.8.8.4 (14/06/2021)
     *
     * @return mixed
     */
    function wpgraphql_acf_register_graphql_field($field_config, $type_name, $field_name, $config){
        
        $acf_field = isset($config['acf_field']) ? $config['acf_field'] : null;
        $acf_type  = isset($acf_field['type']) ? $acf_field['type'] : null;
        
        switch($acf_type){
    
            case 'acfe_advanced_link':
            case 'acfe_forms':
            case 'acfe_post_statuses':
            case 'acfe_post_types':
            case 'acfe_taxonomies':
            case 'acfe_taxonomy_terms':
            case 'acfe_user_roles': {
                $field_config['type'] = array('list_of' => 'String');
                break;
            }
    
            case 'acfe_code_editor':
            case 'acfe_column':
            case 'acfe_hidden':
            case 'acfe_slug': {
                $field_config['type'] = 'String';
                break;
            }
    
        }
        
        return $field_config;
        
    }
    
}

new acfe_third_party();

endif;