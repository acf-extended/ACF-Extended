<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Plugin: Post Types Order
 * https://wordpress.org/plugins/post-types-order/
 * The plugin apply custom order to 'acf-field-group' Post Type. We have to fix this
 */
add_filter('pto/posts_orderby/ignore', 'acfe_compatibility_pto_acf_field_group', 10, 3);
function acfe_compatibility_pto_acf_field_group($ignore, $orderby, $query){
    
    if(is_admin() && $query->is_main_query() && $query->get('post_type') === 'acf-field-group')
        $ignore = true;

    return $ignore;
    
}

/**
 * Plugin: Category Order and Taxonomy Terms Order
 * https://wordpress.org/plugins/taxonomy-terms-order/
 * The plugin add a submenu to 'Custom Fields' to order Field Group Categories. It's unecessary
 */
add_action('admin_menu', 'acfe_compatibility_cotto_submenu', 999);
function acfe_compatibility_cotto_submenu(){
    
	remove_submenu_page('edit.php?post_type=acf-field-group', 'to-interface-acf-field-group');
    
}

/**
 * Plugin: Rank Math SEO
 * https://wordpress.org/plugins/seo-by-rank-math/
 * Fix the plugin post metabox which is always above ACF metaboxes
 */
add_filter('rank_math/metabox/priority', 'acfe_compatibility_rankmath_metaboxes_priority');
function acfe_compatibility_rankmath_metaboxes_priority(){
    
    return 'default';
    
}

/**
 * Plugin: YOAST SEO
 * https://wordpress.org/plugins/wordpress-seo/
 * Fix the plugin post metabox which is always above ACF metaboxes
 */
add_filter('wpseo_metabox_prio', 'acfe_compatibility_yoast_metaboxes_priority');
function acfe_compatibility_yoast_metaboxes_priority(){
    
    return 'default';
    
}