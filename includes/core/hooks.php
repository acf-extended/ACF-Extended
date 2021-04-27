<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_hooks')):

class acfe_hooks{
    
    public $field_group;
    
    function __construct(){
        
        // General
        add_action('acf/save_post',                                 array($this, 'pre_save_post'), 9);
        add_action('acf/save_post',                                 array($this, 'save_post'), 15);
        add_action('acf/validate_save_post',                        array($this, 'validate_save_post'));
        
        // Field Groups
        add_filter('acf/load_field_groups',                         array($this, 'load_field_groups'), 100);
        add_filter('acf/pre_render_fields',                         array($this, 'pre_render_fields'), 10, 2);
        add_action('acf/render_fields',                             array($this, 'render_fields'), 10, 2);
        
        // Fields
        add_filter('acf/field_wrapper_attributes',                  array($this, 'field_wrapper_attributes'), 10, 2);
        add_filter('acf/load_fields',                               array($this, 'load_fields'), 10, 2);
        add_filter('acf/load_field',                                array($this, 'load_field'));
        
        // Options Page
        add_action('acf/input/form_data',                           array($this, 'form_data'));
        
    }
    
    function pre_save_post($post_id = 0){
    
        $this->do_save_post($post_id, true);
    
    }
    
    function save_post($post_id = 0){
    
        $this->do_save_post($post_id);
    
    }
    
    function do_save_post($post_id = 0, $pre = false){
    
        // Validate acf
        if(!acf_maybe_get_POST('acf'))
            return;
        
        // Check data
        $data = $this->decode_object($post_id);
        
        if(!$data)
            return;
        
        // Vars
        $id = $data['id'];
        $type = $data['type'];
        $object = $data['object'];
        $hooks = $data['hooks'];
        $suffix = $pre ? 'pre_' : false;
        
        // All hooks
        $all_hooks = array();
        $all_hooks[] = "acfe/{$suffix}save";
        $all_hooks[] = "acfe/{$suffix}save/id={$post_id}";
        $all_hooks[] = "acfe/{$suffix}save_{$type}";
        foreach($hooks as $hook){
            $all_hooks[] = "acfe/{$suffix}save_{$type}/{$hook}";
        }
        $all_hooks[] = "acfe/{$suffix}save_{$type}/id={$post_id}";
        
        // Check if hooked
        $do_action = false;
        
        foreach($all_hooks as $all_hook){
            
            if(!has_action($all_hook)) continue;
            
            $do_action = true;
            break;
            
        }
        
        // Bail early
        if(!$do_action)
            return;
        
        // Setup Meta
        acfe_setup_meta($_POST['acf'], 'acfe/save', true);
    
        foreach($all_hooks as $all_hook){
    
            do_action($all_hook, $post_id, $object);
        
        }
    
        acfe_reset_meta();
        
    }
    
    function validate_save_post(){
        
        // vars
        $rows = array();
        
        // General
        $acf = acf_maybe_get_POST('acf');
        
        if(!empty($acf)){
    
            $post_id = acf_maybe_get_POST('_acf_post_id');
            
            if($post_id){
                $rows[$post_id] = $acf;
            }
            
        }
        
        // Menu Items
        $menu_items = acf_maybe_get_POST('menu-item-acf');
        
        if(!empty($menu_items)){
            
            foreach($menu_items as $post_id => $fields){
                $rows[$post_id] = $fields;
            }
            
        }
        
        foreach($rows as $post_id => $acf){
    
            // Check data
            $data = $this->decode_object($post_id);
    
            if(!$data)
                continue;
            
            // Vars
            $id = $data['id'];
            $type = $data['type'];
            $object = $data['object'];
            $hooks = $data['hooks'];
    
            // All hooks
            $all_hooks = array();
            $all_hooks[] = "acfe/validate_save";
            $all_hooks[] = "acfe/validate_save/id={$post_id}";
            $all_hooks[] = "acfe/validate_save_{$type}";
            foreach($hooks as $hook){
                $all_hooks[] = "acfe/validate_save_{$type}/{$hook}";
            }
            $all_hooks[] = "acfe/validate_save_{$type}/id={$post_id}";
    
            // Check if hooked
            $do_action = false;
    
            foreach($all_hooks as $all_hook){
        
                if(!has_action($all_hook)) continue;
        
                $do_action = true;
                break;
        
            }
    
            // Bail early
            if(!$do_action)
                continue;
            
            // Setup Meta
            acfe_setup_meta($acf, 'acfe/validate_save', true);
    
            foreach($all_hooks as $all_hook){
        
                do_action($all_hook, $post_id, $object);
        
            }
            
            // Reset meta
            acfe_reset_meta();
            
        }
        
    }
    
