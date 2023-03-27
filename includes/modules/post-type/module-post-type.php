<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_post_type')):

class acfe_module_post_type extends acfe_module{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name         = 'post_type';
        $this->plural       = 'post_types';
        $this->setting      = 'modules/post_types';
        $this->settings     = 'modules.post_types';
        $this->view         = 'edit.php?post_type=%s';
        $this->register     = 'init';
        
        $this->post_type    = 'acfe-dpt';
        $this->args         = array(
            'label'             => __('Post Types', 'acfe'),
            'show_in_menu'      => 'tools.php',
            'labels'            => array(
                'name'          => __('Post Types', 'acfe'),
                'singular_name' => __('Post Type', 'acfe'),
                'menu_name'     => __('Post Types', 'acfe'),
                'edit_item'     => __('Edit Post Type', 'acfe'),
                'add_new_item'  => __('New Post Type', 'acfe'),
                'enter_title'   => __('Post Type Label', 'acfe'),
            ),
        );
        
        $this->messages     = array(
            'export_title'              => __('Export Post Types', 'acfe'),
            'export_description'        => __('Export Post Types', 'acfe'),
            'export_select'             => __('Select Post Types', 'acfe'),
            'export_not_found'          => __('No post type available.', 'acfe'),
            'export_not_selected'       => __('No post types selected', 'acfe'),
            'export_success_single'     => __('1 post type exported', 'acfe'),
            'export_success_multiple'   => __('%s post types exported', 'acfe'),
            'export_instructions'       => sprintf(__('It is recommended to include this code within the <code>init</code> hook (<a href="%s" target="blank">see documentation</a>).', 'acfe'), esc_url('https://developer.wordpress.org/reference/functions/register_post_type/')),
            'import_title'              => __('Import Post Types', 'acfe'),
            'import_description'        => __('Import Post Types', 'acfe'),
            'import_success_single'     => __('1 post type imported', 'acfe'),
            'import_success_multiple'   => __('%s post types imported', 'acfe'),
        );
    
        $this->export_files = array(
            'single'    => 'post-type',
            'multiple'  => 'post-types',
        );
    
        $this->validate = array('name');
    
        $this->columns  = array(
            'acfe-name'         => __('Name', 'acfe'),
            'acfe-taxonomies'   => __('Taxonomies', 'acfe'),
            'acfe-posts'        => __('Posts', 'acfe'),
        );
    
        $this->item     = array(
            'name'                  => '',
            'label'                 => '',
            'active'                => true,
            'description'           => '',
            'hierarchical'          => false,
            'supports'              => array('title', 'editor'),
            'taxonomies'            => array(),
            'public'                => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'can_export'            => true,
            'delete_with_user'      => null,
            'labels'                => array(),
            'menu_position'         => null,
            'menu_icon'             => 'dashicons-admin-post',
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'has_archive'           => true,
            'rewrite'               => true,
            'capability_type'       => 'post',
            'capabilities'          => array(),
            'map_meta_cap'          => null,
            'show_in_rest'          => false,
            'rest_base'             => null,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'acfe_archive_template' => null,
            'acfe_archive_ppp'      => 10,
            'acfe_archive_orderby'  => 'date',
            'acfe_archive_order'    => 'DESC',
            'acfe_archive_meta_key' => '',
            'acfe_archive_meta_type'=> '',
            'acfe_single_template'  => null,
            'acfe_admin_archive'    => false,
            'acfe_admin_ppp'        => 10,
            'acfe_admin_orderby'    => 'date',
            'acfe_admin_order'      => 'DESC',
            'acfe_admin_meta_key'   => '',
            'acfe_admin_meta_type'  => '',
        );
        
