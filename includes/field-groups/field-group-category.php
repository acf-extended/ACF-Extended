<?php

if(!defined('ABSPATH'))
    exit;

add_action('init', 'acfe_field_group_category_register');
function acfe_field_group_category_register(){
    
    register_taxonomy('acf-field-group-category', array('acf-field-group'), array(
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => 'ACFE',
        'show_admin_column' => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'rewrite'           => false,
        'labels'            => array(
            'name'              => _x('Categories', 'Category'),
            'singular_name'     => _x('Categories', 'Category'),
            'search_items'      => __('Search categories', 'acfe'),
            'all_items'         => __('All categories', 'acfe'),
            'parent_item'       => __('Parent category', 'acfe'),
            'parent_item_colon' => __('Parent category:', 'acfe'),
            'edit_item'         => __('Edit category', 'acfe'),
            'update_item'       => __('Update category', 'acfe'),
            'add_new_item'      => __('Add New category', 'acfe'),
            'new_item_name'     => __('New category name', 'acfe'),
            'menu_name'         => __('category', 'acfe'),
        ),
    ));
    
}

add_action('admin_menu', 'acfe_field_group_category_submenu');
function acfe_field_group_category_submenu(){
    
    if(!acf_get_setting('show_admin'))
        return;
    
    add_submenu_page('edit.php?post_type=acf-field-group', __('Categories'), __('Categories'), acf_get_setting('capability'), 'edit-tags.php?taxonomy=acf-field-group-category');
    
}

add_filter('parent_file', 'acfe_field_group_category_submenu_highlight');
function acfe_field_group_category_submenu_highlight($parent_file){
    
	global $submenu_file, $current_screen, $pagenow;
    
	if($current_screen->taxonomy === 'acf-field-group-category' && ($pagenow === 'edit-tags.php' || $pagenow === 'term.php'))
        $parent_file = 'edit.php?post_type=acf-field-group';
    
	return $parent_file;
    
}

add_filter('manage_edit-acf-field-group_columns', 'acfe_field_group_category_column', 11);
function acfe_field_group_category_column($columns){
    
    $new_columns = array();
    foreach($columns as $key => $value) {
        if($key === 'title')
            $new_columns['acf-field-group-category'] = __('Categories');
        
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
    
}

add_action('manage_acf-field-group_posts_custom_column' , 'acfe_field_group_category_column_html', 10, 2);
function acfe_field_group_category_column_html($column, $post_id){
    
    if($column === 'acf-field-group-category'){
        if(!$terms = get_the_terms($post_id, 'acf-field-group-category')){
            echo '—';
            return;
        }
        
        $categories = array();
        foreach($terms as $term){
            $categories[] = '<a href="' . admin_url('edit.php?acf-field-group-category='.$term->slug.'&post_type=acf-field-group') . '">' . $term->name . '</a>';
        }
        
        echo implode(' ', $categories);
    }
    
}

add_filter('views_edit-acf-field-group', 'acfe_field_group_category_views', 9);
function acfe_field_group_category_views($views){
    
    if(!$terms = get_terms('acf-field-group-category', array('hide_empty' => false)))
        return $views;
    
    foreach($terms as $term){
        
        $groups = get_posts( array(
            'post_type'         => 'acf-field-group',
            'posts_per_page'    => -1,
            'suppress_filters'  => false,
			'post_status'       => array('publish', 'acf-disabled'),
            'taxonomy'          => 'acf-field-group-category',
            'term'              => $term->slug,
            'fields'            => 'ids'
        ));

        $count = count($groups);
        
        $html = '';
        if($count > 0)
            $html = ' <span class="count">(' . $count . ')</span>';
        
        global $wp_query;
        $class = '';
        if(isset($wp_query->query_vars['acf-field-group-category']) && $wp_query->query_vars['acf-field-group-category'] === $term->slug)
            $class = ' class="current"';
        
        $views['category-' . $term->slug] = '<a href="' . admin_url('edit.php?acf-field-group-category=' . $term->slug . '&post_type=acf-field-group') . '"' . $class . '>' . $term->name . $html . '</a>';
    }
    
    return $views;
    
}

/**
 * ACF Exclude Field Group Category from available taxonomies
 */
add_filter('acf/get_taxonomies', 'acfe_field_group_category_exclude', 10, 2);
function acfe_field_group_category_exclude($taxonomies, $args){
    
    if(empty($taxonomies))
        return $taxonomies;
    
    foreach($taxonomies as $k => $taxonomy){
        
        if($taxonomy != 'acf-field-group-category')
            continue;
        
        unset($taxonomies[$k]);
        
    }
    
    return $taxonomies;
    
}