    function decode_object($post_id){
    
        //vars
        $id = false;
        $type = false;
        $data = array(
            'id'        => false,
            'type'      => false,
            'object'    => false,
            'hooks'     => array(),
        );
    
        /*
         * @string  $post_id  12   | term_46 | user_22 | my-option | comment_89 | widget_56 | menu_74 | menu_item_96 | block_my-block | blog_55 | site_36 | attachment_24
         * @string  $id       12   | 46      | 22      | my-option | 89         | widget_56 | 74      | 96           | block_my-block | 55      | 36      | 24
         * @string  $type     post | term    | user    | option    | comment    | option    | term    | post         | block          | blog    | blog    | post
         */
        extract(acf_decode_post_id($post_id));
    
        // Validate ID
        if(!$id)
            return false;
        
        $data['id'] = $id;
        $data['type'] = $type;
        
        // Post
        if($type === 'post'){
        
            $post = get_post($id);
        
            if($post && !is_wp_error($post)){
    
                $data['object'] = $post;
            
                if(isset($post->post_type) && post_type_exists($post->post_type)){
                    $data['hooks'][] = "post_type={$post->post_type}";
                }
            
            }
        
        // Term
        }elseif($type === 'term'){
        
            $term = get_term($id);
        
            if($term && !is_wp_error($term)){
    
                $data['object'] = $term;
            
                if(isset($term->taxonomy) && taxonomy_exists($term->taxonomy)){
                    $data['hooks'][] = "taxonomy={$term->taxonomy}";
                }
            
            }
        
        // User
        }elseif($type === 'user'){
        
            $user = get_user_by('id', $id);
        
            if($user && !is_wp_error($user)){
    
                $data['object'] = $user;
            
                if(isset($user->roles) && !empty($user->roles)){
                
                    foreach($user->roles as $role){
                        $data['hooks'][] = "role={$role}";
                    }
                
                }
            
            }
    
        // Option
        }elseif($type === 'option'){
    
            $options_page = acf_maybe_get_POST('_acf_options_page');
            
            if($options_page){
    
                $data['object'] = acf_get_options_page($options_page);
    
                $data['hooks'][] = "slug={$options_page}";
                
            }
    
        // Comment
        }elseif($type === 'comment'){
        
            $comment = get_comment($id);
        
            if($comment && !is_wp_error($comment)){
    
                $data['object'] = $comment;
            
            }
        
        // Block
        }elseif($type === 'block'){
        
            $block = acf_get_block_type("acf/$id");
        
            if($block){
    
                $data['object'] = $block;
            
            }
        
        // Blog
        }elseif($type === 'blog'){
        
            if(function_exists('get_blog_details')){
            
                $blog = get_blog_details($id);
            
                if($blog){
    
                    $data['object'] = $blog;
                
                }
            
            }
        
        }
        
        return $data;
        
    }
    
    /*
     * Load Field Groups
     */
    function load_field_groups($field_groups){
        
        // Do not execute in ACF Field Group UI
        if(acfe_is_admin_screen())
            return $field_groups;
        
        foreach($field_groups as $i => &$field_group){
    
            $field_group = apply_filters("acfe/prepare_field_group", $field_group);
            
            if(isset($field_group['ID']))
                $field_group = apply_filters("acfe/prepare_field_group/ID={$field_group['ID']}", $field_group);
    
            if(isset($field_group['key']))
                $field_group = apply_filters("acfe/prepare_field_group/key={$field_group['key']}", $field_group);
            
            // Do not render if false
            if($field_group === false)
                unset($field_groups[$i]);
        
        }
    
        return $field_groups;
        
    }
    
    /*
     * Pre Render Fields
     */
    function pre_render_fields($fields, $post_id){
        
        $this->field_group = array();
        
        if(!isset($fields[0]))
            return $fields;
        
        if(!acf_maybe_get($fields[0], 'parent'))
            return $fields;
        
        $field_group = acf_get_field_group($fields[0]['parent']);
        
        if(!$field_group)
            return $fields;
        
        $this->field_group = $field_group;
        
        do_action("acfe/pre_render_field_group",                            $field_group, $fields, $post_id);
        do_action("acfe/pre_render_field_group/ID={$field_group['ID']}",    $field_group, $fields, $post_id);
        do_action("acfe/pre_render_field_group/key={$field_group['key']}",  $field_group, $fields, $post_id);
        
        return $fields;
        
    }
    
