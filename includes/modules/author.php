<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/author', true))
    return;

/**
 * Register Field Group
 */
add_filter('acf/get_field_groups', 'acfe_author_field_group_permissions', 999);
function acfe_author_field_group_permissions($field_groups){
    
    if(!is_admin())
        return $field_groups;
    
    $check_current_screen = acf_is_screen(array(
        'edit-acf-field-group',
        'acf-field-group',
        'acf_page_acf-tools'
    ));
    
    if($check_current_screen)
        return $field_groups;
    
    global $post;
    
    // Get Post ID
    $post_id = get_the_ID();
    
    if(empty($post_id) && isset($_REQUEST['post']))
        $post_id = (int) $_REQUEST['post'];
    
    if(empty($post_id) && isset($post->ID))
        $post_id = $post->ID;
    
    if(empty($post_id))
        return $field_groups;
    
    // Get Post Type Object
    $post_type_object = get_post_type_object(get_post_type($post_id));
    if(empty($post_type_object))
        return $field_groups;
    
    foreach($field_groups as $key => $field_group){
        
        if($field_group['key'] != 'group_acfe_author')
            continue;
        
        if(!current_user_can($post_type_object->cap->edit_others_posts))
            unset($field_groups[$key]);
        
    }
    
    return $field_groups;
    
}

/**
 * Register Author Field
 */
add_action('admin_init', 'acfe_author_field_group');
function acfe_author_field_group(){
    
    // Get Post Types Locations
    $get_post_types = get_post_types_by_support('author');
    if(empty($get_post_types))
        return;
    
    // Set Locations
    $locations = array();
    
    foreach($get_post_types as $post_type){
        
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

/**
 * Remove Native WP Metabox
 */
add_action('admin_menu','acfe_author_remove_default_metabox');
function acfe_author_remove_default_metabox(){
    
    $get_post_types = get_post_types_by_support('author');
    if(empty($get_post_types))
        return;
    
    foreach($get_post_types as $post_type){
        
        if(in_array($post_type, array('attachment', 'revision', 'customize_changeset')))
            continue;
        
        // Remove Metabox
        remove_meta_box('authordiv', $post_type, 'normal');
        
    }
    
}

/**
 * Prepare Default Value
 */
add_filter('acf/prepare_field/name=acfe_author', 'acfe_author_prepare');
function acfe_author_prepare($field){
    
    // Get Post ID
    $post_id = get_the_ID();
    if(empty($post_id))
        return false;
    
    // Check Post Type & Permissions
    $post_type_object = get_post_type_object(get_post_type($post_id));
    if(empty($post_type_object) || !current_user_can($post_type_object->cap->edit_others_posts))
        return false;
    
    // Set Default
    $author_id = get_post_field('post_author', $post_id);
    $field['value'] = $author_id;
    
    return $field;
    
}

/**
 * Save Post Action
 */
add_action('acf/save_post', 'acfe_author_post_save', 0);
function acfe_author_post_save($post_id){
    
    // Check Field Exists
    if(!isset($_POST['acf']['acfe_author']))
        return;
    
    // Check Post Type & Permissions
    $post_type_object = get_post_type_object(get_post_type($post_id));
    if(empty($post_type_object) || !current_user_can($post_type_object->cap->edit_others_posts))
        return;
    
    // Set & Validate Author
    $author_id = (int) $_POST['acf']['acfe_author'];
    if(!get_user_by('ID', $author_id))
        return;
    
    // Update Post Author
    wp_update_post(array(
        'ID'            => $post_id,
        'post_author'   => $author_id
    ));
    
}

/**
 * Bypass Metadata Update
 */
add_filter('acf/pre_update_value', 'acfe_author_meta_update', 10, 4);
function acfe_author_meta_update($return, $value, $post_id, $field){
    
    if($field['name'] === 'acfe_author')
        return false;
    
    return $return;
    
}

/**
 * Field Group Hide on Screen
 */
add_filter('acf/get_field_group_style', 'acfe_author_meta_hide_on_screen', 10, 2);
function acfe_author_meta_hide_on_screen($style, $field_group){
    
    $style = str_replace('authordiv', 'acf-group_acfe_author', $style);
    $style = str_replace('display: none;', 'display: none !important;', $style);
    
    return $style;
    
}