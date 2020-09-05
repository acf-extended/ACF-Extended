<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_post_object')):

class acfe_field_post_object{
    
    function __construct(){
        
        // Actions
        add_action('acf/render_field_settings/type=post_object',        array($this, 'field_settings'));
        
        // Filters
        add_filter('acfe/field_wrapper_attributes/type=post_object',    array($this, 'field_wrapper'), 10, 2);
        add_filter('acf/update_value/type=post_object',                 array($this, 'update_value'), 5, 3);
        
    }
    
    function field_settings($field){
    
        // save custom value
        acf_render_field_setting($field, array(
            'label'			=> __('Allow & Save Custom value','acf'),
            'instructions'	=> '',
            'name'			=> 'save_custom',
            'type'			=> 'true_false',
            'ui'			=> 1,
            'message'		=> __("Save 'custom' values as new post", 'acf'),
        ));
    
        // save post_type
        acf_render_field_setting($field, array(
            'label'			=> __('New Post Arguments','acf'),
            'instructions'	=> '',
            'name'			=> 'save_post_type',
            'type'			=> 'acfe_post_types',
            'field_type'    => 'select',
            'conditional_logic'	=> array(
                'field'		=> 'save_custom',
                'operator'	=> '==',
                'value'		=> 1
            )
        ));
    
        // save post_status
        acf_render_field_setting($field, array(
            'label'			=> '',
            'instructions'	=> '',
            'name'			=> 'save_post_status',
            'type'			=> 'acfe_post_statuses',
            'field_type'    => 'select',
            'conditional_logic'	=> array(
                'field'		=> 'save_custom',
                'operator'	=> '==',
                'value'		=> 1
            ),
            '_append'       => 'save_post_type'
        ));
    
        ob_start();
        ?>
        You can change the New Post creation arguments using the following hook:<br /><br />
        <pre>
add_filter('acfe/fields/post_object/custom_save_args/name=my_post_object', 'my_acf_post_object_new_post_args', 10, 4);
function my_acf_post_object_new_post_args($args, $title, $post_id, $field){
    
    /**
     * @array       $args       New Post arguments
     * @string      $title      Post title
     * @bool/string $post_id    Current Post ID
     * @array       $field      Field array
     */
    
    // Add custom Post Content
    // See wp_insert_post(): https://developer.wordpress.org/reference/functions/wp_insert_post/
    $args['post_content'] = 'My post content';
    
    // Return false to stop post creation
    // return false;
    
    // Return
    return $args;

}
</pre>
        <br />
        You can trigger a custom action after the New Post creation using the following hook:<br /><br />
        <pre>
add_action('acfe/fields/post_object/custom_save/name=my_post_object', 'my_acf_post_object_new_post_action', 10, 4);
function my_acf_post_object_new_post_action($new_post_id, $title, $post_id, $field){
    
    /**
     * @bool        $new_post_id    Newly created Post ID
     * @string      $title          Post title
     * @bool/string $post_id        Current Post ID
     * @array       $field          Field array
     */
     
    // Do something...
    // wp_mail();

}
</pre>
        <?php
    
        $message = ob_get_clean();
    
        // ajax instructions
        acf_render_field_setting($field, array(
            'label'			=> __('New Post Instructions','acf'),
            'instructions'	=> '',
            'type'			=> 'message',
            'name'			=> 'instructions',
            'message'       => $message,
            'new_lines'     => false,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'save_custom',
                        'operator'  => '==',
                        'value'     => 1,
                    )
                )
            )
        ));
        
    }
    
    function field_wrapper($wrapper, $field){
    
        if(acf_maybe_get($field, 'save_custom')){
        
            $wrapper['data-acfe-allow-custom'] = 1;
        
        }
    
        return $wrapper;
        
    }
    
    function update_value($value, $post_id, $field){
    
        // Save custom value
        if(empty($value) || !acf_maybe_get($field, 'save_custom'))
            return $value;
    
        // New Post Args
        $post_type = acf_maybe_get($field, 'save_post_type', 'post');
        $post_status = acf_maybe_get($field, 'save_post_status', 'publish');
    
        $is_array = is_array($value) ? true : false;
    
        $value = acf_array($value);
    
        foreach($value as $k => $v){
        
            if(is_numeric($v))
                continue;
        
            $title = $v;
        
            // Create new post
            $args = array(
                'post_title'    => $title,
                'post_type'     => $post_type,
                'post_status'   => $post_status,
            );
        
            // Allow filters
            $args = apply_filters('acfe/fields/post_object/custom_save_args',                           $args, $title, $post_id, $field);
            $args = apply_filters('acfe/fields/post_object/custom_save_args/name=' . $field['name'],    $args, $title, $post_id, $field);
            $args = apply_filters('acfe/fields/post_object/custom_save_args/key=' . $field['key'],      $args, $title, $post_id, $field);
        
            if($args === false){
            
                unset($value[$k]);
                continue;
            
            }
        
            // Insert post
            $_post_id = wp_insert_post($args);
        
            if(empty($_post_id) || is_wp_error($_post_id)){
            
                unset($value[$k]);
                continue;
            
            }
        
            // Allow actions after insert
            do_action('acfe/fields/post_object/custom_save',                           $_post_id, $title, $post_id, $field);
            do_action('acfe/fields/post_object/custom_save/name=' . $field['name'],    $_post_id, $title, $post_id, $field);
            do_action('acfe/fields/post_object/custom_save/key=' . $field['key'],      $_post_id, $title, $post_id, $field);
        
            $value[$k] = $_post_id;
        
        }
    
        if(!$is_array && is_array($value))
            reset($value);
    
        return $value;
        
    }
    
}

new acfe_field_post_object();

endif;