<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_post')):

class acfe_form_post{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/post',                                           array($this, 'load'), 1, 3);
        add_action('acfe/form/prepare/post',                                        array($this, 'prepare'), 1, 3);
        add_action('acfe/form/submit/post',                                         array($this, 'submit'), 10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_post_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_post_load_meta',               array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_type',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_status',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_title',          array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_name',           array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_content',        array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_author',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_parent',         array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_post_map_post_terms',          array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/render_field/name=acfe_form_post_advanced_load',            array($this, 'advanced_load'));
        add_filter('acf/render_field/name=acfe_form_post_advanced_save_args',       array($this, 'advanced_save_args'));
        add_filter('acf/render_field/name=acfe_form_post_advanced_save',            array($this, 'advanced_save'));
        
    }
    
    function load($form, $post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_post_load_values');
        $load_source = get_sub_field('acfe_form_post_load_source');
        $load_meta = get_sub_field('acfe_form_post_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
        
        $_post_type = get_sub_field('acfe_form_post_map_post_type');
        $_post_status = get_sub_field('acfe_form_post_map_post_status');
        $_post_title = get_sub_field('acfe_form_post_map_post_title');
        $_post_name = get_sub_field('acfe_form_post_map_post_name');
        $_post_content = get_sub_field('acfe_form_post_map_post_content');
        $_post_author = get_sub_field('acfe_form_post_map_post_author');
        $_post_parent = get_sub_field('acfe_form_post_map_post_parent');
        $_post_terms = get_sub_field('acfe_form_post_map_post_terms');
        
        $_post_id = 0;
        
        // Custom Post ID
        if($load_source !== 'current_post'){
            
            $_post_id = $load_source;
        
        }
        
        // Current Post
        elseif($load_source === 'current_post'){
            
            if($post_info['type'] === 'post')
                $_post_id = $post_info['id'];
            
        }
        
        $_post_id = apply_filters('acfe/form/load/post_id',                      $_post_id, $form, $action);
        $_post_id = apply_filters('acfe/form/load/post_id/form=' . $form_name,   $_post_id, $form, $action);
        
        if(!empty($action))
            $_post_id = apply_filters('acfe/form/load/post_id/action=' . $action, $_post_id, $form, $action);
        
        // Invalid Post ID
        if(!$_post_id)
            return $form;
        
        // Post type
        if(acf_is_field_key($_post_type)){
            
            $key = array_search($_post_type, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_type]['value'] = get_post_field('post_type', $_post_id);
                
            }
            
        }
        
        // Post status
        if(acf_is_field_key($_post_status)){
            
            $key = array_search($_post_type, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_status]['value'] = get_post_field('post_status', $_post_id);
                
            }
            
        }
        
        // Post title
        if(acf_is_field_key($_post_title)){
            
            $key = array_search($_post_title, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_post_title]['value'] = get_post_field('post_title', $_post_id);
            
            }
            
        }
        
        // Post name
        if(acf_is_field_key($_post_name)){
            
            $key = array_search($_post_name, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_name]['value'] = get_post_field('post_name', $_post_id);
            
            }
            
        }
        
        // Post content
        if(acf_is_field_key($_post_content)){
            
            $key = array_search($_post_content, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_content]['value'] = get_post_field('post_content', $_post_id);
            
            }
            
        }
        
        // Post author
        if(acf_is_field_key($_post_author)){
            
            $key = array_search($_post_author, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_author]['value'] = get_post_field('post_author', $_post_id);
            
            }
            
        }
        
        // Post parent
        if(acf_is_field_key($_post_parent)){
            
            $key = array_search($_post_parent, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                $form['map'][$_post_parent]['value'] = get_post_field('post_parent', $_post_id);
            
            }
            
        }
        
        // Post terms
        if(acf_is_field_key($_post_terms)){
            
            $key = array_search($_post_terms, $load_meta);
            
            if($key !== false){
            
                unset($load_meta[$key]);
                
                $taxonomies = acf_get_taxonomies(array(
                    'post_type' => get_post_type($_post_id)
                ));
                
                if(!empty($taxonomies)){
                    
                    $terms = array();
                    
                    foreach($taxonomies as $taxonomy){
                        
                        $get_the_terms = get_the_terms($_post_id, $taxonomy);
                        if(!$get_the_terms || is_wp_error($get_the_terms))
                            continue;
                        
                        $terms = array_merge($terms, $get_the_terms);
                        
                    }
                    
                    $return = wp_list_pluck($terms, 'term_id');
                    
                    $form['map'][$_post_terms]['value'] = $return;
                    
                }
                
                
            
            }
            
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                $form['map'][$field_key]['value'] = acf_get_value($_post_id, $field);
                
            }
            
        }
        
        return $form;
        
    }
    
    function prepare($form, $post_id, $action){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $post_action = get_sub_field('acfe_form_post_action');
        
        // Mapping
        $map = array(
            'post_type'     => get_sub_field('acfe_form_post_map_post_type'),
            'post_status'   => get_sub_field('acfe_form_post_map_post_status'),
            'post_title'    => get_sub_field('acfe_form_post_map_post_title'),
            'post_name'     => get_sub_field('acfe_form_post_map_post_name'),
            'post_content'  => get_sub_field('acfe_form_post_map_post_content'),
            'post_author'   => get_sub_field('acfe_form_post_map_post_author'),
            'post_parent'   => get_sub_field('acfe_form_post_map_post_parent'),
            'post_terms'    => get_sub_field('acfe_form_post_map_post_terms'),
        );
        
        // Fields
        $_target = get_sub_field('acfe_form_post_save_target');
        
        $_post_type = get_sub_field('acfe_form_post_save_post_type');
        $_post_status = get_sub_field('acfe_form_post_save_post_status');
        
        $_post_title_group = get_sub_field('acfe_form_post_save_post_title_group');
        $_post_title = $_post_title_group['acfe_form_post_save_post_title'];
        $_post_title_custom = $_post_title_group['acfe_form_post_save_post_title_custom'];
        
        $_post_name_group = get_sub_field('acfe_form_post_save_post_name_group');
        $_post_name = $_post_name_group['acfe_form_post_save_post_name'];
        $_post_name_custom = $_post_name_group['acfe_form_post_save_post_name_custom'];
        
        $_post_content_group = get_sub_field('acfe_form_post_save_post_content_group');
        $_post_content = $_post_content_group['acfe_form_post_save_post_content'];
        $_post_content_custom = $_post_content_group['acfe_form_post_save_post_content_custom'];
        
        $_post_author = get_sub_field('acfe_form_post_save_post_author');
        $_post_parent = get_sub_field('acfe_form_post_save_post_parent');
        $_post_terms = get_sub_field('acfe_form_post_save_post_terms');
        
        $_post_id = 0;
        
        // Insert Post
        if($post_action === 'insert_post'){
            
            $temp_title = false;
            
            // Post title
            if(!empty($map['post_title'])){
                
                $temp_title = acfe_form_map_field_value($map['post_title'], $_POST['acf'], $_post_id);
                
            }elseif($_post_title === 'generated_id'){
                
                $temp_title = true;
                
            }elseif($_post_title === 'custom'){
                
                $temp_title = acfe_form_map_field_value($_post_title_custom, $_POST['acf'], $_post_id);
                
            }
            
            if(!empty($temp_title)){
                
                $_post_id = wp_insert_post(array(
                    'post_title' => 'Post'
                ));
            
            }
            
        }
        
        // Update Post
        elseif($post_action === 'update_post'){
            
            // Custom Post ID
            if($_target !== 'current_post'){
                
                $_post_id = $_target;
            
            }
            
            // Current Post
            elseif($_target === 'current_post'){
                
                if($post_info['type'] === 'post')
                    $_post_id = $post_info['id'];
                
            }
            
        }
        
        // Invalid Post ID
        if(!$_post_id)
            return;
        
        $args = array();
        
        // ID
        $args['ID'] = $_post_id;
        
        // Post type
        if(!empty($map['post_type'])){
            
            $args['post_type'] = acfe_form_map_field_value($map['post_type'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_type)){
            
            $args['post_type'] = $_post_type;
        
        }
        
        // Post status
        if(!empty($map['post_status'])){
            
            $args['post_status'] = acfe_form_map_field_value($map['post_status'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_status)){
            
            $args['post_status'] = $_post_status;
        
        }
        
        // Post title
        if(!empty($map['post_title'])){
            
            $args['post_title'] = acfe_form_map_field_value($map['post_title'], $_POST['acf'], $_post_id);
            
        }elseif($_post_title === 'generated_id'){
            
            $args['post_title'] = $_post_id;
            
        }elseif($_post_title === 'custom'){
            
            $args['post_title'] = acfe_form_map_field_value($_post_title_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post name
        if(!empty($map['post_name'])){
            
            $args['post_name'] = acfe_form_map_field_value($map['post_name'], $_POST['acf'], $_post_id);
            
        }elseif($_post_name === 'generated_id'){
            
            $args['post_name'] = $_post_id;
            
        }elseif($_post_name === 'custom'){
            
            $args['post_name'] = acfe_form_map_field_value($_post_name_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post content
        if(!empty($map['post_content'])){
            
            $args['post_content'] = acfe_form_map_field_value($map['post_content'], $_POST['acf'], $_post_id);
            
        }elseif($_post_content === 'custom'){
            
            $args['post_content'] = acfe_form_map_field_value($_post_content_custom, $_POST['acf'], $_post_id);
            
        }
        
        // Post author
        if(!empty($map['post_author'])){
            
            $args['post_author'] = acfe_form_map_field_value($map['post_author'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_author)){
            
            // Custom user ID
            $args['post_author'] = $_post_author;
            
            // Current User
            if($_post_author === 'current_user'){
                
                $args['post_author'] = get_current_user_id();
            
            // Current Post Author
            }elseif($_post_author === 'current_post_author'){
                
                if($post_info['type'] === 'post')
                    $args['post_author'] = get_post_field('post_author', $post_info['id']);
                
            }
            
        }
        
        // Post parent
        if(!empty($map['post_parent'])){
            
            $args['post_parent'] = acfe_form_map_field_value($map['post_parent'], $_POST['acf'], $_post_id);
            
        }elseif(!empty($_post_parent)){
            
            // Custom Post ID
            $args['post_parent'] = $_post_parent;
            
            // Current Post
            if($_post_parent === 'current_post'){
                
                if($post_info['type'] === 'post')
                    $args['post_parent'] = $post_info['id'];
                
            }
            
        }
        
        $terms = array();
        
        // Post terms
        if(!empty($map['post_terms'])){
            
            $terms = acf_array(acfe_form_map_field_value($map['post_terms'], $_POST['acf'], $_post_id));
            
        }elseif(!empty($_post_terms)){
            
            $terms = acf_array($_post_terms);
            
        }
        
        // Tax input
        if(!empty($terms)){
            
            foreach($terms as $term){
                
                $args['tax_input'][$term->taxonomy][] = $term->term_id;
                
            }
            
        }
        
        $args = apply_filters('acfe/form/submit/post_args',                     $args, $post_action, $form, $action);
        $args = apply_filters('acfe/form/submit/post_args/form=' . $form_name,  $args, $post_action, $form, $action);
        
        if(!empty($action))
            $args = apply_filters('acfe/form/submit/post_args/action=' . $action, $args, $post_action, $form, $action);
        
        if($args === false)
            return;
        
        // Update Post
        $_post_id = wp_update_post($args);
        
        // Save meta
        do_action('acfe/form/submit/post',                     $_post_id, $post_action, $args, $form, $action);
        do_action('acfe/form/submit/post/form=' . $form_name,  $_post_id, $post_action, $args, $form, $action);
        
        if(!empty($action))
            $args = do_action('acfe/form/submit/post/action=' . $action, $_post_id, $post_action, $args, $form, $action);
        
    }
    
    function submit($_post_id, $post_action, $args, $form, $action){
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_post_save_meta');
        
        if(!empty($save_meta)){
            
            $data = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($data)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post($_post_id, $data);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
    function advanced_load($field){
        
        $form_id = 100;
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value')){
            
            $form_id = $field['value'];
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_filter('acfe/form/load/post_id', 'my_form_post_values_source', 10, 3);
add_filter('acfe/form/load/post_id/form=<?php echo $form_name; ?>', 'my_form_post_values_source', 10, 3);
add_filter('acfe/form/load/post_id/action=my-post-action', 'my_form_post_values_source', 10, 3);
</pre>
<br />
<pre>
add_filter('acfe/form/load/post_id/form=<?php echo $form_name; ?>', 'my_form_post_values_source', 10, 3);
function my_form_post_values_source($post_id, $form, $action){
    
    /**
     * @int     $post_id    Post ID used as source
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
    
    /**
     * Force to load values from the post ID 145
     */
    $post_id = 145;
    
    
    /**
     * Return
     */
    return $post_id;
    
}
</pre><?php
        
    }
    
    function advanced_save_args($field){
        
        $form_id = 100;
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value')){
            
            $form_id = $field['value'];
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_filter('acfe/form/submit/post_args', 'my_form_post_args', 10, 4);
add_filter('acfe/form/submit/post_args/form=<?php echo $form_name; ?>', 'my_form_post_args', 10, 4);
add_filter('acfe/form/submit/post_args/action=my-post-action', 'my_form_post_args', 10, 4);
</pre>
<br />
<pre>
add_filter('acfe/form/submit/post_args/form=<?php echo $form_name; ?>', 'my_form_post_args', 10, 4);
function my_form_post_args($args, $type, $form, $action){
    
    /**
     * @array   $args   The generated post arguments
     * @string  $type   Action type: 'insert_post' or 'update_post'
     * @array   $form   The form settings
     * @string  $action The action alias name
     */
    
    
    /**
     * Force specific post title if the action type is 'insert_post'
     */
    if($type === 'insert_post'){
        
        $args['post_title'] = 'My title';
        
    }
    
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $my_post_field = get_field('my_field', 145);
    
    
    /**
     * Return arguments
     * Note: Return false will stop post & meta insert/update
     */
    return $args;
    
}
</pre><?php
        
    }
    
    function advanced_save($field){
        
        $form_id = 100;
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value')){
            
            $form_id = $field['value'];
            $form_name = get_field('acfe_form_name', $form_id);
            
        }
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_action('acfe/form/submit/post', 'my_form_post_save', 10, 5);
add_action('acfe/form/submit/post/form=<?php echo $form_name; ?>', 'my_form_post_save', 10, 5);
add_action('acfe/form/submit/post/action=my-post-action', 'my_form_post_save', 10, 5);
</pre>
<br />
<pre>
/**
 * At this point the post & meta fields are already saved in the database
 */
add_action('acfe/form/submit/post/form=<?php echo $form_name; ?>', 'my_form_post_save', 10, 5);
function my_form_post_save($post_id, $type, $args, $form, $action){
    
    /**
     * @int     $post_id    The targeted post ID
     * @string  $type       Action type: 'insert_post' or 'update_post'
     * @array   $args       The generated post arguments
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
    
    /**
     * Get the value from the form input named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the currently saved post
     */
    $my_post_field = get_field('my_field', $post_id);
    
}
</pre><?php
        
    }
    
}

new acfe_form_post();

endif;