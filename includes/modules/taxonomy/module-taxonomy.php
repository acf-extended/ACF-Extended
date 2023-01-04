<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_taxonomy')):

class acfe_module_taxonomy extends acfe_module{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name         = 'taxonomy';
        $this->plural       = 'taxonomies';
        $this->setting      = 'modules/taxonomies';
        $this->settings     = 'modules.taxonomies';
        $this->view         = 'edit-tags.php?taxonomy=%s';
        $this->register     = 'init';
    
        $this->post_type    = 'acfe-dt';
        $this->args         = array(
            'label'             => __('Taxonomies', 'acfe'),
            'show_in_menu'      => 'tools.php',
            'labels'            => array(
                'name'          => __('Taxonomies', 'acfe'),
                'singular_name' => __('Taxonomy', 'acfe'),
                'menu_name'     => __('Taxonomies', 'acfe'),
                'edit_item'     => __('Edit Taxonomy', 'acfe'),
                'add_new_item'  => __('New Taxonomy', 'acfe'),
                'enter_title'   => __('Taxonomy Label', 'acfe'),
            ),
        );
    
        $this->messages     = array(
            'export_title'              => __('Export Taxonomies', 'acfe'),
            'export_description'        => __('Export Taxonomies', 'acfe'),
            'export_select'             => __('Select Taxonomies', 'acfe'),
            'export_not_found'          => __('No taxonomy available.', 'acfe'),
            'export_not_selected'       => __('No taxonomies selected', 'acfe'),
            'export_success_single'     => __('1 taxonomy exported', 'acfe'),
            'export_success_multiple'   => __('%s taxonomies exported', 'acfe'),
            'export_instructions'       => sprintf(__('It is recommended to include this code within the <code>init</code> hook (<a href="%s" target="blank">see documentation</a>).', 'acfe'), esc_url('https://developer.wordpress.org/reference/functions/register_taxonomy/')),
            'import_title'              => __('Import Taxonomies', 'acfe'),
            'import_description'        => __('Import Taxonomies', 'acfe'),
            'import_success_single'     => __('1 taxonomy imported', 'acfe'),
            'import_success_multiple'   => __('%s taxonomies imported', 'acfe'),
        );
    
        $this->export_files = array(
            'single'    => 'taxonomy',
            'multiple'  => 'taxonomies',
        );
    
        $this->validate = array('name');
    
        $this->columns  = array(
            'acfe-name'         => __('Name', 'acf'),
            'acfe-post-types'   => __('Post Types', 'acf'),
            'acfe-terms'        => __('Terms', 'acf'),
        );
    
        $this->item     = array(
            'name'                  => '',
            'label'                 => '',
            'active'                => true,
            'post_types'            => array(),
            'description'           => '',
            'hierarchical'          => false,
            'public'                => true,
            'publicly_queryable'    => true,
            'update_count_callback' => '',
            'meta_box_cb'           => null,
            'sort'                  => false,
            'labels'                => array(),
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_tagcloud'         => true,
            'show_in_quick_edit'    => true,
            'show_admin_column'     => true,
            'rewrite'               => true,
            'show_in_rest'          => false,
            'rest_base'             => '',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'acfe_single_template'  => null,
            'acfe_single_ppp'       => 10,
            'acfe_single_orderby'   => 'date',
            'acfe_single_order'     => 'DESC',
            'acfe_single_meta_key'  => '',
            'acfe_single_meta_type' => '',
            'acfe_admin_ppp'        => 10,
            'acfe_admin_orderby'    => 'name',
            'acfe_admin_order'      => 'ASC',
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
        if(!empty($item['name']) && !taxonomy_exists($item['name'])){
            register_taxonomy($item['name'], $item['post_types'], $item);
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
     * @param $item
     */
    function trashed_item($item){
        flush_rewrite_rules(false);
    }
    
    
    /**
     * untrashed_item
     *
     * acfe/module/untrashed_item
     *
     * @param $item
     */
    function untrashed_item($item){
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
        
        // editing current term
        if($item['name'] === $value){
            return false;
        }
        
        // check sibiling taxonomies (could be disabled)
        $sibiling_item = $this->get_item($value);
        
        if($sibiling_item && $sibiling_item['ID'] !== $item['ID']){
            return __('This taxonomy already exists', 'acfe');
        }
        
        // reserved wp taxonomies
        // see: https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
        $exclude = array('attachment',
            'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'fields', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments', 'year',);
        $exclude = array_merge($exclude, acfe_get_setting('reserved_taxonomies', array()));
        
        // check if reserved name
        if(in_array($value, $exclude)){
            return __('This taxonomy is reserved', 'acfe');
        }
        
        // check existing taxonomies
        global $wp_taxonomies;
        
        foreach((array) $wp_taxonomies as $taxonomy){
            
            // taxonomy already exists
            if($value === $taxonomy->name){
                return __('This taxonomy already exists', 'acfe');
            }
            
        }
        
        // return
        return false;
        
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
        
        // general: meta_box_cb
        if(is_string($item['meta_box_cb']) && $item['meta_box_cb'] !== 'null' && $item['meta_box_cb'] !== 'false'){
            
            $item['meta_box_cb_custom'] = $item['meta_box_cb'];
            $item['meta_box_cb'] = 'custom';
            
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
        
        // general: post types
        $item['post_types'] = acf_get_array($item['post_types']);
        
        // general: meta_box_cb
        if($item['meta_box_cb'] === 'custom'){
            $item['meta_box_cb'] = $item['meta_box_cb_custom'];
        }
        
        // single: rewrite
        if($item['rewrite'] && $item['rewrite_args_select']){
            $item['rewrite'] = $item['rewrite_args'];
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
     * edit_column_acfe_post_types
     *
     * @param $item
     */
    function edit_column_acfe_post_types($item){
        
        $text = '—';
        
        if(empty($item['post_types'])){
            echo $text;
            return;
        }
        
        $post_types = array();
        
        foreach($item['post_types'] as $post_type){
            if(post_type_exists($post_type)){
                $post_types[] = $post_type;
            }
        }
        
        if($post_types){
            
            $labels = acf_get_pretty_post_types($post_types);
            
            if(!empty($labels)){
                
                $output = array();
                
                foreach($labels as $post_type => $label){
                    $output[] = '<a href="' . admin_url("edit.php?post_type={$post_type}") . '">' . $label . '</a>';
                }
                
                $text = implode(', ', $output);
                
            }
            
        }
        
        echo $text;
        
    }
    
    
    /**
     * edit_column_acfe_terms
     *
     * @param $item
     */
    function edit_column_acfe_terms($item){
        
        // vars
        $text = '—';
        
        if(!taxonomy_exists($item['name'])){
            echo $text;
            return;
        }
        
        $count = wp_count_terms($item['name'], array(
            'hide_empty' => false
        ));
        
        if(!is_wp_error($count) && !empty($count)){
            $text = '<a href="' . admin_url("edit-tags.php?taxonomy={$item['name']}") . '">' . $count . '</a>';
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
        
        $post_types = acfe_var_export($args['post_types']);
        return "register_taxonomy('{$args['name']}', {$post_types}, {$code});";
        
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
        return "acfe_register_taxonomy({$code});";
    }
    
}

acfe_register_module('acfe_module_taxonomy');

endif;

function acfe_register_taxonomy($item){
    acfe_get_module('taxonomy')->add_local_item($item);
}