        $this->l10n = array('label', 'description', 'labels');
        
    }
    
    
    /**
     * register_item
     *
     * acfe/module/register_item
     *
     * @param $item
     */
    function register_item($item){
        
        // validate
        if(!empty($item['name']) && !post_type_exists($item['name'])){
            register_post_type($item['name'], $item);
        }
        
    }
    
    
    /**
     * load_post
     *
     * acfe/module/load_post
     */
    function load_post(){
        flush_rewrite_rules(false);
    }
    
    
    /**
     * imported_item
     *
     * acfe/module/imported_item
     *
     * @param $item
     */
    function imported_item($item){
        flush_rewrite_rules(false);
    }
    
    
    /**
     * trashed_item
     *
     * acfe/module/trashed_item
     *
     * @param $id
     */
    function trashed_item($id){
        flush_rewrite_rules(false);
    }
    
    
    /**
     * untrashed_item
     *
     * acfe/module/untrashed_item
     *
     * @param $id
     */
    function untrashed_item($id){
        flush_rewrite_rules(false);
    }
    
    
    /**
     * validate_name
     *
     * @param $value
     * @param $item
     *
     * @return false|string
     */
    function validate_name($value, $item){
        
        // editing current post type
        if($item['name'] === $value){
            return true;
        }
        
        // check sibiling post types (could be disabled)
        $sibiling_item = $this->get_item($value);
        
        if($sibiling_item && $sibiling_item['ID'] !== $item['ID']){
            return __('This post type already exists', 'acfe');
        }
        
        // reserved wp post types
        // see: https://codex.wordpress.org/Function_Reference/register_post_type#Reserved_Post_Types
        $exclude = array('post', 'posts', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'action', 'author', 'order', 'theme');
        $exclude = array_merge($exclude, acfe_get_setting('reserved_post_types', array()));
        
        // check if reserved name
        if(in_array($value, $exclude)){
            return __('This post type is reserved', 'acfe');
        }
        
        // check existing post types
        global $wp_post_types;
        
        foreach((array) $wp_post_types as $post_type){
            
            // post type already exists
            if($value === $post_type->name){
                return __('This post type already exists', 'acfe');
            }
            
        }
        
        return true;
        
    }
    
    
    /**
     * prepare_load_item
     *
     * acfe/module/prepare_load_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_load_item($item){
        
        // menu: show in menu
        if($item['show_in_menu'] && is_string($item['show_in_menu'])){
            
            $item['show_in_menu_text'] = $item['show_in_menu'];
            $item['show_in_menu'] = true;
            
        }
        
        // archive: has archive
        if($item['has_archive'] && is_string($item['has_archive'])){
            
            $item['has_archive_slug'] = $item['has_archive'];
            $item['has_archive'] = true;
            
        }
        
        // single: rewrite
        if($item['rewrite'] && is_array($item['rewrite'])){
            
            $item['rewrite_args'] = $item['rewrite'];
            $item['rewrite'] = true;
            $item['rewrite_args_select'] = true;
            
        }
        
        return $item;
        
    }
    
    
    /**
     * prepare_save_item
     *
     * acfe/module/prepare_save_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_item($item){
        
        // general: taxonomies
        $item['taxonomies'] = acf_get_array($item['taxonomies']);
        
        // general: supports
        if(empty($item['supports'])){
            $item['supports'] = false;
        }
        
        // menu: menu position
        if(!acf_is_empty($item['menu_position'])){
            $item['menu_position'] = (int) $item['menu_position'];
        }
        
        // menu: show in menu
        if($item['show_in_menu'] && !empty($item['show_in_menu_text'])){
            $item['show_in_menu'] = $item['show_in_menu_text'];
        }
        
        // archive: has archive
        if($item['has_archive'] && $item['has_archive_slug']){
            $item['has_archive'] = $item['has_archive_slug'];
        }
        
        // single: rewrite
        if($item['rewrite'] && $item['rewrite_args_select']){
            $item['rewrite'] = $item['rewrite_args'];
        }
        
        if(is_array($item['capability_type']) && count($item['capability_type']) === 1){
            $item['capability_type'] = current($item['capability_type']);
        }
        
        // return
        return $item;
        
    }
    
    
    /**
     * edit_column_acfe_name
     *
     * @param $item
     */
    function edit_column_acfe_name($item){
        echo '<code style="font-size: 12px;">' . $item['name'] . '</code>';
    }
    
    
    /**
     * edit_column_acfe_taxonomies
     *
     * @param $item
     */
    function edit_column_acfe_taxonomies($item){
        
        $text = '—';
        
        if(empty($item['taxonomies'])){
            echo $text;
            return;
        }
        
        $taxonomies = array();
        
        foreach($item['taxonomies'] as $taxonomy){
            if(taxonomy_exists($taxonomy)){
                $taxonomies[] = $taxonomy;
            }
        }
        
        if($taxonomies){
            
            $labels = acf_get_taxonomy_labels($taxonomies);
            
            if(!empty($labels)){
                $text = implode(', ', $labels);
            }
            
        }
        
        echo $text;
        
    }
    
    
    /**
     * edit_column_acfe_posts
     *
     * @param $item
     */
    function edit_column_acfe_posts($item){
        
        // vars
        $text = '—';
        
        if(!post_type_exists($item['name'])){
            echo $text;
            return;
        }
        
        $count = wp_count_posts($item['name']);
        
        if(!empty($count) && isset($count->publish)){
            
            $count_publish = $count->publish;
            $text = '<a href="' . admin_url('edit.php?post_type=' . $item['name']) . '">' . $count_publish . '</a>';
            
        }
        
        echo $text;
        
    }
    
    
    /**
     * export_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_code($code, $args){
        return "register_post_type('{$args['name']}', {$code});";
    }
    
    
    /**
     * export_local_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_local_code($code, $args){
        return "acfe_register_post_type({$code});";
    }
    
}

acfe_register_module('acfe_module_post_type');

endif;

function acfe_register_post_type($item){
    acfe_get_module('post_type')->add_local_item($item);
}