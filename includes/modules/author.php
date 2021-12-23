<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/author'))
    return;

if(!class_exists('acfe_author')):

class acfe_author{
    
    public $post_types = array();
    
    function __construct(){
        
        acf_add_local_field(array(
            'label'                 => '',
            'key'                   => 'field_acfe_author',
            'name'                  => 'acfe_author',
            'type'                  => 'user',
            'instructions'          => '',
            'required'              => 0,
            'conditional_logic'     => 0,
            'allow_null'            => 0,
            'multiple'              => 0,
            'roles'                 => $this->get_roles(),
            'return_format'         => 'array',
        ));
        
        add_action('acfe/add_post_meta_boxes',  array($this, 'add_post_meta_boxes'), 10, 2);
        add_filter('wp_insert_post_data',       array($this, 'wp_insert_post_data'), 10, 2);
        add_filter('acf/get_field_group_style', array($this, 'get_field_group_style'), 10, 2);
        
    }
    
    /*
     * Add Post Meta Boxes
     */
    function add_post_meta_boxes($post_type, $post){
    
        // disable on block editor
        if(acfe_is_block_editor()){
            return;
        }
    
        // validate author supports
        if(!post_type_supports($post_type, 'author')){
            return;
        }
        
        // post type object
        $post_type_object = get_post_type_object($post_type);
    
        // check permission
        if(!current_user_can($post_type_object->cap->edit_others_posts)){
            return;
        }
        
        // remove legacy authordiv
        remove_meta_box('authordiv', $post_type, 'normal');
        
        // add metabox
        add_meta_box('acfe-author', __('Author'), array($this, 'render_meta_box'), $post_type, 'side', 'core', array());
        
        // generate postbox
        // $postboxes = array();
        // $postboxes[] = array(
        //     'id' => 'acfe-author',
        // );
        
        // get postboxes
        // $data = acf_get_instance('ACF_Assets')->data;
        // $acf_postboxes = acf_maybe_get($data, 'postboxes', array());
        // $acf_postboxes = array_merge($acf_postboxes, $postboxes);
        
        // localize postboxes
        // acf_localize_data(array(
        //     'postboxes' => $acf_postboxes
        // ));
        
    }
    
    /*
     * Render Meta Box
     */
    function render_meta_box($post, $metabox){
        
        // retrieve field
        $field = acf_get_field('acfe_author');
        
        // add value
        $field['prefix'] = '';
        $field['value'] = get_post_field('post_author', $post->ID);
        
        // render field
        acf_render_field_wrap($field);
        
    }
    
    /*
     * WP Insert Post Data
     */
    function wp_insert_post_data($data, $post_array){
        
        // check field exists
        if(!acf_maybe_get($post_array, 'field_acfe_author')){
            return $data;
        }
    
        // authors
        $post_author = (int) acf_maybe_get($post_array, 'field_acfe_author');
        $_post_author = (int) acf_maybe_get($post_array, 'post_author');
    
        // check if author has been changed
        if($_post_author === $post_author){
            return $data;
        }
    
        // validate author
        if(!get_user_by('ID', $post_author)){
            return $data;
        }
        
        // set new author
        $data['post_author'] = $post_author;
        
        return $data;
        
    }
    
    /*
     * Get Field Group Style
     */
    function get_field_group_style($style, $field_group){
        
        $style = str_replace('authordiv', 'acfe-author', $style);
        $style = str_replace('display: none;', 'display: none !important;', $style);
        
        return $style;
        
    }
    
    /*
     * Get Roles
     */
    function get_roles(){
    
        $roles = array();
    
        foreach(wp_roles()->roles as $name => $role){
        
            // check capability
            if(empty($role['capabilities']['level_1'])) continue;
        
            $roles[] = $name;
        
        }
        
        return $roles;
        
    }
    
}

// initialize
new acfe_author();

endif;