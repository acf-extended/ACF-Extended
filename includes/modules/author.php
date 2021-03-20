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
        
        add_action('init',                                  array($this, 'init'), 999);
        add_action('admin_menu',                            array($this, 'admin_menu'));
        
        add_action('acf/save_post',                         array($this, 'save_post'), 20);
        add_filter('acf/load_value/name=acfe_author',       array($this, 'load_value'), 10, 3);
        add_filter('acf/pre_update_value',                  array($this, 'update_value'), 10, 4);
        
        add_filter('acf/get_field_group_style',             array($this, 'hide_on_screen'), 10, 2);
        
    }
    
    function init(){
        
        // Get Post Types Locations
        $get_post_types = get_post_types_by_support('author');
        if(empty($get_post_types))
            return;
        
        foreach($get_post_types as $post_type){
            
            if(in_array($post_type, array('attachment', 'revision', 'customize_changeset')))
                continue;
            
            $post_type_object = get_post_type_object($post_type);
            
            if(!current_user_can($post_type_object->cap->edit_others_posts))
                continue;
            
            $this->post_types[] = $post_type;
            
        }
        
        if(!empty($this->post_types)){
            
            // Locations init
            $locations = array();
            
            foreach($this->post_types as $post_type){
                
                // Set Location
                $locations[] = array(
                    array(
                        'param'     => 'post_type',
                        'operator'  => '==',
                        'value'     => $post_type,
                    )
                );
        
            }
            
            // Roles
            global $wp_roles;

            $authors_roles = array();
            foreach($wp_roles->roles as $role_name => $role){
                
                if(!isset($role['capabilities']['level_1']) || empty($role['capabilities']['level_1']))
                    continue;
                
                $authors_roles[] = $role_name;
                
            }
            
            /**
             * Add Local Field Group
             */
            acf_add_local_field_group(array(
                'title'                 => __('Author'),
                'key'                   => 'group_acfe_author',
                'menu_order'            => 99999,
                'position'              => 'side',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => 1,
                'description'           => '',
                'location'              => $locations,
                'fields'                => array(
                    array(
                        'label'                 => '',
                        'key'                   => 'acfe_author',
                        'name'                  => 'acfe_author',
                        'type'                  => 'user',
                        'instructions'          => '',
                        'required'              => 0,
                        'conditional_logic'     => 0,
                        'allow_null'            => 0,
                        'multiple'              => 0,
                        'return_format'         => 'array',
                        'role'                  => $authors_roles,
                        'wrapper'               => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        )
                    ),
                )
            ));
            
        }
        
    }
    
    /**
     * Remove Legacy Authordiv
     */
    function admin_menu(){
        
        foreach($this->post_types as $post_type){
            
            // Remove Metabox
            remove_meta_box('authordiv', $post_type, 'normal');
    
        }
        
    }
    
    /**
     * Save Post Action
     */
    function save_post($post_id){
        
        // Check Field Exists
        if(!isset($_POST['acf']['acfe_author']))
            return;
        
        $post_author = (int) $_POST['acf']['acfe_author'];
        $_post_author = (int) get_post_field('post_author', $post_id);
        
        // Check if author has been changed
        if($_post_author === $post_author)
            return;
        
        $post_type = get_post_type($post_id);
        if(!in_array($post_type, $this->post_types))
            return false;
        
        // Validate Author
        if(!get_user_by('ID', $post_author))
            return;
        
        remove_action('post_updated', 'wp_save_post_revision');
        
        // Update Post Author
        wp_update_post(array(
            'ID'            => $post_id,
            'post_author'   => $post_author
        ));
        
    }
    
    /**
     * Load Default Value
     */
    function load_value($value, $post_id, $field){
        
        $post_type = get_post_type($post_id);
        if(!in_array($post_type, $this->post_types))
            return false;
        
        // Set Default
        $author_id = get_post_field('post_author', $post_id);
        $value = $author_id;
        
        return $value;
        
    }
    
    /**
     * Bypass Metadata Update
     */
    function update_value($return, $value, $post_id, $field){
        
        if($field['name'] === 'acfe_author')
            return false;
        
        return $return;
        
    }
    
    /**
     * Field Group Hide on Screen
     */
    function hide_on_screen($style, $field_group){
        
        $style = str_replace('authordiv', 'acf-group_acfe_author', $style);
        $style = str_replace('display: none;', 'display: none !important;', $style);
        
        return $style;
        
    }
    
}

// initialize
new acfe_author();

endif;