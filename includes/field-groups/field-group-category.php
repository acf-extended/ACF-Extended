<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/categories'))
    return;

if(!class_exists('acfe_field_group_category')):

class acfe_field_group_category{
    
    function __construct(){
    
        add_action('init',                                          array($this, 'init'), 9);
        add_action('admin_menu',                                    array($this, 'admin_menu'));
        add_filter('parent_file',                                   array($this, 'parent_file'));
        add_filter('manage_edit-acf-field-group_columns',           array($this, 'columns'), 11);
        add_action('manage_acf-field-group_posts_custom_column' ,   array($this, 'column_html'), 10, 2);
        add_filter('views_edit-acf-field-group',                    array($this, 'views'), 9);
        add_filter('acf/get_taxonomies',                            array($this, 'acf_get_taxonomies'), 10, 2);
    
        add_filter('acf/prepare_field_group_for_export',            array($this, 'prepare_for_export'));
        add_action('acf/import_field_group',                        array($this, 'prepare_for_import'));
        
    }
    
    /*
     * Register Taxonomy
     */
    function init(){
        
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
    
    /*
     * Admin Menu
     */
    function admin_menu(){
        
        if(!acf_get_setting('show_admin'))
            return;
        
        add_submenu_page('edit.php?post_type=acf-field-group', __('Categories'), __('Categories'), acf_get_setting('capability'), 'edit-tags.php?taxonomy=acf-field-group-category');
        
    }
    
    /*
     * Menu Parent File
     */
    function parent_file($parent_file){
        
        global $submenu_file, $current_screen, $pagenow;
        
        if($current_screen->taxonomy === 'acf-field-group-category' && ($pagenow === 'edit-tags.php' || $pagenow === 'term.php'))
            $parent_file = 'edit.php?post_type=acf-field-group';
        
        return $parent_file;
        
    }
    
    /*
     * ACF Field Group: Columns
     */
    function columns($columns){
        
        $new_columns = array();
        foreach($columns as $key => $value) {
            if($key === 'title')
                $new_columns['acf-field-group-category'] = __('Categories');
            
            $new_columns[$key] = $value;
        }
        
        return $new_columns;
        
    }
    
    /*
     * ACF Field Group: Column HTML
     */
    function column_html($column, $post_id){
        
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
    
    /*
     * ACF Field Group: Views
     */
    function views($views){
        
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
    
    /*
     * ACF Exclude Field Group Category from available taxonomies
     */
    function acf_get_taxonomies($taxonomies, $args){
        
        if(empty($taxonomies))
            return $taxonomies;
        
        foreach($taxonomies as $k => $taxonomy){
            
            if($taxonomy != 'acf-field-group-category')
                continue;
            
            unset($taxonomies[$k]);
            
        }
        
        return $taxonomies;
        
    }
    
    /*
     * Prepare Export
     */
    function prepare_for_export($field_group){
        
        $_field_group = acf_get_field_group($field_group['key']);
        
        if(empty($_field_group))
            return $field_group;
        
        if(!acf_maybe_get($_field_group, 'ID'))
            return $field_group;
        
        $categories = get_the_terms($_field_group['ID'], 'acf-field-group-category');
        
        if(empty($categories) || is_wp_error($categories))
            return $field_group;
        
        $field_group['acfe_categories'] = array();
        
        foreach($categories as $term){
            
            $field_group['acfe_categories'][$term->slug] = $term->name;
            
        }
        
        return $field_group;
        
    }
    
    /*
     * Prepare Import
     */
    function prepare_for_import($field_group){
        
        if(!$categories = acf_maybe_get($field_group, 'acfe_categories'))
            return;
        
        foreach($categories as $term_slug => $term_name){
            
            $new_term_id = false;
            $get_term = get_term_by('slug', $term_slug, 'acf-field-group-category');
            
            // Term doesn't exists
            if(empty($get_term)){
                
                $new_term = wp_insert_term($term_name, 'acf-field-group-category', array(
                    'slug' => $term_slug
                ));
                
                if(!is_wp_error($new_term)){
                    
                    $new_term_id = $new_term['term_id'];
                    
                }
                
                // Term already exists
            }else{
                
                $new_term_id = $get_term->term_id;
                
            }
            
            if($new_term_id){
                
                wp_set_post_terms($field_group['ID'], array($new_term_id), 'acf-field-group-category', true);
                
            }
            
        }
        
    }
    
}

// initialize
new acfe_field_group_category();

endif;