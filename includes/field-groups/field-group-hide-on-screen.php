<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_group_hide_on_screen')):

class acfe_field_group_hide_on_screen{
 
    function __construct(){
     
        // Field Group
        add_action('acf/field_group/admin_head',    array($this, 'admin_head'));
        
        // Post Metaboxes
        add_action('acf/add_meta_boxes',            array($this, 'acf_add_meta_boxes'), 10, 3);
        
        // Hide Block Editor
        add_action('load-post.php',                 array($this, 'hide_block_editor'));
        add_action('load-post-new.php',             array($this, 'hide_block_editor'));
        
    }
    
    function admin_head(){
        
        add_filter('acf/prepare_field/name=hide_on_screen', array($this, 'prepare_hide_on_screen'));
        
    }
    
    /*
     * Hide on screen: Settings
     */
    function prepare_hide_on_screen($field){
    
        $choices = array();
    
        foreach($field['choices'] as $key => $value){
        
            if($key === 'the_content'){
            
                $choices['block_editor'] = __('Block Editor');
            
            }
        
        
            $choices[$key] = $value;
        
        }
    
        $field['choices'] = $choices;
    
        return $field;
        
    }
    
    /*
     * Add Metaboxes: Apply all Field Groups styles
     * Fix ACF only which only use the first Field Group style
     */
    function acf_add_meta_boxes($post_type, $post, $field_groups){
        
        $instance = acf_get_instance('ACF_Form_Post');
        
        $styles = '';
        
        foreach($field_groups as $field_group){
        
            $styles .= acf_get_field_group_style($field_group);
            
        }
    
        $instance->style = $styles;
        
    }
    
    /*
     * Hide Block Editor
     */
    function hide_block_editor(){
        
        // globals
        global $typenow;
        
        // Restrict
        $restricted = array('acf-field-group', 'attachment');
        
        if(in_array($typenow, $restricted))
            return;
        
        $post_type = $typenow;
        $post_id = 0;
        
        if(isset( $_GET['post'])){
            
            $post_id = (int) $_GET['post'];
            
        }elseif(isset($_POST['post_ID'])){
            
            $post_id = (int) $_POST['post_ID'];
            
        }
        
        $field_groups = acf_get_field_groups(array(
            'post_id'   => $post_id,
            'post_type' => $post_type
        ));
        
        $hide_block_editor = false;
        
        foreach($field_groups as $field_group){
            
            $hide_on_screen = acf_get_array($field_group['hide_on_screen']);
            
            if(!in_array('block_editor', $hide_on_screen))
                continue;
            
            $hide_block_editor = true;
            break;
            
        }
        
        if($hide_block_editor){
            
            add_filter('use_block_editor_for_post_type', '__return_false');
            
        }
        
    }
    
}

// initialize
new acfe_field_group_hide_on_screen();

endif;