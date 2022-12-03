<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_hooks')):

class acfe_hooks{
    
    public $field_group;
    public $upload_field = false;
    
    /**
     * construct
     */
    function __construct(){
        
        // save/validate hooks
        add_action('acf/save_post',                                array($this, 'pre_save_post'), 9);
        add_action('acf/save_post',                                array($this, 'save_post'), 15);
        add_action('acf/validate_save_post',                       array($this, 'validate_save_post'), 4); // must be 4 as acf process acf/validate_value on 5
        
        // field groups
        add_filter('acf/load_field_groups',                        array($this, 'load_field_groups'), 100);
        add_filter('acf/pre_render_fields',                        array($this, 'pre_render_fields'), 10, 2);
        add_action('acf/render_fields',                            array($this, 'render_fields'), 10, 2);
        
        // fields
        add_filter('acf/field_wrapper_attributes',                 array($this, 'field_wrapper_attributes'), 10, 2);
        add_filter('acf/load_fields',                              array($this, 'load_fields'), 10, 2);
        add_filter('acf/load_field',                               array($this, 'load_field'));
        
        // form data
        add_filter('acf/location/screen',                          array($this, 'acf_location_screen'), 99);
        add_action('acf/input/form_data',                          array($this, 'acf_form_data'));
        add_action('acf/validate_save_post',                       array($this, 'acf_save_post_form_data'), 0);
        add_action('acf/save_post',                                array($this, 'acf_save_post_form_data'), 0);
        
        // upload
        add_filter('acf/upload_prefilter',                         array($this, 'attachment_upload'), 10, 3);
        
        // hooks variations
        acf_add_filter_variations('acfe/prepare_field_group',      array('ID', 'key'), 0);
        acf_add_action_variations('acfe/pre_render_field_group',   array('ID', 'key'), 0);
        acf_add_action_variations('acfe/render_field_group',       array('ID', 'key'), 0);
        acf_add_filter_variations('acf/field_wrapper_attributes',  array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/field_wrapper_attributes', array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/load_fields',              array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/load_field',               array('type', 'name', 'key'), 0);
        acf_add_filter_variations('acfe/upload_dir',               array('type', 'name', 'key'), 1);
        acf_add_filter_variations('acfe/upload_file',              array('type', 'name', 'key'), 1);
        
    }
    
    
    /**
     * pre_save_post
     *
     * acf/save_post:9
     *
     * @param $post_id
     */
    function pre_save_post($post_id = 0){
        $this->do_save_post($post_id, true);
    }
    
    
    /**
     * save_post
     *
     * acf/save_post:15
     *
     * @param $post_id
     */
    function save_post($post_id = 0){
        $this->do_save_post($post_id);
    }
    
    
    /**
     * do_save_post
     *
     * @param $post_id
     * @param $pre
     */
    function do_save_post($post_id = 0, $pre = false){
    
        // validate acf
        if(!acf_maybe_get_POST('acf')){
            return;
        }
        
        // check data
        $data = $this->decode_object($post_id);
        
        if(!$data){
            return;
        }
        
        // vars
        $id = $data['id'];
        $type = $data['type'];
        $object = $data['object'];
        $hooks = $data['hooks'];
        $suffix = $pre ? 'pre_' : false;
        
        // all hooks
        $all_hooks = array();
        $all_hooks[] = "acfe/{$suffix}save";
        $all_hooks[] = "acfe/{$suffix}save/id={$post_id}";
        $all_hooks[] = "acfe/{$suffix}save_{$type}";
        foreach($hooks as $hook){
            $all_hooks[] = "acfe/{$suffix}save_{$type}/{$hook}";
        }
        $all_hooks[] = "acfe/{$suffix}save_{$type}/id={$post_id}";
        
        // check if hooked
        $do_action = false;
        
        foreach($all_hooks as $all_hook){
            if(has_action($all_hook)){
                $do_action = true;
                break;
            }
        }
        
        // bail early
        if(!$do_action){
            return;
        }
        
        // setup meta
        acfe_setup_meta($_POST['acf'], 'acfe/save', true);
    
        foreach($all_hooks as $all_hook){
            do_action($all_hook, $post_id, $object);
        }
        
        // reset meta
        acfe_reset_meta();
        
    }
    
    
    /**
     * validate_save_post
     *
     * acf/validate_save_post:4
     */
    function validate_save_post(){
        
        // vars
        $rows = array();
        
        // acf
        $acf = acf_maybe_get_POST('acf');
        
        if(!empty($acf)){
    
            $post_id = acf_maybe_get_POST('_acf_post_id');
            
            if($post_id){
                $rows[ $post_id ] = $acf;
            }
            
        }
        
        // menu items
        $menu_items = acf_maybe_get_POST('menu-item-acf');
        
        if(!empty($menu_items)){
            foreach($menu_items as $post_id => $fields){
                $rows[ $post_id ] = $fields;
            }
        }
        
        // loop rows
        foreach($rows as $post_id => $acf){
    
            // check data
            $data = $this->decode_object($post_id);
    
            if(!$data){
                continue;
            }
            
            // vars
            $id = $data['id'];
            $type = $data['type'];
            $object = $data['object'];
            $hooks = $data['hooks'];
    
            // all hooks
            $all_hooks = array();
            $all_hooks[] = "acfe/validate_save";
            $all_hooks[] = "acfe/validate_save/id={$post_id}";
            $all_hooks[] = "acfe/validate_save_{$type}";
            foreach($hooks as $hook){
                $all_hooks[] = "acfe/validate_save_{$type}/{$hook}";
            }
            $all_hooks[] = "acfe/validate_save_{$type}/id={$post_id}";
    
            // check if hooked
            $do_action = false;
    
            foreach($all_hooks as $all_hook){
                if(has_action($all_hook)){
                    $do_action = true;
                    break;
                }
            }
    
            // bail early
            if(!$do_action){
                continue;
            }
            
            // setup meta
            acfe_setup_meta($acf, 'acfe/validate_save', true);
    
            foreach($all_hooks as $all_hook){
                do_action($all_hook, $post_id, $object);
            }
            
            // reset meta
            acfe_reset_meta();
            
        }
        
    }
    
    
    /**
     * decode_object
     *
     * @param $post_id
     *
     * @return array|false
     */
    function decode_object($post_id){
    
        //data
        $data = array(
            'id'     => false,
            'type'   => false,
            'object' => false,
            'hooks'  => array(),
        );
    
        /**
         * @string  $post_id  12   | term_46 | user_22 | my-option | comment_89 | widget_56 | menu_74 | menu_item_96 | block_my-block | blog_55 | site_36 | attachment_24
         * @string  $id       12   | 46      | 22      | my-option | 89         | widget_56 | 74      | 96           | block_my-block | 55      | 36      | 24
         * @string  $type     post | term    | user    | option    | comment    | option    | term    | post         | block          | blog    | blog    | post
         */
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
        
        // validate id
        if(!$id){
            return false;
        }
        
        // assign default
        $data['id'] = $id;
        $data['type'] = $type;
        
        switch($type){
            
            // post
            case 'post': {
    
                $post = get_post($id);
                if($post && !is_wp_error($post)){
        
                    $data['object'] = $post;
        
                    if(isset($post->post_type) && post_type_exists($post->post_type)){
                        $data['hooks'][] = "post_type={$post->post_type}";
                    }
        
                }
                
                break;
            }
            
            // term
            case 'term': {
    
                $term = get_term($id);
                if($term && !is_wp_error($term)){
        
                    $data['object'] = $term;
        
                    if(isset($term->taxonomy) && taxonomy_exists($term->taxonomy)){
                        $data['hooks'][] = "taxonomy={$term->taxonomy}";
                    }
        
                }
        
                break;
            }
            
            // user
            case 'user': {
    
                $user = get_user_by('id', $id);
                if($user && !is_wp_error($user)){
        
                    $data['object'] = $user;
        
                    if(isset($user->roles) && !empty($user->roles)){
                        foreach($user->roles as $role){
                            $data['hooks'][] = "role={$role}";
                        }
                    }
        
                }
        
                break;
            }
            
            // option
            case 'option': {
    
                $location = acf_get_form_data('location');
                $options_page = acf_maybe_get($location, 'options_page');
    
                if($options_page){
        
                    $data['object'] = acf_get_options_page($options_page);
                    $data['hooks'][] = "slug={$options_page}";
        
                }
        
                break;
            }
            
            // comment
            case 'comment': {
    
                $comment = get_comment($id);
                if($comment && !is_wp_error($comment)){
                    $data['object'] = $comment;
                }
        
                break;
            }
            
            // block
            case 'block': {
    
                $block = acf_get_block_type("acf/$id");
                if($block){
                    $data['object'] = $block;
                }
        
                break;
            }
            
            // blog
            case 'blog': {
    
                if(function_exists('get_blog_details')){
        
                    $blog = get_blog_details($id);
                    if($blog){
                        $data['object'] = $blog;
                    }
        
                }
        
                break;
            }
            
        }
        
        // return
        return $data;
        
    }
    
    
    /**
     * load_field_groups
     *
     * acf/load_field_groups:100
     *
     * @param $field_groups
     *
     * @return mixed
     */
    function load_field_groups($field_groups){
        
        // bail early
        if(acfe_is_admin_screen()){
            return $field_groups;
        }
        
        // loop
        foreach(array_keys($field_groups) as $i){
            
            // get field group
            $field_group = $field_groups[ $i ];
            
            // apply filters
            $field_group = apply_filters('acfe/prepare_field_group', $field_group);
            
            // hide field group
            if($field_group === false){
                unset($field_groups[ $i ]);
                
            // assign
            }else{
                $field_groups[ $i ] = $field_group;
            }
        
        }
    
        return $field_groups;
        
    }
    
    
    /**
     * pre_render_fields
     *
     * acf/pre_render_fields
     *
     * @param $fields
     * @param $post_id
     *
     * @return mixed
     */
    function pre_render_fields($fields, $post_id){
        
        $this->field_group = array();
        
        if(!isset($fields[0])){
            return $fields;
        }
        
        if(!acf_maybe_get($fields[0], 'parent')){
            return $fields;
        }
        
        $field_group = acf_get_field_group($fields[0]['parent']);
        
        if(!$field_group){
            return $fields;
        }
        
        $this->field_group = $field_group;
        
        // action
        do_action('acfe/pre_render_field_group', $field_group, $fields, $post_id);
        
        return $fields;
        
    }
    
    
    /**
     * render_fields
     *
     * acf/render_fields
     *
     * @param $fields
     * @param $post_id
     */
    function render_fields($fields, $post_id){
        
        if(empty($this->field_group)){
            return;
        }
        
        $field_group = $this->field_group;
        
        // action
        do_action('acfe/render_field_group', $field_group, $fields, $post_id);
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * acf/field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed|void
     */
    function field_wrapper_attributes($wrapper, $field){
    
        return apply_filters('acfe/field_wrapper_attributes', $wrapper, $field);
        
    }
    
    
    /**
     * load_fields
     *
     * acf/load_fields
     *
     * @param $fields
     * @param $parent
     *
     * @return mixed|void
     */
    function load_fields($fields, $parent){
        
        // validate field
        // this fitler is also applied on field groups
        if(!isset($parent['type'])){
            return $fields;
        }
        
        $fields = apply_filters('acfe/load_fields', $fields, $parent);
        
        return $fields;
        
    }
    
    
    /**
     * load_field
     *
     * acf/load_field
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field($field){
    
        // bail early
        if(acfe_is_admin_screen()){
            return $field;
        }
        
        // hooks
        $field = apply_filters('acfe/load_field', $field);
        
        // todo: find a solution to add filter variations with deprecated notice
        // deprecated: admin
        if(acfe_is_admin()){
    
            $field = apply_filters_deprecated("acfe/load_field_admin",                          array($field), '0.8.8', "acfe/load_field");
            $field = apply_filters_deprecated("acfe/load_field_admin/type={$field['type']}",    array($field), '0.8.8', "acfe/load_field/type={$field['type']}");
            $field = apply_filters_deprecated("acfe/load_field_admin/name={$field['name']}",    array($field), '0.8.8', "acfe/load_field/name={$field['name']}");
            $field = apply_filters_deprecated("acfe/load_field_admin/key={$field['key']}",      array($field), '0.8.8', "acfe/load_field/key={$field['key']}");
    
        // deprecated: front
        }else{
    
            $field = apply_filters_deprecated("acfe/load_field_front",                          array($field), '0.8.8', "acfe/load_field");
            $field = apply_filters_deprecated("acfe/load_field_front/type={$field['type']}",    array($field), '0.8.8', "acfe/load_field/type={$field['type']}");
            $field = apply_filters_deprecated("acfe/load_field_front/name={$field['name']}",    array($field), '0.8.8', "acfe/load_field/name={$field['name']}");
            $field = apply_filters_deprecated("acfe/load_field_front/key={$field['key']}",      array($field), '0.8.8', "acfe/load_field/key={$field['key']}");
            
        }
        
        return $field;
        
    }
    
    
    /**
     * acf_location_screen
     *
     * acf/location/screen:99
     *
     * @param $screen
     *
     * @return mixed
     */
    function acf_location_screen($screen){
        
        // clone var
        $_screen = $screen;
        acf_extract_vars($_screen, array('lang', 'ajax'));
        
        // set form data for later use in acf_form_data()
        acf_set_form_data('location', $_screen);
        
        return $screen;
        
    }
    
    
    /**
     * acf_form_data
     *
     * acf/input/form_data
     */
    function acf_form_data(){
        
        // retrieve location from screen filter
        $location = acf_get_form_data('location');
        
        // generate hidden input
        if($location){
            
            foreach($location as $name => $value){
                
                // generate hidden input
                acf_hidden_input(array(
                    'id'	=> "_acf_location[$name]",
                    'name'	=> "_acf_location[$name]",
                    'value'	=> $value
                ));
                
            }
            
        }
    
    }
    
    
    /**
     * acf_save_post_form_data
     *
     * acf/validate_save_post:0
     * acf/save_post:0
     */
    function acf_save_post_form_data(){
        
        // vars
        $screen = acf_maybe_get_POST('_acf_screen', 'post');
        $post_id = acf_maybe_get_POST('_acf_post_id', 0);
        $location = acf_maybe_get_POST('_acf_location', array());
        
        // set form data
        acf_set_form_data(array(
            'screen'    => $screen,
            'post_id'   => $post_id,
            'location'  => $location,
        ));
        
    }
    
    
    /**
     * attachment_upload
     *
     * acf/upload_prefilter
     *
     * @param $errors
     * @param $file
     * @param $field
     *
     * @return mixed
     */
    function attachment_upload($errors, $file, $field){
        
        // vars
        $this->upload_field = $field;
        
        // filters
        add_filter('upload_dir',                 array($this, 'handle_upload_dir'), 20);
        add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_file'), 20);
        
        // return
        return $errors;
        
    }
    
    
    /**
     * handle_upload_dir
     *
     * upload_dir:20
     *
     * @param $uploads
     *
     * @return mixed|void
     */
    function handle_upload_dir($uploads){
        
        // vars
        $field = $this->upload_field;
        
        // return
        return apply_filters('acfe/upload_dir', $uploads, $field);
        
    }
    
    
    /**
     * handle_upload_file
     *
     * wp_handle_upload_prefilter:20
     *
     * @param $file
     *
     * @return mixed|void
     */
    function handle_upload_file($file){
        
        // vars
        $field = $this->upload_field;
        
        // return
        return apply_filters('acfe/upload_file', $file, $field);
        
    }
    
}

new acfe_hooks();

endif;