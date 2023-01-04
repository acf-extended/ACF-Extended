<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_posts')):

class acfe_module_posts{
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acfe/load_posts', array($this, 'load_posts'));
    
    }
    
    
    /**
     * load_posts
     *
     * @param $post_type
     */
    function load_posts($post_type){
    
        // global
        global $module;
    
        // get module
        $module = acfe_query_module(array('post_type' => $post_type));
    
        // validate module
        if(!$module){
            return;
        }
        
        // post statuses
        global $wp_post_statuses;
        $wp_post_statuses['publish']->label_count = _n_noop('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'acf');
    
        add_filter('admin_body_class',                                array($this, 'admin_body_class'));
        add_action('admin_enqueue_scripts',                           array($this, 'admin_enqueue_scripts'));
        add_filter('acfe/localize_data',                              array($this, 'localize_data'));
        add_filter("manage_{$module->post_type}_posts_columns",       array($this, 'manage_columns'));
        add_action("manage_{$module->post_type}_posts_custom_column", array($this, 'manage_columns_html'), 10, 2);
        add_filter('display_post_states',                             array($this, 'display_post_states'), 10, 2);
        add_filter('post_row_actions',                                array($this, 'post_row_actions'), 10, 2);
        add_filter('page_row_actions',                                array($this, 'post_row_actions'), 10, 2);
        add_filter("bulk_actions-edit-{$module->post_type}",          array($this, 'bulk_actions'));
        add_filter("handle_bulk_actions-edit-{$module->post_type}",   array($this, 'handle_bulk_actions'), 10, 3);
        
        // actions
        $module->do_module_action('acfe/module/load_posts');
        
    }
    
    
    /**
     * admin_body_class
     *
     * @param $classes
     *
     * @return string
     */
    function admin_body_class($classes){
        
        global $module;
        
        $classes .= " acfe-module acfe-module-posts acfe-module-{$module->name}";
        return $classes;
        
    }
    
    
    /**
     * admin_enqueue_scripts
     */
    function admin_enqueue_scripts(){
        
        // enqueue acf global js for tooltips
        acf_enqueue_script('acf');
    }
    
    
    /**
     * localize_data
     *
     * acfe/localize_data
     *
     * @param $data
     *
     * @return mixed
     */
    function localize_data($data){
    
        global $module;
        
        $data['module'] = array(
            'name'   => $module->name,
            'screen' => 'posts',
        );
        
        return $data;
        
    }
    
    
    /**
     * manage_columns
     *
     * manage_post_type_posts_columns
     *
     * @param $columns
     *
     * @return mixed
     */
    function manage_columns($columns){
    
        global $module;
        
        unset($columns['date']);
    
        if(!empty($module->columns)){
            $columns = array_merge($columns, $module->columns);
        }
        
        return $module->apply_module_filters('acfe/module/edit_columns', $columns);
        
    }
    
    
    /**
     * manage_columns_html
     *
     * manage_post_type_posts_custom_column
     *
     * @param $column
     * @param $post_id
     */
    function manage_columns_html($column, $post_id){
    
        global $module;
        
        $item = $module->get_raw_item($post_id);
    
        $column_underscore = str_replace('-', '_', $column);
    
        if(method_exists($module, "edit_column_{$column_underscore}")){
            $module->{"edit_column_{$column_underscore}"}($item);
        }
    
        // actions
        $module->do_module_action('acfe/module/edit_columns_html', $column, $item);
        
    }
    
    
    /**
     * display_post_states
     *
     * @param $post_states
     * @param $post
     *
     * @return mixed
     */
    function display_post_states($post_states, $post){
        
        if($post->post_status === 'acf-disabled'){
            $post_states['acf-disabled'] = '<span class="dashicons dashicons-hidden acf-js-tooltip" title="' . _x('Disabled', 'post status', 'acf') . '"></span>';
        }
        
        return $post_states;
        
    }
    
    
    /**
     * post_row_actions
     *
     * page_row_actions
     *
     * @param $actions
     * @param $post
     *
     * @return mixed
     */
    function post_row_actions($actions, $post){
    
        global $module;
        
        // hide on "trash" post status
        if(!in_array($post->post_status, array('publish', 'acf-disabled'))){
            return $actions;
        }
        
        $item = $module->get_item($post->ID);
        
        unset($actions['inline'], $actions['inline hide-if-no-js']);
        
        // View
        if(!empty($module->view)){
            $actions['view'] = '<a href="' . admin_url(sprintf($module->view, $item['name'])) . '">' . __('View') . '</a>';
        }
        
        // Tools
        foreach($module->export_actions as $action){
            $actions[ $action ] = $module->get_export_link($action, $item['name']);
        }
        
        return $actions;
        
    }
    
    
    /**
     * bulk_actions
     *
     * bulk_actions-edit-post_type
     *
     * @param $actions
     *
     * @return mixed
     */
    function bulk_actions($actions){
    
        global $module;
        
        unset($actions['edit']);
        
        foreach($module->export_actions as $action){
            $actions["export_{$action}"] = __('Export', 'acfe') . ' ' . $module->get_export_action_label($action);
        }
        
        return $actions;
        
    }
    
    
    /**
     * handle_bulk_actions
     *
     * handle_bulk_actions-edit-post_type
     *
     * @param $redirect
     * @param $action
     * @param $post_ids
     *
     * @return mixed|void
     */
    function handle_bulk_actions($redirect, $action, $post_ids){
    
        global $module;
        
        $post_ids = acfe_maybe_get_REQUEST('post');
        
        if(!$post_ids){
            return $redirect;
        }
        
        foreach($module->export_actions as $tool_action){
            
            if($action !== "export_{$tool_action}"){
                continue;
            }
            
            $keys = array();
            foreach($post_ids as $post_id){
                
                $item = $module->get_item($post_id);
                
                if($item){
                    $keys[] = $item['name'];
                }
                
            }
            
            wp_redirect($module->get_export_url($tool_action, implode('+', $keys)));
            exit;
            
        }
        
        return $redirect;
        
    }
    
}

acf_new_instance('acfe_module_posts');

endif;