    /*
     * Pre Render Fields
     */
    function render_fields($fields, $post_id){
        
        if(empty($this->field_group))
            return;
        
        $field_group = $this->field_group;
        
        do_action("acfe/render_field_group",                            $field_group, $fields, $post_id);
        do_action("acfe/render_field_group/ID={$field_group['ID']}",    $field_group, $fields, $post_id);
        do_action("acfe/render_field_group/key={$field_group['key']}",  $field_group, $fields, $post_id);
        
    }
    
    /*
     *  Field Wrapper Attributes
     */
    function field_wrapper_attributes($wrapper, $field){
        
        $wrapper = apply_filters("acfe/field_wrapper_attributes",                       $wrapper, $field);
        $wrapper = apply_filters("acfe/field_wrapper_attributes/type={$field['type']}", $wrapper, $field);
        $wrapper = apply_filters("acfe/field_wrapper_attributes/name={$field['name']}", $wrapper, $field);
        $wrapper = apply_filters("acfe/field_wrapper_attributes/key={$field['key']}",   $wrapper, $field);
        
        return $wrapper;
        
    }
    
    /*
     *  Load Fields
     */
    function load_fields($fields, $parent){
        
        // check if field (fitler is also called on field groups)
        if(!acf_maybe_get($parent, 'type'))
            return $fields;
        
        $fields = apply_filters("acfe/load_fields",                         $fields, $parent);
        $fields = apply_filters("acfe/load_fields/type={$parent['type']}",  $fields, $parent);
        $fields = apply_filters("acfe/load_fields/name={$parent['name']}",  $fields, $parent);
        $fields = apply_filters("acfe/load_fields/key={$parent['key']}",    $fields, $parent);
        
        return $fields;
        
    }
    
    /*
     *  Load Field
     */
    function load_field($field){
    
        // Do not execute in ACF Field Group UI
        if(acfe_is_admin_screen())
            return $field;
        
        // Hooks
        $field = apply_filters("acfe/load_field",                       $field);
        $field = apply_filters("acfe/load_field/type={$field['type']}", $field);
        $field = apply_filters("acfe/load_field/name={$field['name']}", $field);
        $field = apply_filters("acfe/load_field/key={$field['key']}",   $field);
        
        
        // Deprecated: Admin
        if(acfe_is_admin()){
    
            $field = apply_filters_deprecated("acfe/load_field_admin",                          array($field), '0.8.8', "acfe/load_field");
            $field = apply_filters_deprecated("acfe/load_field_admin/type={$field['type']}",    array($field), '0.8.8', "acfe/load_field/type={$field['type']}");
            $field = apply_filters_deprecated("acfe/load_field_admin/name={$field['name']}",    array($field), '0.8.8', "acfe/load_field/name={$field['name']}");
            $field = apply_filters_deprecated("acfe/load_field_admin/key={$field['key']}",      array($field), '0.8.8', "acfe/load_field/key={$field['key']}");
            
        }
        
        // Deprecated: Front
        else{
    
            $field = apply_filters_deprecated("acfe/load_field_front",                          array($field), '0.8.8', "acfe/load_field");
            $field = apply_filters_deprecated("acfe/load_field_front/type={$field['type']}",    array($field), '0.8.8', "acfe/load_field/type={$field['type']}");
            $field = apply_filters_deprecated("acfe/load_field_front/name={$field['name']}",    array($field), '0.8.8', "acfe/load_field/name={$field['name']}");
            $field = apply_filters_deprecated("acfe/load_field_front/key={$field['key']}",      array($field), '0.8.8', "acfe/load_field/key={$field['key']}");
            
        }
        
        return $field;
        
    }
    
    /*
     * Form Data for Options Page
     */
    function form_data($data){
        
        if(acf_maybe_get($data, 'screen') !== 'options')
            return;
    
        global $plugin_page;
        
        if(!$plugin_page)
            return;
    
        acf_hidden_input(array(
            'id'    => '_acf_options_page',
            'name'  => '_acf_options_page',
            'value' => $plugin_page
        ));
        
    }
    
}

new acfe_hooks();

endif;