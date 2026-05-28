<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_hide_on_screen')):

class acfe_field_group_hide_on_screen{
    
    /**
     * construct
     */
    function __construct(){
     
        // field group
        add_action('acf/field_group/admin_head',    array($this, 'admin_head'));
        
        // post metaboxes
        add_action('acf/add_meta_boxes',            array($this, 'acf_add_meta_boxes'), 10, 3);
        add_action('wp_ajax_acf/ajax/check_screen', array($this, 'ajax_check_screen'), 9);
        
        // hide block editor
        add_action('load-post.php',                 array($this, 'hide_block_editor'));
        add_action('load-post-new.php',             array($this, 'hide_block_editor'));
        
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
        add_filter('acf/prepare_field/name=hide_on_screen', array($this, 'prepare_hide_on_screen'));
    }
    
    
    /**
     * prepare_hide_on_screen
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_hide_on_screen($field){
        
        // vars
        $choices = array();
        
        // loop choices
        foreach($field['choices'] as $key => $value){
            
            // prepend block editor choice before content editor
            if($key === 'the_content'){
                $choices['block_editor'] = __('Block Editor');
            }
            
            $choices[ $key ] = $value;
        
        }
        
        // assign choices
        $field['choices'] = $choices;
        
        // return
        return $field;
        
    }
    
    
    /**
     * acf_add_meta_boxes
     *
     * Apply all Field Groups styles
     * Fix ACF only which only use the first Field Group style
     *
     * @param $post_type
     * @param $post
     * @param $field_groups
     */
    function acf_add_meta_boxes($post_type, $post, $field_groups){
        
        // vars
        $styles = '';
        $instance = acf_get_instance('ACF_Form_Post');
        
        // loop through field groups
        foreach($field_groups as $field_group){
            $styles .= acf_get_field_group_style($field_group);
        }
        
        // assign styles to ACF_Form_Post instance
        $instance->style = $styles;
        
    }
    
    
    /**
     * ajax_check_screen
     *
     * Merges hide on screen settings instead of using the first field group style only
     */
    function ajax_check_screen(){
        
        // get ajax check screen instance & simulate request
        $instance = acf_get_instance('ACF_Ajax_Check_Screen');
        $instance->request = wp_unslash($_REQUEST);
        
        // get response from ACF core
        $response = $instance->get_response($instance->request);
    
        // vars
        $args = wp_parse_args($instance->request, array(
            'screen'    => '',
            'post_id'   => 0,
            'ajax'      => true,
            'exists'    => array()
        ));
    
        // get field groups
        $field_groups = acf_get_field_groups($args);
    
        // loop through field groups
        if($field_groups){
            
            // reset style to merge all field groups styles instead of using the first one only
            $response['style'] = '';
            
            // merge styles instead of using only the first field group rules
            foreach($field_groups as $i => $field_group){
                $response['style'] .= acf_get_field_group_style($field_group);
            }
            
        }
    
        // verify error and send request based on ACF_Ajax->request() method
        $error = $instance->verify_request($instance->request);
        if(is_wp_error($error)){
            $instance->send($error);
        }
    
        // send response
        $instance->send($response);
    
    }
    
    
    /**
     * hide_block_editor
     */
    function hide_block_editor(){
        
        // globals
        global $typenow;
        
        // restricted post types
        $restricted = array('acf-field-group', 'attachment');
        if(in_array($typenow, $restricted)){
            return;
        }
        
        // vars
        $post_id = 0;
        $post_type = $typenow;
        
        if(isset($_GET['post'])){
            $post_id = (int) $_GET['post'];
            
        }elseif(isset($_POST['post_ID'])){
            $post_id = (int) $_POST['post_ID'];
        }
        
        // get field groups
        $field_groups = acf_get_field_groups(array(
            'post_id'   => $post_id,
            'post_type' => $post_type
        ));
        
        // default var
        $hide_block_editor = false;
        
        // loop field groups
        foreach($field_groups as $field_group){
            
            // get hide on screen value
            $hide_on_screen = acfe_as_array($field_group['hide_on_screen']);
            
            // check if block editor is hidden
            if(in_array('block_editor', $hide_on_screen, true)){
                $hide_block_editor = true;
                break;
            }
            
        }
        
        // hide block editor
        if($hide_block_editor){
            add_filter('use_block_editor_for_post_type', '__return_false');
        }
        
    }
    
}

// initialize
new acfe_field_group_hide_on_screen();

endif;