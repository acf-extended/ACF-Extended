<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_post')):

class acfe_module_post{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('add_meta_boxes',        array($this, 'add_meta_boxes'), 5, 2);
        add_action('trashed_post',          array($this, 'trashed_post'));
        add_action('untrashed_post',        array($this, 'untrashed_post'));
        add_action('deleted_post',          array($this, 'deleted_post'));
        add_filter('post_updated_messages', array($this, 'post_updated_messages'));
    
    }
    
    
    /**
     * add_meta_boxes
     *
     * add_meta_boxes
     *
     * @param $post_type
     * @param $post
     */
    function add_meta_boxes($post_type, $post){
    
        // globals
        global $item, $module;
    
        // get module
        $module = acfe_get_module_by_item($post->ID);
        
        if(!$module){
            return;
        }
        
        $item = $module->get_item($post->ID);
    
        add_filter('admin_body_class',                  array($this, 'admin_body_class'));
        add_action('acf/input/admin_enqueue_scripts',   array($this, 'admin_enqueue_scripts'));
        add_filter('acfe/localize_data',                array($this, 'localize_data'));
        add_action('post_submitbox_misc_actions',       array($this, 'post_submitbox_misc_actions'));
        add_filter('submenu_file',                      array($this, 'submenu_file'));
        
        // remove native wp slug metabox
        remove_meta_box('slugdiv', $module->post_type, 'normal');
        
        // register field groups
        foreach($module->get_field_groups() as $field_group){
            acf_add_local_field_group($field_group);
        }
    
        // actions
        $module->do_module_action('acfe/module/load_post');
        
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
        
        $classes .= " acfe-module acfe-module-post acfe-module-{$module->name}";
        return $classes;
        
    }
    
    
    /**
     * admin_enqueue_scripts
     *
     * acf/input/admin_enqueue_scripts
     */
    function admin_enqueue_scripts(){
        
        // no autosave
        wp_dequeue_script('autosave');
        
        // remove default 'draft' post status on post-new.php
        // this fix an issue when user press 'enter' when creating a new item
        global $post;
        $post->post_status = 'publish';
        
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
        
        global $module, $item;
        
        $data['module'] = array(
            'name'     => $module->name,
            'screen'   => 'post',
            'messages' => array(
                'status' => $item['active'] ? __('Active', 'acf') : __('Inactive', 'acf'),
                'label'  => sprintf(__('%s value is required', 'acf'), $module->get_label('enter_title')),
            ),
        );
        
        return $data;
        
    }
    
    
    /**
     * post_submitbox_misc_actions
     */
    function post_submitbox_misc_actions(){
        
        global $module, $item;
        
        $links = $module->get_export_links($item);
        
        if($links): ?>
            <div class="misc-pub-section acfe-misc-export">
                <span class="dashicons dashicons-editor-code"></span>
                <?php _e('Export', 'acfe'); ?>: <?php echo implode(' ', $links); ?>
            </div>
        <?php endif;
        
    }
    
    
    /**
     * submenu_file
     * @return string
     */
    function submenu_file(){
    
        global $module;
        return "edit.php?post_type={$module->post_type}";
        
    }
    
    
    /**
     * trashed_post
     *
     * @param $post_id
     */
    function trashed_post($post_id){
    
        // get module
        $module = acfe_get_module_by_item($post_id);
        
        if($module){
            $module->trash_item($post_id);
        }
        
    }
    
    
    /**
     * untrashed_post
     *
     * @param $post_id
     */
    function untrashed_post($post_id){
    
        // get module
        $module = acfe_get_module_by_item($post_id);
        
        if($module){
            $module->untrash_item($post_id);
        }
        
    }
    
    
    /**
     * deleted_post
     *
     * @param $post_id
     */
    function deleted_post($post_id){
        
        // get module
        $module = acfe_get_module_by_item($post_id);
        
        if($module){
            $module->delete_item($post_id);
        }
        
    }
    
    
    /**
     * post_updated_messages
     *
     * @param $messages
     *
     * @return mixed
     */
    function post_updated_messages($messages){
        
        /*
         * 0  => '', // Unused. Messages start at index 1.
         * 1  => __( 'Post updated.' ) . $view_post_link_html,
         * 2  => __( 'Custom field updated.' ),
         * 3  => __( 'Custom field deleted.' ),
         * 4  => __( 'Post updated.' ),
         * 5  => isset( $_GET['revision'] ) ? sprintf( __( 'Post restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
         * 6  => __( 'Post published.' ) . $view_post_link_html,
         * 7  => __( 'Post saved.' ),
         * 8  => __( 'Post submitted.' ) . $preview_post_link_html,
         * 9  => sprintf( __( 'Post scheduled for: %s.' ), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
         * 10 => __( 'Post draft updated.' ) . $preview_post_link_html,
         */
        
        $modules = acfe_get_modules();
        
        foreach($modules as $module){
    
            // label
            $label = $module->get_label();
    
            // append to messages
            $messages[ $module->post_type ] = array(
                0  => '', // unused. messages start at index 1
                1  => sprintf(__('%s updated.', 'acfe'),        $label),
                2  => sprintf(__('%s updated.', 'acfe'),        $label),
                3  => sprintf(__('%s deleted.', 'acfe'),        $label),
                4  => sprintf(__('%s updated.', 'acfe'),        $label),
                5  => false, // no revisions support
                6  => sprintf(__('%s published.', 'acfe'),      $label),
                7  => sprintf(__('%s saved.', 'acfe'),          $label),
                8  => sprintf(__('%s submitted.', 'acfe'),      $label),
                9  => sprintf(__('%s scheduled.', 'acfe'),      $label),
                10 => sprintf(__('%s draft updated.', 'acfe'),  $label),
            );
        
        }
        
        // return
        return $messages;
        
    }
    
}

acf_new_instance('acfe_module_post');

endif;